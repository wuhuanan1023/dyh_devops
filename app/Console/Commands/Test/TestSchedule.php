<?php

namespace App\Console\Commands\Test;

use Illuminate\Console\Command;

class TestSchedule extends Command
{

    /**
     * 命令行执行命令
     * php artisan test:schedule
     * @var string
     */
    protected $signature = "test:schedule {time=60}";

    /** 命令描述 @var string */
    protected $description = '';

    protected $sleep = 5;

    public function handle()
    {
        return true;
    }

}
