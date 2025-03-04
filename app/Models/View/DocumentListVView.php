<?php

namespace App\Models\View;

use App\Models\EMR\Sign;
use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentListVView extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_emr'; 
    protected $table = 'v_emr_document_list';
    public $timestamps = false;
    protected $guarded = [
        'id',
    ];
    public function signs()
    {
        return $this->hasMany(Sign::class, 'document_id', 'id');
    }
}
