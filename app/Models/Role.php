<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $table = 'roles';

    protected $fillable = [
        'name',
    ];

    protected function casts(): array
    {
        return [];
    }

    public function users()
    {
        return $this->hasMany(User::class, 'role_id');
    }
}
