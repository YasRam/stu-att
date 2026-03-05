<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentResource\Pages;
use App\Filament\Resources\StudentResource\RelationManagers\AttendancesRelationManager;
use App\Models\Student;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components as InfolistComponents;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Students';
    protected static ?string $modelLabel = 'Student';
    protected static ?string $pluralModelLabel = 'Students';

    public static function getNavigationLabel(): string
    {
        return __('Students');
    }

    public static function getModelLabel(): string
    {
        return __('Student');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Students');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('Basic Data'))
                    ->schema([
                        Forms\Components\TextInput::make('full_name')
                            ->label(__('Full name'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('national_id')
                            ->label(__('National ID'))
                            ->required()
                            ->length(14)
                            ->numeric()
                            ->unique(ignoreRecord: true)
                            ->helperText(__('National ID helper')),
                        Forms\Components\DatePicker::make('birth_date')
                            ->label(__('Birth date'))
                            ->native(false),
                        Forms\Components\Select::make('gender')
                            ->label(__('Gender'))
                            ->options(['M' => __('Male'), 'F' => __('Female')])
                            ->native(false),
                        Forms\Components\TextInput::make('stage')
                            ->label(__('Stage'))
                            ->maxLength(50),
                        Forms\Components\TextInput::make('group_name')
                            ->label(__('Group'))
                            ->maxLength(255),
                        Forms\Components\Toggle::make('is_taasis')
                            ->label(__('Taasis'))
                            ->default(false),
                        Forms\Components\Toggle::make('is_azhary')
                            ->label(__('Azhary'))
                            ->default(false),
                    ])->columns(2),
                Forms\Components\Section::make(__('Guardian & Contact'))
                    ->schema([
                        Forms\Components\TextInput::make('phone')
                            ->label(__('Phone'))
                            ->tel()
                            ->maxLength(20),
                        Forms\Components\TextInput::make('guardian_name')
                            ->label(__('Guardian name'))
                            ->maxLength(255),
                        Forms\Components\TextInput::make('guardian_phone')
                            ->label(__('Guardian phone'))
                            ->tel()
                            ->maxLength(20),
                        Forms\Components\TextInput::make('guardian_national_id')
                            ->label(__('Guardian National ID'))
                            ->length(14)
                            ->numeric(),
                    ])->columns(2),
                Forms\Components\Textarea::make('notes')
                    ->label(__('Notes'))
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label(__('Name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('national_id')
                    ->label(__('National ID'))
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('birth_date')
                    ->label(__('Birth date'))
                    ->date('Y-m-d')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('gender')
                    ->label(__('Gender'))
                    ->formatStateUsing(fn (?string $state) => $state === 'M' ? __('Male') : ($state === 'F' ? __('Female') : '-'))
                    ->badge()
                    ->color(fn (?string $state) => $state === 'M' ? 'info' : 'success')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('stage')
                    ->label(__('Stage'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('group_name')
                    ->label(__('Group'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_taasis')
                    ->label(__('Taasis'))
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_azhary')
                    ->label(__('Azhary'))
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('absent_30')
                    ->label(__('Absent 30 days'))
                    ->getStateUsing(fn (Student $record) => $record->absentCountLast30Days())
                    ->badge()
                    ->color(fn (Student $record) => $record->absentCountLast30Days() > 5 ? 'danger' : 'gray'),
                Tables\Columns\TextColumn::make('absent_year')
                    ->label(__('Absent year'))
                    ->getStateUsing(fn (Student $record) => $record->absentCountThisYear())
                    ->badge()
                    ->color(fn (Student $record) => $record->absentCountThisYear() > 15 ? 'danger' : 'gray'),
            ])
            ->defaultSort('group_name')
            ->filters([
                Tables\Filters\SelectFilter::make('group_name')
                    ->label(__('Group'))
                    ->options(fn () => Student::query()->distinct()->pluck('group_name', 'group_name')->filter()),
                Tables\Filters\SelectFilter::make('stage')
                    ->label(__('Stage'))
                    ->options(fn () => Student::query()->distinct()->pluck('stage', 'stage')->filter()),
                Tables\Filters\TernaryFilter::make('high_absence')
                    ->label(__('Absence warning'))
                    ->queries(
                        true: fn (Builder $q) => $q->withHighAbsenceWarning(),
                        false: fn (Builder $q) => $q,
                    )
                    ->placeholder(__('All')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->recordClasses(fn (Student $record) => $record->hasHighAbsenceWarning() ? 'bg-danger-50 dark:bg-danger-950/20' : null);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                InfolistComponents\Section::make(__('Basic Data'))
                    ->schema([
                        InfolistComponents\TextEntry::make('full_name')->label(__('Full name')),
                        InfolistComponents\TextEntry::make('national_id')->label(__('National ID')),
                        InfolistComponents\TextEntry::make('birth_date')->label(__('Birth date'))->date('Y-m-d'),
                        InfolistComponents\TextEntry::make('gender')->label(__('Gender'))->formatStateUsing(fn ($state) => $state === 'M' ? __('Male') : ($state === 'F' ? __('Female') : '-')),
                        InfolistComponents\TextEntry::make('stage')->label(__('Stage')),
                        InfolistComponents\TextEntry::make('group_name')->label(__('Group')),
                        InfolistComponents\IconEntry::make('is_taasis')->label(__('Taasis'))->boolean(),
                        InfolistComponents\IconEntry::make('is_azhary')->label(__('Azhary'))->boolean(),
                    ])->columns(2),
                InfolistComponents\Section::make(__('Attendance stats'))
                    ->schema([
                        InfolistComponents\TextEntry::make('total_sessions')
                            ->label(__('Total sessions'))
                            ->state(fn (Student $record) => $record->attendances()->count()),
                        InfolistComponents\TextEntry::make('present_percent')
                            ->label(__('Attendance %'))
                            ->state(function (Student $record) {
                                $total = $record->attendances()->count();
                                if ($total === 0) return '-';
                                $present = $record->attendances()->whereHas('attendanceStatus', fn ($q) => $q->where('is_absent', false))->count();
                                return round($present / $total * 100, 1) . '%';
                            }),
                        InfolistComponents\TextEntry::make('absences_count')
                            ->label(__('Absences count'))
                            ->state(fn (Student $record) => $record->attendances()->whereHas('attendanceStatus', fn ($q) => $q->where('is_absent', true))->count()),
                        InfolistComponents\TextEntry::make('warning')
                            ->label(__('Warning'))
                            ->state(fn (Student $record) => $record->hasHighAbsenceWarning() ? __('Yes — high absence') : __('No'))
                            ->color(fn (Student $record) => $record->hasHighAbsenceWarning() ? 'danger' : 'gray'),
                    ])->columns(2),
                InfolistComponents\Section::make(__('Guardian & Contact'))
                    ->schema([
                        InfolistComponents\TextEntry::make('phone')->label(__('Phone')),
                        InfolistComponents\TextEntry::make('guardian_name')->label(__('Guardian')),
                        InfolistComponents\TextEntry::make('guardian_phone')->label(__('Guardian phone')),
                        InfolistComponents\TextEntry::make('notes')->label(__('Notes'))->columnSpanFull(),
                    ])->columns(2)->collapsed(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            AttendancesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudent::route('/create'),
            'view' => Pages\ViewStudent::route('/{record}'),
            'edit' => Pages\EditStudent::route('/{record}/edit'),
        ];
    }
}
