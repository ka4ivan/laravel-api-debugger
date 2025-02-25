<?php

declare(strict_types=1);

namespace Ka4ivan\ApiDebugger\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ApiDebuggerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  \Closure  $next
     * @return Response
     */
    public function handle(Request $request, Closure $next)
    {
        $request->startDebug();

        $response = $next($request);

        if (!$request->expectsJson() || !config('app.debug', false)) {
            return $response;
        }

        // Перевірка, чи є відповідь типу JsonResponse
        if ($response instanceof \Illuminate\Http\JsonResponse) {
            $response->setData(array_merge($response->getData(true), $request->getDebug()));
        }

        return $response;
    }
}
