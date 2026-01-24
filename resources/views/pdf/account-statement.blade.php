<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Account Statement - {{ $account->account_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #4F46E5;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #4F46E5;
            margin-bottom: 5px;
        }
        .tagline {
            color: #666;
            font-size: 10px;
        }
        .statement-title {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-top: 15px;
        }
        .statement-period {
            color: #666;
            font-size: 11px;
            margin-top: 5px;
        }
        .account-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 25px;
        }
        .account-info-grid {
            display: table;
            width: 100%;
        }
        .account-info-row {
            display: table-row;
        }
        .account-info-cell {
            display: table-cell;
            padding: 5px 10px;
            width: 50%;
        }
        .info-label {
            color: #666;
            font-size: 10px;
            text-transform: uppercase;
        }
        .info-value {
            font-weight: bold;
            color: #333;
            font-size: 12px;
        }
        .summary-section {
            margin-bottom: 25px;
        }
        .summary-title {
            font-size: 14px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #ddd;
        }
        .summary-grid {
            display: table;
            width: 100%;
        }
        .summary-item {
            display: table-cell;
            text-align: center;
            padding: 10px;
            border: 1px solid #eee;
            background: #fafafa;
        }
        .summary-label {
            font-size: 10px;
            color: #666;
            text-transform: uppercase;
        }
        .summary-value {
            font-size: 16px;
            font-weight: bold;
            margin-top: 5px;
        }
        .summary-value.balance {
            color: #4F46E5;
        }
        .summary-value.credit {
            color: #10B981;
        }
        .summary-value.debit {
            color: #EF4444;
        }
        .transactions-section {
            margin-bottom: 25px;
        }
        .transactions-title {
            font-size: 14px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #ddd;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }
        thead {
            background: #4F46E5;
            color: white;
        }
        th {
            padding: 10px 8px;
            text-align: left;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
        }
        td {
            padding: 10px 8px;
            border-bottom: 1px solid #eee;
        }
        tr:nth-child(even) {
            background: #fafafa;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .amount-credit {
            color: #10B981;
            font-weight: bold;
        }
        .amount-debit {
            color: #EF4444;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            color: #666;
            font-size: 9px;
        }
        .footer p {
            margin: 3px 0;
        }
        .confidential {
            color: #EF4444;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 10px;
        }
        .no-transactions {
            text-align: center;
            padding: 30px;
            color: #666;
            font-style: italic;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="logo">{{ config('app.name', 'Finora Bank') }}</div>
        <div class="tagline">Your Trusted Financial Partner</div>
        <div class="statement-title">Account Statement</div>
        <div class="statement-period">
            Generated on {{ $generatedAt->format('F d, Y \a\t h:i A') }}
            @if($dateFrom && $dateTo)
                <br>Period: {{ $dateFrom->format('M d, Y') }} - {{ $dateTo->format('M d, Y') }}
            @endif
        </div>
    </div>

    <!-- Account Information -->
    <div class="account-info">
        <div class="account-info-grid">
            <div class="account-info-row">
                <div class="account-info-cell">
                    <div class="info-label">Account Holder</div>
                    <div class="info-value">{{ $user->name }}</div>
                </div>
                <div class="account-info-cell">
                    <div class="info-label">Account Number</div>
                    <div class="info-value">{{ $account->account_number }}</div>
                </div>
            </div>
            <div class="account-info-row">
                <div class="account-info-cell">
                    <div class="info-label">Account Type</div>
                    <div class="info-value">{{ $account->accountType->name ?? 'Standard Account' }}</div>
                </div>
                <div class="account-info-cell">
                    <div class="info-label">Currency</div>
                    <div class="info-value">{{ $account->currency }}</div>
                </div>
            </div>
            @if($account->routing_number)
            <div class="account-info-row">
                <div class="account-info-cell">
                    <div class="info-label">Routing Number</div>
                    <div class="info-value">{{ $account->routing_number }}</div>
                </div>
                <div class="account-info-cell">
                    <div class="info-label">SWIFT/BIC Code</div>
                    <div class="info-value">{{ $account->swift_code ?? 'N/A' }}</div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Summary Section -->
    <div class="summary-section">
        <div class="summary-title">Account Summary</div>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-label">Current Balance</div>
                <div class="summary-value balance">{{ $currencySymbol }}{{ number_format($account->balance / 100, 2) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Total Credits</div>
                <div class="summary-value credit">+{{ $currencySymbol }}{{ number_format($totalCredits / 100, 2) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Total Debits</div>
                <div class="summary-value debit">-{{ $currencySymbol }}{{ number_format($totalDebits / 100, 2) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Transactions</div>
                <div class="summary-value">{{ $transactions->count() }}</div>
            </div>
        </div>
    </div>

    <!-- Transactions Section -->
    <div class="transactions-section">
        <div class="transactions-title">Transaction History</div>

        @if($transactions->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th style="width: 15%;">Date</th>
                        <th style="width: 20%;">Reference</th>
                        <th style="width: 35%;">Description</th>
                        <th style="width: 15%;" class="text-right">Amount</th>
                        <th style="width: 15%;" class="text-right">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transactions as $transaction)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($transaction->created_at)->format('M d, Y') }}</td>
                            <td style="font-family: monospace; font-size: 9px;">{{ $transaction->reference ?? 'N/A' }}</td>
                            <td>{{ $transaction->description ?? 'Transaction' }}</td>
                            <td class="text-right {{ $transaction->type === 'credit' ? 'amount-credit' : 'amount-debit' }}">
                                {{ $transaction->type === 'credit' ? '+' : '-' }}{{ $currencySymbol }}{{ number_format($transaction->amount / 100, 2) }}
                            </td>
                            <td class="text-right">{{ $currencySymbol }}{{ number_format(($transaction->balance_after ?? 0) / 100, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="no-transactions">
                No transactions found for the selected period.
            </div>
        @endif
    </div>

    <!-- Footer -->
    <div class="footer">
        <p class="confidential">Confidential - For Account Holder Only</p>
        <p>This statement was electronically generated and is valid without signature.</p>
        <p>For any discrepancies, please contact our customer support within 30 days.</p>
        <p>{{ config('app.name', 'Finora Bank') }} | {{ config('app.url') }}</p>
        <p>Document ID: {{ $documentId }}</p>
    </div>
</body>
</html>
