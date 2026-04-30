<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

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
    if (isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
      URL::forceRootUrl('https://' . $_SERVER['HTTP_X_FORWARDED_HOST']);
    }

    if (str_contains(config('app.url'), 'ngrok-free.app')) {
        URL::forceScheme('https');
    }

    if (env('APP_ENV') !== 'local') {
        URL::forceScheme('https');
    }
  }
}
