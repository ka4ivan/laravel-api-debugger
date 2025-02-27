<?php

declare(strict_types=1);

namespace Ka4ivan\ApiDebugger;

use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Ka4ivan\ApiDebugger\Middleware\ApiDebuggerMiddleware;
use Ka4ivan\ApiDebugger\Support\ApiDebugger;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->registerMacros();
        $this->registerMiddleware();

        if (app(ApiDebugger::class)->isActive()) {
            $this->startDebug();
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton(ApiDebugger::class);
    }

    /**
     * Register macros for Request class.
     *
     * @return void
     */
    protected function registerMacros(): void
    {
        Request::macro('startDebug', fn() => app(ApiDebugger::class)->startDebug());
        Request::macro('getDebug', fn() => app(ApiDebugger::class)->getDebug(request()));
    }

    /**
     * Register middleware for API routes.
     *
     * @return void
     */
    protected function registerMiddleware(): void
    {
        $this->app->booted(fn() => tap($this->app->make(Router::class), function(Router $router) {
            $router->middlewareGroup('api', array_merge(
                $router->getMiddlewareGroups()['api'] ?? [],
                [ApiDebuggerMiddleware::class]
            ));
        }));
    }

    /**
     * Start Debugging
     *
     * @return void
     */
    protected function startDebug(): void
    {
        $this->app->booted(function () {
            app(ApiDebugger::class)->startDebug();
        });
    }
}
