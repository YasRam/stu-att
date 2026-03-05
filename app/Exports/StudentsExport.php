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
        return Student::query()->orderBy('group_name')->orderBy('full_name');
    }

    public function headings(): array
    {
        return [
            'الاسم الكامل',
            'الرقم القومي',
            'تاريخ الميلاد',
            'النوع',
            'المرحلة',
            'المجموعة',
            'تأسيس',
            'أزهري',
            'الهاتف',
            'ولي الأمر',
            'هاتف ولي الأمر',
            'رقم قومي ولي الأمر',
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
            $row->stage,
            $row->group_name,
            $row->is_taasis ? 'نعم' : 'لا',
            $row->is_azhary ? 'نعم' : 'لا',
            $row->phone,
            $row->guardian_name,
            $row->guardian_phone,
            $row->guardian_national_id,
            $row->notes,
        ];
    }
}
