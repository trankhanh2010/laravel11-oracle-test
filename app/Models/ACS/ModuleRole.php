<?php

namespace App\Models\ACS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModuleRole extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_acs'; 
    protected $table = 'ACS_MODULE_ROLE';
    protected $fillable = [
        'module_id',
        'role_id',
    ];
    public function module()
    {
        return $this->belongsTo(Module::class, 'module_id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

}
