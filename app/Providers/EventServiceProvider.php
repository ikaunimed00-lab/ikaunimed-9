<?php

namespace App\Providers;

use App\Events\Payments\TripayPaymentSettled;
use App\Listeners\Payments\HandleTripayPaymentSettled;
use App\Listeners\RedirectAfterLogin;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Login::class => [
            RedirectAfterLogin::class,
        ],
        TripayPaymentSettled::class => [
            HandleTripayPaymentSettled::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }
}
