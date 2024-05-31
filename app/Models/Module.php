<?php

namespace App\Models;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_acs'; 
    protected $table = 'ACS_MODULE';

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'acs_module_role', 'module_id', 'role_id');
    }
}
