<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Receipt {{ $sale->receipt_no ?: '#'.$sale->desktop_sale_id }}</title>
    <script>
        const receiptThemes = ['green', 'blue', 'violet', 'amber', 'rose', 'teal', 'indigo', 'red', 'slate', 'cyan'];
        const savedReceiptTheme = localStorage.getItem('kikimo-color-theme');
        document.documentElement.dataset.colorTheme = receiptThemes.includes(savedReceiptTheme) ? savedReceiptTheme : 'green';
    </script>
    <style>
        * { box-sizing: border-box; }
        :root {
            --receipt-primary: #01553d;
            --receipt-mid: #046a4c;
            --receipt-dark: #0a2a1d;
            --receipt-secondary: #79c143;
            --receipt-soft: #ecfdf5;
        }
        html[data-color-theme="blue"] { --receipt-primary: #2563eb; --receipt-mid: #1d4f73; --receipt-dark: #102a43; --receipt-secondary: #60a5fa; --receipt-soft: #eff6ff; }
        html[data-color-theme="violet"] { --receipt-primary: #7c3aed; --receipt-mid: #4c1d95; --receipt-dark: #241044; --receipt-secondary: #c4b5fd; --receipt-soft: #f5f3ff; }
        html[data-color-theme="amber"] { --receipt-primary: #b45309; --receipt-mid: #854d0e; --receipt-dark: #3a1f0c; --receipt-secondary: #fbbf24; --receipt-soft: #fffbeb; }
        html[data-color-theme="rose"] { --receipt-primary: #e11d48; --receipt-mid: #9f1239; --receipt-dark: #4c0519; --receipt-secondary: #fb7185; --receipt-soft: #fff1f2; }
        html[data-color-theme="teal"] { --receipt-primary: #0f766e; --receipt-mid: #115e59; --receipt-dark: #042f2e; --receipt-secondary: #2dd4bf; --receipt-soft: #f0fdfa; }
        html[data-color-theme="indigo"] { --receipt-primary: #4f46e5; --receipt-mid: #3730a3; --receipt-dark: #1e1b4b; --receipt-secondary: #818cf8; --receipt-soft: #eef2ff; }
        html[data-color-theme="red"] { --receipt-primary: #dc2626; --receipt-mid: #991b1b; --receipt-dark: #450a0a; --receipt-secondary: #f87171; --receipt-soft: #fef2f2; }
        html[data-color-theme="slate"] { --receipt-primary: #475569; --receipt-mid: #334155; --receipt-dark: #0f172a; --receipt-secondary: #94a3b8; --receipt-soft: #f8fafc; }
        html[data-color-theme="cyan"] { --receipt-primary: #0891b2; --receipt-mid: #0e7490; --receipt-dark: #083344; --receipt-secondary: #22d3ee; --receipt-soft: #ecfeff; }
        body {
            margin: 0;
            padding: 16px 12px 32px;
            background: var(--receipt-soft);
            color: #26332c;
            font: 13px/1.5 "Courier New", monospace;
        }
        .receipt-scene {
            width: min(100%, 456px);
            margin: 0 auto;
        }
        .printer {
            position: relative;
            z-index: 2;
            width: 86%;
            height: 72px;
            margin: 0 auto;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding-top: 25px;
            border: 1px solid var(--receipt-secondary);
            border-radius: 15px 15px 8px 8px;
            background: linear-gradient(180deg, var(--receipt-mid), var(--receipt-dark));
            box-shadow: 0 10px 20px rgb(15 23 42 / 12%);
            color: #f7e9eb;
            font: 700 12px/1 Arial, sans-serif;
            letter-spacing: .2em;
        }
        .printer::before {
            content: "";
            position: absolute;
            top: 16px;
            right: 20px;
            width: 9px;
            height: 9px;
            border-radius: 50%;
            background: var(--receipt-secondary);
            box-shadow: 0 0 10px var(--receipt-secondary);
        }
        .printer::after {
            content: "";
            position: absolute;
            right: 6%;
            bottom: -3px;
            left: 6%;
            height: 12px;
            border-radius: 4px;
            background: var(--receipt-dark);
            box-shadow: inset 0 2px 3px rgb(0 0 0 / 35%);
        }
        .receipt {
            position: relative;
            z-index: 1;
            width: 100%;
            margin: -1px auto 0;
            padding: 40px 36px 32px;
            border-top: 4px solid var(--receipt-primary);
            background: #fff;
            box-shadow: 0 8px 24px rgb(15 23 42 / 7%);
            transform-origin: top center;
            animation: receipt-feed 1.15s cubic-bezier(.2, .75, .3, 1) both;
        }
        @keyframes receipt-feed {
            from { opacity: .6; transform: translateY(-24px) scaleY(.94); }
            to { opacity: 1; transform: translateY(0) scaleY(1); }
        }
        .center { text-align: center; }
        .muted { color: #718078; }
        .store-name {
            margin: 0;
            color: var(--receipt-primary);
            font: 700 25px/1.2 Arial, sans-serif;
            letter-spacing: .04em;
            overflow-wrap: anywhere;
        }
        .store-details { margin-top: 7px; overflow-wrap: anywhere; }
        .heading { margin: 5px 0 0; color: var(--receipt-primary); font-size: 12px; font-weight: 400; letter-spacing: .2em; }
        .rule { margin: 20px 0; border: 0; border-top: 1px dashed #cbd6d0; }
        .metadata, .totals { display: grid; gap: 4px; }
        .metadata p { display: flex; justify-content: space-between; gap: 10px; margin: 0; }
        .metadata p span:last-child { text-align: right; overflow-wrap: anywhere; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th, td { padding: 6px 2px; text-align: right; vertical-align: top; overflow-wrap: anywhere; }
        th { border-bottom: 1px dashed #89958e; color: #718078; font-size: 11px; font-weight: 400; text-transform: uppercase; }
        th:first-child, td:first-child { width: 58%; text-align: left; }
        th:nth-child(2), td:nth-child(2) { width: 14%; }
        th:last-child, td:last-child { width: 28%; }
        tbody tr:first-child td { padding-top: 10px; }
        .item-detail { display: block; color: #718078; font-size: 12px; }
        .totals p { display: flex; justify-content: space-between; gap: 10px; margin: 0; }
        .totals p span:last-child { text-align: right; }
        .grand-total { margin-top: 5px !important; padding-top: 9px; border-top: 1px solid #e0e7e2; font: 700 22px/1.3 Arial, sans-serif; }
        .grand-total span:first-child { color: #17211b; font: 700 13px/1.5 "Courier New", monospace; }
        .grand-total span:last-child { color: var(--receipt-primary); }
        .thanks { margin: 20px 0 0; color: var(--receipt-primary); font-size: 12px; font-weight: 700; text-transform: uppercase; }
        .footer-note { margin: 8px 0 0; color: #718078; }
        .powered-by { margin: 0; color: #718078; }
        .receipt-actions { display: flex; gap: 8px; margin-top: 18px; }
        .receipt-actions a, .receipt-actions button {
            flex: 1;
            min-height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--receipt-primary);
            border-radius: 6px;
            padding: 8px;
            background: var(--receipt-primary);
            color: #fff;
            font: inherit;
            font-weight: 700;
            text-align: center;
            text-decoration: none;
            cursor: pointer;
        }
        .receipt-actions a { background: #fff; color: var(--receipt-primary); }
        @page { margin: 3mm; }
        @media (prefers-reduced-motion: reduce) {
            .receipt { animation: none; }
        }
        @media print {
            body { padding: 0; background: #fff; color: #000; }
            .receipt-scene { width: 100%; max-width: 80mm; margin: 0; }
            .printer { display: none; }
            .receipt { width: 100%; margin: 0; padding: 0; box-shadow: none; animation: none; }
            .muted { color: #333; }
            .receipt-actions { display: none !important; }
        }
        @media (max-width: 360px) {
            .receipt { padding: 32px 18px 24px; }
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
    <div class="receipt-scene">
        <div class="printer" aria-hidden="true">RECEIPT</div>
        <main class="receipt">
            <header class="center">
                <h1 class="store-name">{{ $sale->business->name }}</h1>
                @if($sale->business->email || $sale->business->phone || $sale->business->address)
                    <div class="store-details muted">
                        @if($sale->business->email)<div>{{ $sale->business->email }}</div>@endif
                        @if($sale->business->phone)<div>{{ $sale->business->phone }}</div>@endif
                        @if($sale->business->address)<div>{{ $sale->business->address }}</div>@endif
                    </div>
                @endif
                <h2 class="heading">SALES RECEIPT</h2>
            </header>

            <hr class="rule">
            <div class="metadata">
                <p><span>Receipt No.</span><span>{{ $sale->receipt_no ?: '#'.$sale->desktop_sale_id }}</span></p>
                <p><span>Date</span><span>{{ $sale->sold_at?->setTimezone(config('app.business_timezone'))->format('d/m/Y, H:i:s') ?: 'Date unavailable' }}</span></p>
                <p><span>Served by</span><span>{{ $sale->cashier_name ?: '—' }}</span></p>
            </div>

            <hr class="rule">
            <table aria-label="Purchased items">
                <thead>
                    <tr><th>Item</th><th>Qty</th><th>Amount</th></tr>
                </thead>
                <tbody>
                    @forelse($sale->items as $item)
                        <tr>
                            <td>
                                {{ $item->product_name }}
                                <span class="item-detail">KES {{ number_format((float) $item->unit_price, 2) }} x {{ rtrim(rtrim(number_format((float) $item->quantity, 3, '.', ''), '0'), '.') }}</span>
                            </td>
                            <td>{{ rtrim(rtrim(number_format((float) $item->quantity, 3, '.', ''), '0'), '.') }}</td>
                            <td>KES {{ number_format((float) $item->line_total, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="muted">No item details available.</td></tr>
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
                <p><span>Payment method</span><span>{{ $sale->payment_method ?: 'Not specified' }}</span></p>
                <p><span>Amount paid</span><span>KES {{ number_format((float) $sale->amount_paid, 2) }}</span></p>
                <p><span>Change</span><span>KES {{ number_format((float) $sale->change_due, 2) }}</span></p>
            </div>

            <hr class="rule">
            <p class="center thanks">Thank you for shopping with us</p>
            <p class="center footer-note">Thank you for your purchase.</p>
            <hr class="rule">
            <p class="center powered-by">Powered by <strong>Kikimo POS</strong></p>

            <div class="receipt-actions">
                <a href="{{ route('sales.show', $sale) }}" onclick="if (history.length > 1) { history.back(); return false; }">Close receipt</a>
                <button type="button" onclick="window.print()">Print receipt</button>
            </div>
        </main>
    </div>
</body>
</html>
