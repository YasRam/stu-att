<?php

namespace App\Models\Concerns;

use App\Models\ActivityLog;
use Illuminate\Support\Arr;

trait LogsActivity
{
    protected static array $activityLogHidden = ['password', 'remember_token'];

    public static function bootLogsActivity(): void
    {
        static::created(function (self $model): void {
            $model->logActivity('created', null, $model->getAttributesForLog());
        });

        static::updated(function (self $model): void {
            $model->logActivity('updated', $model->getOriginal(), $model->getAttributesForLog());
        });

        static::deleted(function (self $model): void {
            $model->logActivity('deleted', $model->getAttributesForLog(), null);
        });
    }

    protected function logActivity(string $action, ?array $oldValues, ?array $newValues): void
    {
        $oldValues = $oldValues ? $this->filterAttributesForLog($oldValues) : null;
        $newValues = $newValues ? $this->filterAttributesForLog($newValues) : null;

        ActivityLog::query()->create([
            'action' => $action,
            'subject_type' => $this->getMorphClass(),
            'subject_id' => $this->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'description' => $this->getActivityDescription($action, $oldValues, $newValues),
            'user_id' => auth()->id(),
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);
    }

    protected function getAttributesForLog(): array
    {
        return $this->getAttributes();
    }

    protected function filterAttributesForLog(array $attributes): array
    {
        return Arr::except($attributes, array_merge(
            static::$activityLogHidden,
            $this->getHidden()
        ));
    }

    protected function getActivityDescription(string $action, ?array $oldValues, ?array $newValues): string
    {
        $label = $this->getActivitySubjectLabel();
        return match ($action) {
            'created' => "تم إنشاء: {$label}",
            'updated' => "تم تعديل: {$label}",
            'deleted' => "تم حذف: {$label}",
            default => $action,
        };
    }

    protected function getActivitySubjectLabel(): string
    {
        if (method_exists($this, 'getActivityLabel')) {
            return (string) $this->getActivityLabel();
        }
        $name = $this->getAttribute('full_name') ?? $this->getAttribute('name') ?? $this->getAttribute('title');
        if ($name !== null) {
            return (string) $name;
        }
        return class_basename($this) . ' #' . $this->getKey();
    }
}
