<?php

namespace App\Filament\Resources\StudentResource\Pages;

use App\Exports\StudentsExport;
use App\Filament\Resources\StudentResource;
use App\Imports\StudentsImport;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ListStudents extends ListRecords
{
    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('export')
                ->label('تصدير Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(function () {
                    return Excel::download(new StudentsExport(), 'الطلاب-' . now()->format('Y-m-d') . '.xlsx');
                }),
            Actions\Action::make('import')
                ->label('استيراد Excel')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('gray')
                ->form([
                    \Filament\Forms\Components\FileUpload::make('file')
                        ->label('ملف Excel')
                        ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'])
                        ->required()
                        ->storeFiles(false),
                ])
                ->action(function (array $data) {
                    $file = $data['file'];
                    if ($file instanceof TemporaryUploadedFile) {
                        Excel::import(new StudentsImport(), $file->getRealPath());
                    }
                    \Filament\Notifications\Notification::make()
                        ->title('تم الاستيراد بنجاح')
                        ->success()
                        ->send();
                    $this->redirect(static::getUrl());
                }),
            Actions\CreateAction::make()->label('إضافة طالب'),
        ];
    }
}
