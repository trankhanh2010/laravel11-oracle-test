<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his'; // Kết nối CSDL mặc định
    protected $table = 'HIS_ROOM';
    protected $fillable = [
        'id',
        'default_cashier_room_id',
        'default_instr_patient_type_id',
        'default_service_id',
        'default_drug_store_ids',
        'deposit_account_book_id',
        'bill_account_book_id',
        'room_type_id',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function default_drug_stores()
    {
        return MediStock::whereIn('id', explode(',', $this->default_drug_store_ids))->get();
    }

    public function deposit_account_book()
    {
        return $this->belongsTo(AccountBook::class,'deposit_account_book_id');
    }

    public function bill_account_book()
    {
        return $this->belongsTo(AccountBook::class,'bill_account_book_id');
    }

    public function default_cashier_room()
    {
        return $this->belongsTo(CashierRoom::class,'default_cashier_room_id');
    }   

    public function default_instr_patient_type()
    {
        return $this->belongsTo(PatientType::class,'default_instr_patient_type_id');
    }   

    public function default_service()
    {
        return $this->belongsTo(Service::class,'default_service_id');
    }   

    public function room_type()
    {
        return $this->belongsTo(RoomType::class);
    }

    public function speciality()
    {
        return $this->belongsTo(Speciality::class);
    }

    public function bedRoom()
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
}
