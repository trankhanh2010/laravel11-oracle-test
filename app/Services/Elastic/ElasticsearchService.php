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

    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    public function buildSearchBody($table_name)
    {
        try {
            $query = $this->buildSearchQuery($this->elastic_search_type, $this->elastic_field, $this->keyword, $table_name);
            $highlight = $this->buildHighlight($this->elastic_search_type);
            $paginate = $this->buildPaginateElastic();
            $body = $this->buildArrSearchBody($query, $highlight, $paginate, $table_name);
            return $body;
        } catch (\Throwable $e) {
            Log::error(config('params')['elastic']['error']['elasticsearch_service'], [
                'function' => __FUNCTION__,
                'class' => __CLASS__,
                'error' => $e->getMessage(),
            ]);
            throw new \Exception(config('params')['elastic']['error']['elasticsearch_service'], 0, $e);
        }
    }
    public  function executeSearch($index, $body, $id)
    {
        try {
            return $this->buildSearch($index, $body, $id);
        } catch (\Throwable $e) {
            Log::error(config('params')['elastic']['error']['elasticsearch_service'], [
                'function' => __FUNCTION__,
                'class' => __CLASS__,
                'error' => $e->getMessage(),
            ]);
            throw new \Exception(config('params')['elastic']['error']['elasticsearch_service'], 0, $e);
        }
    }

    public function buildSearchQuery($searchType, $field, $value, $name_table)
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
                        if ($this->elastic_operator !== null) {
                            $matchQuery['operator'] = $this->elastic_operator;
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
                        if (in_array($field, get_arr_elastic_index_keyword($name_table))) {
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
                                'fields' => $this->elastic_fields
                            ]
                        ];
                        break;
                    case 'bool':
                        // Mảng kết quả sau khi đổi key
                        $updated_elastic_must = [];
                        $updated_elastic_should = [];
                        $updated_elastic_must_not = [];

                        // Lặp qua từng phần tử trong mảng ban đầu
                        if ($this->elastic_must != null) {
                            foreach ($this->elastic_must as $key => $item) {
                                // Tạo key mới bằng cách thêm '.keyword'
                                foreach ($item as $key_item => $item2) {
                                    foreach ($item2 as $key_item2 => $item3) {
                                        if (in_array($key_item2, get_arr_elastic_index_keyword($name_table)) && in_array($key_item, ['term', 'wildcard', 'prefix'])) {
                                            $newKey = $key_item2 . '.keyword';
                                        } else {
                                            $newKey = $key_item2;
                                        }
                                        if (in_array($key_item, ['wildcard'])) {
                                            $item3 = '*' . $item3 . '*';
                                        }
                                        if (in_array($key_item, ['query_string'])) {
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
                                        if (in_array($key_item, ['query_string'])) {
                                            $updated_elastic_must[$key][$key_item] = [
                                                'query' => $item3,
                                                'fields' => [$key_item2],
                                            ];
                                        } else {
                                            $updated_elastic_must[$key][$key_item] = [
                                                $newKey => $item3,
                                            ];
                                        }
                                    }
                                }
                            }
                        }

                        // Lặp qua từng phần tử trong mảng ban đầu
                        if ($this->elastic_should != null) {
                            foreach ($this->elastic_should as $key => $item) {
                                // Tạo key mới bằng cách thêm '.keyword'
                                foreach ($item as $key_item => $item2) {
                                    foreach ($item2 as $key_item2 => $item3) {
                                        if (in_array($key_item2, get_arr_elastic_index_keyword($name_table)) && in_array($key_item, ['term', 'wildcard', 'prefix'])) {
                                            $newKey = $key_item2 . '.keyword';
                                        } else {
                                            $newKey = $key_item2;
                                        }
                                        if (in_array($key_item, ['wildcard'])) {
                                            $item3 = '*' . $item3 . '*';
                                        }
                                        if (in_array($key_item, ['query_string'])) {
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
                                        if (in_array($key_item, ['query_string'])) {
                                            $updated_elastic_should[$key][$key_item] = [
                                                'query' => $item3,
                                                'fields' => [$key_item2],
                                            ];
                                        } else {
                                            $updated_elastic_should[$key][$key_item] = [
                                                $newKey => $item3,
                                            ];
                                        }
                                    }
                                }
                            }
                        }


                        // Lặp qua từng phần tử trong mảng ban đầu
                        if ($this->elastic_must_not != null) {
                            foreach ($this->elastic_must_not as $key => $item) {
                                // Tạo key mới bằng cách thêm '.keyword'
                                foreach ($item as $key_item => $item2) {
                                    foreach ($item2 as $key_item2 => $item3) {
                                        if (in_array($key_item2, get_arr_elastic_index_keyword($name_table)) && in_array($key_item, ['term', 'wildcard', 'prefix'])) {
                                            $newKey = $key_item2 . '.keyword';
                                        } else {
                                            $newKey = $key_item2;
                                        }
                                        if (in_array($key_item, ['wildcard'])) {
                                            $item3 = '*' . $item3 . '*';
                                        }
                                        if (in_array($key_item, ['query_string'])) {
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
                                        if (in_array($key_item, ['query_string'])) {
                                            $updated_elastic_must_not[$key][$key_item] = [
                                                'query' => $item3,
                                                'fields' => [$key_item2],
                                            ];
                                        } else {
                                            $updated_elastic_must_not[$key][$key_item] = [
                                                $newKey => $item3,
                                            ];
                                        }
                                    }
                                }
                            }
                        }

                        $matchQuery = [];
                        if ($this->elastic_must !== null) {
                            $matchQuery['must'] = $updated_elastic_must;
                        }
                        if ($this->elastic_should !== null) {
                            $matchQuery['should'] = $updated_elastic_should;
                        }
                        if ($this->elastic_must_not !== null) {
                            $matchQuery['must_not'] = $updated_elastic_must_not;
                        }
                        if ($this->elastic_filter !== null) {
                            $matchQuery['filter'] = $this->elastic_filter;
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
            Log::error(config('params')['elastic']['error']['elasticsearch_service'], [
                'function' => __FUNCTION__,
                'class' => __CLASS__,
                'error' => $e->getMessage(),
            ]);
            throw new \Exception(config('params')['elastic']['error']['elasticsearch_service'], 0, $e);
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
                            $this->elastic_field => [
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
                            $this->elastic_field . '.keyword' => [
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
                            $this->elastic_field . '.keyword' => [
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
                            $this->elastic_field => [
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
                            $this->elastic_field => [
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
                            $this->elastic_field => [
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
                    foreach ($this->elastic_fields as $key => $item) {
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
                    if ($this->elastic_fields != null) {
                        foreach ($this->elastic_fields as $key => $item) {
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
            Log::error(config('params')['elastic']['error']['elasticsearch_service'], [
                'function' => __FUNCTION__,
                'class' => __CLASS__,
                'error' => $e->getMessage(),
            ]);
            throw new \Exception(config('params')['elastic']['error']['elasticsearch_service'], 0, $e);
        }
    }
    public function buildPaginateElastic()
    {
        try {
            if ($this->get_all) {
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
            Log::error(config('params')['elastic']['error']['elasticsearch_service'], [
                'function' => __FUNCTION__,
                'class' => __CLASS__,
                'error' => $e->getMessage(),
            ]);
            throw new \Exception(config('params')['elastic']['error']['elasticsearch_service'], 0, $e);
        }
    }
    public function buildSort($name)
    {
        try {
            $sort = [];
            // Mảng kết quả sau khi đổi key
            $updatedSortArray = [];
            // Lặp qua từng phần tử trong mảng ban đầu
            foreach ($this->order_by_elastic as $key => $order) {
                // Tạo key mới bằng cách thêm '.keyword'
                if (in_array($key, get_arr_elastic_index_keyword($name))) {
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
            Log::error(config('params')['elastic']['error']['elasticsearch_service'], [
                'function' => __FUNCTION__,
                'class' => __CLASS__,
                'error' => $e->getMessage(),
            ]);
            throw new \Exception(config('params')['elastic']['error']['elasticsearch_service'], 0, $e);
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
            if ($this->order_by_elastic != null) {
                $body['sort'] = $this->buildSort($index_name);
            }
            return $body;
        } catch (\Throwable $e) {
            Log::error(config('params')['elastic']['error']['elasticsearch_service'], [
                'function' => __FUNCTION__,
                'class' => __CLASS__,
                'error' => $e->getMessage(),
            ]);
            throw new \Exception(config('params')['elastic']['error']['elasticsearch_service'], 0, $e);
        }
    }
    public function buildSearch($index, $body, $id = null)
    {
        try {
            $data = [];
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
                if ($this->is_active !== null) {
                    $data['body']['query']['bool']['must'] = [
                        ['term' => ['_id' => $id]],       // Truy vấn theo ID
                        ['term' => ['is_active' => $this->is_active]],
                    ];
                }
            }

            return $this->client->search($data);
        } catch (\Throwable $e) {
            Log::error(config('params')['elastic']['error']['elasticsearch_service'], [
                'function' => __FUNCTION__,
                'class' => __CLASS__,
                'error' => $e->getMessage(),
            ]);
            throw new \Exception(config('params')['elastic']['error']['elasticsearch_service'], 0, $e);
        }
    }
    public function applyResource($data)
    {
        try {
            $data = ElasticResource::collection($data['hits']['hits']);
            return $data;
        } catch (\Throwable $e) {
            Log::error(config('params')['elastic']['error']['elasticsearch_service'], [
                'function' => __FUNCTION__,
                'class' => __CLASS__,
                'error' => $e->getMessage(),
            ]);
            throw new \Exception(config('params')['elastic']['error']['elasticsearch_service'], 0, $e);
        }
    }
    public function counting($data)
    {
        try {
            $count = $data['hits']['total']['value'];
            return $count;
        } catch (\Throwable $e) {
            Log::error(config('params')['elastic']['error']['elasticsearch_service'], [
                'function' => __FUNCTION__,
                'class' => __CLASS__,
                'error' => $e->getMessage(),
            ]);
            throw new \Exception(config('params')['elastic']['error']['elasticsearch_service'], 0, $e);
        }
    }

    public function handleElasticSearchSearch($table_name)
    {
        try {
            $body = $this->buildSearchBody($table_name);
            $data = $this->executeSearch($table_name, $body, null);
            $count = $this->counting($data);
            $data = $this->applyResource($data);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            Log::error(config('params')['elastic']['error']['elasticsearch_service'], [
                'function' => __FUNCTION__,
                'class' => __CLASS__,
                'error' => $e->getMessage(),
            ]);
            throw new \Exception(config('params')['elastic']['error']['elasticsearch_service'], 0, $e);
        }
    }
    public function handleElasticSearchGetAll($table_name)
    {
        try {
            $data = Cache::remember('elastic_' . $table_name . '_start_' . $this->start . '_limit_' . $this->limit . $this->order_by_tring . '_is_active_' . $this->elastic_is_active . '_get_all_' . $this->get_all, $this->time, function () use ($table_name) {
                $body = $this->buildSearchBody($table_name);
                $data = $this->executeSearch($table_name, $body, null);
                $count = $this->counting($data);
                $data = $this->applyResource($data);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            Log::error(config('params')['elastic']['error']['elasticsearch_service'], [
                'function' => __FUNCTION__,
                'class' => __CLASS__,
                'error' => $e->getMessage(),
            ]);
            throw new \Exception(config('params')['elastic']['error']['elasticsearch_service'], 0, $e);
        }
    }

    public function handleElasticSearchGetWithId($table_name, $id)
    {
        try {
            $data = Cache::remember('elastic_' . $table_name . '_' . $id . '_is_active_' . $this->elastic_is_active, $this->time, function () use ($table_name, $id) {
                $body = $this->buildSearchBody($table_name);
                $data = $this->executeSearch($table_name, $body, $id);
                $data = $this->applyResource($data);
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            Log::error(config('params')['elastic']['error']['elasticsearch_service'], [
                'function' => __FUNCTION__,
                'class' => __CLASS__,
                'error' => $e->getMessage(),
            ]);
            throw new \Exception(config('params')['elastic']['error']['elasticsearch_service'], 0, $e);
        }
    }
}
