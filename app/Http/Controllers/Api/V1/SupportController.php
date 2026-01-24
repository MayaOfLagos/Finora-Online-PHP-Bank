<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Http\Controllers\Controller;
use App\Mail\NewTicketNotificationMail;
use App\Models\Faq;
use App\Models\KnowledgeBaseArticle;
use App\Models\Setting;
use App\Models\SupportCategory;
use App\Models\SupportTicket;
use App\Models\TicketMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Enum;

class SupportController extends Controller
{
    /**
     * Get available ticket categories.
     */
    public function categories(): JsonResponse
    {
        $categories = SupportCategory::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn ($cat) => [
                'value' => $cat->id,
                'label' => $cat->name,
                'description' => $cat->description,
            ]);

        $priorities = collect(TicketPriority::cases())->map(fn ($pri) => [
            'value' => $pri->value,
            'label' => $pri->label(),
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'categories' => $categories,
                'priorities' => $priorities,
            ],
        ]);
    }

    /**
     * Get FAQs.
     */
    public function faq(Request $request): JsonResponse
    {
        $query = Faq::where('is_active', true);

        if ($request->category) {
            $query->where('category', $request->category);
        }

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('question', 'like', "%{$request->search}%")
                    ->orWhere('answer', 'like', "%{$request->search}%");
            });
        }

        $faqs = $query->orderBy('sort_order')
            ->orderBy('question')
            ->get();

        // Group by category
        $groupedFaqs = $faqs->groupBy('category')->map(function ($items, $category) {
            return [
                'category' => $category,
                'items' => $items->map(fn ($faq) => [
                    'id' => $faq->id,
                    'question' => $faq->question,
                    'answer' => $faq->answer,
                ]),
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => [
                'faqs' => $groupedFaqs,
            ],
        ]);
    }

    /**
     * Get knowledge base articles.
     */
    public function knowledgeBase(Request $request): JsonResponse
    {
        // Get categories with their articles
        $categories = SupportCategory::where('is_active', true)
            ->withCount(['articles' => fn ($q) => $q->where('is_published', true)])
            ->orderBy('sort_order')
            ->get()
            ->map(fn ($cat) => [
                'id' => $cat->id,
                'name' => $cat->name,
                'description' => $cat->description,
                'articles_count' => $cat->articles_count,
            ]);

        // Get featured/popular articles
        $featuredArticles = KnowledgeBaseArticle::where('is_published', true)
            ->with('category:id,name')
            ->orderBy('view_count', 'desc')
            ->limit(5)
            ->get()
            ->map(fn ($article) => $this->formatArticle($article, false));

        return response()->json([
            'success' => true,
            'data' => [
                'categories' => $categories,
                'featured_articles' => $featuredArticles,
            ],
        ]);
    }

    /**
     * Get articles by category.
     */
    public function knowledgeBaseCategory(Request $request, string $slug): JsonResponse
    {
        // Try to find by ID first (since SupportCategory doesn't have slug)
        $category = SupportCategory::where('id', $slug)
            ->orWhere('name', 'like', str_replace('-', ' ', $slug))
            ->where('is_active', true)
            ->first();

        if (! $category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found.',
            ], 404);
        }

        $articles = $category->articles()
            ->where('is_published', true)
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => [
                'category' => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'description' => $category->description,
                ],
                'articles' => collect($articles->items())->map(fn ($a) => $this->formatArticle($a, false)),
                'pagination' => [
                    'current_page' => $articles->currentPage(),
                    'last_page' => $articles->lastPage(),
                    'per_page' => $articles->perPage(),
                    'total' => $articles->total(),
                ],
            ],
        ]);
    }

    /**
     * Get single knowledge base article.
     */
    public function knowledgeBaseArticle(Request $request, string $slug): JsonResponse
    {
        $article = KnowledgeBaseArticle::where('slug', $slug)
            ->where('is_published', true)
            ->with('category:id,name')
            ->first();

        if (! $article) {
            return response()->json([
                'success' => false,
                'message' => 'Article not found.',
            ], 404);
        }

        // Increment view count
        $article->increment('views_count');

        // Get related articles
        $relatedArticles = KnowledgeBaseArticle::where('category_id', $article->category_id)
            ->where('id', '!=', $article->id)
            ->where('is_published', true)
            ->limit(3)
            ->get()
            ->map(fn ($a) => $this->formatArticle($a, false));

        return response()->json([
            'success' => true,
            'data' => [
                'article' => $this->formatArticle($article, true),
                'related_articles' => $relatedArticles,
            ],
        ]);
    }

    /**
     * Search knowledge base.
     */
    public function searchKnowledgeBase(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|min:2',
        ]);

        $articles = KnowledgeBaseArticle::where('is_published', true)
            ->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->query('query')}%")
                    ->orWhere('content', 'like', "%{$request->query('query')}%");
            })
            ->with('category:id,name')
            ->limit(20)
            ->get()
            ->map(fn ($a) => $this->formatArticle($a, false));

        return response()->json([
            'success' => true,
            'data' => [
                'articles' => $articles,
                'query' => $request->query('query'),
            ],
        ]);
    }

    /**
     * Get user's support tickets.
     */
    public function tickets(Request $request): JsonResponse
    {
        $query = SupportTicket::where('user_id', $request->user()->id);

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $tickets = $query->latest()
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => [
                'tickets' => collect($tickets->items())->map(fn ($t) => $this->formatTicket($t, false)),
                'pagination' => [
                    'current_page' => $tickets->currentPage(),
                    'last_page' => $tickets->lastPage(),
                    'per_page' => $tickets->perPage(),
                    'total' => $tickets->total(),
                ],
            ],
        ]);
    }

    /**
     * Create a support ticket.
     */
    public function createTicket(Request $request): JsonResponse
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
            'category' => ['required', 'exists:support_categories,id'],
            'priority' => ['nullable', new Enum(TicketPriority::class)],
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|max:10240|mimes:jpg,jpeg,png,gif,pdf,doc,docx',
        ]);

        $user = $request->user();

        // Generate ticket number
        $ticketNumber = 'TKT-'.strtoupper(Str::random(8));

        $ticket = SupportTicket::create([
            'user_id' => $user->id,
            'ticket_number' => $ticketNumber,
            'subject' => $request->subject,
            'category_id' => $request->category,
            'priority' => $request->priority ? TicketPriority::from($request->priority) : TicketPriority::Medium,
            'status' => TicketStatus::Open,
        ]);

        // Create initial message
        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('ticket-attachments/'.$ticket->id, 'private');
                $attachments[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'mime' => $file->getMimeType(),
                ];
            }
        }

        TicketMessage::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'message' => $request->message,
            'attachments' => ! empty($attachments) ? $attachments : null,
            'is_staff_reply' => false,
        ]);

        // Send notification to support team
        $this->notifySupportTeam($ticket, $request->message);

        return response()->json([
            'success' => true,
            'message' => 'Support ticket created successfully.',
            'data' => [
                'ticket' => $this->formatTicket($ticket->fresh(), true),
            ],
        ], 201);
    }

    /**
     * Get single support ticket.
     */
    public function showTicket(Request $request, SupportTicket $ticket): JsonResponse
    {
        if ($ticket->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket not found.',
            ], 404);
        }

        // Mark user's unread messages as read
        $ticket->messages()
            ->where('is_staff_reply', true)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'data' => [
                'ticket' => $this->formatTicket($ticket, true),
            ],
        ]);
    }

    /**
     * Reply to a support ticket.
     */
    public function replyToTicket(Request $request, SupportTicket $ticket): JsonResponse
    {
        if ($ticket->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket not found.',
            ], 404);
        }

        if ($ticket->status === TicketStatus::Closed) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot reply to a closed ticket. Please open a new ticket.',
            ], 422);
        }

        $request->validate([
            'message' => 'required|string|max:5000',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|max:10240|mimes:jpg,jpeg,png,gif,pdf,doc,docx',
        ]);

        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('ticket-attachments/'.$ticket->id, 'private');
                $attachments[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'mime' => $file->getMimeType(),
                ];
            }
        }

        TicketMessage::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => $request->user()->id,
            'message' => $request->message,
            'attachments' => ! empty($attachments) ? $attachments : null,
            'is_staff_reply' => false,
        ]);

        // Reopen ticket if it was awaiting user response
        if ($ticket->status === TicketStatus::Waiting) {
            $ticket->update(['status' => TicketStatus::InProgress]);
        }

        $ticket->touch(); // Update the updated_at timestamp

        return response()->json([
            'success' => true,
            'message' => 'Reply sent successfully.',
            'data' => [
                'ticket' => $this->formatTicket($ticket->fresh(), true),
            ],
        ]);
    }

    /**
     * Close a support ticket.
     */
    public function closeTicket(Request $request, SupportTicket $ticket): JsonResponse
    {
        if ($ticket->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket not found.',
            ], 404);
        }

        if ($ticket->status === TicketStatus::Closed) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket is already closed.',
            ], 422);
        }

        $ticket->update([
            'status' => TicketStatus::Closed,
            'closed_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Ticket closed successfully.',
        ]);
    }

    /**
     * Rate a closed ticket.
     */
    public function rateTicket(Request $request, SupportTicket $ticket): JsonResponse
    {
        if ($ticket->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket not found.',
            ], 404);
        }

        if ($ticket->status !== TicketStatus::Closed) {
            return response()->json([
                'success' => false,
                'message' => 'Can only rate closed tickets.',
            ], 422);
        }

        if ($ticket->rating) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket has already been rated.',
            ], 422);
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'feedback' => 'nullable|string|max:1000',
        ]);

        $ticket->update([
            'rating' => $request->rating,
            'rating_feedback' => $request->feedback,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Thank you for your feedback!',
        ]);
    }

    /**
     * Format article for response.
     */
    private function formatArticle(KnowledgeBaseArticle $article, bool $includeContent): array
    {
        $data = [
            'id' => $article->id,
            'title' => $article->title,
            'slug' => $article->slug,
            'category' => $article->category ? [
                'id' => $article->category->id,
                'name' => $article->category->name,
            ] : null,
            'view_count' => $article->view_count,
            'is_published' => $article->is_published,
            'created_at' => $article->created_at?->toIso8601String(),
        ];

        if ($includeContent) {
            $data['content'] = $article->content;
        }

        return $data;
    }

    /**
     * Format ticket for response.
     */
    private function formatTicket(SupportTicket $ticket, bool $includeMessages): array
    {
        $data = [
            'id' => $ticket->id,
            'ticket_number' => $ticket->ticket_number,
            'subject' => $ticket->subject,
            'category' => [
                'value' => $ticket->category_id,
                'label' => $ticket->category?->name ?? 'General',
            ],
            'priority' => [
                'value' => $ticket->priority->value,
                'label' => $ticket->priority->label(),
            ],
            'status' => [
                'value' => $ticket->status->value,
                'label' => $ticket->status->label(),
                'color' => $ticket->status->color(),
            ],
            'unread_count' => $ticket->messages()
                ->where('is_staff_reply', true)
                ->whereNull('read_at')
                ->count(),
            'rating' => $ticket->rating,
            'closed_at' => $ticket->closed_at?->toIso8601String(),
            'created_at' => $ticket->created_at->toIso8601String(),
            'updated_at' => $ticket->updated_at->toIso8601String(),
        ];

        if ($includeMessages) {
            $data['messages'] = $ticket->messages()
                ->with('user:id,first_name,last_name,profile_photo')
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(fn ($m) => [
                    'id' => $m->id,
                    'message' => $m->message,
                    'is_staff_reply' => $m->is_staff_reply,
                    'user' => $m->user ? [
                        'id' => $m->user->id,
                        'name' => $m->user->first_name.' '.$m->user->last_name,
                        'avatar' => $m->user->profile_photo,
                    ] : null,
                    'staff_name' => $m->is_staff_reply ? ($m->staff_name ?? 'Support Team') : null,
                    'attachments' => $m->attachments ?? [],
                    'read_at' => $m->read_at?->toIso8601String(),
                    'created_at' => $m->created_at->toIso8601String(),
                ]);
        }

        return $data;
    }

    /**
     * Send notification to support team about new ticket.
     */
    private function notifySupportTeam(SupportTicket $ticket, string $message): void
    {
        try {
            $supportEmail = Setting::get('general', 'support_email', config('mail.from.address'));

            if ($supportEmail) {
                Mail::to($supportEmail)->send(new NewTicketNotificationMail($ticket, $message));
            }
        } catch (\Exception $e) {
            // Log error but don't fail the ticket creation
            report($e);
        }
    }
}
