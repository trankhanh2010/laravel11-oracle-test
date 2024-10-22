<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Scopes\IsDeleteScope;
use Illuminate\Support\Facades\Cache;

class MediStock extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his';
    protected $table = 'his_medi_stock';
    protected $guarded = [
        'id',
    ];
    public $timestamps = false;
    protected $appends = [
        'patient_classifys',
    ];
    /// Chạy Scope để thêm điều kiện is_delete = 0 hoặc null
    // protected static function booted()
    // {
    //     static::addGlobalScope(new IsDeleteScope);
    // }

    // /// Lấy ra bản ghi đã xóa mềm is_delete = 1
    // public static function withDeleted()
    // {
    //     return with(new static)->newQueryWithoutScope(new IsDeleteScope)->where('is_delete', 1);
    // }
    public function getPatientClassifysAttribute()
    {
        if($this->patient_classify_ids != null){
            return Cache::remember('patient_classify_ids_' . $this->patient_classify_ids, $this->time, function ()  {
                $data = PatientClassify::select(['patient_Classify_Code', 'patient_Classify_Name'])->whereIn('id', explode(',', $this->patient_classify_ids))->get();
                return $data;
        });
        }
        return null;

    }
    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function parent()
    {
        return $this->belongsTo(MediStock::class, 'parent_id');
    }

    public function patient_types()
    {
        return $this->belongsToMany(PatientType::class, MestPatientType::class, 'medi_stock_id', 'patient_type_id');
    }

    public function medicine_types()
    {
        return $this->belongsToMany(MedicineType::class, MediStockMety::class, 'medi_stock_id', 'medicine_type_id')
        ->withPivot('exp_medi_stock_id');
    }

    public function material_types()
    {
        return $this->belongsToMany(MaterialType::class, MediStockMaty::class, 'medi_stock_id', 'material_type_id');
    }

    public function rooms()
    {
        return $this->belongsToMany(Room::class, MestRoom::class, 'medi_stock_id', 'room_id');
    }

    public function department($id)
    {
        $department = DB::connection('oracle_his')->table('his_medi_stock')
            ->join('his_room', 'his_medi_stock.room_id', '=', 'his_room.id')
            ->join('his_department', 'his_room.department_id', '=', 'his_department.id')
            ->select('his_department.*')
            ->where('his_medi_stock.id', $id)
            ->first();
        return $department;
    }

    public function room_type($id)
    {
        $room_type = DB::connection('oracle_his')->table('his_medi_stock')
            ->join('his_room', 'his_medi_stock.room_id', '=', 'his_room.id')
            ->join('his_room_type', 'his_room.room_type_id', '=', 'his_room_type.id')
            ->select('his_room_type.*')
            ->where('his_medi_stock.id', $id)
            ->first();
        return $room_type;
    }

    public function exp_mest_types()
    {
        return $this->belongsToMany(ExpMestType::class, MediStockExty::class)
        ->withPivot('is_auto_approve', 'is_auto_execute', 'id');
    }
    public function imp_mest_types()
    {
        return $this->belongsToMany(ImpMestType::class, MediStockImty::class)
        ->withPivot('is_auto_approve', 'is_auto_execute', 'id');
    }
}
