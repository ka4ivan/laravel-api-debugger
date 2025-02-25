<?php

declare(strict_types=1);

namespace Ka4ivan\ApiDebugger\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiDebugger
{
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
        if ($this->isActive()) {
            DB::enableQueryLog();
        }
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
            'body' => $request->all(),
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
        $queries = DB::getQueryLog();
        $totalTime = round(array_reduce($queries, fn ($carry, $query) => $carry + $query['time'], 0), 2);

        $longQueries = $this->checkLongQueries($queries);
        $nPlusOneIssues = $this->checkNPlusOne($queries);

        return [
            'count' => count($queries),
            'time' => $totalTime,
            'data' => $queries,
            'long_queries' => $longQueries,
            'n_plus_one' => $nPlusOneIssues,
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
    protected function checkNPlusOne(array $queries): array
    {
        $queryCounts = [];
        $nPlusOneIssues = [];

        foreach ($queries as $query) {
            $queryKey = $query['query'];
            if (isset($queryCounts[$queryKey])) {
                $queryCounts[$queryKey]++;
            } else {
                $queryCounts[$queryKey] = 1;
            }
        }

        foreach ($queryCounts as $queryKey => $count) {
            if ($count > 1) {
                $nPlusOneIssues[] = [
                    'query' => $queryKey,
                    'count' => $count,
                ];
            }
        }

        return $nPlusOneIssues;
    }
}
