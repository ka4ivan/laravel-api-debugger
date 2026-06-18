<?php

declare(strict_types=1);

namespace Ka4ivan\ApiDebugger;

use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\DB;
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
        $this->publishConfig();
        $this->registerMacros();
        $this->registerMiddleware();

        if (class_exists(\Laravel\Octane\Events\RequestReceived::class)) {
            $this->app['events']->listen(\Laravel\Octane\Events\RequestReceived::class, function () {
                app(ApiDebugger::class)->reset();
            });
        }

        if (class_exists(\Laravel\Octane\Events\RequestTerminated::class)) {
            $this->app['events']->listen(\Laravel\Octane\Events\RequestTerminated::class, function () {
                app(ApiDebugger::class)->reset();
            });
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->scoped(ApiDebugger::class, function () {
            return new ApiDebugger();
        });
    }

    protected function publishConfig(): void
    {
        $this->publishes([
            __DIR__ . '/../config/api-debugger.php' => config_path('api-debugger.php'),
        ], 'api-debugger-config');
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
