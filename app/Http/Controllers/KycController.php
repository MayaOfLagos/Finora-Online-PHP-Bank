<?php

namespace App\Http\Controllers;

use App\Enums\KycStatus;
use App\Mail\KycSubmittedAdminMail;
use App\Models\KycDocumentTemplate;
use App\Models\KycVerification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class KycController extends Controller
{
    /**
     * Display the KYC verification index page.
     */
    public function index()
    {
        $user = auth()->user();

        // Get user's KYC verifications
        $verifications = $user->kycVerifications()
            ->with('template')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($verification) {
                return [
                    'id' => $verification->id,
                    'document_type_name' => $verification->document_type_name,
                    'document_number' => $verification->document_number,
                    'status' => $verification->status->value,
                    'status_label' => $verification->status->label(),
                    'status_color' => $verification->status->color(),
                    'rejection_reason' => $verification->rejection_reason,
                    'has_document_front' => $verification->has_document_front,
                    'has_document_back' => $verification->has_document_back,
                    'has_selfie' => $verification->has_selfie,
                    'created_at' => $verification->created_at->format('M j, Y'),
                    'created_at_human' => $verification->created_at->diffForHumans(),
                    'verified_at' => $verification->verified_at?->format('M j, Y'),
                    'template' => $verification->template ? [
                        'id' => $verification->template->id,
                        'name' => $verification->template->name,
                    ] : null,
                ];
            });

        // Get active templates for submission
        $templates = KycDocumentTemplate::active()
            ->ordered()
            ->get()
            ->map(function ($template) {
                return [
                    'id' => $template->id,
                    'name' => $template->name,
                    'slug' => $template->slug,
                    'description' => $template->description,
                    'instructions' => $template->instructions,
                    'is_required' => $template->is_required,
                    'requires_front_image' => $template->requires_front_image,
                    'requires_back_image' => $template->requires_back_image,
                    'requires_selfie' => $template->requires_selfie,
                    'requires_document_number' => $template->requires_document_number,
                    'accepted_formats' => $template->accepted_formats ?? ['jpg', 'jpeg', 'png', 'pdf'],
                    'accepted_formats_list' => $template->accepted_formats_list,
                    'max_file_size' => $template->max_file_size,
                    'max_file_size_human' => $template->max_file_size_for_humans,
                    'requirements_list' => $template->requirements_list,
                ];
            });

        // Determine overall KYC status
        $overallStatus = $this->getOverallKycStatus($user, $verifications);

        return Inertia::render('Kyc/Index', [
            'verifications' => $verifications,
            'templates' => $templates,
            'overallStatus' => $overallStatus,
            'isVerified' => $user->is_verified,
            'kycLevel' => $user->kyc_level ?? 0,
        ]);
    }

    /**
     * Show the submission form for a specific template.
     */
    public function create(KycDocumentTemplate $template)
    {
        if (! $template->is_active) {
            return back()->with('error', 'This document type is not available for submission.');
        }

        $user = auth()->user();

        // Check if user already has a pending verification for this template
        $existingPending = $user->kycVerifications()
            ->where('template_id', $template->id)
            ->where('status', KycStatus::Pending)
            ->exists();

        if ($existingPending) {
            return redirect()
                ->route('kyc.index')
                ->with('warning', 'You already have a pending verification for this document type. Please wait for it to be reviewed.');
        }

        return Inertia::render('Kyc/Submit', [
            'template' => [
                'id' => $template->id,
                'name' => $template->name,
                'slug' => $template->slug,
                'description' => $template->description,
                'instructions' => $template->instructions,
                'is_required' => $template->is_required,
                'requires_front_image' => $template->requires_front_image,
                'requires_back_image' => $template->requires_back_image,
                'requires_selfie' => $template->requires_selfie,
                'requires_document_number' => $template->requires_document_number,
                'accepted_formats' => $template->accepted_formats ?? ['jpg', 'jpeg', 'png', 'pdf'],
                'accepted_formats_list' => $template->accepted_formats_list,
                'max_file_size' => $template->max_file_size,
                'max_file_size_human' => $template->max_file_size_for_humans,
                'requirements_list' => $template->requirements_list,
            ],
        ]);
    }

    /**
     * Store a new KYC verification submission.
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        $template = KycDocumentTemplate::findOrFail($request->template_id);

        // Build validation rules dynamically based on template requirements
        $rules = [
            'template_id' => 'required|exists:kyc_document_templates,id',
        ];

        $maxSize = $template->max_file_size; // KB
        $acceptedFormats = $template->accepted_formats ?? ['jpg', 'jpeg', 'png', 'pdf'];
        $mimes = implode(',', $acceptedFormats);

        if ($template->requires_document_number) {
            $rules['document_number'] = 'required|string|max:100';
        }

        if ($template->requires_front_image) {
            $rules['document_front'] = "required|file|mimes:{$mimes}|max:{$maxSize}";
        }

        if ($template->requires_back_image) {
            $rules['document_back'] = "required|file|mimes:{$mimes}|max:{$maxSize}";
        }

        if ($template->requires_selfie) {
            $rules['selfie'] = "required|file|mimes:jpg,jpeg,png|max:{$maxSize}";
        }

        $validated = $request->validate($rules, [
            'document_front.required' => 'Please upload the front side of your document.',
            'document_front.mimes' => 'Document front must be a file of type: '.$template->accepted_formats_list.'.',
            'document_front.max' => 'Document front must not exceed '.$template->max_file_size_for_humans.'.',
            'document_back.required' => 'Please upload the back side of your document.',
            'document_back.mimes' => 'Document back must be a file of type: '.$template->accepted_formats_list.'.',
            'document_back.max' => 'Document back must not exceed '.$template->max_file_size_for_humans.'.',
            'selfie.required' => 'Please upload a selfie with your document.',
            'selfie.mimes' => 'Selfie must be a JPG or PNG image.',
            'selfie.max' => 'Selfie must not exceed '.$template->max_file_size_for_humans.'.',
            'document_number.required' => 'Please enter your document number.',
        ]);

        // Check for existing pending submission
        $existingPending = $user->kycVerifications()
            ->where('template_id', $template->id)
            ->where('status', KycStatus::Pending)
            ->exists();

        if ($existingPending) {
            return back()
                ->withErrors(['template_id' => 'You already have a pending verification for this document type.']);
        }

        // Store files
        $documentFrontPath = null;
        $documentBackPath = null;
        $selfiePath = null;

        if ($request->hasFile('document_front')) {
            $documentFrontPath = $request->file('document_front')
                ->store('kyc/'.$user->id, 'public');
        }

        if ($request->hasFile('document_back')) {
            $documentBackPath = $request->file('document_back')
                ->store('kyc/'.$user->id, 'public');
        }

        if ($request->hasFile('selfie')) {
            $selfiePath = $request->file('selfie')
                ->store('kyc/'.$user->id, 'public');
        }

        // Create KYC verification record
        $verification = KycVerification::create([
            'user_id' => $user->id,
            'template_id' => $template->id,
            'document_type' => $template->slug,
            'document_number' => $validated['document_number'] ?? null,
            'document_front_path' => $documentFrontPath,
            'document_back_path' => $documentBackPath,
            'selfie_path' => $selfiePath,
            'status' => KycStatus::Pending,
        ]);

        // Send submission email to user
        $verification->sendSubmissionEmail();

        // Send notification to admin
        $this->notifyAdmin($verification);

        return redirect()
            ->route('kyc.index')
            ->with('success', 'Your KYC verification has been submitted successfully. We will review it within 1-3 business days.');
    }

    /**
     * Get the overall KYC status for a user.
     */
    protected function getOverallKycStatus(User $user, $verifications): array
    {
        if ($user->is_verified) {
            return [
                'status' => 'approved',
                'label' => 'Verified',
                'color' => 'success',
                'message' => 'Your identity has been verified.',
            ];
        }

        $pendingCount = $verifications->where('status', 'pending')->count();
        $rejectedCount = $verifications->where('status', 'rejected')->count();
        $approvedCount = $verifications->where('status', 'approved')->count();

        if ($pendingCount > 0) {
            return [
                'status' => 'pending',
                'label' => 'Pending Review',
                'color' => 'warning',
                'message' => 'Your verification is being reviewed. This typically takes 1-3 business days.',
            ];
        }

        if ($rejectedCount > 0 && $approvedCount === 0) {
            return [
                'status' => 'rejected',
                'label' => 'Resubmission Required',
                'color' => 'danger',
                'message' => 'Your previous submission was rejected. Please submit new documents.',
            ];
        }

        return [
            'status' => 'not_started',
            'label' => 'Not Verified',
            'color' => 'secondary',
            'message' => 'Please submit your identity documents to verify your account.',
        ];
    }

    /**
     * Notify admin about new KYC submission.
     */
    protected function notifyAdmin(KycVerification $verification): void
    {
        // Get admin users (role check)
        $admins = User::where('role', 'admin')->get();

        foreach ($admins as $admin) {
            if ($admin->email) {
                Mail::to($admin->email)->queue(new KycSubmittedAdminMail($verification));
            }
        }
    }
}
