<?php

namespace App\Http\Controllers\Admin\Apps;

use App\Http\Controllers\App\BaseController;
use App\Models\Devops\Apps\AppContact;
use App\Models\Devops\Apps\AppHealthCheckDetail;
use App\Models\Devops\Apps\Apps;
use App\Models\Devops\Apps\AppWarning;
use App\Models\Devops\Platform\Platform;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

/**
 * 应用告警
 * Class AppWarningController
 * @package App\Http\Controllers\Admin\Apps
 */
class AppWarningController extends BaseController
{

    /**
     * 选项
     * @return mixed
     */
    public function option()
    {
        $status = [];
        foreach (AppWarning::STATUS_MAP as $key => $value) {
            $status[] = ['key'=> $key, 'value' => $value];
        }
        $level = [];
        foreach (AppWarning::LEVEL_MAP as $key => $value) {
            $level[] = ['key'=> $key, 'value' => $value];
        }
        return $this->success([
            'status' => $status,
            'level'  => $level
        ]);
    }


    /**
     * 列表
     * @param Request $request
     * @return mixed
     */
    public function list(Request $request)
    {
        $app_id = $request->post('app_id');
        $level = $request->post('level');
        $content = $request->post('content');
        $request_ip = $request->post('request_ip');
        $status = $request->post('status');

        $app_warning_table = (new AppWarning())->getTable();
        $app_table      = (new Apps())->getTable();
        $platform_table = (new Platform())->getTable();

        $query = AppWarning::query()
            ->select([
                'w.*',
                'a.app_name',
                'p.id as platform_id', 'p.name as platform_name',
            ])
            ->from("{$app_warning_table} as w")
            ->leftJoin("{$app_table} as a", 'a.id', '=', 'w.app_id')
            ->leftJoin("{$platform_table} as p", 'p.id', '=', 'a.platform_id');
        if (is_numeric($app_id) && $app_id) {
            $query->where('w.app_id', $app_id);
        }
        if (is_numeric($status)) {
            $query->where('w.status', $status);
        }
        if (is_numeric($level)) {
            $query->where('w.level', $level);
        }
        if ($request_ip) {
            $query->where('w.request_ip', $request_ip);
        }
        if ($content) {
            $query->where('w.content', 'like', "%{$content}%");
        }
        $query->orderByDesc('w.id');
        //获取列表
        $list = $this->getPagingRows($query, function (LengthAwarePaginator $paginator) {
            $paginator->getCollection()->transform(function ($row) {
                return [
                    'id'            => $row['id'],
                    'app_id'        => $row['app_id'],
                    'app_name'      => $row['app_name'],
                    'platform_id'   => $row['platform_id'],
                    'platform_name' => $row['platform_name'],
                    'level'         => $row['level'],
                    'level_remark'  => AppWarning::LEVEL_MAP[$row['level']] ?? '未知',
                    'content'       => $row['content'],
                    'request_ip'    => $row['request_ip'],
                    'status'        => $row['status'],
                    'status_remark' => AppContact::STATUS_MAP[$row['status']] ?? '',
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
        if (!isset(AppWarning::STATUS_MAP[$status])) {
            return $this->failed('状态参数错误');
        }
        if (empty($id_arr)) {
            return $this->success();
        }
        try {
            AppWarning::query()
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
