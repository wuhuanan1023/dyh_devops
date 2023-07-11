<?php

namespace App\Http\Controllers\App\Apps;

use App\Http\Controllers\App\BaseController;
use App\Models\Devops\Apps\AppHealthRequest;
use App\Models\Devops\Apps\AppHealthRequestDetail;
use App\Models\Devops\Apps\Apps;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;


class AppHealthLogController extends BaseController
{

    /**
     * 健康检查
     * @param Request $request
     * @return mixed
     * @throws ValidationException
     */
    public function healthCheck(Request $request)
    {
        $this->validate($request, [
            'app_key'   => 'required',
            'data'      => 'required',
            'status'    => 'required|int',
        ]);
        $app_key    = $request->post('app_key');
        $data       = $request->post('data');
        $status     = $request->post('status'); //状态：0-未知；1-正常；2-异常；

        //错误App
        if (!$app = Apps::query()->where('app_key', $app_key)->first()) {
            return $this->failed('Unknown app_key');
        }
        //错误状态值
        if (!in_array($status, [AppHealthRequest::STATUS_NORMAL, AppHealthRequest::STATUS_ERROR])) {
            return $this->failed('Invalid status');
        }

        DB::beginTransaction();
        try {
            $request = AppHealthRequest::query()->create([
                'app_id'        => $app->id,
                'data'          => is_array($data) ? json_encode($data) : $data,
                'status'        => $status, //状态：0-未知；1-正常；2-异常；
                'created_ts'    => time(),
                'updated_ts'    => time(),
            ]);

            $id = $request->id;

            //insert data
            $i_data = [
                'app_id'        => $app->id,
                'request_id'    => $id,
                'created_ts'    => time(),
                'updated_ts'    => time()
            ];
            if ($status == AppHealthRequest::STATUS_NORMAL) {
                $i_data['msg'] = '';
                AppHealthRequestDetail::query()->create($i_data);
            } else {
                foreach ($data as $msg) {
                    $i_data['msg'] = $msg;
                    AppHealthRequestDetail::query()->create($i_data);
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('app_health_check')->error(json_encode([
                'time'      => date('Y-m-d H:i:s'),
                'data'      => is_array($data) ? json_encode($data) : $data,
                'error'     => $e->getMessage(),
            ]));
            return $this->failed($e->getMessage());
        }
        return $this->success();
    }



}
