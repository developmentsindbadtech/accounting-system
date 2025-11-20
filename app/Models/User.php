<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'azure_id',
    ];

    protected $attributes = [
        'tenant_id' => 1,
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

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
            'is_active' => 'boolean',
        ];
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isAccountant(): bool
    {
        return $this->role === 'accountant' || $this->isAdmin();
    }

    public function canEdit(): bool
    {
        return $this->isAdmin() || $this->isAccountant();
    }

    public function isViewer(): bool
    {
        return $this->role === 'viewer';
    }

    public function canDelete(): bool
    {
        return $this->isAdmin();
    }

    public function canDeleteLog(): bool
    {
        return $this->isAdmin();
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    /**
     * Get Microsoft profile picture URL
     */
    public function getProfilePictureUrl(): string
    {
        // Use Azure AD user ID if available, otherwise use user ID
        $id = $this->azure_id ?: $this->id;
        return route('user.profile-picture', ['id' => $id]);
    }

    /**
     * Get initials for avatar fallback
     */
    public function getInitialsAvatar(): string
    {
        $initials = strtoupper(substr($this->name, 0, 1));
        if (strlen($this->name) > 1) {
            $parts = explode(' ', $this->name);
            if (count($parts) > 1) {
                $initials = strtoupper(substr($parts[0], 0, 1) . substr($parts[count($parts) - 1], 0, 1));
            }
        }
        return 'data:image/svg+xml;base64,' . base64_encode('<svg width="40" height="40" xmlns="http://www.w3.org/2000/svg"><rect width="40" height="40" fill="#4F46E5"/><text x="50%" y="50%" font-family="Arial" font-size="16" fill="white" text-anchor="middle" dominant-baseline="central">' . htmlspecialchars($initials) . '</text></svg>');
    }
}
