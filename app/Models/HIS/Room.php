<?php

namespace App\Models\HIS;
use App\Scopes\IsDeleteScope;
use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
class Room extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his'; // Kết nối CSDL mặc định
    protected $table = 'his_room';
    // Đặt thuộc tính $timestamps thành false để tắt tự động thêm created_at và updated_at
    public $timestamps = false;
    protected $guarded = [
        'id',
    ];
    // protected static function booted()
    // {
    //     static::addGlobalScope(new IsDeleteScope);
    // }

    // /// Lấy ra bản ghi đã xóa mềm is_delete = 1
    // public static function withDeleted()
    // {
    //     return with(new static)->newQueryWithoutScope(new IsDeleteScope)->where('is_delete', 1);
    // }
    public function getDefaultDrugStoreIdsAttribute($value)
    {
        if($value != null){
             // Tạo Cache để tránh trùng lặp truy vấn
             return Cache::remember('default_drug_store_ids'.$value, $this->time, function () use ($value) {
                return MediStock::
                select('id', 'medi_stock_code', 'medi_stock_name')
                ->whereIn('id', explode(',', $value))->get();
            });        
        }else{
            return $value;
        }
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    // public function default_drug_stores()
    // {
    //     return MediStock::whereIn('id', explode(',', $this->default_drug_store_ids))->get();
    // }

    public function deposit_account_book()
    {
        return $this->belongsTo(AccountBook::class, 'deposit_account_book_id');
    }

    public function bill_account_book()
    {
        return $this->belongsTo(AccountBook::class, 'bill_account_book_id');
    }

    public function default_cashier_room()
    {
        return $this->belongsTo(CashierRoom::class, 'default_cashier_room_id');
    }

    public function default_instr_patient_type()
    {
        return $this->belongsTo(PatientType::class, 'default_instr_patient_type_id');
    }

    public function default_service()
    {
        return $this->belongsTo(Service::class, 'default_service_id');
    }

    public function room_type()
    {
        return $this->belongsTo(RoomType::class);
    }

    public function speciality()
    {
        return $this->belongsTo(Speciality::class);
    }

    public function bed_room()
    {
        return $this->hasOne(BedRoom::class);
    }

    public function execute_room()
    {
        return $this->hasOne(ExecuteRoom::class, 'room_id', 'id');
    }

    public function medi_stocks()
    {
        return $this->belongsToMany(MediStock::class, MestRoom::class, 'room_id', 'medi_stock_id');
    }

    public function execute_rooms()
    {
        return $this->belongsToMany(ExecuteRoom::class, ExroRoom::class, 'room_id', 'execute_room_id')
            ->withPivot('is_hold_order', 'is_allow_request', 'is_priority_require');
    }

    public function patient_types()
    {
        return $this->belongsToMany(PatientType::class, PatientTypeRoom::class, 'room_id', 'patient_type_id');
    }
    public function room_group()
    {
        return $this->belongsTo(RoomGroup::class);
    }
    public function area()
    {
        return $this->belongsTo(Area::class);
    }
    public function services()
    {
        return $this->belongsToMany(Service::class, ServiceRoom::class, 'room_id', 'service_id');
    }
}
