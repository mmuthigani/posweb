<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Receipt {{ $sale->receipt_no ?: '#'.$sale->desktop_sale_id }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 24px 12px;
            background: #f1f5f3;
            color: #17211b;
            font: 12px/1.45 Arial, sans-serif;
        }
        .receipt {
            width: min(100%, 80mm);
            margin: 0 auto;
            padding: 20px;
            background: #fff;
            box-shadow: 0 2px 12px rgb(15 23 42 / 10%);
        }
        .center { text-align: center; }
        .muted { color: #58645d; }
        .store-name { margin: 0; font-size: 18px; line-height: 1.2; overflow-wrap: anywhere; }
        .store-details { margin-top: 5px; }
        .heading { margin: 14px 0 10px; font-size: 15px; letter-spacing: .12em; }
        .rule { margin: 12px 0; border: 0; border-top: 1px dashed #7b857e; }
        .metadata, .totals { display: grid; gap: 4px; }
        .metadata p { display: flex; justify-content: space-between; gap: 10px; margin: 0; }
        .metadata p span:last-child { text-align: right; overflow-wrap: anywhere; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th, td { padding: 5px 2px; text-align: right; vertical-align: top; overflow-wrap: anywhere; }
        th { border-bottom: 1px solid #9aa39d; font-size: 10px; }
        th:first-child, td:first-child { width: 42%; text-align: left; }
        th:nth-child(2), td:nth-child(2) { width: 21%; }
        th:nth-child(3), td:nth-child(3) { width: 13%; }
        th:last-child, td:last-child { width: 24%; }
        tbody tr:first-child td { padding-top: 9px; }
        .totals p { display: flex; justify-content: space-between; gap: 10px; margin: 0; }
        .totals p span:last-child { text-align: right; }
        .grand-total { margin-top: 5px !important; padding-top: 7px; border-top: 1px solid #17211b; font-size: 16px; font-weight: 700; }
        .thanks { margin: 14px 0 0; font-weight: 600; }
        .receipt-actions { display: flex; gap: 8px; margin-top: 18px; }
        .receipt-actions a, .receipt-actions button {
            flex: 1;
            min-height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #14532d;
            border-radius: 6px;
            padding: 8px;
            background: #14532d;
            color: #fff;
            font: inherit;
            font-weight: 700;
            text-align: center;
            text-decoration: none;
            cursor: pointer;
        }
        .receipt-actions a { background: #fff; color: #14532d; }
        @page { margin: 3mm; }
        @media print {
            body { padding: 0; background: #fff; color: #000; }
            .receipt { width: 100%; max-width: 80mm; margin: 0; padding: 0; box-shadow: none; }
            .muted { color: #333; }
            .receipt-actions { display: none !important; }
        }
        @media (max-width: 360px) {
            .receipt { padding: 14px; }
            body { padding: 12px 6px; }
        }
    </style>
</head>
<body>
    @php
        $subtotal = (float) $sale->subtotal;
        $total = (float) $sale->total;
        $savings = max($subtotal - $total, 0);
    @endphp
    <main class="receipt">
        <header class="center">
            <h1 class="store-name">{{ $sale->business->name }}</h1>
            @if($sale->business->phone || $sale->business->address)
                <div class="store-details muted">
                    @if($sale->business->phone)<div>{{ $sale->business->phone }}</div>@endif
                    @if($sale->business->address)<div>{{ $sale->business->address }}</div>@endif
                </div>
            @endif
            <h2 class="heading">SALES RECEIPT</h2>
        </header>

        <hr class="rule">
        <div class="metadata">
            <p><span>Receipt</span><span>{{ $sale->receipt_no ?: '#'.$sale->desktop_sale_id }}</span></p>
            <p><span>Date</span><span>{{ $sale->sold_at?->format('d/m/Y H:i') ?: 'Date unavailable' }}</span></p>
            <p><span>Cashier</span><span>{{ $sale->cashier_name ?: '—' }}</span></p>
        </div>

        <hr class="rule">
        <table aria-label="Purchased items">
            <thead>
                <tr><th>Item</th><th>Unit price</th><th>Qty</th><th>Total</th></tr>
            </thead>
            <tbody>
                @forelse($sale->items as $item)
                    <tr>
                        <td>{{ $item->product_name }}</td>
                        <td>KES {{ number_format((float) $item->unit_price, 2) }}</td>
                        <td>{{ rtrim(rtrim(number_format((float) $item->quantity, 3, '.', ''), '0'), '.') }}</td>
                        <td>KES {{ number_format((float) $item->line_total, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="muted">No item details available.</td></tr>
                @endforelse
            </tbody>
        </table>

        <hr class="rule">
        <div class="totals">
            <p><span>Subtotal</span><span>KES {{ number_format($subtotal, 2) }}</span></p>
            @if($savings > 0)
                <p><span>Discount / offer savings</span><span>- KES {{ number_format($savings, 2) }}</span></p>
            @endif
            <p class="grand-total"><span>TOTAL</span><span>KES {{ number_format($total, 2) }}</span></p>
        </div>

        <hr class="rule">
        <div class="totals">
            <p><span>Payment</span><span>{{ $sale->payment_method ?: 'Not specified' }}</span></p>
            <p><span>Amount paid</span><span>KES {{ number_format((float) $sale->amount_paid, 2) }}</span></p>
            <p><span>Change due</span><span>KES {{ number_format((float) $sale->change_due, 2) }}</span></p>
        </div>

        <hr class="rule">
        <p class="center thanks">Thank you for shopping with us!</p>

        <div class="receipt-actions">
            <a href="{{ route('sales.show', $sale) }}" onclick="if (history.length > 1) { history.back(); return false; }">Close receipt</a>
            <button type="button" onclick="window.print()">Print receipt</button>
        </div>
    </main>
</body>
</html>
