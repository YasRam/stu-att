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

    protected static ?string $title = 'سجل الحضور';

    protected static ?string $recordTitleAttribute = 'id';

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('dailySession.session_date')
                    ->label('التاريخ')
                    ->date('Y-m-d')
                    ->sortable(),
                Tables\Columns\TextColumn::make('dailySession.subject_name')
                    ->label('المادة'),
                Tables\Columns\TextColumn::make('attendanceStatus.name_ar')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn ($record) => $record->attendanceStatus?->color ?? 'gray'),
                Tables\Columns\TextColumn::make('reason')
                    ->label('السبب')
                    ->limit(30),
                Tables\Columns\TextColumn::make('taken_at')
                    ->label('تاريخ التسجيل')
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
