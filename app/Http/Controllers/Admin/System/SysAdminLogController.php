<?php

namespace App\Http\Controllers\Admin\System;

use App\Http\Controllers\Admin\BaseController;
use App\Models\Devops\Admin\Admin;
use App\Models\Devops\System\SysAdminLog;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class SysAdminLogController extends BaseController
{

    /**
     * 获取管理员日志列表
     * @param Request $request
     * @return mixed
     */
    public function list(Request $request)
    {
        $admin_id  = $request->post('admin_id');

        $admin_table         = (new Admin())->getTable();
        $sys_admin_log_table = (new SysAdminLog())->getTable();
        $sql = SysAdminLog::query()
            ->select([
                'l.*',
                'a.username', 'a.nickname'
            ])
            ->from("{$sys_admin_log_table} as l")
            ->leftJoin("{$admin_table} as a", 'a.id', '=', 'l.admin_id');

        if(is_numeric($admin_id) && $admin_id) {
            $sql->where('l.admin_id', $admin_id);
        }
        $sql->orderBy('l.id', 'desc');
        $list = $this->getPagingRows($sql, function (LengthAwarePaginator $paginator) {
            $paginator->getCollection()->transform(function ($row) {
                return [
                    'id'            => $row['id'],
                    'admin_id'      => $row['admin_id'],
                    'username'      => $row['username'],
                    'nickname'      => $row['nickname'],
                    'request_ip'    => $row['request_ip'],
                    'request_url'   => $row['request_url'],
                    'request_data'  => $row['request_data'],
                    'response_data' => $row['response_data'],
                    'created_ts' => $row['created_ts'] ? func_datetime_trans((int)$row['created_ts']) : '',
                ];
            });
            return $paginator;
        });
        return $this->success($list);
    }



}
