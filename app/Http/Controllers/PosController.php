<?php

namespace App\Http\Controllers;

use App\Models\DesktopPosSyncLog;
use App\Models\PosSale;
use App\Models\PosTerminal;
use App\Models\Product;
use App\Services\SyncProcessor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PosController extends Controller
{
    public function sales(Request $request): View
    {
        $this->validateSalesFilters($request);
        $sales = $this->filteredSales($request)->with('terminal')->latest('sold_at')->paginate(15)->withQueryString();

        return view('sales.index', compact('sales'));
    }

    public function showSale(PosSale $sale): View
    {
        $sale = $this->businessSales()->with(['items', 'terminal'])->findOrFail($sale->id);

        return view('sales.show', compact('sale'));
    }

    public function receipt(PosSale $sale): View
    {
        $sale = $this->businessSales()->with(['items', 'terminal', 'business'])->findOrFail($sale->id);

        return view('sales.receipt', compact('sale'));
    }

    public function exportSales(Request $request): StreamedResponse
    {
        $this->validateSalesFilters($request);
        $sales = $this->filteredSales($request)->with('terminal')->orderByDesc('sold_at')->get();

        return response()->streamDownload(function () use ($sales): void {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Receipt', 'Date', 'Cashier', 'Payment method', 'Subtotal', 'Total', 'Received', 'Change', 'Terminal']);

            foreach ($sales as $sale) {
                fputcsv($output, [
                    $this->safeCsv($sale->receipt_no),
                    $sale->sold_at?->toDateTimeString(),
                    $this->safeCsv($sale->cashier_name),
                    $this->safeCsv($sale->payment_method),
                    $sale->subtotal,
                    $sale->total,
                    $sale->amount_paid,
                    $sale->change_due,
                    $this->safeCsv($sale->terminal?->terminal_name),
                ]);
            }

            fclose($output);
        }, 'kikimo-sales-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function products(Request $request): View
    {
        $request->validate(['q' => ['nullable', 'string', 'max:150']]);
        $products = Product::query()
            ->where('business_id', Auth::user()->business_id)
            ->when(! Auth::user()->isAdministrator(), fn (Builder $query) => $query->where('terminal_id', Auth::user()->pos_terminal_id))
            ->when($request->filled('q'), function (Builder $query) use ($request): void {
                $term = '%'.$request->string('q')->toString().'%';
                $query->where(function (Builder $search) use ($term): void {
                    $search->where('name', 'like', $term)->orWhere('sku', 'like', $term)->orWhere('barcode', 'like', $term);
                });
            })
            ->with('terminal')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('products.index', compact('products'));
    }

    public function cashiers(Request $request): View
    {
        $request->validate(['q' => ['nullable', 'string', 'max:150']]);
        $cashiers = $this->businessSales()
            ->whereNotNull('cashier_name')
            ->when($request->filled('q'), function (Builder $query) use ($request): void {
                $term = '%'.$request->string('q')->toString().'%';
                $query->where(function (Builder $search) use ($term): void {
                    $search->where('cashier_name', 'like', $term)->orWhere('cashier_id', 'like', $term);
                });
            })
            ->selectRaw('cashier_id, MAX(cashier_name) AS cashier_name, COUNT(*) AS transactions, SUM(total) AS total_sales, AVG(total) AS average_sale')
            ->groupBy('cashier_id', 'cashier_name')
            ->orderByDesc('total_sales')
            ->paginate(15)
            ->withQueryString();

        return view('cashiers.index', compact('cashiers'));
    }

    public function terminals(): View
    {
        $terminals = PosTerminal::query()
            ->where('business_id', Auth::user()->business_id)
            ->when(! Auth::user()->isAdministrator(), fn (Builder $query) => $query->whereKey(Auth::user()->pos_terminal_id))
            ->withCount('sales')
            ->latest()
            ->paginate(15);

        return view('terminals.index', compact('terminals'));
    }

    public function terminalSales(PosTerminal $terminal, Request $request): View
    {
        abort_unless($terminal->business_id === Auth::user()->business_id, 404);
        abort_unless(Auth::user()->isAdministrator() || $terminal->id === Auth::user()->pos_terminal_id, 404);
        $this->validateSalesFilters($request);
        $sales = $this->filteredSales($request)->where('terminal_id', $terminal->id)->with('terminal')->latest('sold_at')->paginate(15)->withQueryString();

        return view('sales.index', ['sales' => $sales, 'terminal' => $terminal]);
    }

    public function syncHistory(Request $request): View
    {
        $request->validate([
            'status' => ['nullable', 'in:pending,processing,success,failed'],
            'entity_type' => ['nullable', 'string', 'max:50'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);
        $terminals = PosTerminal::query()
            ->where('business_id', Auth::user()->business_id)
            ->when(! Auth::user()->isAdministrator(), fn (Builder $query) => $query->whereKey(Auth::user()->pos_terminal_id));
        $machineIds = (clone $terminals)->pluck('machine_id');
        $syncLogs = DesktopPosSyncLog::query()
            ->whereIn('machine_id', $machineIds)
            ->when($request->filled('status'), fn (Builder $query) => $query->where('status', $request->string('status')->toString()))
            ->when($request->filled('entity_type'), fn (Builder $query) => $query->where('entity_type', $request->string('entity_type')->toString()))
            ->when($request->filled('from'), fn (Builder $query) => $query->where('received_at', '>=', $request->date('from')->startOfDay()))
            ->when($request->filled('to'), fn (Builder $query) => $query->where('received_at', '<=', $request->date('to')->endOfDay()))
            ->latest('received_at')
            ->paginate(20)
            ->withQueryString();

        return view('sync.index', compact('syncLogs'));
    }

    public function showSync(DesktopPosSyncLog $syncLog): View
    {
        $this->authorizeSyncLog($syncLog);

        return view('sync.show', compact('syncLog'));
    }

    public function retrySync(DesktopPosSyncLog $syncLog, SyncProcessor $processor)
    {
        $this->authorizeSyncLog($syncLog);
        $syncLog = $processor->process($syncLog);

        return redirect()->route('sync.show', $syncLog)->with(
            $syncLog->status === 'success' ? 'success' : 'error',
            $syncLog->status === 'success' ? 'Synchronization processed successfully.' : 'Import failed: '.$syncLog->error_message,
        );
    }

    private function businessSales(): Builder
    {
        return PosSale::query()
            ->where('business_id', Auth::user()->business_id)
            ->when(! Auth::user()->isAdministrator(), fn (Builder $query) => $query->where('terminal_id', Auth::user()->pos_terminal_id));
    }

    private function filteredSales(Request $request): Builder
    {
        return $this->businessSales()
            ->when($request->filled('q'), function (Builder $query) use ($request): void {
                $term = '%'.$request->string('q')->toString().'%';
                $query->where(function (Builder $search) use ($term): void {
                    $search->where('receipt_no', 'like', $term)->orWhere('cashier_name', 'like', $term);
                });
            })
            ->when($request->filled('payment_method'), fn (Builder $query) => $query->where('payment_method', $request->string('payment_method')->toString()))
            ->when($request->filled('from'), fn (Builder $query) => $query->whereDate('sold_at', '>=', $request->date('from')))
            ->when($request->filled('to'), fn (Builder $query) => $query->whereDate('sold_at', '<=', $request->date('to')));
    }

    private function authorizeSyncLog(DesktopPosSyncLog $syncLog): void
    {
        abort_unless(
            PosTerminal::query()
                ->where('business_id', Auth::user()->business_id)
                ->when(! Auth::user()->isAdministrator(), fn (Builder $query) => $query->whereKey(Auth::user()->pos_terminal_id))
                ->where('machine_id', $syncLog->machine_id)
                ->exists(),
            404,
        );
    }

    private function safeCsv(?string $value): string
    {
        return Str::startsWith($value ?? '', ['=', '+', '-', '@', "\t", "\r"]) ? "'".$value : (string) $value;
    }

    private function validateSalesFilters(Request $request): void
    {
        $request->validate([
            'q' => ['nullable', 'string', 'max:150'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'terminal_id' => ['nullable', 'integer', 'min:1'],
        ]);

        if ($request->filled('terminal_id')) {
            $terminal = PosTerminal::query()
                ->where('business_id', Auth::user()->business_id)
                ->findOrFail($request->integer('terminal_id'));
            $request->merge(['terminal_id' => $terminal->id]);
        }
    }
}
