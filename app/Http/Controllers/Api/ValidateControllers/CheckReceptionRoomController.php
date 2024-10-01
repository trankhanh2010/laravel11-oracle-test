<?php

namespace App\Http\Controllers\Api\ValidateControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Models\HIS\ReceptionRoom;
use Illuminate\Http\Request;

class CheckReceptionRoomController extends BaseApiCacheController
{
    public function __construct(Request $request){
        parent::__construct($request); // Gọi constructor của BaseController
        $this->receptionRoom = new ReceptionRoom();
    }
    public function checkCode(Request $request){
        $code = $request->code;
        $id = $request->id;

        if($code != null){
            $exists = $this->receptionRoom::where('reception_room_code', $code);
            if ($id) {
                if (!is_numeric($id)) {
                    return returnIdError($id);
                }
                $exists->where('id', '!=', $id);
            }
            $exists = $exists->exists();
            $paramReturn = [
                'code' => $code
            ];
            return returnCheckData($paramReturn, !$exists);        
        }
    }
}
