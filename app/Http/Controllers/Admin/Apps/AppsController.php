<?php

namespace App\Http\Controllers\Admin\Apps;

use App\Http\Controllers\App\BaseController;
use App\Models\Devops\Apps\Apps;
use App\Models\Devops\Platform\Platform;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;


class AppsController extends BaseController
{

    /**
     * APP选项
     * @return mixed
     */
    public function option()
    {
        $status = [];
        foreach (Apps::APP_STATUS_MAP as $key => $value) {
            $status[] = ['key'=> $key, 'value' => $value];
        }
        return $this->success([
            'status' => $status
        ]);
    }

    /**
     * APP列表
     * @param Request $request
     * @return mixed
     */
    public function list(Request $request)
    {
        $platform_id    = $request->post('platform_id');
        $app_name       = $request->post('app_name');
        $app_key        = $request->post('app_key');
        $app_secret     = $request->post('app_secret');
        $status         = $request->post('status');

        $app_table      = (new Apps())->getTable();
        $platform_table = (new Platform())->getTable();

        $query = Apps::query()
            ->select([
                'a.*',
                'p.name as platform_name'
            ])
            ->from("{$app_table} as a")
            ->leftJoin("{$platform_table} as p", 'p.id', '=', 'a.platform_id');

        if (is_numeric($platform_id) && $platform_id) {
            $query->where('a.platform_id', $platform_id);
        }
        if (is_numeric($status)) {
            $query->where('a.status', $status);
        }
        if ($app_name) {
            $query->where('a.app_name', 'like', "%{$app_name}%");
        }
        if ($app_key) {
            $query->where('a.app_key', $app_key);
        }
        if ($app_secret) {
            $query->where('a.app_secret', $app_secret);
        }
        $query->orderByDesc('a.id');

        //获取列表
        $list = $this->getPagingRows($query, function (LengthAwarePaginator $paginator) {
            $paginator->getCollection()->transform(function ($row) {
                return [
                    'id'            => $row['id'],
                    'app_name'      => $row['app_name'],
                    'app_key'       => $row['app_key'],
                    'app_secret'    => $row['app_secret'],
                    'platform_id'   => $row['platform_id'],
                    'platform_name' => $row['platform_name'],
                    'remark'        => $row['remark'],
                    'status'        => $row['status'],
                    'status_remark' => Apps::APP_STATUS_MAP[$row['status']] ?? '',
                    'created_time'  => $row['created_ts'] ? func_datetime_trans((int)$row['created_ts'], DEFAULT_TIMEZONE) : '-',
                    'updated_time'  => $row['updated_ts'] ? func_datetime_trans((int)$row['updated_ts'], DEFAULT_TIMEZONE) : '-',
                ];
            });
            return $paginator;
        });
        return $this->success($list);
    }


    /**
     * 创建APP
     * @param Request $request
     * @return mixed
     * @throws ValidationException
     */
    public function add(Request $request)
    {
        $this->validate($request, [
            'platform_id' => 'required',
            'app_name'    => 'required',
        ]);
        $platform_id = $request->post('platform_id');
        $app_name    = $request->post('app_name');
        $remark      = $request->post('remark');
        $status      = $request->post('status', Apps::APP_STATUS_ON);

        if (!isset(Apps::APP_STATUS_MAP[$status])) {
            return $this->failed('状态参数错误');
        }

        try {
            $app_key    = Apps::createAppKey();
            $app_secret = Apps::createAppSecret();
            Apps::createApp($platform_id, $app_name, $app_key, $app_secret, $remark, $status);
        } catch (\Exception $e) {
            return $this->failed($e->getMessage());
        }
        $return = [
            'app_name'   => $app_name,
            'app_key'    => $app_key,
            'app_secret' => $app_secret,
        ];
        return $this->success($return);
    }

    /**
     * 修改APP
     * @param Request $request
     * @return mixed
     * @throws ValidationException
     */
    public function edit(Request $request)
    {
        $this->validate($request, [
            'app_id'    => 'required',
            'app_name'  => 'required',
        ]);
        $id         = $request->post('app_id');
        $app_name   = $request->post('app_name');
        $remark = $request->post('remark');
        $status = $request->post('status');
        if (!isset(Apps::APP_STATUS_MAP[$status])) {
            return $this->failed('状态参数错误');
        }
        if (!$app = Apps::query()->find($id)) {
            return $this->failed('未能找到相关平台');
        }
        try {
            $app->app_name = $app_name;
            $app->remark = $remark;
            $app->status = $status;
            $app->updated_ts = time();
            $app->save();
        } catch (\Exception $e) {
            return $this->failed($e->getMessage());
        }
        return $this->success();
    }

    /**
     * 修改APP
     * @param Request $request
     * @return mixed
     * @throws ValidationException
     */
    public function del(Request $request)
    {
        $this->validate($request, [
            'app_ids' => 'required',
        ]);
        $ids = $request->post('app_ids');
        $id_arr = func_get_param_ids($ids);

        if (empty($id_arr)) {
            return $this->success();
        }
        try {
            Platform::query()->whereIn('id', $id_arr)->update([
                'status'     => Apps::APP_STATUS_OFF,
                'deleted_ts' => time(),
                'updated_ts' => time(),
            ]);
            return $this->success();
        } catch (\Exception $e) {
            return $this->failed($e->getMessage());
        }
    }


    /**
     * 修改平台状态
     * @param Request $request
     * @return mixed
     * @throws ValidationException
     */
    public function setStatus(Request $request)
    {
        $this->validate($request, [
            'app_ids'   => 'required',
            'status'        => 'required',
        ]);
        $ids    = $request->post('app_ids');
        $status = $request->post('status');
        $id_arr = func_get_param_ids($ids);
        if (!isset(Apps::APP_STATUS_MAP[$status])) {
            return $this->failed('状态参数错误');
        }
        if (empty($id_arr)) {
            return $this->success();
        }
        try {
            Apps::query()->whereIn('id', $id_arr)->update([
                'status'     => $status,
                'updated_ts' => time(),
            ]);
            return $this->success();
        } catch (\Exception $e) {
            return $this->failed($e->getMessage());
        }
    }


}
