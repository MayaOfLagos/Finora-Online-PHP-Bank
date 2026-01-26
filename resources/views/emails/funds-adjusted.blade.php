<x-mail::message>
# Account Balance Update

Hello {{ $user->first_name }},

Your account balance has been {{ $action === 'add' ? 'credited' : 'debited' }} by an administrator.

<x-mail::panel>
**Account:** {{ $account->account_number }}<br>
**Transaction Type:** {{ $action === 'add' ? 'Credit' : 'Debit' }}<br>
**Amount:** ${{ number_format($amount, 2) }}<br>
**Previous Balance:** ${{ number_format($previousBalance, 2) }}<br>
**New Balance:** ${{ number_format($newBalance, 2) }}<br>
**Reason:** {{ $reason }}
</x-mail::panel>

@if($action === 'add')
The funds have been successfully added to your account and are now available for use.
@else
The funds have been deducted from your account. If you believe this is an error, please contact support immediately.
@endif

<x-mail::button :url="route('filament.admin.pages.dashboard')">
View Account
</x-mail::button>

If you have any questions regarding this transaction, please contact our support team.

Thanks,<br>
{{ app_name() }} Team
</x-mail::message>
