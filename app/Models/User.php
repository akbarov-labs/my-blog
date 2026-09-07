<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Filament's Authenticate middleware aborts with 403 for any user model
     * that does not implement FilamentUser whenever APP_ENV is not "local".
     * That is why the admin panel opens fine in local dev but returned 403 in
     * production — implementing this contract is what grants panel access.
     *
     * Returning true is safe here: this app has no public registration route
     * and the panel has no ->registration(), so the only accounts that can
     * exist are ones created by hand with `php artisan make:filament-user`.
     * To tighten this later, gate on an email allowlist or an is_admin column.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
