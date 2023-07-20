<?php

namespace App\Http\Controllers\Admin\Apps;

use App\Http\Controllers\App\BaseController;
use App\Models\Devops\Apps\AppHealthHeck;
use App\Models\Devops\Apps\Apps;
use App\Models\Devops\Platform\Platform;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

/**
 * 应用健康监测
 * Class AppHealthCheckController
 * @package App\Http\Controllers\Admin\Apps
 */
class AppHealthCheckController extends BaseController
{

    /**
     * 选项
     * @return mixed
     */
    public function option()
    {
        $status = [];
        foreach (AppHealthHeck::STATUS_MAP as $key => $value) {
            $status[] = ['key'=> $key, 'value' => $value];
        }
        return $this->success([
            'status' => $status
        ]);
    }

    /**
     * 列表
     * @param Request $request
     * @return mixed
     */
    public function list(Request $request)
    {
        $app_id     = $request->post('app_id');
        $data       = $request->post('data');
        $request_ip = $request->post('request_ip'); //请求IP
        $status     = $request->post('status');

        $app_health_request_table   = (new AppHealthHeck())->getTable();
        $app_table                  = (new Apps())->getTable();
        $platform_table             = (new Platform())->getTable();

        $query = AppHealthHeck::query()
            ->select([
                'ahr.*',
                'a.app_name',
                'p.id as platform_id', 'p.name as platform_name',
            ])
            ->from("{$app_health_request_table} as ahr")
            ->leftJoin("{$app_table} as a", 'a.id', '=', 'ahr.app_id')
            ->leftJoin("{$platform_table} as p", 'p.id', '=', 'a.platform_id');

        if (is_numeric($app_id) && $app_id) {
            $query->where('ahr.app_id', $app_id);
        }
        if (is_numeric($status)) {
            $query->where('ahr.status', $status);
        }
        if ($data) {
            $query->where('ahr.data', 'like', "%{$data}%");
        }
        if ($request_ip) {
            $query->where('ahr.request_ip', $request_ip);
        }
        $query->orderByDesc('ac.id');

        //获取列表
        $list = $this->getPagingRows($query, function (LengthAwarePaginator $paginator) {
            $paginator->getCollection()->transform(function ($row) {
                return [
                    'id'            => $row['id'],
                    'app_id'        => $row['app_id'],
                    'app_name'      => $row['app_name'],
                    'platform_id'   => $row['platform_id'],
                    'platform_name' => $row['platform_name'],
                    'data'          => $row['data'],
                    'request_ip'    => $row['request_ip'],
                    'status'        => $row['status'],
                    'status_remark' => AppHealthHeck::STATUS_MAP[$row['status']] ?? '',
                    'created_time'  => $row['created_ts'] ? func_datetime_trans((int)$row['created_ts'], DEFAULT_TIMEZONE) : '-',
                    'updated_time'  => $row['updated_ts'] ? func_datetime_trans((int)$row['updated_ts'], DEFAULT_TIMEZONE) : '-',
                ];
            });
            return $paginator;
        });
        return $this->success($list);
    }

    /**
     * 修改状态
     * @param Request $request
     * @return mixed
     * @throws ValidationException
     */
    public function setStatus(Request $request)
    {
        $this->validate($request, [
            'ids'   => 'required',
            'status'        => 'required',
        ]);
        $ids    = $request->post('ids');
        $status = $request->post('status');
        $id_arr = func_get_param_ids($ids);
        if (!isset(AppHealthHeck::STATUS_MAP[$status])) {
            return $this->failed('状态参数错误');
        }
        if (empty($id_arr)) {
            return $this->success();
        }
        try {
            AppHealthHeck::query()
                ->whereIn('id', $id_arr)
                ->update([
                    'status'     => $status,
                    'updated_ts' => time(),
                ]);
            return $this->success();
        } catch (\Exception $e) {
            return $this->failed($e->getMessage());
        }
    }


}
