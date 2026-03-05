<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityLogResource\Pages;
use App\Models\ActivityLog;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components as InfolistComponents;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ActivityLogResource extends Resource
{
    protected static ?string $model = ActivityLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'سجل العمليات';
    protected static ?string $modelLabel = 'سجل عملية';
    protected static ?string $pluralModelLabel = 'سجل العمليات';
    protected static ?string $navigationGroup = 'الإعدادات';

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('الوقت')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('action')
                    ->label('الإجراء')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'created' => 'إنشاء',
                        'updated' => 'تعديل',
                        'deleted' => 'حذف',
                        default => $state,
                    })
                    ->color(fn (string $state) => match ($state) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('subject_type')
                    ->label('النوع')
                    ->formatStateUsing(fn (?string $state) => $state ? class_basename($state) : '—'),
                Tables\Columns\TextColumn::make('subject_id')
                    ->label('المعرف')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('description')
                    ->label('الوصف')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('المستخدم'),
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('action')
                    ->label('الإجراء')
                    ->options([
                        'created' => 'إنشاء',
                        'updated' => 'تعديل',
                        'deleted' => 'حذف',
                    ]),
                Tables\Filters\SelectFilter::make('subject_type')
                    ->label('نوع السجل')
                    ->options(fn () => ActivityLog::query()->distinct()->pluck('subject_type', 'subject_type')->map(fn ($v) => class_basename($v))->toArray()),
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('المستخدم')
                    ->options(fn () => \App\Models\User::query()->pluck('name', 'id')->toArray())
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([])
            ->recordUrl(null);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                InfolistComponents\Section::make('تفاصيل العملية')
                    ->schema([
                        InfolistComponents\TextEntry::make('created_at')->label('الوقت')->dateTime('Y-m-d H:i:s'),
                        InfolistComponents\TextEntry::make('action')->label('الإجراء')->formatStateUsing(fn ($state) => match ($state) { 'created' => 'إنشاء', 'updated' => 'تعديل', 'deleted' => 'حذف', default => $state }),
                        InfolistComponents\TextEntry::make('description')->label('الوصف'),
                        InfolistComponents\TextEntry::make('subject_type')->label('النوع')->formatStateUsing(fn ($state) => $state ? class_basename($state) : '—'),
                        InfolistComponents\TextEntry::make('subject_id')->label('معرف السجل'),
                        InfolistComponents\TextEntry::make('user.name')->label('المستخدم'),
                        InfolistComponents\TextEntry::make('ip_address')->label('عنوان IP'),
                        InfolistComponents\TextEntry::make('user_agent')->label('المتصفح')->columnSpanFull(),
                    ])->columns(2),
                InfolistComponents\Section::make('القيم القديمة')
                    ->schema([
                        InfolistComponents\KeyValueEntry::make('old_values')->label('')->columnSpanFull(),
                    ])->visible(fn (ActivityLog $record) => !empty($record->old_values)),
                InfolistComponents\Section::make('القيم الجديدة')
                    ->schema([
                        InfolistComponents\KeyValueEntry::make('new_values')->label('')->columnSpanFull(),
                    ])->visible(fn (ActivityLog $record) => !empty($record->new_values)),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivityLogs::route('/'),
            'view' => Pages\ViewActivityLog::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes();
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }
}
