@php
    /** @var \App\Models\User $user */
    /** @var \Illuminate\Database\Eloquent\Model $deposit */
    /** @var string $depositType */
    $title = match ($depositType) {
        'mobile' => 'Mobile Deposit Initiated',
        'check' => 'Check Deposit Submitted',
        'crypto' => 'Crypto Deposit Registered',
        default => 'Deposit Notification',
    };
@endphp

@component('mail::message')
# {{ $title }}

Hello {{ $user->full_name }},

We have recorded your {{ ucfirst($depositType) }} deposit.

@if($depositType === 'mobile')
- Gateway: **{{ $deposit->gateway->value ?? strtoupper($deposit->gateway) }}**
- Amount: **{{ number_format(($deposit->amount ?? 0) / 100, 2) }} {{ $deposit->currency ?? 'USD' }}**
@elseif($depositType === 'check')
- Check #: **{{ $deposit->check_number }}**
- Amount: **{{ number_format(($deposit->amount ?? 0) / 100, 2) }} {{ $deposit->currency ?? 'USD' }}**
- Funds available after hold: **{{ optional($deposit->hold_until)->format('M d, Y') }}**
@elseif($depositType === 'crypto')
- Cryptocurrency: **{{ optional($deposit->cryptocurrency)->symbol ?? 'CRYPTO' }}**
- Crypto Amount: **{{ $deposit->crypto_amount }}**
- USD Amount: **{{ number_format(($deposit->usd_amount ?? 0) / 100, 2) }} USD**
- Transaction: **{{ $deposit->transaction_hash }}**
@endif

**Reference:** {{ $deposit->reference_number ?? 'N/A' }}

We will notify you once processing is complete.

Thanks,
{{ config('app.name') }}
@endcomponent
