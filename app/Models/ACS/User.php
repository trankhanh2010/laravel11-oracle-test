<?php

namespace App\Models\ACS;

use App\Models\HIS\ExecuteRole;
use App\Models\HIS\ExecuteRoleUser;
use App\Traits\dinh_dang_ten_truong;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
class User extends Authenticatable
{
    use HasFactory, Notifiable, dinh_dang_ten_truong;
    protected $connection = 'oracle_acs'; // Kết nối CSDL khác
    protected $table = 'ACS_USER';

    protected $fillable = [
        'loginname',
        'username',
        'phone',
        'email',
    ];
     /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'create_time',
        'modify_time',
        'creator',
        'modifier',
        'password',
        'app_creator',
        'app_modifier',
        'is_active',
        'is_delete',
        'roles',
    ];
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'ACS_ROLE_USER','user_id', 'role_id');
    }
    public function modules()
    {
        return $this->roles()
            ->join('acs_module_role', 'acs_role.id', '=', 'acs_module_role.role_id')
            ->join('acs_module', 'acs_module_role.module_id', '=', 'acs_module.id')
            ->select('acs_module.id', 'acs_module.*')
            ->distinct();
    }
    public function hasModule($module)
    {
        return $this->modules()->where('acs_module.module_link', $module)->exists();
    }
    public function checkSuperAdmin()
    {
        $spAdmin = 0;
        $data =$this->belongsToMany(Role::class, 'ACS_ROLE_USER','user_id', 'role_id')->get();
        foreach($data as $key => $item){
            if($item->is_full){
                $spAdmin = 1;
            }
        }
        return $spAdmin;
    }
    
}
