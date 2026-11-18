<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $table = 'roles';

    public const ADMIN = 'Admin / Kepala Puskesmas';
    public const RESIDENT = 'Warga / Orang Tua';
    public const SYSTEM_NAMES = [self::ADMIN, 'Nakes / Bidan', 'Kader Posyandu', self::RESIDENT];

    protected $fillable = [
        'name_role',
        'description',
    ];

    public function isSystem(): bool
    {
        return in_array($this->name_role, self::SYSTEM_NAMES, true);
    }

    // Relasi: Role hasMany User
    public function users()
    {
        return $this->hasMany(User::class, 'role_id');
    }

    // Relasi: Role hasMany Permission
    public function permissions()
    {
        return $this->hasMany(Permission::class, 'role_id');
    }
}
