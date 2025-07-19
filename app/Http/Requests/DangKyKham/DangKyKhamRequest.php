<?php

namespace App\Http\Requests\DangKyKham;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DangKyKhamRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        $ngayHienTaiCong1 = Carbon::now()->addDay()->format('YmdHis'); // VD: 20250718142000
        $ngayHienTaiTru1 = Carbon::now()->subDay()->format('YmdHis');
        $minDob = '19000101000000'; // Giới hạn kỹ thuật tối thiểu

        return [
            'hoVaTen' =>      'required|string|max:100',
            'genderId' =>                  [
                'required',
                'integer',
                Rule::exists('App\Models\HIS\Gender', 'id')
                    ->where(function ($query) {
                        $query = $query
                            ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                    }),
            ],
            'dob' => [
                'required',
                'integer',
                'regex:/^\d{14}$/',
                'lte:' . $ngayHienTaiCong1,
                'gte:' . $minDob,
            ],
            'nationalId' =>                  [
                'nullable',
                'integer',
                Rule::exists('App\Models\SDA\National', 'id')
                    ->where(function ($query) {
                        $query = $query
                            ->where(DB::connection('oracle_sda')->raw("is_active"), 1);
                    }),
            ],
            'ethnicId' =>                  [
                'nullable',
                'integer',
                Rule::exists('App\Models\SDA\Ethnic', 'id')
                    ->where(function ($query) {
                        $query = $query
                            ->where(DB::connection('oracle_sda')->raw("is_active"), 1);
                    }),
            ],
            'provinceId' =>                  [
                'nullable',
                'integer',
                Rule::exists('App\Models\SDA\Province', 'id')
                    ->where(function ($query) {
                        $query = $query
                            ->where(DB::connection('oracle_sda')->raw("is_active"), 1);
                    }),
            ],
            'communeId' =>                  [
                'nullable',
                'integer',
                Rule::exists('App\Models\SDA\Commune', 'id')
                    ->where(function ($query) {
                        $query = $query
                            ->where(DB::connection('oracle_sda')->raw("is_active"), 1);
                    }),
            ],
            'address' =>      'nullable|string|max:200',
            'htAddress' =>      'nullable|string|max:200',
            'phone' =>      'required|string|max:20',
            'relativeType' =>      'nullable|string|max:50',
            'relativeName' =>      'required|string|max:100',
            'relativeAddress' =>      'nullable|string|max:200',
            'relativePhone' =>      'required|string|max:12',
            'careerId' =>                  [
                'required',
                'integer',
                Rule::exists('App\Models\HIS\Career', 'id')
                    ->where(function ($query) {
                        $query = $query
                            ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                    }),
            ],
            'workPlace' =>      'nullable|string|max:500',
            'cccdNumber' =>     [
                'required',
                'regex:/^\d{12}$/',
                Rule::unique('HIS_PATIENT', 'CCCD_NUMBER'),
            ],
            'cccdDate' => [
                'nullable',
                'integer',
                'regex:/^\d{14}$/',
                'lte:' . $ngayHienTaiCong1,
                'gte:' . $minDob,
            ],
            'cccdPlace' =>      'nullable|string|max:100',
            'motherName' =>      'nullable|string|max:100',
            'fatherName' =>      'nullable|string|max:100',
            'taxCode' =>      'nullable|string|max:20',
            'thoiGianYeuCauKhac' => [
                'required',
                'integer',
                'regex:/^\d{14}$/',
                'gte:' . $ngayHienTaiTru1,
            ],

            'serviceReqDetails' => 'nullable|array',
            'serviceReqDetails.*.serviceId' => [
                'required',
                'integer',
                Rule::exists('App\Models\HIS\Service', 'id')
                    ->where(function ($query) {
                        $query = $query
                            ->where(DB::connection('oracle_his')->raw("is_active"), 1)
                            ->where(DB::connection('oracle_his')->raw("is_delete"), 0);
                    }),
            ],
            'serviceReqDetails.*.roomId' => [
                'required',
                'integer',
                Rule::exists('App\Models\View\RoomVView', 'id')
                    ->where(function ($query) {
                        $query = $query
                            ->where(DB::connection('oracle_his')->raw("is_active"), 1)
                            ->where(DB::connection('oracle_his')->raw("is_delete"), 0);
                    }),
            ],
        ];
    }
    public function messages()
    {
        return [
            'hoVaTen.required'    => 'Họ và tên' . config('keywords')['error']['required'],
            'hoVaTen.string'      => 'Họ và tên' . config('keywords')['error']['string'],
            'hoVaTen.max'         => 'Họ và tên' . config('keywords')['error']['string_max'],

            'genderId.required'    => 'Giới tính' . config('keywords')['error']['required'],
            'genderId.integer'     => 'Id giới tính' . config('keywords')['error']['integer'],
            'genderId.exists'      => 'Trường Id giới tính' . config('keywords')['error']['exists'],

            'dob.required'    => 'Ngày sinh' . config('keywords')['error']['required'],
            'dob.integer'            => 'Ngày sinh phải được định dạng lại sang số!',
            'dob.regex'              => 'Ngày sinh' . config('keywords')['error']['regex_ymdhis'],
            'dob.gte'                => 'Ngày sinh không hợp lệ!',
            'dob.lte'                => 'Ngày sinh không hợp lệ!',

            'nationalId.integer'     => 'Id quốc tịch' . config('keywords')['error']['integer'],
            'nationalId.exists'      => 'Trường Id quốc tịch' . config('keywords')['error']['exists'],

            'ethnicId.integer'     => 'Id dân tộc' . config('keywords')['error']['integer'],
            'ethnicId.exists'      => 'Trường Id dân tộc' . config('keywords')['error']['exists'],

            'provinceId.integer'     => 'Id tỉnh' . config('keywords')['error']['integer'],
            'provinceId.exists'      => 'Trường Id tỉnh' . config('keywords')['error']['exists'],

            'communeId.integer'     => 'Id xã' . config('keywords')['error']['integer'],
            'communeId.exists'      => 'Trường Id xã' . config('keywords')['error']['exists'],

            'address.string'      => 'Địa chỉ liên hệ' . config('keywords')['error']['string'],
            'address.max'         => 'Địa chỉ liên hệ' . config('keywords')['error']['string_max'],

            'htAddress.string'      => 'Địa chỉ hiện tại' . config('keywords')['error']['string'],
            'htAddress.max'         => 'Địa chỉ hiện tại' . config('keywords')['error']['string_max'],

            'phone.required'    => 'Số điện thoại liên hệ ' . config('keywords')['error']['required'],
            'phone.string'      => 'Số điện thoại liên hệ không hợp lệ!',
            'phone.max'         => 'Số điện thoại liên hệ không hợp lệ!',

            'relativeType.string'      => 'Quan hệ của người nhà với người đăng ký khám' . config('keywords')['error']['string'],
            'relativeType.max'         => 'Quan hệ của người nhà với người đăng ký khám' . config('keywords')['error']['string_max'],

            'relativeName.required'    => 'Tên người nhà' . config('keywords')['error']['required'],
            'relativeName.string'      => 'Tên người nhà' . config('keywords')['error']['string'],
            'relativeName.max'         => 'Tên người nhà' . config('keywords')['error']['string_max'],

            'relativeAddress.string'      => 'Địa chỉ liên hệ với người nhà' . config('keywords')['error']['string'],
            'relativeAddress.max'         => 'Địa chỉ liên hệ với người nhà' . config('keywords')['error']['string_max'],

            'relativePhone.required'    => 'SĐT liên hệ với người nhà' . config('keywords')['error']['required'],
            'relativePhone.string'      => 'SĐT liên hệ với người nhà' . config('keywords')['error']['string'],
            'relativePhone.max'         => 'SĐT liên hệ với người nhà' . config('keywords')['error']['string_max'],

            'careerId.required'    => 'Nghề nghiệp' . config('keywords')['error']['required'],
            'careerId.integer'     => 'Id nghề nghiệp' . config('keywords')['error']['integer'],
            'careerId.exists'      => 'Trường Id nghề nghiệp' . config('keywords')['error']['exists'],

            'workPlace.string'      => 'Nơi làm việc' . config('keywords')['error']['string'],
            'workPlace.max'         => 'Nơi làm việc' . config('keywords')['error']['string_max'],

            'cccdNumber.required'    => 'Số CCCD' . config('keywords')['error']['required'],
            'cccdNumber.regex'       => 'Số CCCD không hợp lệ, phải là 12 số!',
            'cccdNumber.unique'      => 'Đã tồn tại hồ sơ khám chữa bệnh với số CCCD này!',

            'cccdDate.integer'            => 'Ngày cấp CCCD phải được định dạng lại sang số!',
            'cccdDate.regex'              => 'Ngày cấp CCCD' . config('keywords')['error']['regex_ymdhis'],
            'cccdDate.gte'                => 'Ngày cấp CCCD không hợp lệ!',
            'cccdDate.lte'                => 'Ngày cấp CCCD không hợp lệ!',

            'cccdPlace.string'      => 'Nơi cấp CCCD' . config('keywords')['error']['string'],
            'cccdPlace.max'         => 'Nơi cấp CCCD' . config('keywords')['error']['string_max'],

            'motherName.string'      => 'Họ tên mẹ' . config('keywords')['error']['string'],
            'motherName.max'         => 'Họ tên mẹ' . config('keywords')['error']['string_max'],

            'fatherName.string'      => 'Họ tên cha' . config('keywords')['error']['string'],
            'fatherName.max'         => 'Họ tên cha' . config('keywords')['error']['string_max'],

            'taxCode.string'      => 'Mã số thuế' . config('keywords')['error']['string'],
            'taxCode.max'         => 'Mã số thuế' . config('keywords')['error']['string_max'],

            'thoiGianYeuCauKhac.required'    => 'Thời gian yêu cầu khám' . config('keywords')['error']['required'],
            'thoiGianYeuCauKhac.integer'            => 'Thời gian yêu cầu khám phải được định dạng lại sang số!',
            'thoiGianYeuCauKhac.regex'              => 'Thời gian yêu cầu khám' . config('keywords')['error']['regex_ymdhis'],
            'thoiGianYeuCauKhac.gte'                => 'Thời gian yêu cầu khám không hợp lệ, không thể là ngày trong quá khứ!',

            'serviceReqDetails.array' => 'Danh sách các dịch vụ công khám phải là mảng!',

            'serviceReqDetails.*.serviceId.required'     => 'Id dịch vụ công khám' . config('keywords')['error']['required'],
            'serviceReqDetails.*.serviceId.integer'    => 'Id dịch vụ công khám' . config('keywords')['error']['integer'],
            'serviceReqDetails.*.serviceId.exists'     => 'Dịch vụ công khám' . config('keywords')['error']['exists'],

            'serviceReqDetails.*.roomId.required'     => 'Id phòng thực hiện' . config('keywords')['error']['required'],
            'serviceReqDetails.*.roomId.integer'    => 'Id phòng thực hiện' . config('keywords')['error']['integer'],
            'serviceReqDetails.*.roomId.exists'     => 'Phòng thực hiện' . config('keywords')['error']['exists'],
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success'   => false,
            'message'   => 'Dữ liệu không hợp lệ!',
            'data'      => $validator->errors()
        ], 422));
    }
}
