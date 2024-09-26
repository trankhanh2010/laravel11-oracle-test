<?php

namespace App\Http\Controllers\BaseControllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\ServicePaty\CreateServicePatyRequest;
use Illuminate\Http\Request;

class BaseApiRequestController extends Controller
{
    public function getAllRequestName(Request $request){
        return config('keywords');
    }
}
