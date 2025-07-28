<?php

declare(strict_types=1);

namespace Ka4ivan\ApiDebugger\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class ApiDebugger
{
    private array $queries = [];

    /**
     * Check if the debugger is active based on APP_DEBUG environment variable.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return (bool) env('APP_DEBUG');
    }

    /**
     * Start debugging by enabling query logging if the debugger is active.
     *
     * @return void
     */
    public function startDebug(): void
    {
        if (!$this->isActive()) {
            return;
        }

        $this->queries = [];

        DB::listen(function ($query) {
            $this->queries[] = [
                'sql' => $query->sql,
                'bindings' => $query->bindings,
                'time' => $query->time,
                'trace' => collect(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS))
                    ->filter(fn ($trace) => isset($trace['file']) && !str_contains($trace['file'], 'vendor'))
                    ->map(fn ($trace) => "{$trace['file']}:{$trace['line']}")
                    ->values()
                    ->all(),
            ];
        });
    }

    /**
     * Get the debugging information including request data and queries executed.
     *
     * @param Request $request
     * @return array
     */
    public function getDebug(Request $request): array
    {
        return [
            'debugger' => [
                'queries' => $this->getQueriesInfo(),
                'request' => $this->getRequestInfo($request),
            ],
        ];
    }

    /**
     * Retrieve information about the request such as body and headers.
     *
     * @param Request $request
     * @return array
     */
    protected function getRequestInfo(Request $request): array
    {
        return [
            'body' => Arr::except($request->input(), ['password', 'confirm_password', 'password_confirmation', '_destination', '_method', '_token', '_modal', 'destination']),
            'headers' => $request->header(),
        ];
    }

    /**
     * Retrieve the query log information, including count, executed queries, and checks for N+1 and long queries.
     *
     * @return array
     */
    protected function getQueriesInfo(): array
    {
        $queries = $this->queries;

        $totalTime = round(array_reduce($queries, fn ($carry, $query) => $carry + $query['time'], 0), 2);
        $longQueries = $this->checkLongQueries($queries);
        $repeatedQueries = $this->checkRepeatedQueries($queries);

        return [
            'count' => count($queries),
            'time' => $totalTime,
            'data' => $queries,
            'long_queries' => $longQueries,
            'repeated_queries' => $repeatedQueries,
        ];
    }


    /**
     * Check for long queries (taking longer than a specified threshold).
     *
     * @param array $queries
     * @param float $threshold
     * @return array
     */
    protected function checkLongQueries(array $queries, float $threshold = 10.0): array
    {
        $longQueries = [];

        foreach ($queries as $query) {
            if ($query['time'] > $threshold) {
                $longQueries[] = $query;
            }
        }

        return $longQueries;
    }

    /**
     * Check for N+1 query issues.
     *
     * This method compares queries to detect repeating queries with similar structures.
     *
     * @param array $queries
     * @return array
     */
    protected function checkRepeatedQueries(array $queries): array
    {
        $queryMap = [];
        $repeatedQueries = [];

        foreach ($queries as $query) {
            $queryKey = $query['sql'];
            $queryMap[$queryKey][] = $query;
        }

        foreach ($queryMap as $sql => $instances) {
            if (count($instances) > 1) {
                $backtraces = array_map(
                    fn ($q) => $q['trace'],
                    $instances
                );

                $repeatedQueries[] = [
                    'sql' => $sql,
                    'count' => count($instances),
                    'backtrace' => $backtraces,
                ];
            }
        }

        return $repeatedQueries;
    }
}
