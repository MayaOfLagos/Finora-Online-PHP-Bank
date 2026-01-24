<div>
    <x-filament::button
        wire:click="{{ $action }}"
        color="{{ $color ?? 'primary' }}"
        icon="{{ $icon ?? '' }}"
        size="sm"
    >
        {{ $label }}
    </x-filament::button>
</div>
