<?php

namespace App\Filament\Resources;

use App\Enums\SessionStatus;
use App\Filament\Resources\DailySessionResource\Pages;
use App\Filament\Resources\DailySessionResource\RelationManagers\AttendancesRelationManager;
use App\Models\DailySession;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DailySessionResource extends Resource
{
    protected static ?string $model = DailySession::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'Daily Sessions';
    protected static ?string $modelLabel = 'Session';
    protected static ?string $pluralModelLabel = 'Daily Sessions';

    public static function getNavigationLabel(): string
    {
        return __('Daily Sessions');
    }

    public static function getModelLabel(): string
    {
        return __('Session');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Daily Sessions');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('session_date')
                    ->label(__('Session date'))
                    ->required()
                    ->native(false)
                    ->default(now()),
                Forms\Components\TextInput::make('subject_name')
                    ->label(__('Subject'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('stage_or_group')
                    ->label(__('Stage or Group'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('status')
                    ->label(__('Status'))
                    ->options(collect(SessionStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])->all())
                    ->default(SessionStatus::Normal->value)
                    ->native(false),
                Forms\Components\Select::make('teacher_id')
                    ->label(__('Teacher'))
                    ->options(function () {
                        $q = User::query();
                        if (!auth()->user()?->isAdmin()) {
                            $q->where('id', auth()->id());
                        }
                        return $q->pluck('name', 'id')->toArray();
                    })
                    ->required()
                    ->default(fn () => auth()->id())
                    ->native(false)
                    ->searchable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('session_date')
                    ->label(__('Date'))
                    ->date('Y-m-d')
                    ->sortable(),
                Tables\Columns\TextColumn::make('subject_name')
                    ->label(__('Subject'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('stage_or_group')
                    ->label(__('Stage or Group'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('Status'))
                    ->formatStateUsing(fn ($state) => $state instanceof SessionStatus ? $state->label() : (SessionStatus::tryFrom($state)?->label() ?? $state))
                    ->badge()
                    ->color(fn ($state) => match ($state?->value ?? $state) {
                        'exam' => 'warning',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('teacher.name')
                    ->label(__('Teacher')),
            ])
            ->defaultSort('session_date', 'desc')
            ->filters([
                Tables\Filters\Filter::make('my_sessions')
                    ->label(__('My sessions only'))
                    ->query(fn (Builder $q) => auth()->user()?->isAdmin() ? $q : $q->where('teacher_id', auth()->id()))
                    ->default(fn () => !auth()->user()?->isAdmin()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListDailySessions::route('/'),
            'create' => Pages\CreateDailySession::route('/create'),
            'edit' => Pages\EditDailySession::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $q = parent::getEloquentQuery();
        if (!auth()->user()?->isAdmin()) {
            $q->where('teacher_id', auth()->id());
        }
        return $q;
    }
}
