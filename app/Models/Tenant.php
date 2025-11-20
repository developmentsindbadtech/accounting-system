<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    protected $fillable = [
        'name',
        'domain',
        'database_name',
        'database_host',
        'database_username',
        'database_password',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function getConnectionConfig(): array
    {
        return [
            'driver' => 'pgsql',
            'host' => $this->database_host,
            'port' => env('DB_PORT', '5432'),
            'database' => $this->database_name,
            'username' => $this->database_username,
            'password' => $this->database_password,
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'public',
            'sslmode' => 'prefer',
        ];
    }
}
