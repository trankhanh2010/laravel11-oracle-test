<?php

namespace App\Services\Elastic;

use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Resources\Elastic\ElasticResource;
use Illuminate\Support\Facades\Log;

class ElasticsearchService extends BaseApiCacheController
{
    protected $client;
    protected $index;
    protected $request;
    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->request = $request;
    }

    public function buildSearchBody($tableName)
    {
        try {
            $query = $this->buildSearchQuery($this->elasticSearchType, $this->elasticField, $this->keyword, $tableName);
            $highlight = $this->buildHighlight($this->elasticSearchType);
            $paginate = $this->buildPaginateElastic();
            $body = $this->buildArrSearchBody($query, $highlight, $paginate, $tableName);
            return $body;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['elastic']['error']['build_search_body'], $e);
        }
    }
    public  function executeSearch($index, $body, $id)
    {
        try {
            return $this->buildSearch($index, $body, $id);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['elastic']['error']['execute_search'], $e);
        }
    }

    public function buildSearchQuery($searchType, $field, $value, $nameTable)
    {
        try {
            $query = [];
            if ($searchType != null) {
                switch ($searchType) {
                    case 'match':
                        $matchQuery = [
                            'query' => $value,
                        ];
                        // Thêm 'operator' vào truy vấn chỉ khi nó có giá trị
                        if ($this->elasticOperator !== null) {
                            $matchQuery['operator'] = $this->elasticOperator;
                        }
                        $query =  [
                            'match' => [
                                $field => $matchQuery
                            ]
                        ];
                        break;
                    case 'term':
                        $query =  [
                            'term' => [
                                $field . '.keyword' => $value
                            ]
                        ];
                        break;
                    case 'wildcard':
                        $query =  [
                            'wildcard' => [
                                $field . '.keyword' => '*' . $value . '*',
                            ]
                        ];
                        break;
                    case 'query_string':
                        $query =  [
                            'query_string' => [
                                'query' =>  $value,
                            ]
                        ];
                        break;
                    case 'match_phrase':
                        $query =  [
                            'match_phrase' => [
                                $field => $value
                            ]
                        ];
                        break;
                    case 'prefix':
                        if (in_array($field, getArrElasticIndexKeyword($nameTable))) {
                            $query =  [
                                'prefix' => [
                                    $field . '.keyword' => $value
                                ]
                            ];
                        } else {
                            $query =  [
                                'prefix' => [
                                    $field => $value
                                ]
                            ];
                        }
                        break;
                    case 'multi_match':
                        $query = [
                            'multi_match' =>  [
                                'query' => $value,
                                'fields' => $this->elasticFields
                            ]
                        ];
                        break;
                    case 'bool':
                        // Mảng kết quả sau khi đổi key
                        $updatedElasticMust = [];
                        $updatedElasticShould = [];
                        $updatedElasticMustNot = [];

                        // Lặp qua từng phần tử trong mảng ban đầu
                        if ($this->elasticMust != null) {
                            foreach ($this->elasticMust as $key => $item) {
                                // Tạo key mới bằng cách thêm '.keyword'
                                foreach ($item as $keyItem => $item2) {
                                    foreach ($item2 as $keyItem2 => $item3) {
                                        if (in_array($keyItem2, getArrElasticIndexKeyword($nameTable)) && in_array($keyItem, ['term', 'wildcard', 'prefix'])) {
                                            $newKey = $keyItem2 . '.keyword';
                                        } else {
                                            $newKey = $keyItem2;
                                        }
                                        if (in_array($keyItem, ['wildcard'])) {
                                            $item3 = '*' . $item3 . '*';
                                        }
                                        if (in_array($keyItem, ['query_string'])) {
                                            // Tách chuỗi thành các từ bằng khoảng trắng
                                            $item3 = explode(' ', $item3);
                                            // Biến từng từ thành dạng wildcard
                                            $wildcards = array_map(function ($word) {
                                                return '*' . $word . '*';
                                            }, $item3);
                                            // Kết hợp các từ với toán tử OR (||)
                                            $item3 = implode(' || ', $wildcards);
                                        }
                                        // Thêm phần tử vào mảng kết quả với key mới
                                        if (in_array($keyItem, ['query_string'])) {
                                            $updatedElasticMust[$key][$keyItem] = [
                                                'query' => $item3,
                                                'fields' => [$keyItem2],
                                            ];
                                        } else {
                                            $updatedElasticMust[$key][$keyItem] = [
                                                $newKey => $item3,
                                            ];
                                        }
                                    }
                                }
                            }
                        }

                        // Lặp qua từng phần tử trong mảng ban đầu
                        if ($this->elasticShould != null) {
                            foreach ($this->elasticShould as $key => $item) {
                                // Tạo key mới bằng cách thêm '.keyword'
                                foreach ($item as $keyItem => $item2) {
                                    foreach ($item2 as $keyItem2 => $item3) {
                                        if (in_array($keyItem2, getArrElasticIndexKeyword($nameTable)) && in_array($keyItem, ['term', 'wildcard', 'prefix'])) {
                                            $newKey = $keyItem2 . '.keyword';
                                        } else {
                                            $newKey = $keyItem2;
                                        }
                                        if (in_array($keyItem, ['wildcard'])) {
                                            $item3 = '*' . $item3 . '*';
                                        }
                                        if (in_array($keyItem, ['query_string'])) {
                                            // Tách chuỗi thành các từ bằng khoảng trắng
                                            $item3 = explode(' ', $item3);
                                            // Biến từng từ thành dạng wildcard
                                            $wildcards = array_map(function ($word) {
                                                return '*' . $word . '*';
                                            }, $item3);
                                            // Kết hợp các từ với toán tử OR (||)
                                            $item3 = implode(' || ', $wildcards);
                                        }
                                        // Thêm phần tử vào mảng kết quả với key mới
                                        if (in_array($keyItem, ['query_string'])) {
                                            $updatedElasticShould[$key][$keyItem] = [
                                                'query' => $item3,
                                                'fields' => [$keyItem2],
                                            ];
                                        } else {
                                            $updatedElasticShould[$key][$keyItem] = [
                                                $newKey => $item3,
                                            ];
                                        }
                                    }
                                }
                            }
                        }


                        // Lặp qua từng phần tử trong mảng ban đầu
                        if ($this->elasticMustNot != null) {
                            foreach ($this->elasticMustNot as $key => $item) {
                                // Tạo key mới bằng cách thêm '.keyword'
                                foreach ($item as $keyItem => $item2) {
                                    foreach ($item2 as $keyItem2 => $item3) {
                                        if (in_array($keyItem2, getArrElasticIndexKeyword($nameTable)) && in_array($keyItem, ['term', 'wildcard', 'prefix'])) {
                                            $newKey = $keyItem2 . '.keyword';
                                        } else {
                                            $newKey = $keyItem2;
                                        }
                                        if (in_array($keyItem, ['wildcard'])) {
                                            $item3 = '*' . $item3 . '*';
                                        }
                                        if (in_array($keyItem, ['query_string'])) {
                                            // Tách chuỗi thành các từ bằng khoảng trắng
                                            $item3 = explode(' ', $item3);
                                            // Biến từng từ thành dạng wildcard
                                            $wildcards = array_map(function ($word) {
                                                return '*' . $word . '*';
                                            }, $item3);
                                            // Kết hợp các từ với toán tử OR (||)
                                            $item3 = implode(' || ', $wildcards);
                                        }
                                        // Thêm phần tử vào mảng kết quả với key mới
                                        if (in_array($keyItem, ['query_string'])) {
                                            $updatedElasticMustNot[$key][$keyItem] = [
                                                'query' => $item3,
                                                'fields' => [$keyItem2],
                                            ];
                                        } else {
                                            $updatedElasticMustNot[$key][$keyItem] = [
                                                $newKey => $item3,
                                            ];
                                        }
                                    }
                                }
                            }
                        }

                        $matchQuery = [];
                        if ($this->elasticMust !== null) {
                            $matchQuery['must'] = $updatedElasticMust;
                        }
                        if ($this->elasticShould !== null) {
                            $matchQuery['should'] = $updatedElasticShould;
                        }
                        if ($this->elasticMustNot !== null) {
                            $matchQuery['must_not'] = $updatedElasticMustNot;
                        }
                        if ($this->elasticFilter !== null) {
                            $matchQuery['filter'] = $this->elasticFilter;
                        }
                        $query =  [
                            'bool' =>
                            $matchQuery

                        ];
                        break;
                }
            }

            return $query;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['elastic']['error']['build_search_query'], $e);
        }
    }
    public function buildHighlight($searchType)
    {
        try {
            $highlight = [];

            switch ($searchType) {
                case 'match':
                    $highlight  = [
                        'fields' => [
                            $this->elasticField => [
                                'pre_tags' => ['<em>'],  // Tag mở đầu cho highlight
                                'post_tags' => ['</em>'], // Tag kết thúc cho highlight
                                'number_of_fragments' => 0, // Hiển thị toàn bộ văn bản
                                'fragment_size' => 150  // Kích thước của mỗi đoạn highlight
                            ]
                        ]
                    ];
                    break;
                case 'term':
                    $highlight  = [
                        'fields' => [
                            $this->elasticField . '.keyword' => [
                                'pre_tags' => ['<em>'],  // Tag mở đầu cho highlight
                                'post_tags' => ['</em>'], // Tag kết thúc cho highlight
                                'number_of_fragments' => 0, // Hiển thị toàn bộ văn bản
                                'fragment_size' => 150  // Kích thước của mỗi đoạn highlight
                            ]
                        ]
                    ];
                    break;
                case 'wildcard':
                    $highlight  = [
                        'fields' => [
                            $this->elasticField . '.keyword' => [
                                'pre_tags' => ['<em>'],  // Tag mở đầu cho highlight
                                'post_tags' => ['</em>'], // Tag kết thúc cho highlight
                                'number_of_fragments' => 0, // Hiển thị toàn bộ văn bản
                                'fragment_size' => 150  // Kích thước của mỗi đoạn highlight
                            ]
                        ]
                    ];
                    break;
                case 'query_string':
                    $highlight  = [
                        'fields' => [
                            $this->elasticField => [
                                'pre_tags' => ['<em>'],  // Tag mở đầu cho highlight
                                'post_tags' => ['</em>'], // Tag kết thúc cho highlight
                                'number_of_fragments' => 0, // Hiển thị toàn bộ văn bản
                                'fragment_size' => 150  // Kích thước của mỗi đoạn highlight
                            ]
                        ]
                    ];
                    break;
                case 'match_phrase':
                    $highlight  = [
                        'fields' => [
                            $this->elasticField => [
                                'pre_tags' => ['<em>'],  // Tag mở đầu cho highlight
                                'post_tags' => ['</em>'], // Tag kết thúc cho highlight
                                'number_of_fragments' => 0, // Hiển thị toàn bộ văn bản
                                'fragment_size' => 150  // Kích thước của mỗi đoạn highlight
                            ]
                        ]
                    ];
                    break;
                case 'prefix':
                    $highlight  = [
                        'fields' => [
                            $this->elasticField => [
                                'pre_tags' => ['<em>'],  // Tag mở đầu cho highlight
                                'post_tags' => ['</em>'], // Tag kết thúc cho highlight
                                'number_of_fragments' => 0, // Hiển thị toàn bộ văn bản
                                'fragment_size' => 150  // Kích thước của mỗi đoạn highlight
                            ]
                        ]
                    ];
                    break;
                case 'multi_match':
                    $fields = [];
                    foreach ($this->elasticFields as $key => $item) {
                        $fields[$item] = [
                            'pre_tags' => ['<em>'],  // Tag mở đầu cho highlight
                            'post_tags' => ['</em>'], // Tag kết thúc cho highlight
                            'number_of_fragments' => 0, // Hiển thị toàn bộ văn bản
                            'fragment_size' => 150  // Kích thước của mỗi đoạn highlight
                        ];
                    }
                    $highlight  = [
                        'fields' => $fields
                    ];
                    break;
                case 'bool':
                    $fields = [];
                    if ($this->elasticFields != null) {
                        foreach ($this->elasticFields as $key => $item) {
                            $fields[$item] = [
                                'pre_tags' => ['<em>'],  // Tag mở đầu cho highlight
                                'post_tags' => ['</em>'], // Tag kết thúc cho highlight
                                'number_of_fragments' => 0, // Hiển thị toàn bộ văn bản
                                'fragment_size' => 150  // Kích thước của mỗi đoạn highlight
                            ];
                        }
                        $highlight  = [
                            'fields' => $fields
                        ];
                    }
                    break;
            }

            return $highlight;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['elastic']['error']['build_highlight'], $e);
        }
    }
    public function buildPaginateElastic()
    {
        try {
            if ($this->getAll) {
                return [
                    'size' => 10000,
                    'from' => 0,
                ];
            }
            return [
                'size' => $this->limit,
                'from' => $this->start,
            ];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['elastic']['error']['build_paginate_elastic'], $e);
        }
    }
    public function buildSort($name)
    {
        try {
            $sort = [];
            // Mảng kết quả sau khi đổi key
            $updatedSortArray = [];
            // Lặp qua từng phần tử trong mảng ban đầu
            foreach ($this->orderByElastic as $key => $order) {
                // Tạo key mới bằng cách thêm '.keyword'
                if (in_array($key, getArrElasticIndexKeyword($name))) {
                    $newKey = $key . '.keyword';
                } else {
                    $newKey = $key;
                }

                // Thêm phần tử vào mảng kết quả với key mới
                $updatedSortArray[] = [
                    $newKey => [
                        'order' => $order
                    ]
                ];
            }
            $sort = $updatedSortArray;
            return $sort;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['elastic']['error']['build_sort'], $e);
        }
    }
    public function buildArrSearchBody($query, $highlight, $paginate, $index_name)
    {
        try {
            $body = [];
            if ($query != null) {
                $body['query'] = $query;
            }
            if ($highlight != null) {
                $body['highlight'] = $highlight;
            }
            $body = array_merge($body, $paginate);
            if ($this->orderByElastic != null) {
                $body['sort'] = $this->buildSort($index_name);
            }
            return $body;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['elastic']['error']['build_arr_search_body'], $e);
        }
    }
    public function buildSearch($index, $body, $id = null)
    {
        try {
            $data = [];
            if($this->isNoCacheSelectField()){
                $data['_source'] = config('params')['elastic']['no_cache_select_field'][str_replace('-', '_', $this->isNoCacheSelectField())];
            }
            // Đếm chính xác số lượng bản ghi
            $data['track_total_hits'] = true;
            if ($index != null) {
                $data['index'] = $index;
            }
            if ($body != null) {
                $data['body'] = $body;
            }
            if ($id != null) {
                $data['body'] = [
                    'query' => [
                        'bool' => [
                            'must' => [
                                ['term' => ['_id' => $id]],       // Truy vấn theo ID
                            ]
                        ]
                    ]
                ];
                if ($this->isActive !== null) {
                    $data['body']['query']['bool']['must'] = [
                        ['term' => ['_id' => $id]],       // Truy vấn theo ID
                        ['term' => ['is_active' => $this->isActive]],
                    ];
                }
            }
            return $this->client->search($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['elastic']['error']['build_search'], $e);
        }
    }
    public function applyResource($data)
    {
        try {
            $data = ElasticResource::collection($data['hits']['hits']);
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['elastic']['error']['apply_resource'], $e);
        }
    }
    public function counting($data)
    {
        try {
            $count = $data['hits']['total']['value'];
            return $count;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['elastic']['error']['counting'], $e);
        }
    }

    public function handleElasticSearchSearch($tableName)
    {
        try {
            $body = $this->buildSearchBody($tableName);
            $data = $this->executeSearch($tableName, $body, null);
            $count = $this->counting($data);
            $data = $this->applyResource($data);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['elastic']['error']['handle_elastic_search_search'], $e);
        }
    }
    public function handleElasticSearchGetAll($tableName)
    {
        try {
            if($this->isNoCacheSelectField()){
                $body = $this->buildSearchBody($tableName);
                $data = $this->executeSearch($tableName, $body, null);
                $count = $this->counting($data);
                $data = $this->applyResource($data);
                return ['data' => $data, 'count' => $count];
            }else{
                $data = Cache::remember('elastic_' . $tableName . '_start_' . $this->start . '_limit_' . $this->limit . $this->orderByString . '_is_active_' . $this->elasticIsActive . '_get_all_' . $this->getAll, $this->time, function () use ($tableName) {
                    $body = $this->buildSearchBody($tableName);
                    $data = $this->executeSearch($tableName, $body, null);
                    $count = $this->counting($data);
                    $data = $this->applyResource($data);
                    return ['data' => $data, 'count' => $count];
                });
            }
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['elastic']['error']['handle_elastic_search_get_all'], $e);
        }
    }

    
    public function handleElasticSearchGetWithId($tableName, $id)
    {
        try {
            if($this->isNoCacheSelectField()){
                $body = $this->buildSearchBody($tableName);
                $data = $this->executeSearch($tableName, $body, $id);
                $data = $this->applyResource($data);
                return $data;
            }else{
                $data = Cache::remember('elastic_' . $tableName . '_' . $id . '_is_active_' . $this->elasticIsActive, $this->time, function () use ($tableName, $id) {
                    $body = $this->buildSearchBody($tableName);
                    $data = $this->executeSearch($tableName, $body, $id);
                    $data = $this->applyResource($data);
                    return $data;
                });
            }
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['elastic']['error']['handle_elastic_search_get_with_id'], $e);
        }
    }
    public function getNameApi(){
       $data = $this->request->segments();

        return $data;
    }
    public function isNoCacheSelectField(){
        $arr =  $this->getNameApi();
        foreach($arr as $key => $item){
            if(in_array(str_replace('-', '_', $item), array_keys(config('params')['elastic']['no_cache_select_field']))){
                return $item;
            }
        }
        return false;
    }
}
