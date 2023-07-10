<?php

namespace App\Http\Controllers\App\Auth;

use App\Http\Controllers\App\BaseController;
use App\Models\Manhe\Sms\SmsLog;
use App\Models\Manhe\Sms\SmsTpl;
use App\Models\Manhe\User\User;
use App\Models\Manhe\User\UserAuth;
use App\Models\Manhe\User\UserCoins;
use App\Models\Manhe\User\UserLoginLog;
use App\Models\Manhe\User\UserRegisterLog;
use App\Services\Sms\SmsBaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends BaseController
{

    /**
     * 短信验证码登录注册
     * @param Request $request
     * @return mixed
     * @throws ValidationException
     */
    public function mobileLogin(Request $request)
    {
        $this->validate($request,
            [
                'mobile'    => 'required',
                'captcha'   => 'required',
            ],
        );
        //电话号码
        $mobile = $request->post('mobile');
        if (!UserAuth::validatePhone($mobile)) {
            DB::rollBack();
            return $this->failed(__('手機號碼錯誤'));
        }
        //验证码
        $captcha = $request->post('captcha');

        //是否弹出填写邀请码
        $is_invite = 0;

        try {
            //开启事务
            DB::beginTransaction();

            //验证验证码
            if (!SmsBaseService::validateCode(SmsTpl::TYPE_LOGIN, $mobile, $captcha)) {
                DB::rollBack();
                return $this->failed(__('驗證碼錯誤'));
            }

            //IP
            $ip = func_app_ip();
            //国家
            $country = func_ip_trans_country($ip);

            //更新短信记录表
            SmsLog::query()
                ->where('tpl_type', SmsTpl::TYPE_LOGIN)
                ->where('mobile', $mobile)
                ->where('captcha', $captcha)
                ->update([
                    'is_used' => 1
                ]);

            //是否注册
            $user = User::query()
                ->where('mobile',$mobile)
                ->first();
            //未注册 则注册
            if(!$user){
                //创建用户
                $user = User::query()->create([
                    'username' => uniqid().Str::random(4),
                    'password' => Hash::make(Str::random(8)),
                    'mobile' => $mobile,
                    'avatar' => User::DEFAULT_AVATAR,
                    'last_login_ts' => time(),
                    'last_login_ip' => $ip,
                    'last_login_country' => $country,
                    'invite_code' => func_invite_code(),
                ]);
                //用户创建日志
                UserRegisterLog::query()->create([
                    'user_id'       => $user->id,
                    'version'       => func_app_version(),
                    'version_code'  => func_app_version_code(),
                    'country'       => $country,
                    'ip'            => $ip,
                    'imei'          => func_app_imei(),
                    'android_id'    => func_app_anid(),
                    'advertising_id'=> func_app_adid(),
                    'created_ts'    => time(),
                ]);
                //初始化金币
                UserCoins::query()->create([
                    'user_id'      => $user->id,
                    'all_coins'    => 0,
                    'buy_coins'    => 0,
                    'give_coins'   => 0,
                    'freeze_all_coins' => 0,
                    'modify_ts'    => time(),
                ]);
                //注册弹出邀请码
                $is_invite = 1;
            }

            //登录TOKEN
            $token = $this->token($user);
            //登录日志
            UserLoginLog::query()->create([
                'user_id' => $user->id,
                'imei'          => func_app_imei(),
                'android_id'    => func_app_anid(),
                'advertising_id'=> func_app_adid(),
                'ip' => $ip,
                'country' => $country,
                'login_ts' => time(),
            ]);

            DB::commit();
            return $this->success([
                'user_id'   => $user->id,
                'phone'     => func_mask_mobile($user->mobile),
                'token'     => $token,
                'is_invite' => $is_invite
            ]);

        }catch (\Exception $e){
            DB::rollBack();
            return $this->failed();
        }

    }

    /**
     * 密码登录
     * @param Request $request
     * @return mixed
     * @throws ValidationException
     */
    public function pwdLogin(Request $request)
    {

        $this->validate($request,
            [
                'mobile'       => 'required',
                'password'     => 'required|min:6|max:16',
            ],
            [
                'password.min' => __('密碼格式為6-16位'),
                'password.max' => __('密碼格式為6-16位')
            ]
        );

        //电话号码
        $mobile = $request->post('mobile');
        //密码
        $password = $request->post('password');

        if(!$user = User::query()
            ->where('mobile',$mobile)
            ->first()){
            return $this->failed(__('該手機號未註冊'));
        }
        //验证密码
        if (!Hash::check($password, $user->password)) {
            return $this->failed(__('輸入的密碼不正確'));
        }

        try {
            DB::beginTransaction();
            //IP
            $ip = func_app_ip();
            //国家
            $country = func_ip_trans_country($ip);

            //登录TOKEN
            $token = $this->token($user);
            //登录日志
            UserLoginLog::query()->create([
                'user_id' => $user->id,
                'imei'          => func_app_imei(),
                'android_id'    => func_app_anid(),
                'advertising_id'=> func_app_adid(),
                'ip' => $ip,
                'country' => $country,
                'login_ts' => time(),
            ]);

            //更新最后登录数据
            $user->last_login_ts = time();
            $user->last_login_ip = $ip;
            $user->last_login_country = $country;
            $user->save();

            DB::commit();
            return $this->success([
                'user_id'   => $user->id,
                'phone'     => func_mask_mobile($user->mobile),
                'token'     => $token,
                'is_invite' => 0
            ]);
        }catch (\Exception $e){
            DB::rollBack();
            return $this->failed();
        }

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
