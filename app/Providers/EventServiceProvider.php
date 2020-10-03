<?php

namespace App\Providers;

use App\Events\AppLoadedEvent;
use App\Events\UserRegisteredEvent;
use App\Listeners\AppLoadedListener;
use App\Listeners\UserRegisteredListener;
use App\Listeners\UserWelcomeEmailListener;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        UserRegisteredEvent::class => [
            UserRegisteredListener::class,
            UserWelcomeEmailListener::class,
        ],
        AppLoadedEvent::class => [
            AppLoadedListener::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return true;
    }
}
