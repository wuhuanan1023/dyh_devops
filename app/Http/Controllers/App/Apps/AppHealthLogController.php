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
        ]);
        $app_key    = $request->post('app_key');
        $data       = $request->post('data');

        //错误App
        if (!$app = Apps::query()->where('app_key', $app_key)->first()) {
            return $this->failed('Invalid param: app_key');
        }

        //默认正常
        $status = AppHealthRequest::STATUS_NORMAL;

        $health_codes = [];
        foreach ($data as $item) {
            $code = $item['code'] ?? 0;
            $health_codes[] = $code;
            //错误code
            if (!isset(AppHealthRequest::SERVER_CODE_MAP[$code])) {
                return $this->failed('Invalid param: data');
            }
            if ($code != AppHealthRequest::SERVER_SUCCESS) {
                $status = AppHealthRequest::STATUS_ERROR;
            }
        }

        DB::beginTransaction();
        try {
            $request = AppHealthRequest::query()->create([
                'app_id'        => $app->id,
                'health_codes'  => json_encode($health_codes), //健康码，[20000, 50000]
                'data'          => json_encode($data),
                'status'        => $status, //状态：0-未知；1-正常；2-异常；
                'created_ts'    => time(),
                'updated_ts'    => time(),
            ]);

            $id = $request->id;
            foreach ($data as $item) {
                $code = $item['code'] ?? 0;
                $msg = $item['msg'] ?? '';
                AppHealthRequestDetail::query()->create([
                    'app_id'        => $app->id,
                    'request_id'    => $id,
                    'health_code'   => $code,
                    'msg'           => $msg,
                    'created_ts'    => time(),
                    'updated_ts'    => time(),
                ]);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('app_health_check')->error(json_encode([
                'time'      => date('Y-m-d H:i:s'),
                'data'      => json_encode($data),
                'error'     => $e->getMessage(),
            ]));
            return $this->failed($e->getMessage());
        }
        return $this->success();
    }



}
