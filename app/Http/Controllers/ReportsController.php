<?php

namespace App\Http\Controllers;

use App\Models\PosSale;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportsController extends Controller
{
    private const TYPES = ['daily', 'weekly', 'monthly', 'cashiers', 'products', 'payments', 'terminals'];

    public function index(Request $request, ?string $type = null): View
    {
        $type = $type ?? 'daily';
        abort_unless(in_array($type, self::TYPES, true), 404);
        $dates = $this->validatedDates($request);
        $rows = $this->reportRows($type, $dates['from'], $dates['to']);

        return view('reports.index', [
            'type' => $type,
            'rows' => $rows,
            'from' => $dates['from'],
            'to' => $dates['to'],
            'types' => self::TYPES,
        ]);
    }

    public function export(Request $request, string $type): StreamedResponse
    {
        abort_unless(in_array($type, self::TYPES, true), 404);
        $dates = $this->validatedDates($request);
        $rows = $this->reportRows($type, $dates['from'], $dates['to']);
        $headers = match ($type) {
            'daily', 'weekly', 'monthly' => ['Period', 'Transactions', 'Sales', 'Received', 'Change'],
            'cashiers' => ['Cashier', 'Cashier ID', 'Transactions', 'Sales', 'Average sale'],
            'products' => ['Product', 'Product ID', 'Units sold', 'Sales'],
            'payments' => ['Payment method', 'Transactions', 'Sales'],
            'terminals' => ['Terminal', 'Machine ID', 'Transactions', 'Sales'],
        };

        return response()->streamDownload(function () use ($rows, $headers): void {
            $output = fopen('php://output', 'w');
            fputcsv($output, $headers);
            foreach ($rows as $row) {
                fputcsv($output, array_map(fn ($value) => $this->safeCsv((string) $value), array_values((array) $row)));
            }
            fclose($output);
        }, "kikimo-report-{$type}-".now(config('app.business_timezone'))->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function reportRows(string $type, string $from, string $to)
    {
        $sales = $this->salesBetween($from, $to);

        return match ($type) {
            'daily' => $this->periodRows($sales, 'daily'),
            'weekly' => $this->periodRows($sales, 'weekly'),
            'monthly' => $this->periodRows($sales, 'monthly'),
            'cashiers' => (clone $sales)
                ->whereNotNull('cashier_name')
                ->selectRaw('cashier_name, cashier_id, COUNT(*) AS transactions, SUM(total) AS sales, AVG(total) AS average_sale')
                ->groupBy('cashier_name', 'cashier_id')
                ->orderByDesc('sales')
                ->get(),
            'products' => DB::table('pos_sale_items')
                ->join('pos_sales', 'pos_sales.id', '=', 'pos_sale_items.pos_sale_id')
                ->where('pos_sales.business_id', Auth::user()->business_id)
                ->when(! Auth::user()->isAdministrator(), fn ($query) => $query->where('pos_sales.terminal_id', Auth::user()->pos_terminal_id))
                ->whereBetween('pos_sales.sold_at', $this->utcDateRange($from, $to))
                ->selectRaw('pos_sale_items.product_name, pos_sale_items.product_id, SUM(pos_sale_items.quantity) AS units_sold, SUM(pos_sale_items.line_total) AS sales')
                ->groupBy('pos_sale_items.product_name', 'pos_sale_items.product_id')
                ->orderByDesc('units_sold')
                ->get(),
            'payments' => (clone $sales)
                ->selectRaw("COALESCE(payment_method, 'Other') AS payment_method, COUNT(*) AS transactions, SUM(total) AS sales")
                ->groupBy('payment_method')
                ->orderByDesc('sales')
                ->get(),
            'terminals' => (clone $sales)
                ->join('pos_terminals', 'pos_terminals.id', '=', 'pos_sales.terminal_id')
                ->selectRaw('pos_terminals.terminal_name, pos_terminals.machine_id, COUNT(*) AS transactions, SUM(pos_sales.total) AS sales')
                ->groupBy('pos_terminals.id', 'pos_terminals.terminal_name', 'pos_terminals.machine_id')
                ->orderByDesc('sales')
                ->get(),
        };
    }

    private function periodRows(Builder $sales, string $period)
    {
        $driver = DB::connection()->getDriverName();
        $offsetMinutes = (int) (Carbon::now(config('app.business_timezone'))->getOffset() / 60);
        $localSoldAt = $driver === 'sqlite'
            ? "datetime(sold_at, '{$offsetMinutes} minutes')"
            : "DATE_ADD(sold_at, INTERVAL {$offsetMinutes} MINUTE)";
        $expression = match ($period) {
            'daily' => "DATE({$localSoldAt})",
            'weekly' => $driver === 'sqlite'
                ? "strftime('%Y-%W', {$localSoldAt})"
                : "DATE_FORMAT({$localSoldAt}, '%x-W%v')",
            'monthly' => $driver === 'sqlite'
                ? "strftime('%Y-%m', {$localSoldAt})"
                : "DATE_FORMAT({$localSoldAt}, '%Y-%m')",
        };

        return $sales
            ->selectRaw("{$expression} AS period, COUNT(*) AS transactions, SUM(total) AS sales, SUM(amount_paid) AS received, SUM(change_due) AS change_given")
            ->groupByRaw($expression)
            ->orderBy('period')
            ->get();
    }

    private function salesBetween(string $from, string $to): Builder
    {
        return PosSale::query()
            ->where('business_id', Auth::user()->business_id)
            ->when(! Auth::user()->isAdministrator(), fn (Builder $query) => $query->where('terminal_id', Auth::user()->pos_terminal_id))
            ->whereBetween('sold_at', $this->utcDateRange($from, $to));
    }

    private function validatedDates(Request $request): array
    {
        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        return [
            'from' => $validated['from'] ?? Carbon::now(config('app.business_timezone'))->startOfMonth()->toDateString(),
            'to' => $validated['to'] ?? Carbon::now(config('app.business_timezone'))->toDateString(),
        ];
    }

    private function utcDateRange(string $from, string $to): array
    {
        return [
            Carbon::parse($from, config('app.business_timezone'))->startOfDay()->utc(),
            Carbon::parse($to, config('app.business_timezone'))->endOfDay()->utc(),
        ];
    }

    private function safeCsv(string $value): string
    {
        return preg_match('/^[=+\-@\t\r]/', $value) ? "'".$value : $value;
    }
}
