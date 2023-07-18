<?php

namespace App\Console\Commands\Test;

use App\Models\Devops\Apps\Apps;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class Test extends Command
{

    /**
     * 命令行执行命令
     * php artisan test
     * @var string
     */
    protected $signature = "test {time=60}";

    /** 命令描述 @var string */
    protected $description = '';

    protected $sleep = 5;

    public function handle()
    {
        $sleep = $this->argument('time'); //任务间隔时间


        //

        dd(
            Hash::make('admin123456')
        );



        #############  生成 APP  ##############
        $name = 'Vod_Api';
        $app_key = Apps::createAppKey();
        $app_secret = Apps::createAppSecret();
        $remark = '';
        $status = Apps::APP_STATUS_ON;
        dd(
            Apps::createApp($name, $app_key, $app_secret, $remark, $status)
        );

        echo time();
    }



}
