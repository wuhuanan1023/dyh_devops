<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\App\BaseController;
use App\Models\Devops\Admin\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Laravel\Lumen\Application;

class AuthController extends BaseController
{

    /**
     * 登录页
     * @return View|Application
     */
    public function loginView()
    {
        return view('admin.auth.login');
    }



    /**
     * 账户登录
     * @param Request $request
     * @return mixed
     * @throws ValidationException
     */
    public function login(Request $request)
    {
        //数据验证
        $this->validate($request, [
            'username' => 'required',
            'password' => 'required',
        ]);

        $username = $request->post('username');
        $password = $request->post('password');

        $admin = Admin::query()->where('username', $username)->first();
        if (!$admin) {
            return $this->failed('账号不存在!');
        }
        // 判断用户状态
        if ($admin->status === Admin::STATUS_OFF) {
            return $this->failed('账号被禁用');
        }
        // 登录密码验证
        if (!Hash::check($password, $admin->password)) {
            return $this->failed('密码错误');
        }

        $token = Auth::guard($this->guard)->login($admin);
        return $this->success([
            'token'      => $token,
            'admin_info' => [
                'admin_id' => $admin->id,
                'username' => $admin->nickname ?: $admin->username,
                'nickname' => $admin->nickname,
                'avatar'   => $admin->avatar,
                'is_super' => $admin->is_super ?: 0
            ],
        ]);

    }

    /**
     * 修改密码
     * @param Request $request
     * @return mixed
     * @throws ValidationException
     */
    public function resetPwd(Request $request)
    {
        $this->validate($request, [
            'old_password' => 'required|min:6',
            'new_password' => 'required|confirmed|min:6'
        ]);

        $old_password   = $request->post('old_password');
        $new_password   = $request->post('new_password');

        //获取当前账户ID
        $admin = $this->user();

        //检测旧密码是否匹配
        if (!Hash::check($old_password, $admin->password))
        {
            return $this->failed(__('原密码输入错误'));
        }
        if ($old_password == $new_password)
        {
            return $this->failed(__('新旧密码不能一致'));
        }

        $admin->password = Hash::make($new_password);
        $admin->save();

        return $this->success();

    }




    /**
     * 账户登出
     * @return mixed
     */
    public function logout()
    {

        if (Auth::guard($this->guard)->guest() === false) {
            $this->invalidate();
            Auth::guard($this->guard)->logout();
        }
        return $this->success();
    }

}
