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

    protected static ?string $title = 'Attendance record';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return __('Attendance record');
    }

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.full_name')->label(__('Student'))->searchable(),
                Tables\Columns\TextColumn::make('attendanceStatus')
                    ->label(__('Status'))
                    ->formatStateUsing(fn ($state) => $state?->translated_name ?? '—')
                    ->badge()
                    ->color(fn ($record) => $record->attendanceStatus?->color ?? 'gray'),
                Tables\Columns\TextColumn::make('reason')->label(__('Reason'))->limit(40),
                Tables\Columns\TextColumn::make('taken_at')->label(__('Time taken'))->dateTime(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label(__('Add record'))
                    ->form([
                        Forms\Components\Select::make('student_id')
                            ->label(__('Student'))
                            ->options(fn () => Student::query()->where('group_name', $this->getOwnerRecord()->stage_or_group)->pluck('full_name', 'id'))
                            ->required()
                            ->searchable(),
                        Forms\Components\Select::make('attendance_status_id')
                            ->label(__('Status'))
                            ->options(fn () => AttendanceStatus::all()->mapWithKeys(fn ($s) => [$s->id => $s->translated_name])->all())
                            ->required(),
                        Forms\Components\Textarea::make('reason')->label(__('Reason')),
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
                            ->label(__('Status'))
                            ->options(fn () => AttendanceStatus::all()->mapWithKeys(fn ($s) => [$s->id => $s->translated_name])->all())
                            ->required(),
                        Forms\Components\Textarea::make('reason')->label(__('Reason')),
                    ]),
            ])
            ->bulkActions([]);
    }
}
