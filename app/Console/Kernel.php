<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Auto Daily Check-in dijalankan setiap hari pada pukul 01:00 UTC (08:00 WIB/reset harian HoYoverse)
        $schedule->command('hoyolab:auto-checkin')
            ->dailyAt('01:00')
            ->withoutOverlapping()
            ->runInBackground();

        // Pengecekan Resin Alert setiap 30 menit
        $schedule->command('hoyolab:check-resin-alerts')
            ->everyThirtyMinutes()
            ->withoutOverlapping()
            ->runInBackground();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
