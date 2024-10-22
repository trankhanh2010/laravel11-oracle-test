<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his';
    protected $table = 'his_employee';
    public $timestamps = false;
    protected $guarded = [
        'id',
    ];
    // protected $hidden = [
    //     'erx_password'
    // ];
    public function getErxPasswordAttribute($value)
    {
        return '******';
    }
    public function department()
    {
        return $this->belongsTo(Department::class,'department_id');
    }

    public function default_medi_stocks()
    {
        return MediStock::whereIn('id', explode(',', $this->default_medi_stock_idsart_ids))->get();
    }

    public function gender()
    {
        return $this->belongsTo(Gender::class,'gender_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class,'branch_id');
    }

    public function career_title()
    {
        return $this->belongsTo(CareerTitle::class,'career_title_id');
    }

    public function execute_roles()
    {
        return $this->belongsToMany(ExecuteRole::class, ExecuteRoleUser::class, 'loginname', 'execute_role_id','loginname','id');
    }

}
