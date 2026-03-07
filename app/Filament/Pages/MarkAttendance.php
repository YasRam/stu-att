<?php

namespace App\Filament\Pages;

use App\Models\Attendance;
use App\Models\AttendanceStatus;
use App\Models\DailySession;
use App\Models\Stage;
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
    protected static ?string $navigationLabel = 'Mark Attendance';
    protected static ?string $title = 'Mark Daily Attendance';

    public static function getNavigationLabel(): string
    {
        return __('Mark Attendance');
    }

    public function getTitle(): string
    {
        return __('Mark Daily Attendance');
    }
    protected static string $view = 'filament.pages.mark-attendance';
    protected static ?int $navigationSort = 2;

    public ?array $sessionFilter = null;

    public function mount(): void
    {
        $this->sessionFilter = [
            'session_date' => now()->format('Y-m-d'),
            'stage_id' => null,
            'subject_name' => '',
        ];
    }

    public function form(Form $form): Form
    {
        $stages = Stage::query()->orderBy('order_index')->pluck('name_ar', 'id')->toArray();
        return $form
            ->statePath('sessionFilter')
            ->schema([
                DatePicker::make('session_date')
                    ->label(__('Date'))
                    ->required()
                    ->native(false)
                    ->reactive(),
                Select::make('stage_id')
                    ->label(__('Stage'))
                    ->options($stages)
                    ->required()
                    ->searchable()
                    ->reactive(),
                TextInput::make('subject_name')
                    ->label(__('Subject'))
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getStudentsQuery())
            ->columns([
                TextColumn::make('full_name')->label(__('Student'))->searchable(),
                TextColumn::make('national_id')->label(__('National ID'))->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('current_status')
                    ->label(__('Status'))
                    ->badge()
                    ->color(function (Student $record) {
                        $a = $this->getAttendanceFor($record);
                        return $a?->attendanceStatus?->color ?? 'gray';
                    })
                    ->getStateUsing(function (Student $record) {
                        $a = $this->getAttendanceFor($record);
                        return $a?->attendanceStatus?->translated_name ?? '—';
                    }),
                TextColumn::make('current_reason')
                    ->label(__('Reason'))
                    ->getStateUsing(fn (Student $record) => $this->getAttendanceFor($record)?->reason ?? ''),
            ])
            ->headerActions([])
            ->actions([
                \Filament\Tables\Actions\Action::make('setStatus')
                    ->label(__('Edit'))
                    ->form([
                        Select::make('attendance_status_id')
                            ->label(__('Status'))
                            ->options(fn () => AttendanceStatus::all()->mapWithKeys(fn ($s) => [$s->id => $s->translated_name])->all())
                            ->required(),
                        \Filament\Forms\Components\Textarea::make('reason')->label(__('Reason')),
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
                        Notification::make()->title(__('Saved'))->success()->send();
                    }),
            ])
            ->bulkActions([
                \Filament\Tables\Actions\BulkAction::make('setBulkStatus')
                    ->label(__('Set status for selected'))
                    ->icon('heroicon-o-check-circle')
                    ->form([
                        Select::make('attendance_status_id')
                            ->label(__('Status'))
                            ->options(fn () => AttendanceStatus::all()->mapWithKeys(fn ($s) => [$s->id => $s->translated_name])->all())
                            ->required(),
                        \Filament\Forms\Components\Textarea::make('reason')->label(__('Reason')),
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
                        Notification::make()->title(__('Updated :count students', ['count' => $records->count()]))->success()->send();
                    }),
            ])
            ->striped();
    }

    protected function getStudentsQuery(): Builder
    {
        $stageId = $this->sessionFilter['stage_id'] ?? null;
        if (!$stageId) {
            return Student::query()->whereRaw('1 = 0');
        }
        return Student::query()->where('stage_id', $stageId)->orderBy('full_name');
    }

    protected function getOrCreateSession(): ?DailySession
    {
        $date = $this->sessionFilter['session_date'] ?? null;
        $stageId = $this->sessionFilter['stage_id'] ?? null;
        $subject = $this->sessionFilter['subject_name'] ?? null;
        if (!$date || !$stageId || !$subject) {
            return null;
        }
        $stageName = Stage::find($stageId)?->name_ar ?? (string) $stageId;
        return DailySession::firstOrCreate(
            [
                'session_date' => $date,
                'stage_or_group' => $stageName,
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
