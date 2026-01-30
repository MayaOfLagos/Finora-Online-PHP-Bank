@props(['url'])
@php
    // Use email_logo (PNG) for emails, fallback to site_logo
    // Gmail doesn't support SVG images, so we need PNG for email
    $logoPath = \App\Models\Setting::getValue('branding', 'email_logo')
        ?? \App\Models\Setting::getValue('branding', 'site_logo');

    // If the logo is SVG, try to use favicon as fallback (usually PNG)
    if ($logoPath && str_ends_with(strtolower($logoPath), '.svg')) {
        $faviconPath = \App\Models\Setting::getValue('branding', 'site_favicon');
        if ($faviconPath && !str_ends_with(strtolower($faviconPath), '.svg')) {
            $logoPath = $faviconPath;
        }
    }

    $logoUrl = $logoPath ? url(\Illuminate\Support\Facades\Storage::url($logoPath)) : null;
    $appName = app_name();
@endphp
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if ($logoUrl)
<img src="{{ $logoUrl }}" class="logo" alt="{{ $appName }} Logo" style="max-height: 60px; max-width: 200px;">
@else
<span style="font-size: 24px; font-weight: bold; color: #3d4852;">{{ $appName }}</span>
@endif
</a>
</td>
</tr>
