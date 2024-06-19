<?php

namespace App\Models\ACS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModuleGroup extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_acs'; 
    protected $table = 'ACS_MODULE_GROUP';

    public function modules()
    {
        return $this->hasMany(Module::class);
    }

}
