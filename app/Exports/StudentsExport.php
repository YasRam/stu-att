<?php

namespace App\Exports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StudentsExport implements FromQuery, WithHeadings, WithMapping
{
    public function query()
    {
        return Student::query()->with(['stage', 'enrollmentStatus'])->orderBy('stage_id')->orderBy('full_name');
    }

    public function headings(): array
    {
        return [
            'الاسم الكامل',
            'الرقم القومي',
            'تاريخ الميلاد',
            'النوع',
            'المرحلة',
            'حالة القبول',
            'الهاتف',
            'الجوال',
            'ملاحظات',
        ];
    }

    public function map($row): array
    {
        return [
            $row->full_name,
            $row->national_id,
            $row->birth_date?->format('Y-m-d'),
            $row->gender === 'M' ? 'ذكر' : ($row->gender === 'F' ? 'أنثى' : ''),
            $row->stage?->name_ar ?? '',
            $row->enrollmentStatus?->name_ar ?? '',
            $row->phone,
            $row->mobile,
            $row->notes,
        ];
    }
}
