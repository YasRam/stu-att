<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">{{ __('Select date, group and subject') }}</x-slot>
            {{ $this->form }}
        </x-filament::section>

        @if($sessionFilter['group_name'] ?? null)
            <x-filament::section>
                <x-slot name="heading">{{ __('Students —') }} {{ $sessionFilter['group_name'] }}</x-slot>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                    {{ __('Mark attendance hint') }}
                </p>
                {{ $this->table }}
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
