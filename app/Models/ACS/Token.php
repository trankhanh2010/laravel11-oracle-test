<?php

namespace App\Models\ACS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Token extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_acs'; // Kết nối CSDL khác
    // protected $connection = 'oracle'; // Kết nối CSDL mặc định
    protected $table = 'acs_token';


}
