<x-mail::message>
# New KYC Verification Submitted

A new KYC verification has been submitted and requires your review.

## User Details

<x-mail::table>
| Detail | Information |
|:-------|:------------|
| Name | {{ $user->first_name }} {{ $user->last_name }} |
| Email | {{ $user->email }} |
| Document Type | {{ $documentType }} |
| Submitted On | {{ $submittedAt }} |
</x-mail::table>

<x-mail::button :url="$adminUrl" color="primary">
Review KYC Submission
</x-mail::button>

Please review this submission at your earliest convenience.

Thanks,<br>
{{ app_name() }} System
</x-mail::message>
