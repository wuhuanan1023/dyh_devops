<?php

namespace App\Http\Controllers\Admin\Platform;

use App\Http\Controllers\App\BaseController;
use App\Models\Devops\Apps\Apps;
use App\Models\Devops\Platform\Platform;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;


class PlatformController extends BaseController
{
    /**
     * 平台 - option
     * @return mixed
     */
    public function option()
    {
        $status = [];
        foreach (Platform::STATUS_MAP as $key => $value) {
            $status[] = ['key'=> $key, 'value' => $value];
        }
        return $this->success([
            'status' => $status
        ]);
    }

    /**
     * 平台列表
     * @param Request $request
     * @return mixed
     */
    public function list(Request $request)
    {
        $platform_id    = $request->post('platform_id');
        $platform_name  = $request->post('platform_name');
        $status         = $request->post('status');

        $query = Platform::query();
        if (is_numeric($platform_id) && $platform_id) {
            $query->where('id', $platform_id);
        }
        if (is_numeric($status)) {
            $query->where('status', $status);
        }
        if ($platform_name) {
            $query->where('name', 'like', "%{$platform_name}%");
        }
        $query->orderByDesc('id');

        $list = $this->getPagingRows($query, function (LengthAwarePaginator $paginator) {
            $paginator->getCollection()->transform(function ($row) {
                return [
                    'id'            => $row['id'],
                    'name'          => $row['name'],
                    'remark'        => $row['remark'],
                    'status'        => $row['status'],
                    'status_remark' => Platform::STATUS_MAP[$row['status']] ?? '',
                    'created_time'  => $row['created_ts'] ? func_datetime_trans((int)$row['created_ts'], DEFAULT_TIMEZONE) : '',
                    'updated_time'  => $row['updated_ts'] ? func_datetime_trans((int)$row['updated_ts'], DEFAULT_TIMEZONE) : '',
                ];
            });
            return $paginator;
        });
        return $this->success($list);
    }

    /**
     * 创建平台
     * @param Request $request
     * @return mixed
     * @throws ValidationException
     */
    public function add(Request $request)
    {
        $this->validate($request, [
            'platform_name'   => 'required',
        ]);
        $name   = $request->post('platform_name');
        $remark = $request->post('remark');
        $status = $request->post('status', Platform::STATUS_ON);  //状态：0-禁用;1-启用;

        if (!isset(Platform::STATUS_MAP[$status])) {
            return $this->failed('状态参数错误');
        }

        try {
            Apps::query()->create([
                'name'   => $name,
                'remark' => $remark,
                'status' => $status,
                'created_ts' => time(),
                'updated_ts' => time(),
            ]);
        } catch (\Exception $e) {
            return $this->failed($e->getMessage());
        }
        return $this->success();
    }

    /**
     * 修改平台
     * @param Request $request
     * @return mixed
     * @throws ValidationException
     */
    public function edit(Request $request)
    {
        $this->validate($request, [
            'platform_id'   => 'required',
            'platform_name' => 'required',
        ]);
        $id     = $request->post('platform_id');
        $name   = $request->post('platform_name');
        $remark = $request->post('remark');
        $status = $request->post('status');
        if (!isset(Platform::STATUS_MAP[$status])) {
            return $this->failed('状态参数错误');
        }
        if (!$platform = Platform::query()->find($id)) {
            return $this->failed('未能找到相关平台');
        }
        try {
            $platform->name = $name;
            $platform->remark = $remark;
            $platform->status = $status;
            $platform->updated_ts = time();
            $platform->save();
        } catch (\Exception $e) {
            return $this->failed($e->getMessage());
        }
        return $this->success();
    }

    /**
     * 修改平台
     * @param Request $request
     * @return mixed
     * @throws ValidationException
     */
    public function del(Request $request)
    {
        $this->validate($request, [
            'platform_ids'   => 'required',
        ]);
        $ids     = $request->post('platform_ids');
        $id_arr = func_get_param_ids($ids);

        if (empty($id_arr)) {
            return $this->success();
        }
        try {
            Platform::query()->whereIn('id', $id_arr)->delete();
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
            'platform_ids'   => 'required',
            'status'        => 'required',
        ]);
        $ids    = $request->post('platform_ids');
        $status = $request->post('status');
        $id_arr = func_get_param_ids($ids);
        if (!isset(Platform::STATUS_MAP[$status])) {
            return $this->failed('状态参数错误');
        }
        if (empty($id_arr)) {
            return $this->success();
        }
        try {
            Platform::query()->whereIn('id', $id_arr)->update([
                'status'     => $status,
                'updated_ts' => time(),
            ]);
            return $this->success();
        } catch (\Exception $e) {
            return $this->failed($e->getMessage());
        }
    }

}
