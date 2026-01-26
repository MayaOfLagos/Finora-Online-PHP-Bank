<?php

namespace App\Http\Controllers;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Mail\TicketCreatedMail;
use App\Mail\TicketStatusChangedMail;
use App\Models\SupportCategory;
use App\Models\SupportTicket;
use App\Models\TicketMessage;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Enum;
use Inertia\Inertia;
use Inertia\Response;

class SupportController extends Controller
{
    /**
     * Display the support index page with tickets and categories.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        // Get user's tickets
        $tickets = $user->supportTickets()
            ->with(['category', 'messages' => fn ($q) => $q->latest()->limit(1)])
            ->latest()
            ->get()
            ->map(fn ($ticket) => [
                'id' => $ticket->id,
                'uuid' => $ticket->uuid,
                'ticket_number' => $ticket->ticket_number,
                'subject' => $ticket->subject,
                'status' => $ticket->status->value,
                'status_label' => $ticket->status->label(),
                'status_color' => $ticket->status->color(),
                'priority' => $ticket->priority->value,
                'priority_label' => $ticket->priority->label(),
                'category' => $ticket->category?->name,
                'created_at' => $ticket->created_at->format('M d, Y'),
                'updated_at' => $ticket->updated_at->diffForHumans(),
                'last_message' => $ticket->messages->first()?->message,
                'unread_count' => $ticket->messages()
                    ->where('type', 'agent')
                    ->whereNull('read_at')
                    ->count(),
            ]);

        // Get active categories
        $categories = SupportCategory::where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'name', 'description']);

        // Get priorities
        $priorities = collect(TicketPriority::cases())->map(fn ($p) => [
            'value' => $p->value,
            'label' => $p->label(),
        ]);

        // FAQs (could be from database later)
        $faqs = [
            ['question' => 'How do I reset my PIN?', 'answer' => 'Go to Security settings and follow the PIN reset flow.'],
            ['question' => 'Where can I see transfer limits?', 'answer' => 'Open Accounts, select an account, and view Limits.'],
            ['question' => 'How long do international transfers take?', 'answer' => 'Wire transfers typically take 1-3 business days depending on the destination country.'],
            ['question' => 'How do I report a suspicious transaction?', 'answer' => 'Contact our support team immediately through this page or call our fraud hotline.'],
        ];

        return Inertia::render('Support/Index', [
            'tickets' => $tickets,
            'categories' => $categories,
            'priorities' => $priorities,
            'faqs' => $faqs,
        ]);
    }

    /**
     * Store a new support ticket.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
            'category_id' => 'required|exists:support_categories,id',
            'priority' => ['nullable', new Enum(TicketPriority::class)],
        ]);

        $user = $request->user();

        // Generate ticket number
        $ticketNumber = 'TKT-' . strtoupper(Str::random(8));

        // Create ticket
        $ticket = SupportTicket::create([
            'user_id' => $user->id,
            'ticket_number' => $ticketNumber,
            'subject' => $validated['subject'],
            'category_id' => $validated['category_id'],
            'priority' => $validated['priority'] ? TicketPriority::from($validated['priority']) : TicketPriority::Medium,
            'status' => TicketStatus::Open,
        ]);

        // Create initial message
        TicketMessage::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'message' => $validated['message'],
            'type' => 'customer',
        ]);

        // Send email notification
        try {
            Mail::to($user->email)->send(new TicketCreatedMail($ticket));
        } catch (\Exception $e) {
            \Log::error('Failed to send ticket created email: ' . $e->getMessage());
        }

        // Log activity
        ActivityLogger::logSupport(
            'ticket_created',
            $ticket,
            $user,
            ['subject' => $ticket->subject]
        );

        return redirect()->route('support.show', $ticket->uuid)
            ->with('success', 'Your support ticket has been created. Ticket number: ' . $ticketNumber);
    }

    /**
     * Display a single ticket with messages (chat view).
     */
    public function show(Request $request, string $uuid): Response
    {
        $user = $request->user();

        $ticket = SupportTicket::where('uuid', $uuid)
            ->where('user_id', $user->id)
            ->with(['category', 'messages.user', 'assignedAgent'])
            ->firstOrFail();

        // Mark agent messages as read
        $ticket->messages()
            ->where('type', 'agent')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        // Format messages for chat view
        $messages = $ticket->messages
            ->sortBy('created_at')
            ->values()
            ->map(fn ($msg) => [
                'id' => $msg->id,
                'message' => $msg->message,
                'type' => $msg->type,
                'is_agent' => $msg->type === 'agent',
                'sender_name' => $msg->user?->name ?? 'Support Agent',
                'sender_avatar' => $msg->user?->avatar_url,
                'created_at' => $msg->created_at->format('M d, Y H:i'),
                'time_ago' => $msg->created_at->diffForHumans(),
            ]);

        return Inertia::render('Support/Show', [
            'ticket' => [
                'id' => $ticket->id,
                'uuid' => $ticket->uuid,
                'ticket_number' => $ticket->ticket_number,
                'subject' => $ticket->subject,
                'status' => $ticket->status->value,
                'status_label' => $ticket->status->label(),
                'status_color' => $ticket->status->color(),
                'priority' => $ticket->priority->value,
                'priority_label' => $ticket->priority->label(),
                'category' => $ticket->category?->name,
                'assigned_agent' => $ticket->assignedAgent?->name,
                'created_at' => $ticket->created_at->format('M d, Y H:i'),
                'updated_at' => $ticket->updated_at->diffForHumans(),
                'can_reply' => !in_array($ticket->status, [TicketStatus::Closed, TicketStatus::Resolved]),
            ],
            'messages' => $messages,
        ]);
    }

