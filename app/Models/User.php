<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Models\Concerns\LogsActivity;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser, HasAvatar
{
    use HasFactory, LogsActivity, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'avatar',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return in_array($this->role, [UserRole::Admin, UserRole::Teacher], true);
    }

    public function getFilamentAvatarUrl(): ?string
    {
        if ($this->avatar) {
            return Storage::disk('public')->url($this->avatar);
        }
        return null;
    }

    public function dailySessions(): HasMany
    {
        return $this->hasMany(DailySession::class, 'teacher_id');
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    /**
     * Display name translated when a lang key exists (e.g. "System Administrator" → "مدير النظام").
     */
    public function getNameAttribute(?string $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }
        // Backward compatibility: old seeded Arabic names → translate via key
        $key = match ($value) {
            'مدير النظام' => 'System Administrator',
            'معلم تجريبي' => 'Demo Teacher',
            default => $value,
        };
        $translated = __($key);
        // If we mapped old Arabic to key, always use translation; else use translation only when it exists
        if ($key !== $value) {
            return $translated;
        }
        return $translated !== $key ? $translated : $value;
    }
}
