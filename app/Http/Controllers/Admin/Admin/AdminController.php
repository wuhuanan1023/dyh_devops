<?php

namespace App\Http\Controllers\Admin\Admin;


use App\Http\Controllers\Admin\BaseController;
use App\Models\Devops\Admin\Admin;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AdminController extends BaseController
{


    /**
     * 选项
     * @return mixed
     */
    public function option()
    {
        $status = [];
        foreach (Admin::STATUS_MAP as $key => $value) {
            $status[] = ['key'=> $key, 'value' => $value];
        }

        $is_super = [];
        foreach (Admin::IS_SUPER_MAP as $key => $value) {
            $is_super[] = ['key'=> $key, 'value' => $value];
        }

        return $this->success([
            'status' => $status,
            'is_super' => $is_super
        ]);
    }


    /**
     * 全部员工
     * @return mixed
     */
    public function all()
    {
        $res = Admin::query()
            ->select('id', 'username', 'nickname')
            ->where('status', Admin::STATUS_ON)
            ->get()->toArray();
        $list = [];
        foreach ($res as $item) {
            $list[] = ['key' => $item['id'], 'val' => $item['nickname'] ?: ($item['username'] ?: '')];
        }
        return $this->success(['list' => $list]);
    }

    /**
     * 列表
     * @param Request $request
     * @return mixed
     */
    public function list(Request $request)
    {
        $username   = $request->post('username');
        $nickname   = $request->post('nickname');
        $phone      = $request->post('phone');
        $email      = $request->post('email');
        $status     = $request->post('status');
        $is_super   = $request->post('is_super');

        $query = Admin::query();
        if (is_numeric($status)) {
            $query->where('status', $status);
        }
        if (is_numeric($is_super)) {
            $query->where('is_super', $is_super);
        }
        if ($username) {
            $query->where('username', 'like', "%{$username}%");
        }
        if ($nickname) {
            $query->where('nickname', 'like', "%{$nickname}%");
        }
        if ($phone) {
            $query->where('phone', 'like', "%{$phone}%");
        }
        if ($email) {
            $query->where('email', 'like', "%{$email}%");
        }
        $query->orderByDesc('id');

        //获取列表
        $list = $this->getPagingRows($query, function (LengthAwarePaginator $paginator) {
            $paginator->getCollection()->transform(function ($row) {
                return [
                    'id'            => $row['id'],
                    'username'      => $row['username'],    //管理员账号
                    'nickname'      => $row['nickname'],//昵称
                    'phone'         => $row['phone'],//手机号
                    'email'         => $row['email'],//邮箱
                    'avatar'        => func_full_oss_url($row['avatar']), //头像
                    'status'        => $row['status'],
                    'status_remark' => Admin::STATUS_MAP[$row['status']] ?? '未知',
                    'is_super'      => $row['is_super'],
                    'is_super_remark' => Admin::IS_SUPER_MAP[$row['is_super']] ?? '',
                    'created_time'  => $row['created_ts'] ? func_datetime_trans((int)$row['created_ts'], DEFAULT_TIMEZONE) : '-',
                    'updated_time'  => $row['updated_ts'] ? func_datetime_trans((int)$row['updated_ts'], DEFAULT_TIMEZONE) : '-',
                ];
            });
            return $paginator;
        });
        return $this->success($list);
    }


    /**
     * 创建管理员
     * @param Request $request
     * @return mixed
     * @throws ValidationException
     */
    public function add(Request $request)
    {
        $this->validate($request, [
            'username' => 'required',
            'password' => 'required',
            'status'   => 'required',
            'is_super' => 'required',
        ]);
        $username   = $request->post('username');
        $password   = $request->post('password');
        $status     = $request->post('status');
        $is_super   = $request->post('status');
        $nickname   = $request->post('nickname', '');
        $phone      = $request->post('phone', '');
        $email      = $request->post('email', '');
        $remark     = $request->post('remark', '');

        if (!isset(Admin::STATUS_MAP[$status])) {
            return $this->failed('状态参数错误');
        }
        if (!isset(Admin::IS_SUPER_MAP[$is_super])) {
            return $this->failed('是否超管参数错误');
        }
        try {
            Admin::query()->create([
                'username'  => $username,
                'nickname'  => $nickname,
                'password'  => Hash::make($password),
                'phone'     => $phone,
                'email'     => $email,
                'remark'    => $remark,
                'status'    => $status,
                'is_super'  => $is_super,
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
            'id'       => 'required',
            'status'   => 'required',
            'is_super' => 'required',
        ]);
        $id             = $request->post('contact_id');
        $status     = $request->post('status');
        $is_super   = $request->post('is_super');
        $nickname   = $request->post('nickname', '');
        $phone      = $request->post('phone', '');
        $email      = $request->post('email', '');
        $remark     = $request->post('remark', '');

        if (!isset(Admin::STATUS_MAP[$status])) {
            return $this->failed('状态参数错误');
        }
        if (!isset(Admin::IS_SUPER_MAP[$is_super])) {
            return $this->failed('是否超管参数错误');
        }
        if (!$admin = Admin::query()->find($id)) {
            return $this->failed('未能找到相关管理员');
        }
        try {
            $admin->nickname    = $nickname;
            $admin->phone       = $phone;
            $admin->email       = $email;
            $admin->remark      = $remark;
            $admin->status      = $status;
            $admin->is_super    = $is_super;
            $admin->updated_ts  = time();
            $admin->save();
        } catch (\Exception $e) {
            return $this->failed($e->getMessage());
        }
        return $this->success();
    }


    /**
     * 删除
     * @param Request $request
     * @return mixed
     * @throws ValidationException
     */
    public function del(Request $request)
    {
        $this->validate($request, [
            'id' => 'required',
        ]);
        $id = $request->post('id');
        if (!$admin = Admin::query()->find($id)) {
            return $this->failed('未能找到相关管理员');
        }

        DB::beginTransaction();
        try {
            // 删除员工
            Admin::query()->where('id', $id)->delete();
            DB::commit();
            return $this->success();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->failed();
        }
    }

    /**
     * 设置密码
     * @param Request $request
     * @return mixed
     * @throws ValidationException
     */
    public function setPassword(Request $request)
    {
        $this->validate($request, [
            'id' => 'required',
            'password' => 'required',
        ]);
        $id = $request->post('id');
        $password = $request->post('password');

        if (!$admin = Admin::query()->find($id)) {
            return $this->failed('未能找到相关管理员');
        }
        //检测与旧密码是否匹配
        if (Hash::check($password, $admin->password)){
            return $this->failed('密码未作改变');
        }
        DB::beginTransaction();
        try {
            $admin->password    = Hash::make($password);
            $admin->updated_ts  = time();
            $admin->save();
            DB::commit();
            return $this->success();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->failed();
        }
    }

    /**
     * 设置密码
     * @param Request $request
     * @return mixed
     * @throws ValidationException
     */
    public function setAvatar(Request $request)
    {
        $this->validate($request, [
            'avatar' => 'required',
        ]);
        $id     = $request->post('id');
        $avatar = $request->post('avatar');

        $admin_id = $id ?: $this->user()->id;

        if (!$admin = Admin::query()->find($admin_id)) {
            return $this->failed('未能找到相关管理员');
        }

        DB::beginTransaction();
        try {
            $admin->avatar      = $avatar;
            $admin->updated_ts  = time();
            $admin->save();
            DB::commit();
            return $this->success();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->failed();
        }
    }


}
