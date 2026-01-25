<?php

namespace App\Http\Controllers;

use App\Enums\GrantStatus;
use App\Models\GrantApplication;
use App\Models\GrantProgram;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class GrantController extends Controller
{
    public function programs(): Response
    {
        $user = Auth::user();

        $programs = GrantProgram::query()
            ->where('status', GrantStatus::Open)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function (GrantProgram $program) use ($user) {
                $userApplication = $user->grantApplications()
                    ->where('grant_program_id', $program->id)
                    ->first();

                return [
                    'id' => $program->id,
                    'uuid' => $program->uuid ?? $program->id,
                    'name' => $program->name,
                    'description' => $program->description,
                    'amount' => $program->amount,
                    'amount_display' => $program->amount_in_dollars,
                    'currency' => $program->currency,
                    'eligibility_criteria' => $program->eligibility_criteria ?? [],
                    'required_documents' => $program->required_documents ?? [],
                    'start_date' => $program->start_date?->format('Y-m-d'),
                    'end_date' => $program->end_date?->format('Y-m-d'),
                    'max_recipients' => $program->max_recipients,
                    'applications_count' => $program->applications()->count(),
                    'user_has_applied' => (bool) $userApplication,
                    'user_application_status' => $userApplication?->status->value,
                ];
            });

        return Inertia::render('Grants/Programs', [
            'programs' => $programs,
        ]);
    }

    public function applications(): Response
    {
        $user = Auth::user();
        $user->load('grantApplications.grantProgram');

        $applications = $user->grantApplications()
            ->latest()
            ->get()
            ->map(function (GrantApplication $application) {
                return [
                    'id' => $application->id,
                    'uuid' => $application->uuid,
                    'reference_number' => $application->reference_number,
                    'grant_program' => [
                        'id' => $application->grantProgram->id,
                        'name' => $application->grantProgram->name,
                        'amount' => $application->grantProgram->amount,
                        'amount_display' => $application->grantProgram->amount_in_dollars,
                        'currency' => $application->grantProgram->currency,
                    ],
                    'status' => $application->status->value,
                    'status_label' => $application->status->label(),
                    'status_color' => $application->status->color(),
                    'rejection_reason' => $application->rejection_reason,
                    'approved_at' => $application->approved_at?->format('M d, Y'),
                    'created_at' => $application->created_at->format('M d, Y'),
                ];
            });

        return Inertia::render('Grants/Applications', [
            'applications' => $applications,
        ]);
    }

    public function create(GrantProgram $program): Response|\Illuminate\Http\RedirectResponse
    {
        $user = Auth::user();

        // Check if user already has an application for this program
        $existingApplication = $user->grantApplications()
            ->where('grant_program_id', $program->id)
            ->first();

        if ($existingApplication) {
            return redirect()->route('grants.applications')->with('error', 'You have already applied for this grant.');
        }

        return Inertia::render('Grants/Apply', [
            'program' => [
                'id' => $program->id,
                'uuid' => $program->uuid ?? $program->id,
                'name' => $program->name,
                'description' => $program->description,
                'amount' => $program->amount,
                'amount_display' => $program->amount_in_dollars,
                'eligibility_criteria' => $program->eligibility_criteria ?? [],
                'required_documents' => $program->required_documents ?? [],
            ],
        ]);
    }

    public function store(): \Illuminate\Http\RedirectResponse
    {
        $user = Auth::user();
        $validated = request()->validate([
            'grant_program_id' => 'required|exists:grant_programs,id',
            'cover_letter' => 'required|string|min:50',
            'documents.*' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif|max:5120',
        ]);

        $program = GrantProgram::findOrFail($validated['grant_program_id']);

        // Check if user already applied
        $existing = $user->grantApplications()
            ->where('grant_program_id', $program->id)
            ->first();

        if ($existing) {
            return redirect()->back()->withErrors(['error' => 'You have already applied for this grant.']);
        }

        // Create application
        $application = $user->grantApplications()->create([
            'grant_program_id' => $program->id,
            'status' => \App\Enums\GrantApplicationStatus::Pending,
            'reference_number' => 'GA-'.uniqid(),
        ]);

        // Upload documents
        if (request()->hasFile('documents')) {
            foreach (request()->file('documents') as $file) {
                $path = $file->store("grant-applications/{$application->id}", 'public');
                $application->documents()->create([
                    'document_type' => 'supporting_document',
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                ]);
            }
        }

        return redirect()->route('grants.applications')->with('success', 'Application submitted successfully. We will review your application and get back to you soon.');
    }

    public function show(GrantApplication $application): Response
    {
        // Check authorization
        if ($application->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $application->load('grantProgram', 'documents', 'disbursement');

        return Inertia::render('Grants/ApplicationDetails', [
            'application' => [
                'id' => $application->id,
                'uuid' => $application->uuid,
                'reference_number' => $application->reference_number,
                'grant_program' => [
                    'id' => $application->grantProgram->id,
                    'name' => $application->grantProgram->name,
                    'description' => $application->grantProgram->description,
                    'amount' => $application->grantProgram->amount,
                    'amount_display' => $application->grantProgram->amount_in_dollars,
                ],
                'status' => $application->status->value,
                'status_label' => $application->status->label(),
                'status_color' => $application->status->color(),
                'rejection_reason' => $application->rejection_reason,
                'approved_at' => $application->approved_at?->format('M d, Y'),
                'created_at' => $application->created_at->format('M d, Y'),
                'documents' => $application->documents->map(fn ($doc) => [
                    'id' => $doc->id,
                    'file_name' => $doc->original_name ?? $doc->file_path,
                    'file_path' => $doc->file_path,
                    'file_size' => $doc->file_size ?? null,
                ]),
                'disbursement' => $application->disbursement ? [
                    'id' => $application->disbursement->id,
                    'amount' => $application->disbursement->amount,
                    'amount_display' => $application->disbursement->amount / 100,
                    'disbursement_date' => $application->disbursement->disbursement_date?->format('M d, Y'),
                    'status' => $application->disbursement->status,
                ] : null,
            ],
        ]);
    }
}
