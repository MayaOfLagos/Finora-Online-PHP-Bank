<x-mail::message>
# {{ ucfirst($transferType) }} Transfer Verification Code

Hello {{ $user->first_name }},

You are completing a {{ $transferType }} transfer. Please use the verification code below to complete your transaction.

<x-mail::panel>
<div style="text-align: center; font-size: 32px; font-weight: bold; letter-spacing: 8px; color: #3B82F6; padding: 20px 0;">
{{ $otp }}
</div>
</x-mail::panel>

**Transfer Details:**
- **Amount:** ${{ number_format($transfer->amount / 100, 2) }} {{ $transfer->currency ?? 'USD' }}
@if(isset($transfer->fee))
- **Fee:** ${{ number_format($transfer->fee / 100, 2) }}
- **Total:** ${{ number_format($transfer->total_amount / 100, 2) }}
@endif
@if(isset($transfer->beneficiary_name))
- **Beneficiary:** {{ $transfer->beneficiary_name }}
@elseif(isset($transfer->recipient_name))
- **Recipient:** {{ $transfer->recipient_name }}
@elseif(isset($transfer->recipient_account_number))
- **Account:** {{ $transfer->recipient_account_number }}
@endif
- **Reference:** {{ $transfer->reference_number }}

**Important Security Information:**
- This code will expire in **{{ $expiresIn }} minutes**
- Never share this code with anyone
- {{ app_name() }} will never ask for your OTP via phone or SMS
- If you did not initiate this transfer, please contact us immediately

@if($transferType === 'wire' && isset($transfer->beneficiary_bank_name))
**Destination Bank:** {{ $transfer->beneficiary_bank_name }}
@elseif($transferType === 'domestic' && isset($transfer->recipient_bank_name))
**Destination Bank:** {{ $transfer->recipient_bank_name }}
@endif

<x-mail::button :url="route('transfers.' . $transferType)">
Complete Transfer
</x-mail::button>

If you have any questions or concerns, please contact our support team.

Thanks,<br>
{{ app_name() }} Security Team

<x-slot:subcopy>
This is an automated security message. Please do not reply to this email.
</x-slot:subcopy>
</x-mail::message>
