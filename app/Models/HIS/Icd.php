<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Icd extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his'; 
    protected $table = 'HIS_ICD';
    protected $fillable = [
    ];

    public function icd_group()
    {
        return $this->belongsTo(IcdGroup::class, 'icd_group_id');
    }

    public function icd_chapter()
    {
        return $this->belongsTo(Icd::class, 'icd_chapter_id');
    }

    public function gender()
    {
        return $this->belongsTo(Gender::class, 'gender_id');
    }

    public function age_type()
    {
        return $this->belongsTo(AgeType::class, 'age_type_id');
    }
}