    /**
     * Reply to a ticket.
     */
    public function reply(Request $request, string $uuid): RedirectResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|max:5000',
        ]);

        $user = $request->user();

        $ticket = SupportTicket::where('uuid', $uuid)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Check if ticket can be replied to
        if (in_array($ticket->status, [TicketStatus::Closed, TicketStatus::Resolved])) {
            return back()->with('error', 'Cannot reply to a closed or resolved ticket. Please open a new ticket.');
        }

        // Create message
        TicketMessage::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'message' => $validated['message'],
            'type' => 'customer',
        ]);

        // Update status if it was waiting for customer response
        $oldStatus = $ticket->status;
        if ($ticket->status === TicketStatus::Waiting) {
            $ticket->update(['status' => TicketStatus::InProgress]);

            // Send status changed email
            try {
                Mail::to($user->email)->send(new TicketStatusChangedMail(
                    $ticket,
                    $oldStatus->label(),
                    TicketStatus::InProgress->label()
                ));
            } catch (\Exception $e) {
                \Log::error('Failed to send ticket status changed email: ' . $e->getMessage());
            }
        }

        $ticket->touch();

        // Log activity
        ActivityLogger::logSupport(
            'ticket_replied',
            $ticket,
            $user,
            ['message_preview' => Str::limit($validated['message'], 100)]
        );

        return back()->with('success', 'Your reply has been sent.');
    }

    /**
     * Close a ticket.
     */
    public function close(Request $request, string $uuid): RedirectResponse
    {
        $user = $request->user();

        $ticket = SupportTicket::where('uuid', $uuid)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if ($ticket->status === TicketStatus::Closed) {
            return back()->with('error', 'Ticket is already closed.');
        }

        $oldStatus = $ticket->status;
        $ticket->update([
            'status' => TicketStatus::Closed,
            'closed_at' => now(),
        ]);

        // Create system message
        TicketMessage::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'message' => 'Ticket closed by customer.',
            'type' => 'system',
        ]);

        // Send status changed email
        try {
            Mail::to($user->email)->send(new TicketStatusChangedMail(
                $ticket,
                $oldStatus->label(),
                TicketStatus::Closed->label()
            ));
        } catch (\Exception $e) {
            \Log::error('Failed to send ticket status changed email: ' . $e->getMessage());
        }

        // Log activity
        ActivityLogger::logSupport(
            'ticket_closed',
            $ticket,
            $user,
            ['closed_by' => 'customer']
        );

        return redirect()->route('support.index')
            ->with('success', 'Ticket has been closed.');
    }
}
