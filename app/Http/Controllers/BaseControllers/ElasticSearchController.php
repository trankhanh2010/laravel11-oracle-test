<?php

namespace App\Http\Controllers\BaseControllers;

use App\Events\Cache\DeleteCache;
use App\Http\Controllers\Controller;
use App\Http\Resources\Elastic\ElasticMappingResource;
use App\Http\Resources\Elastic\ElasticResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class ElasticSearchController extends Controller
{
    protected $client;
    protected $all_table;

    public function __construct()
    {
        $this->client = app('Elasticsearch');
        $this->all_table = config('params')['elastic']['all_table'];
    }
    public function get_all_name(Request $request)
    {
        // Chỉ trả về key
        $data = array_keys($this->all_table);
        return returnDataSuccess([], $data);
    }
    public function index_records_to_elasticsearch(Request $request)
    {
        // Tăng thời gian chờ lên 600 giây (10 phút)
        set_time_limit(600);
        // Lấy tham số từ request (nếu có)
        $table = $request->input('table', 'all'); // Mặc định là 'all'

        // Gọi command với Artisan::call
        Artisan::call('app:index-records-to-elasticsearch', [
            '--table' => $table
        ]);

        // Lấy kết quả từ command (nếu cần)
        $output = Artisan::output();

        return response()->json([
            'status'    => 200,
            'message' => 'Xong!',
            'output' => $output,
        ], 200);
    }

    public function delete_index(Request $request)
    {
        $table = $this->all_table;

        $tables = explode(",", $request->tables);
        if ($request->tables == null) {
            $tables = $table;
        }
        foreach ($tables as $key => $item) {
            if (!in_array($item, $table)) {
                return response()->json([
                    'status'    => 422,
                    'success' => true,
                    'message' => 'Giá trị ' . $item . ' không hợp lệ!'
                ], 422);
            }
        }
        if ($tables != null) {
            foreach ($tables as $key => $item) {
                $first_table = strtolower(explode('_', $item)[0]);
                $name_table = strtolower(substr($item, strlen($first_table . '_')));
                $exists = $this->client->indices()->exists(['index' => $name_table])->asBool();
                if ($exists) {
                    $params = ['index' => $name_table];
                    event(new DeleteCache($name_table));
                    $this->client->indices()->delete($params);
                }
            }
            return response()->json([
                'status'    => 200,
                'success' => true,
                'message' => 'Xong!'
            ], 200);
        }
    }
    public function get_mapping(Request $request)
    {
        $params = [
            'index' => $request->index,
        ];
        $response = new ElasticMappingResource($this->client->indices()->getMapping($params)[$request->index]);

        return returnDataSuccess([], $response);
    }
    public function get_index_settings(Request $request)
    {
        $index = $request->index;
        $detail = $request->detail;
        $params = [
            'index' => $index
        ];

        $response = $this->client->indices()->get($params);
        switch ($detail) {
            case 'stop_filter':
                $response = $response[$index]['settings']['index']['analysis']['filter']['my_stop_filter'];
                break;

            default:
                // Xử lý mặc định hoặc xử lý khi không có bảng khớp
                $response = [];
                break;
        }
        return returnDataSuccess([], $response);
    }
}
