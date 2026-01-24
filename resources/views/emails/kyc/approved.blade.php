<x-mail::message>
# 🎉 KYC Verification Approved!

Dear {{ $user->first_name }},

Great news! Your KYC (Know Your Customer) verification has been **approved**. You now have full access to all features of your {{ config('app.name') }} account.

## Verification Details

<x-mail::table>
| Detail | Information |
|:-------|:------------|
| Document Type | {{ $documentType }} |
| Approved On | {{ $approvedAt }} |
| Status | ✅ Verified |
</x-mail::table>

## What You Can Now Do

With your verified account, you can now:

- 💸 Make unlimited transfers
- 💳 Apply for credit cards
- 💰 Apply for loans
- 🏦 Access all banking features
- 📊 View detailed account statements

<x-mail::button :url="config('app.url') . '/dashboard'" color="success">
Access Your Account
</x-mail::button>

Thank you for choosing {{ config('app.name') }}. We're committed to providing you with the best banking experience.

If you have any questions, our support team is always here to help.

Thanks,<br>
{{ config('app.name') }} Team
</x-mail::message>
