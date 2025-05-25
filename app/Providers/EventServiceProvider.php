<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Auth\Events\Login;
use App\Listeners\MergeCartAfterLogin;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Сопоставления событий с их слушателями.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Login::class => [
            MergeCartAfterLogin::class,
        ],
    ];

    /**
     * Регистрация любых событий для вашего приложения.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Определяет, нужно ли автоматически обнаруживать события и слушатели.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
