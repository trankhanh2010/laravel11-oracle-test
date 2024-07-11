<?php

namespace App\Http\Controllers\Api\ValidateControllers;

use App\Http\Controllers\BaseControllers\BaseValidateController;
use App\Models\HIS\Speciality;
use Illuminate\Http\Request;

class CheckSpecialityController extends BaseValidateController
{
    public function __construct(Request $request){
        parent::__construct($request); // Gọi constructor của BaseController
        $this->speciality = new Speciality();
    }
    public function check_code(Request $request){
        $code = $request->code;
        $id = $request->id;

        if($code != null){
            $exists = $this->speciality::where('speciality_code', $code);
            if ($id) {
                if (!is_numeric($id)) {
                    return return_id_error($id);
                }
                $exists->where('id', '!=', $id);
            }
            $exists = $exists->exists();
            $param_return = [
                'code' => $code
            ];
            return return_check_data($param_return, !$exists);        
        }
    }
}
