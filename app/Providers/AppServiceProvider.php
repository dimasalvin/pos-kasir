<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Barang;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Share stok rendah count ke semua view (untuk notifikasi sidebar)
        View::composer('layouts.dashboard', function ($view) {
            if (auth()->check()) {
                $stokRendahCount = Barang::stokRendah()->count();
                $view->with('stokRendahCount', $stokRendahCount);
            }
        });
    }
}
