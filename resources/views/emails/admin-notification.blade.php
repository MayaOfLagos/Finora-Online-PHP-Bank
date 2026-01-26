<x-mail::message>
# Hello {{ $user->first_name }},

{{ $message }}

@if($actionText && $actionUrl)
<x-mail::button :url="$actionUrl">
{{ $actionText }}
</x-mail::button>
@endif

If you have any questions or concerns, please don't hesitate to contact our support team.

Thanks,<br>
{{ app_name() }} Team
</x-mail::message>
