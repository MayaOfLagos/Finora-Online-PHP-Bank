<x-mail::message>
# KYC Verification Submitted

Dear {{ $user->first_name }},

Thank you for submitting your KYC (Know Your Customer) verification documents. We have received your submission and it is now under review.

## Submission Details

<x-mail::table>
| Detail | Information |
|:-------|:------------|
| Document Type | {{ $documentType }} |
| Submitted On | {{ $submittedAt }} |
| Status | Pending Review |
</x-mail::table>

## What's Next?

Our verification team will carefully review your submitted documents. This process typically takes **1-3 business days**. You will receive an email notification once the review is complete.

<x-mail::panel>
**Important:** Please ensure that all your submitted documents are clear, valid, and not expired. If we need any additional information, we will contact you via email.
</x-mail::panel>

<x-mail::button :url="config('app.url') . '/dashboard'" color="primary">
View Dashboard
</x-mail::button>

If you did not submit this verification request, please contact our support team immediately.

Thanks,<br>
{{ app_name() }} Team
</x-mail::message>
