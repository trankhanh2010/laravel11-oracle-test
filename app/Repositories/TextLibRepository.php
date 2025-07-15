<?php

namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\HIS\TextLib;
use Illuminate\Support\Facades\DB;

class TextLibRepository
{
    protected $textLib;
    public function __construct(TextLib $textLib)
    {
        $this->textLib = $textLib;
    }

    public function applyJoins()
    {
        return $this->textLib
            ->select(
                'his_text_lib.id as key',
                'his_text_lib.*'
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('lower(his_text_lib.hot_key)'), 'like', '%' . strtolower($keyword) . '%')
                ->orWhere(DB::connection('oracle_his')->raw('lower(his_text_lib.title)'), 'like', '%' . strtolower($keyword) . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_text_lib.is_active'), $isActive);
        }
        return $query;
    }
    public function applyIsDeleteFilter($query, $isDelete)
    {
        if ($isDelete !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_text_lib.is_delete'), $isDelete);
        }
        return $query;
    }
    public function applyHashTagsFilter($query, $param)
    {
        if ($param != null) {
            foreach ($param as $key => $item) {
                if ($item) {
                    $query->where(DB::connection('oracle_his')->raw('lower(his_text_lib.hashtag)'), 'like', '%' . strtolower($item) . '%');
                }
            }
        }
        return $query;
    }
    public function applyTabFilter($query, $param, $currentLoginname, $currentDepartmentId)
    {
        switch ($param) {
            case 'yeuCauKhamClsPttt':
                $query
                    ->where(function ($qr) use ($currentLoginname, $currentDepartmentId) {
                        $qr->where(function ($q) use ($currentLoginname) {
                            $q->where('his_text_lib.is_public', 1)
                                ->orWhere('creator', $currentLoginname);
                        })
                            ->orWhere(function ($q) use ($currentDepartmentId) {
                                $q->where('his_text_lib.department_id', $currentDepartmentId)
                                    ->where('his_text_lib.is_public_in_department', 1);
                            });
                    });
                return $query;
            default:
                return $query;
        }
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('his_text_lib.' . $key, $item);
                }
            }
        }

        return $query;
    }
    public function fetchData($query, $getAll, $start, $limit)
    {
        if ($getAll) {
            // Lấy tất cả dữ liệu
            return $query->get();
        } else {
            // Lấy dữ liệu phân trang
            return $query
                ->skip($start)
                ->take($limit)
                ->get();
        }
    }
    public function getById($id)
    {
        return $this->textLib->find($id);
    }
    public function create($request, $time, $appCreator, $appModifier, $currentDepartmentId)
    {
        $base64 = $request->content;
        $decoded = base64_decode($base64, true);

        if ($decoded === false) {
            throw new \Exception('Nội dung không hợp lệ (không phải base64)');
        }

        // Chuẩn bị kết nối thủ công
        $config = config('database.connections.oracle_his');
        $connectionString = "//{$config['host']}:{$config['port']}/{$config['service_name']}"; // THÊM service_name
        $conn = oci_connect(
            $config['username'],
            $config['password'],
            $connectionString,
            $config['charset'] ?? 'AL32UTF8'
        );

        if (!$conn) {
            $e = oci_error();
            throw new \Exception("Lỗi kết nối Oracle: " . $e['message']);
        }

        // Prepare SQL với RETURNING INTO BLOB
        $sql = "INSERT INTO HIS_TEXT_LIB (
        CREATE_TIME, MODIFY_TIME, CREATOR, MODIFIER, APP_CREATOR, APP_MODIFIER,
        IS_ACTIVE, IS_DELETE, TITLE, CONTENT, HASHTAG, HOT_KEY, IS_PUBLIC, IS_PUBLIC_IN_DEPARTMENT,
        DEPARTMENT_ID, LIB_TYPE_ID
    ) VALUES (
        :create_time, :modify_time, :creator, :modifier, :app_creator, :app_modifier,
        :is_active, :is_delete, :title, EMPTY_BLOB(), :hashtag, :hot_key, :is_public, :is_public_in_department,
        :department_id, :lib_type_id
    ) RETURNING CONTENT INTO :blob";

        $stmt = oci_parse($conn, $sql);
        if (!$stmt) {
            $e = oci_error($conn);
            throw new \Exception("Lỗi parse SQL: " . $e['message']);
        }

        // Thông tin bind
        $createTime = now()->format('YmdHis');
        $modifyTime = now()->format('YmdHis');
        $creator = get_loginname_with_token($request->bearerToken(), $time);
        $modifier = $creator;
        $title = $request->title;
        $hashtag = $request->hashtag;
        $hotKey = $request->hot_key;
        $isActive = 1;
        $isDelete = 0;
        $isPublicInDepartment = $request->is_public_in_department;
        $isPublic = $request->is_public;
        $libTypeId = 1;

        // Bind data
        oci_bind_by_name($stmt, ':create_time', $createTime);
        oci_bind_by_name($stmt, ':modify_time', $modifyTime);
        oci_bind_by_name($stmt, ':creator', $creator);
        oci_bind_by_name($stmt, ':modifier', $modifier);
        oci_bind_by_name($stmt, ':app_creator', $appCreator);
        oci_bind_by_name($stmt, ':app_modifier', $appModifier);
        oci_bind_by_name($stmt, ':is_active', $isActive);
        oci_bind_by_name($stmt, ':is_delete', $isDelete);
        oci_bind_by_name($stmt, ':title', $title);
        oci_bind_by_name($stmt, ':hashtag', $hashtag);
        oci_bind_by_name($stmt, ':hot_key', $hotKey);
        oci_bind_by_name($stmt, ':is_public', $isPublic);
        oci_bind_by_name($stmt, ':is_public_in_department', $isPublicInDepartment);
        oci_bind_by_name($stmt, ':department_id', $currentDepartmentId);
        oci_bind_by_name($stmt, ':lib_type_id', $libTypeId);

        // Chuẩn bị BLOB
        $blob = oci_new_descriptor($conn, OCI_D_LOB);
        oci_bind_by_name($stmt, ':blob', $blob, -1, OCI_B_BLOB);

        // Thực thi
        if (!oci_execute($stmt, OCI_NO_AUTO_COMMIT)) {
            $e = oci_error($stmt);
            throw new \Exception("Lỗi khi insert: " . $e['message']);
        }

        // Ghi dữ liệu vào BLOB
        if (!$blob->save($decoded)) {
            throw new \Exception("Không thể ghi dữ liệu vào BLOB");
        }

        oci_commit($conn);
        $blob->free();
        oci_free_statement($stmt);
        oci_close($conn);

        return response()->json(['success' => true]);
    }

    public function update($request, $data, $time, $appModifier)
    {
        $base64 = $request->content;
        $decoded = base64_decode($base64, true);

        if ($decoded === false) {
            throw new \Exception('Nội dung không hợp lệ (không phải base64)');
        }

        // Kết nối Oracle thủ công
        $config = config('database.connections.oracle_his');
        $connectionString = "//{$config['host']}:{$config['port']}/{$config['service_name']}";
        $conn = oci_connect(
            $config['username'],
            $config['password'],
            $connectionString,
            $config['charset'] ?? 'AL32UTF8'
        );

        if (!$conn) {
            $e = oci_error();
            throw new \Exception("Lỗi kết nối Oracle: " . $e['message']);
        }

        // Chuẩn bị SQL UPDATE với EMPTY_BLOB + RETURNING INTO
        $sql = "UPDATE HIS_TEXT_LIB SET
        MODIFY_TIME = :modify_time,
        MODIFIER = :modifier,
        APP_MODIFIER = :app_modifier,
        TITLE = :title,
        CONTENT = EMPTY_BLOB(),
        HASHTAG = :hashtag,
        IS_PUBLIC = :is_public,
        HOT_KEY = :hot_key,
        IS_PUBLIC_IN_DEPARTMENT = :is_public_in_department
    WHERE ID = :id
    RETURNING CONTENT INTO :blob";

        $stmt = oci_parse($conn, $sql);
        if (!$stmt) {
            $e = oci_error($conn);
            throw new \Exception("Lỗi parse SQL: " . $e['message']);
        }

        // Các bind biến
        $modifyTime = now()->format('YmdHis');
        $modifier = get_loginname_with_token($request->bearerToken(), $time);
        $title = $request->title;
        $hashtag = $request->hashtag;
        $hotKey = $request->hot_key;
        $isPublic = $request->is_public;
        $isPublicInDepartment = $request->is_public_in_department;
        $id = $data->id;

        oci_bind_by_name($stmt, ':modify_time', $modifyTime);
        oci_bind_by_name($stmt, ':modifier', $modifier);
        oci_bind_by_name($stmt, ':app_modifier', $appModifier);
        oci_bind_by_name($stmt, ':title', $title);
        oci_bind_by_name($stmt, ':hashtag', $hashtag);
        oci_bind_by_name($stmt, ':is_public', $isPublic);
        oci_bind_by_name($stmt, ':hot_key', $hotKey);
        oci_bind_by_name($stmt, ':is_public_in_department', $isPublicInDepartment);
        oci_bind_by_name($stmt, ':id', $id);

        // BLOB
        $blob = oci_new_descriptor($conn, OCI_D_LOB);
        oci_bind_by_name($stmt, ':blob', $blob, -1, OCI_B_BLOB);

        if (!oci_execute($stmt, OCI_NO_AUTO_COMMIT)) {
            $e = oci_error($stmt);
            throw new \Exception("Lỗi khi update: " . $e['message']);
        }

        if (!$blob->save($decoded)) {
            throw new \Exception("Không thể ghi dữ liệu vào BLOB khi update");
        }

        oci_commit($conn);
        $blob->free();
        oci_free_statement($stmt);
        oci_close($conn);

        return response()->json(['success' => true]);
    }

    public function delete($data)
    {
        $data->delete();
        return $data;
    }
    public function getDataFromDbToElastic($batchSize = 5000, $id = null)
    {
        $numJobs = config('queue')['num_queue_worker']; // Số lượng job song song
        if ($id != null) {
            $data = $this->applyJoins()->where('his_text_lib.id', '=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('his_text_lib.id');
            $maxId = $this->applyJoins()->max('his_text_lib.id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('text_lib', 'his_text_lib', $startId, $endId, $batchSize);
            }
        }
    }
}
