<x-mail::message>
# KYC Verification Update

Dear {{ $user->first_name }},

We have completed the review of your KYC (Know Your Customer) verification submission. Unfortunately, we were unable to approve your documents at this time.

## Submission Details

<x-mail::table>
| Detail | Information |
|:-------|:------------|
| Document Type | {{ $documentType }} |
| Reviewed On | {{ $rejectedAt }} |
| Status | ❌ Not Approved |
</x-mail::table>

@if($rejectionReason)
## Reason for Non-Approval

<x-mail::panel>
{{ $rejectionReason }}
</x-mail::panel>
@endif

## What You Can Do

Don't worry! You can submit a new verification request with the following recommendations:

1. **Ensure document clarity** - Make sure all text and photos are clearly visible
2. **Use valid documents** - Ensure your documents are not expired
3. **Match your information** - Document details should match your account information
4. **Take clear selfies** - If required, ensure your face is clearly visible alongside the document

<x-mail::button :url="config('app.url') . '/dashboard/kyc'" color="primary">
Submit New Verification
</x-mail::button>

If you believe this decision was made in error or need assistance, please don't hesitate to contact our support team.

Thanks,<br>
{{ config('app.name') }} Team

---

<small>This email was sent because a KYC verification request associated with your account was reviewed. If you have any concerns, please contact support.</small>
</x-mail::message>
