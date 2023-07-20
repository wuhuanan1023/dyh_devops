<?php

namespace App\Http\Controllers\Admin\Apps;

use App\Http\Controllers\App\BaseController;
use App\Models\Devops\Apps\AppContact;
use App\Models\Devops\Apps\Apps;
use App\Models\Devops\Platform\Platform;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

/**
 * 应用联系人
 * Class AppContactController
 * @package App\Http\Controllers\Admin\Apps
 */
class AppContactController extends BaseController
{

    /**
     * 选项
     * @return mixed
     */
    public function option()
    {
        $status = [];
        foreach (AppContact::STATUS_MAP as $key => $value) {
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
        $app_id         = $request->post('app_id');
        $contact_name   = $request->post('contact_name');  //应用联系人
        $contact_phone  = $request->post('contact_phone'); //应用联系人电话
        $status         = $request->post('status');

        $app_contact_table  = (new AppContact())->getTable();
        $app_table          = (new Apps())->getTable();
        $platform_table     = (new Platform())->getTable();

        $query = AppContact::query()
            ->select([
                'ac.*',
                'a.app_name',
                'p.id as platform_id', 'p.name as platform_name',
            ])
            ->from("{$app_contact_table} as ac")
            ->leftJoin("{$app_table} as a", 'a.id', '=', 'ac.app_id')
            ->leftJoin("{$platform_table} as p", 'p.id', '=', 'ac.platform_id');

        //未删除的
        $query->where('ac.deleted_ts', 0);

        if (is_numeric($app_id) && $app_id) {
            $query->where('ac.app_id', $app_id);
        }
        if (is_numeric($status)) {
            $query->where('ac.status', $status);
        }
        if ($contact_name) {
            $query->where('ac.contact_name', 'like', "%{$contact_name}%");
        }
        if ($contact_phone) {
            $query->where('ac.contact_phone', $contact_phone);
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
                    'contact_name'  => $row['contact_name'],
                    'contact_phone' => $row['contact_phone'],
                    'sort'          => $row['sort'],
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
     * 创建 联系人
     * @param Request $request
     * @return mixed
     * @throws ValidationException
     */
    public function add(Request $request)
    {
        $this->validate($request, [
            'app_id'      => 'required',
            'contact_name'    => 'required',
            'contact_phone'   => 'required',
        ]);
        $app_id         = $request->post('app_id');
        $contact_name   = $request->post('contact_name');
        $contact_phone  = $request->post('contact_phone');
        $sort           = $request->post('sort', 0);
        $status         = $request->post('status', AppContact::STATUS_ON);

        if (!isset(AppContact::STATUS_MAP[$status])) {
            return $this->failed('状态参数错误');
        }
        if (!$app = Apps::query()->find($app_id)) {
            return $this->failed('未能找到相关应用');
        }

        try {
            AppContact::query()->create([
                'platform_id' => $app->platform_id,
                'app_id' => $app_id,
                'contact_name' => $contact_name,
                'contact_phone' => $contact_phone,
                'sort' => $sort,
                'status' => $status,
                'created_ts' => time(),
                'updated_ts' => time(),
            ]);
            return $this->success();
        } catch (\Exception $e) {
            return $this->failed($e->getMessage());
        }
    }

    /**
     * 修改APP联系人
     * @param Request $request
     * @return mixed
     * @throws ValidationException
     */
    public function edit(Request $request)
    {
        $this->validate($request, [
            'contact_id'    => 'required',
            'contact_name'  => 'required',
            'contact_phone' => 'required',
        ]);
        $id             = $request->post('contact_id');
        $contact_name   = $request->post('contact_name');
        $contact_phone  = $request->post('contact_phone');
        $sort           = $request->post('sort', 0);
        $status         = $request->post('status', AppContact::STATUS_ON);

        if (!isset(AppContact::STATUS_MAP[$status])) {
            return $this->failed('状态参数错误');
        }
        if (!$app_contact = AppContact::query()->find($id)) {
            return $this->failed('未能找到相关应用联系人');
        }
        try {
            $app_contact->contact_name = $contact_name;
            $app_contact->contact_phone = $contact_phone;
            $app_contact->sort = $sort;
            $app_contact->status = $status;
            $app_contact->updated_ts = time();
            $app_contact->save();
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
            'contact_ids' => 'required',
        ]);
        $ids = $request->post('contact_ids');
        $id_arr = func_get_param_ids($ids);

        if (empty($id_arr)) {
            return $this->success();
        }
        try {
            AppContact::query()->whereIn('id', $id_arr)->update([
                'status'     => AppContact::STATUS_OFF,
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
            'contact_ids'   => 'required',
            'status'        => 'required',
        ]);
        $ids    = $request->post('contact_ids');
        $status = $request->post('status');
        $id_arr = func_get_param_ids($ids);
        if (!isset(AppContact::STATUS_MAP[$status])) {
            return $this->failed('状态参数错误');
        }
        if (empty($id_arr)) {
            return $this->success();
        }
        try {
            AppContact::query()
                ->where('deleted_ts', 0)
                ->whereIn('id', $id_arr)
                ->update([
                    'status' => $status,
                    'updated_ts' => time(),
                ]);
            return $this->success();
        } catch (\Exception $e) {
            return $this->failed($e->getMessage());
        }
    }


}
