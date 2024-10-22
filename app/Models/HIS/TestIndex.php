<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestIndex extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his';
    protected $table = 'his_test_index';
    public $timestamps = false;
    protected $guarded = [
        'id',
    ];

    public function test_service_type()
    {
        return $this->belongsTo(Service::class, 'test_service_type_id');
    }

    public function test_index_unit()
    {
        return $this->belongsTo(TestIndexUnit::class, 'test_index_unit_id');
    }

    public function test_index_group()
    {
        return $this->belongsTo(TestIndexGroup::class, 'test_index_group_id');
    }

    public function material_type()
    {
        return $this->belongsTo(MaterialType::class, 'material_type_id');
    }
}
