<?php

namespace App\Models;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_acs'; 
    protected $table = 'ACS_ROLE';

    public function users()
    {
        return $this->belongsToMany(User::class, 'acs_role_user', 'role_id', 'user_id');
    }

    public function modules()
    {
        return $this->belongsToMany(Module::class, 'acs_module_role', 'role_id', 'module_id');
    }
}
