<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Authenticated user ($u) ကို view အားလုံးသို့ မျှဝေ
        View::composer('*', function ($view) {
            $view->with('u', auth()->user());
        });
    }
}
