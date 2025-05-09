<?php

namespace App\Models\HIS;

use App\Models\SAR\SarPrint;
use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SereServExt extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his'; 
    protected $table = 'his_sere_serv_ext';
    protected $fillable = [

    ];
    public function sar_print()
    {
        return $this->belongsTo(SarPrint::class, 'description_sar_print_id');
    }
    public function machine()
    {
        return $this->belongsTo(Machine::class, 'machine_id');
    }
    public function film_size()
    {
        return $this->belongsTo(FilmSize::class, 'film_size_id');
    }
}
