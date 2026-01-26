<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Account Statement</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .email-container {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #4F46E5 0%, #6366F1 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0 0 5px 0;
            font-size: 28px;
            font-weight: bold;
        }
        .header p {
            margin: 0;
            opacity: 0.9;
            font-size: 14px;
        }
        .content {
            padding: 30px;
        }
        .greeting {
            font-size: 18px;
            color: #333;
            margin-bottom: 20px;
        }
        .message {
            color: #555;
            margin-bottom: 25px;
        }
        .account-box {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
            border-left: 4px solid #4F46E5;
        }
        .account-box h3 {
            margin: 0 0 15px 0;
            color: #333;
            font-size: 16px;
        }
        .account-detail {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .account-detail:last-child {
            border-bottom: none;
        }
        .account-detail .label {
            color: #666;
            font-size: 13px;
        }
        .account-detail .value {
            font-weight: 600;
            color: #333;
        }
        .highlight-box {
            background: linear-gradient(135deg, #4F46E5 0%, #6366F1 100%);
            color: white;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin-bottom: 25px;
        }
        .highlight-box .label {
            font-size: 12px;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .highlight-box .amount {
            font-size: 32px;
            font-weight: bold;
            margin: 10px 0;
        }
        .info-text {
            color: #666;
            font-size: 13px;
            margin-bottom: 25px;
            padding: 15px;
            background: #fff8e6;
            border-radius: 6px;
            border-left: 4px solid #f59e0b;
        }
        .info-text strong {
            color: #333;
        }
        .security-notice {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .security-notice h4 {
            color: #dc2626;
            margin: 0 0 10px 0;
            font-size: 14px;
        }
        .security-notice p {
            color: #7f1d1d;
            font-size: 12px;
            margin: 0;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px 30px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        .footer p {
            margin: 5px 0;
        }
        .footer a {
            color: #4F46E5;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>{{ app_name() }}</h1>
            <p>Your Trusted Financial Partner</p>
        </div>

        <div class="content">
            <p class="greeting">Dear {{ $user->name }},</p>

            <p class="message">
                Your account statement has been generated and is attached to this email as a PDF document.
                This statement contains a detailed record of all transactions on your account.
            </p>

            <div class="account-box">
                <h3>📋 Statement Details</h3>
                <div class="account-detail">
                    <span class="label">Account Number</span>
                    <span class="value">****{{ substr($account->account_number, -4) }}</span>
                </div>
                <div class="account-detail">
                    <span class="label">Account Type</span>
                    <span class="value">{{ $account->accountType->name ?? 'Standard Account' }}</span>
                </div>
                <div class="account-detail">
                    <span class="label">Statement Period</span>
                    <span class="value">
                        @if($dateFrom && $dateTo)
                            {{ $dateFrom->format('M d, Y') }} - {{ $dateTo->format('M d, Y') }}
                        @else
                            All Time
                        @endif
                    </span>
                </div>
                <div class="account-detail">
                    <span class="label">Total Transactions</span>
                    <span class="value">{{ $transactionCount }}</span>
                </div>
                <div class="account-detail">
                    <span class="label">Generated On</span>
                    <span class="value">{{ $generatedAt->format('M d, Y \a\t h:i A') }}</span>
                </div>
            </div>

            <div class="highlight-box">
                <div class="label">Current Balance</div>
                <div class="amount">{{ $currencySymbol }}{{ number_format($account->balance / 100, 2) }}</div>
            </div>

            <div class="info-text">
                <strong>📎 Attachment:</strong> Your statement is attached as a PDF file named
                <strong>"Statement_{{ substr($account->account_number, -4) }}_{{ $generatedAt->format('Ymd') }}.pdf"</strong>.
                Please download and save it for your records.
            </div>

            <div class="security-notice">
                <h4>🔒 Security Notice</h4>
                <p>
                    This email contains confidential financial information. If you did not request this statement
                    or believe you received it in error, please contact our support team immediately and delete this email.
                </p>
            </div>

            <p class="message">
                If you have any questions about your statement or notice any discrepancies,
                please don't hesitate to contact our customer support team. We're here to help!
            </p>
        </div>

        <div class="footer">
            <p><strong>{{ app_name() }}</strong></p>
            <p>This is an automated email. Please do not reply directly to this message.</p>
            <p>For support, visit <a href="{{ config('app.url') }}">{{ config('app.url') }}</a></p>
            <p>{{ copyright_text() }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
