<?php

namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\View\KetQuaClsVView;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class KetQuaClsVViewRepository
{
    protected $ketQuaClsVView;
    protected $serviceRepository;
    public function __construct(
        KetQuaClsVView $ketQuaClsVView,
        ServiceRepository $serviceRepository,
    ) {
        $this->ketQuaClsVView = $ketQuaClsVView;
        $this->serviceRepository = $serviceRepository;
    }

    public function applyJoins()
    {
        return $this->ketQuaClsVView
            ->select(
                'xa_v_his_ket_qua_cls.*'
            );
    }
    public function applyJoinsChonKetQuaCls()
    {
        return $this->ketQuaClsVView
            ->select([
                "xa_v_his_ket_qua_cls.id as key",
                "xa_v_his_ket_qua_cls.intruction_date",
                "xa_v_his_ket_qua_cls.tdl_treatment_id",
                "xa_v_his_ket_qua_cls.ket_qua",
                "xa_v_his_ket_qua_cls.nhan_xet",
                "xa_v_his_ket_qua_cls.ghi_chu",
                "xa_v_his_ket_qua_cls.service_code",
                "xa_v_his_ket_qua_cls.service_name",
                "xa_v_his_ket_qua_cls.parent_id",
                "xa_v_his_ket_qua_cls.service_type_code",
                "xa_v_his_ket_qua_cls.service_type_name",
                "xa_v_his_ket_qua_cls.test_index_num_order",
                "xa_v_his_ket_qua_cls.ma_chi_so",
                "xa_v_his_ket_qua_cls.ten_chi_so",
                "xa_v_his_ket_qua_cls.is_important",
                "xa_v_his_ket_qua_cls.is_leaf",
                "xa_v_his_ket_qua_cls.sri_code",
            ]);
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('xa_v_his_ket_qua_cls.ket_qua_cls_code'), 'like', '%' . $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('lower(xa_v_his_ket_qua_cls.ket_qua_cls_name)'), 'like', '%' . strtolower($keyword) . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('xa_v_his_ket_qua_cls.is_active'), $isActive);
        }
        return $query;
    }
    public function applyIsDeleteFilter($query, $isDelete)
    {
        if ($isDelete !== null) {
            $query->where(DB::connection('oracle_his')->raw('xa_v_his_ket_qua_cls.is_delete'), $isDelete);
        }
        return $query;
    }
    public function applyTreatmentIdFilter($query, $param)
    {
        if ($param != null) {
            $query->where(DB::connection('oracle_his')->raw('xa_v_his_ket_qua_cls.tdl_treatment_id'), $param);
        }
        return $query;
    }
    public function applyIntructionTimeFromFilter($query, $param)
    {
        if ($param != null) {
            $query->where(DB::connection('oracle_his')->raw('xa_v_his_ket_qua_cls.intruction_time'), '>=', $param);
        }
        return $query;
    }
    public function applyIntructionTimeToFilter($query, $param)
    {
        if ($param != null) {
            $query->where(DB::connection('oracle_his')->raw('xa_v_his_ket_qua_cls.intruction_time'), '<=', $param);
        }
        return $query;
    }
    public function applyChiSoQuanTrongFilter($query, $param)
    {
        if ($param != null) {
            $query->where(DB::connection('oracle_his')->raw('xa_v_his_ket_qua_cls.is_important'),1);
        }
        return $query;
    }
    public function applyTrenNguongDuoiNguongFilter($query, $trenNguong, $duoiNguong)
    {
        // if ($trenNguong || $duoiNguong) {
        //     $query->where('xa_v_his_ket_qua_cls.service_type_code', 'XN'); // Chỉ áp dụng cho loại dịch vụ là xét nghiệm

        //     $query->where(function ($q) use ($trenNguong, $duoiNguong) {
        //         if ($trenNguong) {
        //             $q->orWhereRaw('CAST(xa_v_his_ket_qua_cls.value AS FLOAT) > CAST(his_test_index_range.max_value AS FLOAT)');
        //         }
        //         if ($duoiNguong) {
        //             $q->orWhereRaw('CAST(xa_v_his_ket_qua_cls.value AS FLOAT) < CAST(his_test_index_range.min_value AS FLOAT)');
        //         }
        //     });
        // }

        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('xa_v_his_ket_qua_cls.' . $key, $item);
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
    public function applyGroupByField($data, $groupByFields = [], $hienThiDichVuChaLoaiXN = false)
    {
        if (empty($groupByFields)) {
            return $data;
        }

        // Chuyển các field thành snake_case trước khi nhóm
        $fieldMappings = [];
        foreach ($groupByFields as $field) {
            $snakeField = Str::snake($field);
            $fieldMappings[$snakeField] = $field;
        }

        $snakeFields = array_keys($fieldMappings);

        // Đệ quy nhóm dữ liệu theo thứ tự fields đã convert
        $groupData = function ($items, $fields) use (&$groupData, $fieldMappings, $hienThiDichVuChaLoaiXN) {
            if (empty($fields)) {
                return $items->values(); // Hết field nhóm -> Trả về danh sách gốc
            }

            $currentField = array_shift($fields);
            $originalField = $fieldMappings[$currentField];

            return $items->groupBy(function ($item) use ($currentField, $hienThiDichVuChaLoaiXN) {
                return $item[$currentField] ?? null;
            })->map(function ($group, $key) use ($fields, $groupData, $originalField, $currentField, $hienThiDichVuChaLoaiXN) {
                $result =  [
                    'key' => (string)$key,
                    $originalField => (string)$key, // Hiển thị tên gốc
                    'total' => $group->count(),
                ];

                switch ($currentField) {
                    case 'service_type_name':
                        $result['children'] = $groupData($group, $fields);
                        $danhMuc = $this->serviceRepository->getDanhMucXetNghiemJoinTheoDichVuCha();
                        $firstItem = $group->first();
                        if ($firstItem['service_type_code'] == 'XN' && $hienThiDichVuChaLoaiXN) {
                            $result['children'] = $this->buildTreeFromDanhMuc($result['children'], $danhMuc);
                        }
                        break;
                    default:
                        $result['children'] = $groupData($group, $fields);
                        break;
                }

                return $result;
            })->values();
        };

        return $groupData(collect($data), $snakeFields);
    }
    public function buildTreeFromDanhMuc($data, $danhMuc)
    {
        // 1. Lọc danh mục theo các parent_id từ data
        $danhMucMap = $danhMuc
            // ->filter(fn($item) => $parentIds->contains($item['id']))
            ->map(function ($item) {
                $item['children'] = collect();
                return $item;
            })
            ->keyBy('id');

        // 2. Gắn từng row vào danh mục tương ứng
        foreach ($data as $row) {
            $parentId = $row['parent_id'] ?? null;

            if ($parentId && $danhMucMap->has($parentId)) {
                $danhMuc = $danhMucMap->get($parentId);
                $danhMuc['children']->push($row);
                $danhMucMap->put($parentId, $danhMuc);
            }
        }

        // 3. Build cây danh mục
        $tree = collect();

        foreach ($danhMucMap as $id => $item) {
            $parentId = $item['parent_id'] ?? null;
            if ($parentId && $danhMucMap->has($parentId)) {
                $parent = $danhMucMap->get($parentId);
                $parent['children']->push($item);
                $danhMucMap->put($parentId, $parent);
            } else {
                $tree->push($item);
            }
        }

        // 4. Đệ quy lọc bỏ node children rỗng
        return $this->filterEmptyChildren($tree)->values();
    }
    protected function filterEmptyChildren($nodes)
    {
        return $nodes
            ->map(function ($node) {
                if (isset($node['children'])) {
                    $node['children'] = $this->filterEmptyChildren($node['children']);
                }
                return $node;
            })
            ->filter(function ($node) {
                // Luôn chắc chắn children là Collection
                $hasChildren = isset($node['children']) && $node['children']->isNotEmpty();

                // Nếu không phải là danh mục (không có id), giữ lại vì là dữ liệu thật
                $isData = !isset($node['id']);

                return $hasChildren || $isData;
            })
            ->values();
    }

    public function getById($id)
    {
        return $this->ketQuaClsVView->find($id);
    }
    public function getDataFromDbToElastic($batchSize = 5000, $id = null)
    {
        $numJobs = config('queue')['num_queue_worker']; // Số lượng job song song
        if ($id != null) {
            $data = $this->applyJoins()->where('xa_v_his_ket_qua_cls.id', '=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('xa_v_his_ket_qua_cls.id');
            $maxId = $this->applyJoins()->max('xa_v_his_ket_qua_cls.id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('ket_qua_cls_v_view', 'xa_v_his_ket_qua_cls', $startId, $endId, $batchSize);
            }
        }
    }
}
