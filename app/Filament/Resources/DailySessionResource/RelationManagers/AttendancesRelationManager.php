<?php

namespace App\Filament\Resources\DailySessionResource\RelationManagers;

use App\Models\Attendance;
use App\Models\AttendanceStatus;
use App\Models\Student;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AttendancesRelationManager extends RelationManager
{
    protected static string $relationship = 'attendances';

    protected static ?string $title = 'سجل الحضور';

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.full_name')->label('الطالب')->searchable(),
                Tables\Columns\TextColumn::make('attendanceStatus.name_ar')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn ($record) => $record->attendanceStatus?->color ?? 'gray'),
                Tables\Columns\TextColumn::make('reason')->label('السبب')->limit(40),
                Tables\Columns\TextColumn::make('taken_at')->label('وقت التسجيل')->dateTime(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('إضافة تسجيل')
                    ->form([
                        Forms\Components\Select::make('student_id')
                            ->label('الطالب')
                            ->options(fn () => Student::query()->where('group_name', $this->getOwnerRecord()->stage_or_group)->pluck('full_name', 'id'))
                            ->required()
                            ->searchable(),
                        Forms\Components\Select::make('attendance_status_id')
                            ->label('الحالة')
                            ->options(AttendanceStatus::pluck('name_ar', 'id'))
                            ->required(),
                        Forms\Components\Textarea::make('reason')->label('السبب'),
                    ])
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['taken_at'] = now();
                        $data['taken_by'] = auth()->id();
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->form([
                        Forms\Components\Select::make('attendance_status_id')
                            ->label('الحالة')
                            ->options(AttendanceStatus::pluck('name_ar', 'id'))
                            ->required(),
                        Forms\Components\Textarea::make('reason')->label('السبب'),
                    ]),
            ])
            ->bulkActions([]);
    }
}
