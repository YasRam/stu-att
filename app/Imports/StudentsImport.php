<?php

namespace App\Imports;

use App\Models\Student;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class StudentsImport implements ToCollection, WithHeadingRow, WithValidation
{
    /**
     * Expects Arabic or English headers. We use first row as headers.
     * Headings will be slugged (e.g. "الاسم الكامل" -> keys in Arabic or use column index).
     * Using column index via ToModel is simpler; WithHeadingRow with Arabic may need custom mapping.
     */
    public function collection(Collection $rows): void
    {
        $headers = ['الاسم الكامل', 'الرقم القومي', 'تاريخ الميلاد', 'النوع', 'المرحلة', 'المجموعة', 'تأسيس', 'أزهري', 'الهاتف', 'ولي الأمر', 'هاتف ولي الأمر', 'رقم قومي ولي الأمر', 'ملاحظات'];
        foreach ($rows as $row) {
            $arr = is_array($row) ? $row : $row->toArray();
            $fullName = $arr[$headers[0]] ?? $arr['full_name'] ?? $arr[0] ?? null;
            $nationalId = $arr[$headers[1]] ?? $arr['national_id'] ?? $arr[1] ?? null;
            if (!$fullName || !$nationalId) {
                continue;
            }
            $nationalId = preg_replace('/\D/', '', (string) $nationalId);
            if (strlen($nationalId) !== 14) {
                continue;
            }
            Student::updateOrCreate(
                ['national_id' => $nationalId],
                [
                    'full_name' => $fullName,
                    'birth_date' => $this->parseDate($arr[$headers[2]] ?? $arr[2] ?? null),
                    'gender' => $this->parseGender($arr[$headers[3]] ?? $arr[3] ?? null),
                    'stage' => $arr[$headers[4]] ?? $arr[4] ?? null,
                    'group_name' => $arr[$headers[5]] ?? $arr[5] ?? null,
                    'is_taasis' => $this->parseBool($arr[$headers[6]] ?? $arr[6] ?? false),
                    'is_azhary' => $this->parseBool($arr[$headers[7]] ?? $arr[7] ?? false),
                    'phone' => $arr[$headers[8]] ?? $arr[8] ?? null,
                    'guardian_name' => $arr[$headers[9]] ?? $arr[9] ?? null,
                    'guardian_phone' => $arr[$headers[10]] ?? $arr[10] ?? null,
                    'guardian_national_id' => $arr[$headers[11]] ?? $arr[11] ?? null,
                    'notes' => $arr[$headers[12]] ?? $arr[12] ?? null,
                ]
            );
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
