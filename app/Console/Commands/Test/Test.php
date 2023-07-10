<?php

namespace App\Console\Commands\Test;

use Illuminate\Console\Command;

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
        $sleep = $this->argument('sleep'); //任务间隔时间
        echo time();
    }

}
