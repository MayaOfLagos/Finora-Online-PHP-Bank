<x-mail::message>
# {{ $template === 'weekly' ? 'Weekly Newsletter' : ($template === 'monthly' ? 'Monthly Newsletter' : 'Special Promotion') }}

Hello {{ $user->first_name }},

@if($template === 'weekly')
Here's your weekly update from Finora Bank with the latest news and insights.

<x-mail::panel>
**This Week's Highlights:**
- New features added to your dashboard
- Security updates and improvements
- Upcoming maintenance schedule
- Market insights and trends
</x-mail::panel>

@elseif($template === 'monthly')
Thank you for being a valued customer of Finora Bank. Here's your monthly roundup.

<x-mail::panel>
**This Month's Summary:**
- Account activity overview
- New services available
- Exclusive offers for loyal customers
- Financial tips and advice
</x-mail::panel>

@else
We have an exciting promotion just for you!

<x-mail::panel>
**Special Offer:**
- Limited time promotion
- Exclusive benefits for our customers
- Enhanced rewards and cashback
- Premium features access
</x-mail::panel>

@endif

@if($customMessage)
---

**Personal Message:**

{{ $customMessage }}

@endif

<x-mail::button :url="route('filament.admin.pages.dashboard')">
View Your Account
</x-mail::button>

To unsubscribe from newsletters, please update your email preferences in your account settings.

Thanks,<br>
{{ config('app.name') }} Team
</x-mail::message>
