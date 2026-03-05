<?php

namespace App\Filament\Pages;

use App\Models\Attendance;
use App\Models\AttendanceStatus;
use App\Models\DailySession;
use App\Models\Student;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MarkAttendance extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'تسجيل الحضور';
    protected static ?string $title = 'تسجيل الحضور اليومي';
    protected static string $view = 'filament.pages.mark-attendance';
    protected static ?int $navigationSort = 2;

    public ?array $sessionFilter = null;

    public function mount(): void
    {
        $this->sessionFilter = [
            'session_date' => now()->format('Y-m-d'),
            'group_name' => null,
            'subject_name' => '',
        ];
    }

    public function form(Form $form): Form
    {
        $groups = Student::query()->distinct()->pluck('group_name', 'group_name')->filter()->toArray();
        return $form
            ->statePath('sessionFilter')
            ->schema([
                DatePicker::make('session_date')
                    ->label('التاريخ')
                    ->required()
                    ->native(false)
                    ->reactive(),
                Select::make('group_name')
                    ->label('المجموعة')
                    ->options($groups)
                    ->required()
                    ->searchable()
                    ->reactive(),
                TextInput::make('subject_name')
                    ->label('المادة')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getStudentsQuery())
            ->columns([
                TextColumn::make('full_name')->label('الطالب')->searchable(),
                TextColumn::make('national_id')->label('الرقم القومي')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('current_status')
                    ->label('الحالة')
                    ->badge()
                    ->color(function (Student $record) {
                        $a = $this->getAttendanceFor($record);
                        return $a?->attendanceStatus?->color ?? 'gray';
                    })
                    ->getStateUsing(function (Student $record) {
                        $a = $this->getAttendanceFor($record);
                        return $a?->attendanceStatus?->name_ar ?? '—';
                    }),
                TextColumn::make('current_reason')
                    ->label('السبب')
                    ->getStateUsing(fn (Student $record) => $this->getAttendanceFor($record)?->reason ?? ''),
            ])
            ->headerActions([])
            ->actions([
                \Filament\Tables\Actions\Action::make('setStatus')
                    ->label('تعديل')
                    ->form([
                        Select::make('attendance_status_id')
                            ->label('الحالة')
                            ->options(AttendanceStatus::pluck('name_ar', 'id'))
                            ->required(),
                        \Filament\Forms\Components\Textarea::make('reason')->label('السبب'),
                    ])
                    ->action(function (Student $record, array $data) {
                        $session = $this->getOrCreateSession();
                        if (!$session) return;
                        Attendance::updateOrCreate(
                            ['daily_session_id' => $session->id, 'student_id' => $record->id],
                            [
                                'attendance_status_id' => $data['attendance_status_id'],
                                'reason' => $data['reason'] ?? null,
                                'taken_at' => now(),
                                'taken_by' => auth()->id(),
                            ]
                        );
                        Notification::make()->title('تم الحفظ')->success()->send();
                    }),
            ])
            ->bulkActions([
                \Filament\Tables\Actions\BulkAction::make('setBulkStatus')
                    ->label('تحديد الحالة للمحددين')
                    ->icon('heroicon-o-check-circle')
                    ->form([
                        Select::make('attendance_status_id')
                            ->label('الحالة')
                            ->options(AttendanceStatus::pluck('name_ar', 'id'))
                            ->required(),
                        \Filament\Forms\Components\Textarea::make('reason')->label('السبب'),
                    ])
                    ->action(function (\Illuminate\Support\Collection $records, array $data) {
                        $session = $this->getOrCreateSession();
                        if (!$session) return;
                        foreach ($records as $student) {
                            Attendance::updateOrCreate(
                                ['daily_session_id' => $session->id, 'student_id' => $student->id],
                                [
                                    'attendance_status_id' => $data['attendance_status_id'],
                                    'reason' => $data['reason'] ?? null,
                                    'taken_at' => now(),
                                    'taken_by' => auth()->id(),
                                ]
                            );
                        }
                        Notification::make()->title('تم تحديث ' . $records->count() . ' طالب')->success()->send();
                    }),
            ])
            ->striped();
    }

    protected function getStudentsQuery(): Builder
    {
        $group = $this->sessionFilter['group_name'] ?? null;
        if (!$group) {
            return Student::query()->whereRaw('1 = 0');
        }
        return Student::query()->where('group_name', $group)->orderBy('full_name');
    }

    protected function getOrCreateSession(): ?DailySession
    {
        $date = $this->sessionFilter['session_date'] ?? null;
        $group = $this->sessionFilter['group_name'] ?? null;
        $subject = $this->sessionFilter['subject_name'] ?? null;
        if (!$date || !$group || !$subject) {
            return null;
        }
        return DailySession::firstOrCreate(
            [
                'session_date' => $date,
                'stage_or_group' => $group,
                'subject_name' => $subject,
                'teacher_id' => auth()->id(),
            ],
            ['status' => 'normal']
        );
    }

    protected function getAttendanceFor(Student $record): ?Attendance
    {
        $session = $this->getOrCreateSession();
        if (!$session) return null;
        return Attendance::where('daily_session_id', $session->id)->where('student_id', $record->id)->with('attendanceStatus')->first();
    }
}
