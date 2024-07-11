<?php

namespace App\Http\Controllers\Api\ValidateControllers;

use App\Http\Controllers\BaseControllers\BaseValidateController;
use App\Http\Controllers\Controller;
use App\Models\HIS\BodyPart;
use Illuminate\Http\Request;

class CheckBodyPartController extends BaseValidateController
{
    public function __construct(Request $request){
        parent::__construct($request); // Gọi constructor của BaseController
        $this->body_part = new BodyPart();
    }
    public function check_code(Request $request){
        $code = $request->code;
        $id = $request->id;

        if($code != null){
            $exists = $this->body_part::where('body_part_code', $code);
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
