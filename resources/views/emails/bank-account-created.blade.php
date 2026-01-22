<x-mail::message>
# New Bank Account Created

Hello {{ $user->first_name }},

Great news! A new bank account has been created for you.

<x-mail::panel>
**Account Type:** {{ $account->accountType->name }}<br>
**Account Number:** {{ $account->account_number }}<br>
**Currency:** {{ $account->currency }}<br>
**Initial Balance:** ${{ number_format($account->balance / 100, 2) }}<br>
**Status:** {{ $account->is_primary ? 'Primary Account' : 'Additional Account' }}
</x-mail::panel>

@if($account->routing_number)
**Routing Number:** {{ $account->routing_number }}<br>
@endif

@if($account->swift_code)
**SWIFT Code:** {{ $account->swift_code }}<br>
@endif

You can now use this account for all your banking needs including transfers, deposits, and payments.

<x-mail::button :url="route('filament.admin.pages.dashboard')">
View Account Details
</x-mail::button>

Thanks,<br>
{{ config('app.name') }} Team
</x-mail::message>
