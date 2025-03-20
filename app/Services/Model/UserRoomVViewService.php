<?php

namespace App\Services\Model;

use App\DTOs\UserRoomVViewDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\UserRoomVView\InsertUserRoomVViewIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\UserRoomVViewRepository;

class UserRoomVViewService
{
    protected $userRoomVViewRepository;
    protected $params;
    public function __construct(UserRoomVViewRepository $userRoomVViewRepository)
    {
        $this->userRoomVViewRepository = $userRoomVViewRepository;
    }
    public function withParams(UserRoomVViewDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleCustomParamElasticSearch()
    {
        $data = null;
        if ($this->params->tab == 'executeRoomBedRoom') {
            $data =  [
                "bool" => [
                    "filter" => [
                        ["term" => ["loginname.keyword" => get_loginname_with_token($this->params->request->bearerToken(), $this->params->time)]],
                        $this->params->departmentCode ? ["term" => ["department_code.keyword" => $this->params->departmentCode]] : "",
                        ["term" => ["is_active" => 1]],
                        ["term" => ["is_delete" => 0]],
                        [
                            "bool" => [
                                "should" => [
                                    ["term" => ["room_type_code.keyword" => "GI"]],
                                    ["term" => ["room_type_code.keyword" => "XL"]]
                                ],
                                "minimum_should_match" => 1
                            ]
                        ],
                        [
                            "bool" => [
                                "should" => [
                                    ["term" => ["is_pause" => 0]],
                                    ["bool" => ["must_not" => ["exists" => ["field" => "is_pause"]]]]
                                ],
                                "minimum_should_match" => 1
                            ]
                        ],
                        [
                            "bool" => [
                                "should" => [
                                    ["bool" => [
                                        "must" => [
                                            ["term" => ["room_type_code.keyword" => "XL"]],
                                            ["term" => ["is_exam" => 1]]
                                        ]
                                    ]],
                                    ["bool" => [
                                        "must_not" => [
                                            ["term" => ["room_type_code.keyword" => "XL"]]
                                        ]
                                    ]]
                                ],
                                "minimum_should_match" => 1
                            ]
                        ]
                    ],
                    "must" => [
                        [
                            "bool" => [
                                "should" => [
                                    ["wildcard" => ["room_name.keyword" => "*" . $this->params->keyword . "*"]],
                                    ["match_phrase" => ["room_name" => $this->params->keyword]],
                                    ["match_phrase_prefix" => ["room_name" => $this->params->keyword]],

                                    ["match_phrase_prefix" => ["room_code" => $this->params->keyword]]
                                ],
                                "minimum_should_match" => 1
                            ]
                        ]
                    ]
                ]
            ];
        }

        return $data;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->userRoomVViewRepository->applyJoins();
            $data = $this->userRoomVViewRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->userRoomVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->userRoomVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->userRoomVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['user_room_v_view'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = $this->userRoomVViewRepository->applyJoins();
            $data = $this->userRoomVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->userRoomVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->userRoomVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['user_room_v_view'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = $this->userRoomVViewRepository->applyJoins()
                ->where('id', $id);
            $data = $this->userRoomVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $data->first();
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['user_room_v_view'], $e);
        }
    }

    public function createUserRoomVView($request)
    {
        try {
            $data = $this->userRoomVViewRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertUserRoomVViewIndex($data, $this->params->userRoomVViewName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->userRoomVViewName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['user_room_v_view'], $e);
        }
    }

    public function updateUserRoomVView($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->userRoomVViewRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->userRoomVViewRepository->update($request, $data, $this->params->time, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertUserRoomVViewIndex($data, $this->params->userRoomVViewName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->userRoomVViewName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['user_room_v_view'], $e);
        }
    }

    public function deleteUserRoomVView($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->userRoomVViewRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->userRoomVViewRepository->delete($data);

            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->userRoomVViewName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->userRoomVViewName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['user_room_v_view'], $e);
        }
    }
}
