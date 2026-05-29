<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Event;
use App\Domains\Order\Events\OrderPlaced;
use App\Domains\Notification\Listeners\SendOrderNotifications;

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
        // تعريف الـ Rate Limiter المسمى [api] المعتمد على الـ Redis
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // ربط الحدث بالمستمع يدوياً لضمان عمل الـ DDD المخصص
        Event::listen(
            OrderPlaced::class,
            SendOrderNotifications::class
        );
    }
}