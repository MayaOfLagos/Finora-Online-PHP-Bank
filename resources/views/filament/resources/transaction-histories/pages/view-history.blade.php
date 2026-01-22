@php
    use Filament\Facades\Filament;
@endphp

<x-filament-panels::page>
    {{ $this->getTable() }}
</x-filament-panels::page>
