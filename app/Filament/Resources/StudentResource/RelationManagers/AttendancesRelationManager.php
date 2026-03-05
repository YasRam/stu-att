<?php

namespace App\Filament\Resources\StudentResource\RelationManagers;

use App\Models\AttendanceStatus;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class AttendancesRelationManager extends RelationManager
{
    protected static string $relationship = 'attendances';

    protected static ?string $title = 'Attendance record';

    protected static ?string $recordTitleAttribute = 'id';

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return __('Attendance record');
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('dailySession.session_date')
                    ->label(__('Date'))
                    ->date('Y-m-d')
                    ->sortable(),
                Tables\Columns\TextColumn::make('dailySession.subject_name')
                    ->label(__('Subject')),
                Tables\Columns\TextColumn::make('attendanceStatus')
                    ->label(__('Status'))
                    ->formatStateUsing(fn ($state) => $state?->translated_name ?? '—')
                    ->badge()
                    ->color(fn ($record) => $record->attendanceStatus?->color ?? 'gray'),
                Tables\Columns\TextColumn::make('reason')
                    ->label(__('Reason'))
                    ->limit(30),
                Tables\Columns\TextColumn::make('taken_at')
                    ->label(__('Time taken'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('taken_at', 'desc')
            ->filters([])
            ->headerActions([])
            ->actions([])
            ->bulkActions([]);
    }
}
