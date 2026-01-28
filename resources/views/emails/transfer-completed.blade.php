<x-mail::message>
# {{ ucfirst($transferType) }} Transfer {{ $transferType === 'wire' || $transferType === 'domestic' ? 'Submitted' : 'Completed' }}

Hello {{ $user->first_name }},

@if($transferType === 'wire' || $transferType === 'domestic')
Your {{ $transferType }} transfer has been submitted successfully and is now being processed.
@else
Your {{ $transferType }} transfer has been completed successfully.
@endif

<x-mail::panel>
<div style="text-align: center; padding: 15px 0;">
@php
    $amount = $transfer->amount / 100;
    $currency = $transfer->currency ?? 'USD';
@endphp
<span style="font-size: 28px; font-weight: bold; color: #059669;">
    {{ $currency }} {{ number_format($amount, 2) }}
</span>
@if($transferType === 'wire' || $transferType === 'domestic')
<br><span style="font-size: 14px; color: #6B7280;">Processing</span>
@else
<br><span style="font-size: 14px; color: #059669;">Completed</span>
@endif
</div>
</x-mail::panel>

**Transfer Details:**
@if($transferType === 'internal')
- **Type:** Internal Transfer (Within {{ app_name() }})
- **Recipient:** {{ $recipientName }}
@elseif($transferType === 'wire')
- **Type:** Wire Transfer (International)
- **Beneficiary:** {{ $transfer->beneficiary_name ?? $recipientName }}
@if(isset($transfer->beneficiary_bank_name))
- **Beneficiary Bank:** {{ $transfer->beneficiary_bank_name }}
@endif
@elseif($transferType === 'domestic')
- **Type:** Domestic Transfer (Local Bank)
- **Beneficiary:** {{ $transfer->beneficiary_name ?? $recipientName }}
@if(isset($transfer->bank))
- **Destination Bank:** {{ $transfer->bank->name ?? 'N/A' }}
@endif
@elseif($transferType === 'account')
- **Type:** Account-to-Account Transfer
- **Transfer Between:** Your own accounts
@endif
- **Reference Number:** {{ $transfer->reference_number ?? 'N/A' }}
@if(isset($transfer->fee) && $transfer->fee > 0)
- **Fee:** {{ $currency }} {{ number_format($transfer->fee / 100, 2) }}
- **Total Debited:** {{ $currency }} {{ number_format(($transfer->amount + $transfer->fee) / 100, 2) }}
@endif
- **Date:** {{ now()->format('M d, Y \a\t h:i A') }}

@if($transferType === 'wire' || $transferType === 'domestic')
**Processing Time:**
@if($transferType === 'wire')
Wire transfers typically take 3-5 business days to be processed and delivered.
@else
Domestic transfers typically take 1-3 business days to be processed.
@endif
@endif

@if($transfer->description ?? false)
**Description/Remarks:**
{{ $transfer->description }}
@endif

<x-mail::button :url="route('transactions.history')">
View Transaction History
</x-mail::button>

If you did not initiate this transfer, please contact our support team immediately.

Thanks,<br>
{{ app_name() }}

<x-slot:subcopy>
This is an automated notification. Please do not reply to this email directly.
For assistance, contact us at {{ support_email() }}
</x-slot:subcopy>
</x-mail::message>
