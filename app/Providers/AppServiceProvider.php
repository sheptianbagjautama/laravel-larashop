<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

//untuk memanage akses ke route route yang ada 
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);

        Gate::define('manage-users', function($user){
            // TODO: logika untuk mengizinkan manage users
            return count(array_intersect(["ADMIN"], json_decode($user->roles)));
        });
      
        Gate::define('manage-categories', function($user){
            // TODO: logika untuk mengizinkan manage categories
            return count(array_intersect(["ADMIN", "STAFF"], json_decode($user->roles)));
        });
      
        Gate::define('manage-books', function($user){
            // TODO: logika untuk mengizinkan manage books
            return count(array_intersect(["ADMIN", "STAFF"], json_decode($user->roles)));
        });
      
        Gate::define('manage-orders', function($user){
            // TODO: logika untuk mengizinkan manage orders
            return count(array_intersect(["ADMIN", "STAFF"], json_decode($user->roles)));
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
