@props(['url'])
@php
    $logoUrl = \App\Models\Setting::getValue('branding', 'site_logo');
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
