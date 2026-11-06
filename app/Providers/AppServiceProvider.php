<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\Paginator;
use App\Models\KelompokUrusan;
use App\Models\Tahun;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        View::composer('*', function ($view) {
            static $sidebarKelompok = null;

            if ($sidebarKelompok === null && Schema::hasTable('kelompok_urusan')) {
                try {
                    $allKelompok = KelompokUrusan::with(['urusans' => function ($q) {
                        $q->where('status_aktif', true)->withCount('indikators')->orderBy('urutan');
                    }])->orderBy('urutan')->get();
                    $byId = $allKelompok->keyBy('id');
                    $allKelompok->each(function ($kelompok) {
                        $kelompok->setRelation('children', collect());
                    });
                    foreach ($allKelompok as $kelompok) {
                        if ($kelompok->parent_id && $byId->has($kelompok->parent_id)) {
                            $parent = $byId->get($kelompok->parent_id);
                            $parent->getRelation('children')->push($kelompok);
                        }
                    }
                    $sidebarKelompok = $allKelompok->filter(function ($kelompok) {
                        return $kelompok->parent_id === null;
                    })->values();
                } catch (\Exception $e) {
                    $sidebarKelompok = collect();
                }
            }

            $listTahunGlobal = collect();
            if (Schema::hasTable('tahun')) {
                try {
                    $listTahunGlobal = Tahun::syncActiveYearsFromSystem();
                } catch (\Exception $e) {
                    $listTahunGlobal = collect();
                }
            }

            $view->with('sidebarKelompok', $sidebarKelompok ?? collect());
            $view->with('listTahunGlobal', $listTahunGlobal ?? collect());
        });
    }
}
