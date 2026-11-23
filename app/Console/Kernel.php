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
        // Pilar 4: Otomatisasi Backup Database harian pada pukul 02:00
        $schedule->command('db:backup --keep=14')
            ->dailyAt('02:00')
            ->runInBackground();

        // Pilar 4: Otomatisasi Pembersihan Log Aktivitas (>180 hari) setiap hari Minggu pukul 03:00
        $schedule->command('activity-log:prune --days=180')
            ->weeklyOn(0, '03:00')
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
