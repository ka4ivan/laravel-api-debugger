<?php

declare(strict_types=1);

namespace Ka4ivan\ApiDebugger\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Ka4ivan\ApiDebugger\Support\ApiDebugger;

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
        $response = $next($request);

        $debugger = app(ApiDebugger::class);

        if (!$request->expectsJson() || !$debugger->isActive()) {
            return $response;
        }

        if ($response instanceof JsonResponse) {
            $response->setData(array_merge($response->getData(true), $request->getDebug()));
        }

        return $response;
    }
}
