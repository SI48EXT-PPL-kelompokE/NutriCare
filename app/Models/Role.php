<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $table = 'roles';

    protected $fillable = [
        'name_role',
    ];

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
