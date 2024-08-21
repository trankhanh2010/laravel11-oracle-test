<?php

namespace App\Http\Controllers\BaseControllers;

use App\Http\Controllers\Controller;
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
    public function index_records_to_elasticsearch(Request $request)
    {
        // Lấy tham số từ request (nếu có)
        $table = $request->input('table', 'all'); // Mặc định là 'all'

        // Gọi command với Artisan::call
        Artisan::call('app:index-records-to-elasticsearch', [
            '--table' => $table
        ]);

        // Lấy kết quả từ command (nếu cần)
        $output = Artisan::output();

        return response()->json([
            'message' => 'Xong!',
            'output' => $output,
        ], 200);
    }

    public function delete_index(Request $request)
    {
        $table = $this->all_table;

        $tables = explode(",", $request->tables);
        if($request->tables == null){
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
        try {
            if ($tables != null) {
                foreach ($tables as $key => $item) {
                    $first_table = strtolower(explode('_', $item)[0]);
                    $name_table = strtolower(substr($item, strlen($first_table . '_')));
                    $exists = $this->client->indices()->exists(['index' => $name_table])->asBool();
                    if ($exists) {
                        $params = ['index' => $name_table];
                        $this->client->indices()->delete($params);
                    } 
                }
                return response()->json([
                    'status'    => 200,
                    'success' => true,
                    'message' => 'Xong!'
                ], 200);
            }
        } catch (\Exception $e) {
            return return_500_error();
        }
    }
}
