<?php

namespace App\Http\Controllers\Admin\Apps;

use App\Http\Controllers\App\BaseController;
use App\Models\Devops\Apps\AppHealthRequestDetail;
use App\Models\Devops\Apps\Apps;
use App\Models\Devops\Platform\Platform;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * 应用健康汇报细目
 * Class AppHealthRequestDetailController
 * @package App\Http\Controllers\Admin\Apps
 */
class AppHealthRequestDetailController extends BaseController
{
    /**
     * 列表
     * @param Request $request
     * @return mixed
     * @throws ValidationException
     */
    public function detail(Request $request)
    {
        $this->validate($request, [
            'request_id' => 'required'
        ]);
        $request_id = $request->post('request_id');

        $app_health_request_detail_table = (new AppHealthRequestDetail())->getTable();
        $app_table      = (new Apps())->getTable();
        $platform_table = (new Platform())->getTable();

        $result = AppHealthRequestDetail::query()
            ->select([
                'd.*',
                'a.app_name',
                'p.id as platform_id', 'p.name as platform_name',
            ])
            ->from("{$app_health_request_detail_table} as d")
            ->leftJoin("{$app_table} as a", 'a.id', '=', 'd.app_id')
            ->leftJoin("{$platform_table} as p", 'p.id', '=', 'a.platform_id')
            ->where('request_id', $request_id)
            ->orderByDesc('d.id')
            ->get()->toArray();

        $list = [];
        foreach ($result as $row) {
            $list[] = [
                'id'            => $row['id'],
                'app_id'        => $row['app_id'],
                'app_name'      => $row['app_name'],
                'platform_id'   => $row['platform_id'],
                'platform_name' => $row['platform_name'],
                'msg'           => $row['msg'],
                'created_time'  => $row['created_ts'] ? func_datetime_trans((int)$row['created_ts'], DEFAULT_TIMEZONE) : '-',
                'updated_time'  => $row['updated_ts'] ? func_datetime_trans((int)$row['updated_ts'], DEFAULT_TIMEZONE) : '-',
            ];
        }
        return $this->success($list);
    }

}
