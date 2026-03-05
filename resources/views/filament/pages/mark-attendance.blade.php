<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">اختر التاريخ والمجموعة والمادة</x-slot>
            {{ $this->form }}
        </x-filament::section>

        @if($sessionFilter['group_name'] ?? null)
            <x-filament::section>
                <x-slot name="heading">الطلاب — {{ $sessionFilter['group_name'] }}</x-slot>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                    اضغط «تعديل» بجانب أي طالب لتسجيل الحالة والسبب، أو حدد عدة طلاب واستخدم «تحديد الحالة للمحددين».
                </p>
                {{ $this->table }}
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
