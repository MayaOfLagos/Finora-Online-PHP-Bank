<x-mail::message>
# New Support Ticket Received

A new support ticket has been submitted and requires attention.

**Ticket Details:**

| Field | Value |
|-------|-------|
| Ticket # | {{ $ticket->ticket_number }} |
| Subject | {{ $ticket->subject }} |
| Category | {{ $ticket->category?->name ?? 'General' }} |
| Priority | {{ $ticket->priority->label() }} |
| Submitted By | {{ $ticket->user?->full_name ?? 'Unknown' }} |
| Email | {{ $ticket->user?->email ?? 'N/A' }} |
| Date | {{ $ticket->created_at->format('M j, Y \a\t g:i A') }} |

---

**Customer's Message:**

<x-mail::panel>
{{ $initialMessage }}
</x-mail::panel>

<x-mail::button :url="config('app.url') . '/admin/support-tickets/' . $ticket->id" color="primary">
View Ticket in Admin Panel
</x-mail::button>

@if($ticket->priority->value === 'urgent' || $ticket->priority->value === 'high')
<x-mail::panel>
⚠️ **This is a {{ strtoupper($ticket->priority->label()) }} priority ticket and requires immediate attention.**
</x-mail::panel>
@endif

Thanks,<br>
{{ app_name() }} Support System
</x-mail::message>
