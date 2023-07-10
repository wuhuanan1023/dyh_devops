<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * 注册命令
     * The Artisan commands provided by your application.
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\Test\Test::class,
    ];

    /**
     * 调度器
     * Define the application's command schedule.
     * @param Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //
        // 每分钟执行
        $schedule->command(\App\Console\Commands\Test\TestSchedule::class)->everyMinute()->withoutOverlapping()->runInBackground();
    }
}
