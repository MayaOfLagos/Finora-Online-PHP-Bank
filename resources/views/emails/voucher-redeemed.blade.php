<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voucher Redeemed - {{ app_name() }}</title>
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
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .header .logo {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        .content {
            padding: 30px 20px;
        }
        .success-icon {
            width: 64px;
            height: 64px;
            background-color: #dcfce7;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
        .success-icon svg {
            width: 32px;
            height: 32px;
            color: #16a34a;
        }
        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
        }
        .details-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .details-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        .details-row:last-child {
            border-bottom: none;
        }
        .details-label {
            color: #64748b;
            font-size: 14px;
        }
        .details-value {
            font-weight: 600;
            color: #1e293b;
        }
        .amount-highlight {
            background: linear-gradient(135deg, #16a34a 0%, #22c55e 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin: 20px 0;
        }
        .amount-highlight .amount {
            font-size: 32px;
            font-weight: 700;
        }
        .amount-highlight .label {
            font-size: 14px;
            opacity: 0.9;
        }
        .warning-box {
            background-color: #fef3c7;
            border: 1px solid #f59e0b;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            font-size: 14px;
        }
        .warning-box strong {
            color: #92400e;
        }
        .footer {
            background-color: #f8fafc;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #64748b;
        }
        .footer a {
            color: #3b82f6;
            text-decoration: none;
        }
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <div class="logo">{{ app_name() }}</div>
            <h1>Voucher Redeemed Successfully!</h1>
        </div>
        
        <div class="content">
            <div class="success-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            
            <p class="greeting">Hello {{ $userName }},</p>
            
            <p>Great news! Your voucher has been successfully redeemed and the funds have been credited to your account.</p>
            
            <div class="amount-highlight">
                <div class="label">Amount Credited</div>
                <div class="amount">${{ $amount }} {{ $currency }}</div>
            </div>
            
            <div class="details-box">
                <div class="details-row">
                    <span class="details-label">Voucher Code</span>
                    <span class="details-value">{{ $voucherCode }}</span>
                </div>
                <div class="details-row">
                    <span class="details-label">Credited To</span>
                    <span class="details-value">{{ $accountName }}</span>
                </div>
                <div class="details-row">
                    <span class="details-label">Account Number</span>
                    <span class="details-value">{{ $accountNumber }}</span>
                </div>
                <div class="details-row">
                    <span class="details-label">Date & Time</span>
                    <span class="details-value">{{ $redeemedAt }}</span>
                </div>
            </div>
            
            <div style="text-align: center;">
                <a href="{{ url('/dashboard') }}" class="btn">View Your Account</a>
            </div>
            
            <div class="warning-box">
                <strong>🔒 Security Notice:</strong> If you did not perform this transaction, please contact our support team immediately at {{ config('mail.from.address', 'support@example.com') }} or call our 24/7 helpline.
            </div>
            
            <p>Thank you for choosing {{ app_name() }}!</p>
        </div>
        
        <div class="footer">
            <p>© {{ date('Y') }} {{ app_name() }}. All rights reserved.</p>
            <p>
                <a href="{{ url('/privacy') }}">Privacy Policy</a> · 
                <a href="{{ url('/terms') }}">Terms of Service</a> · 
                <a href="{{ url('/support') }}">Contact Support</a>
            </p>
            <p style="margin-top: 15px; font-size: 11px;">
                This is an automated message from {{ app_name() }}. Please do not reply to this email.
            </p>
        </div>
    </div>
</body>
</html>
