<?php

namespace App\Http\Controllers;

use App\Models\DesktopPosSyncLog;
use App\Models\PosSale;
use App\Models\PosSaleItem;
use App\Models\PosTerminal;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $businessId = Auth::user()->business_id;
        $terminalId = Auth::user()->isAdministrator() ? null : Auth::user()->pos_terminal_id;
        $today = Carbon::now(config('app.business_timezone'))->startOfDay();
        $todayStartUtc = $today->copy()->utc();
        $todayEndUtc = $today->copy()->endOfDay()->utc();
        $sales = PosSale::query()
            ->where('business_id', $businessId)
            ->when($terminalId, fn ($query) => $query->where('terminal_id', $terminalId));
        $todaySales = (clone $sales)->whereBetween('sold_at', [$todayStartUtc, $todayEndUtc]);
        $terminals = PosTerminal::query()
            ->where('business_id', $businessId)
            ->when($terminalId, fn ($query) => $query->whereKey($terminalId));
        $machineIds = (clone $terminals)->pluck('machine_id');

        $daily = collect(range(6, 0))->map(function (int $days) use ($sales, $today): array {
            $date = $today->copy()->subDays($days);
            $dateStartUtc = $date->copy()->utc();
            $dateEndUtc = $date->copy()->endOfDay()->utc();

            return [
                'label' => $date->format('D'),
                'date' => $date->toDateString(),
                'total' => (float) (clone $sales)->whereBetween('sold_at', [$dateStartUtc, $dateEndUtc])->sum('total'),
            ];
        });

        $paymentMethods = (clone $todaySales)
            ->selectRaw('COALESCE(payment_method, ?) AS method, SUM(total) AS amount', ['Other'])
            ->groupBy('payment_method')
            ->orderByDesc('amount')
            ->get();

        $topProducts = PosSaleItem::query()
            ->join('pos_sales', 'pos_sales.id', '=', 'pos_sale_items.pos_sale_id')
            ->where('pos_sales.business_id', $businessId)
            ->when($terminalId, fn ($query) => $query->where('pos_sales.terminal_id', $terminalId))
            ->whereBetween('pos_sales.sold_at', [$todayStartUtc, $todayEndUtc])
            ->selectRaw('pos_sale_items.product_name, SUM(pos_sale_items.quantity) AS units, SUM(pos_sale_items.line_total) AS amount')
            ->groupBy('pos_sale_items.product_name')
            ->orderByDesc('units')
            ->limit(5)
            ->get();

        $topCashiers = (clone $todaySales)
            ->selectRaw('cashier_name, COUNT(*) AS transactions, SUM(total) AS amount')
            ->whereNotNull('cashier_name')
            ->groupBy('cashier_name')
            ->orderByDesc('amount')
            ->limit(5)
            ->get();

        return view('dashboard', [
            'todayTransactions' => (clone $todaySales)->count(),
            'todaySales' => (float) (clone $todaySales)->sum('total'),
            'todayReceived' => (float) (clone $todaySales)->sum('amount_paid'),
            'todayChange' => (float) (clone $todaySales)->sum('change_due'),
            'monthlySales' => (clone $sales)->whereBetween('sold_at', [
                Carbon::now(config('app.business_timezone'))->startOfMonth()->utc(),
                Carbon::now(config('app.business_timezone'))->endOfMonth()->utc(),
            ])->sum('total'),
            'daily' => $daily,
            'dailyMaximum' => max(1, (float) $daily->max('total')),
            'paymentMethods' => $paymentMethods,
            'topProducts' => $topProducts,
            'topCashiers' => $topCashiers,
            'activeTerminals' => (clone $terminals)->where('is_active', true)->count(),
            'recentSales' => (clone $sales)->with('terminal')->latest('sold_at')->limit(8)->get(),
            'syncPending' => DesktopPosSyncLog::query()->whereIn('machine_id', $machineIds)->whereIn('status', ['pending', 'processing'])->count(),
            'syncFailed' => DesktopPosSyncLog::query()->whereIn('machine_id', $machineIds)->where('status', 'failed')->count(),
            'todayLabel' => Carbon::now(config('app.business_timezone'))->format('l, F j'),
        ]);
    }
}
