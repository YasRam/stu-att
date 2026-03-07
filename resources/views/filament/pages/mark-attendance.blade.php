<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">{{ __('Select date, group and subject') }}</x-slot>
            {{ $this->form }}
        </x-filament::section>

        @if($sessionFilter['stage_id'] ?? null)
            <x-filament::section>
                <x-slot name="heading">{{ __('Students —') }} {{ \App\Models\Stage::find($sessionFilter['stage_id'])?->name_ar ?? $sessionFilter['stage_id'] }}</x-slot>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                    {{ __('Mark attendance hint') }}
                </p>
                {{ $this->table }}
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
