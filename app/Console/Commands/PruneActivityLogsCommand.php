<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use Illuminate\Console\Command;

class PruneActivityLogsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'activity-log:prune {--days=180 : Batas usia log dalam hari yang akan dipertahankan}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Membersihkan log aktivitas yang lebih lama dari jumlah hari tertentu';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days') ?: 180;

        if ($days <= 0) {
            $this->error('Parameter --days harus berupa angka bulat positif.');
            return self::FAILURE;
        }

        $cutoff = now()->subDays($days);
        $this->info("Menghitung log aktivitas sebelum: {$cutoff->toDateTimeString()} ({$days} hari lalu)...");

        $query = ActivityLog::where('created_at', '<', $cutoff);
        $count = $query->count();

        if ($count === 0) {
            $this->info('Tidak ada log aktivitas lama yang perlu dibersihkan.');
            return self::SUCCESS;
        }

        $deleted = $query->delete();
        $this->info("Berhasil membersihkan {$deleted} catatan log aktivitas.");

        // Catat tindakan pembersihan ke Activity Log sistem
        ActivityLog::create([
            'user_id' => null,
            'action' => 'activity_logs_pruned',
            'description' => "Pembersihan otomatis {$deleted} catatan log aktivitas lebih lama dari {$days} hari.",
            'ip_address' => '127.0.0.1',
        ]);

        return self::SUCCESS;
    }
}

