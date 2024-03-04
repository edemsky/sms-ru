<?php

namespace NotificationChannels\SmsBee;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class SmsServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function register(): void
    {
        $this->app->singleton(SmsApi::class, static function ($app) {
            return new SmsApi($app['config']['services.sms']);
        });
    }

    public function provides(): array
    {
        return [
            SmsApi::class,
        ];
    }
}
