<x-mail::message>
# {{ $title }}

Hello {{ $user->first_name }},

{{ $message }}

<x-mail::button :url="route('filament.admin.pages.dashboard')">
View Dashboard
</x-mail::button>

Thanks,<br>
{{ config('app.name') }} Team
</x-mail::message>
