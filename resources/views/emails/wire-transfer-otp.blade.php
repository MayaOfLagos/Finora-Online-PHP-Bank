<x-mail::message>
# Wire Transfer Verification Code

Hello {{ $user->first_name }},

You are completing a wire transfer. Please use the verification code below to complete your transaction.

<x-mail::panel>
<div style="text-align: center; font-size: 32px; font-weight: bold; letter-spacing: 8px; color: #3B82F6; padding: 20px 0;">
{{ $otp }}
</div>
</x-mail::panel>

**Transfer Details:**
- **Amount:** ${{ number_format($transfer->amount / 100, 2) }} {{ $transfer->currency }}
- **Fee:** ${{ number_format($transfer->fee / 100, 2) }}
- **Total:** ${{ number_format($transfer->total_amount / 100, 2) }}
- **Beneficiary:** {{ $transfer->beneficiary_name }}
- **Reference:** {{ $transfer->reference_number }}

**Important Security Information:**
- This code will expire in **{{ $expiresIn }} minutes**
- Never share this code with anyone
- {{ app_name() }} will never ask for your OTP via phone or SMS
- If you did not initiate this transfer, please contact us immediately

@if($transfer->beneficiary_bank_name)
**Destination Bank:** {{ $transfer->beneficiary_bank_name }}
@endif

<x-mail::button :url="route('transfers.wire')">
Complete Transfer
</x-mail::button>

If you have any questions or concerns, please contact our support team.

Thanks,<br>
{{ app_name() }} Security Team

<x-slot:subcopy>
This is an automated security message. Please do not reply to this email.
</x-slot:subcopy>
</x-mail::message>
