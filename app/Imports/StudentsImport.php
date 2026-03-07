<?php

namespace App\Imports;

use App\Models\EnrollmentStatus;
use App\Models\Stage;
use App\Models\Student;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class StudentsImport implements ToCollection, WithHeadingRow, WithValidation
{
    /**
     * Expects Arabic or English headers. Stage can be given by name_ar (e.g. "3 ب"); we resolve to stage_id.
     */
    public function collection(Collection $rows): void
    {
        $headers = ['الاسم الكامل', 'الرقم القومي', 'تاريخ الميلاد', 'النوع', 'المرحلة', 'حالة القبول', 'الهاتف', 'الجوال', 'ملاحظات'];
        foreach ($rows as $row) {
            $arr = is_array($row) ? $row : $row->toArray();
            $fullName = $arr[$headers[0]] ?? $arr['full_name'] ?? $arr[0] ?? null;
            $nationalId = $arr[$headers[1]] ?? $arr['national_id'] ?? $arr[1] ?? null;
            if (!$fullName) {
                continue;
            }
            $nationalId = $nationalId ? preg_replace('/\D/', '', (string) $nationalId) : null;
            if ($nationalId && strlen($nationalId) !== 14) {
                continue;
            }
            $stageName = $arr[$headers[4]] ?? $arr[4] ?? null;
            $stageId = null;
            if ($stageName) {
                $stage = Stage::query()->where('name_ar', $stageName)->orWhere('name_en', $stageName)->first();
                $stageId = $stage?->id;
            }
            $stageId = $stageId ?? Stage::query()->orderBy('order_index')->first()?->id;
            if (!$stageId) {
                continue;
            }
            $enrollmentStatusName = $arr[$headers[5]] ?? $arr[5] ?? 'انتظار';
            $enrollmentStatus = EnrollmentStatus::query()
                ->where('name_ar', $enrollmentStatusName)
                ->orWhere('name_en', $enrollmentStatusName)
                ->first();
            $enrollmentStatusId = $enrollmentStatus?->id ?? EnrollmentStatus::query()->where('name_ar', 'انتظار')->value('id') ?? 1;

            $data = array_filter([
                'full_name' => $fullName,
                'national_id' => $nationalId ?: null,
                'birth_date' => $this->parseDate($arr[$headers[2]] ?? $arr[2] ?? null),
                'gender' => $this->parseGender($arr[$headers[3]] ?? $arr[3] ?? null),
                'stage_id' => $stageId,
                'enrollment_status_id' => $enrollmentStatusId,
                'phone' => $arr[$headers[6]] ?? $arr[6] ?? null,
                'mobile' => $arr[$headers[7]] ?? $arr[7] ?? null,
                'notes' => $arr[$headers[8]] ?? $arr[8] ?? null,
            ]);

            if ($nationalId) {
                Student::updateOrCreate(['national_id' => $nationalId], $data);
            } else {
                Student::create($data);
            }
        }
    }

    public function rules(): array
    {
        return [
            '*.national_id' => ['nullable', 'string', 'size:14'],
            '*.full_name' => ['nullable', 'string', 'max:255'],
        ];
    }

    private function parseDate(mixed $value): ?string
    {
        if (empty($value)) {
            return null;
        }
        if ($value instanceof \Carbon\Carbon || $value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }
        if (is_numeric($value)) {
            try {
                $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value);
                return $date->format('Y-m-d');
            } catch (\Throwable) {
                return null;
            }
        }
        $str = trim((string) $value);
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $str)) {
            return $str;
        }
        try {
            return now()->parse($str)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    private function parseGender(mixed $value): ?string
    {
        if (empty($value)) {
            return null;
        }
        $v = strtoupper(substr(trim((string) $value), 0, 1));
        if (in_array($v, ['M', 'F'], true)) {
            return $v;
        }
        if (str_contains((string) $value, 'ذكر') || (string) $value === 'M') {
            return 'M';
        }
        if (str_contains((string) $value, 'أنثى') || (string) $value === 'F') {
            return 'F';
        }
        return null;
    }

    private function parseBool(mixed $value): bool
    {
        if ($value === true || $value === 1) {
            return true;
        }
        if (is_string($value)) {
            $v = trim($value);
            return in_array(mb_strtolower($v), ['نعم', 'yes', '1', 'true', 'y'], true);
        }
        return false;
    }
}
