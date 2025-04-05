<?php

namespace App\Models\View;

use App\Models\HIS\ActiveIngredient;
use App\Models\HIS\DebateEkipUser;
use App\Models\HIS\DebateInviteUser;
use App\Models\HIS\MedicineType;
use App\Models\HIS\Tracking;
use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DebateDetailVView extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his'; 
    protected $table = 'xa_v_his_debate_detail';
    public $timestamps = false;
    protected $guarded = [
        'id',
    ];
    public function getMedicineTypeIdsAttribute($value)
    {
        if($value != null){
                return MedicineType::
                select('id', 'medicine_type_code', 'medicine_type_name')
                ->whereIn('id', explode(',', $value))->get();
        }else{
            return $value;
        }
    }
    public function getActiveIngredientIdsAttribute($value)
    {
        if($value != null){
                return ActiveIngredient::
                select('id', 'active_ingredient_code', 'active_ingredient_name')
                ->whereIn('id', explode(',', $value))->get();
        }else{
            return $value;
        }
    }

    public function debate_ekip_users()
    {
        return $this->hasMany(DebateEkipUser::class, 'debate_id', 'id');
    }
    public function debate_invite_users()
    {
        return $this->hasMany(DebateInviteUser::class, 'debate_id', 'id');
    }
    public function tracking()
    {
        return $this->belongsTo(Tracking::class);
    }

}
