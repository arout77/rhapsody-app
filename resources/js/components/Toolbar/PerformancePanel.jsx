/**
 * PerformancePanel – renders performance metrics inside the debug toolbar.
 *
 * Props (passed from Toolbar.php via the React island):
 *   - time: float, execution time in milliseconds
 *   - memory: float, memory usage in MB
 *   - queries: integer, total database queries
 *   - route: object { method, path, controller, action, params? }
 *   - cache: object { hits, misses, ratio }
 *   - nPlusOne: array of queries flagged as potential N+1 patterns
 */
import React from 'react';

export default function PerformancePanel({ time, memory, queries, route, cache, nPlusOne }) {
    const timeMs = time ?? 0;
    const memMB = memory ?? 0;
    const queryCount = queries ?? 0;
    const cacheStats = cache ?? { hits: 0, misses: 0, ratio: 0 };
    const nPlusOneCount = nPlusOne?.length ?? 0;

    // Colour thresholds
    const timeColor = timeMs > 500 ? 'text-red-400' : timeMs > 200 ? 'text-yellow-400' : 'text-green-400';
    const memColor = memMB > 8 ? 'text-red-400' : memMB > 4 ? 'text-yellow-400' : 'text-green-400';
    const queryColor = queryCount > 50 ? 'text-red-400' : queryCount > 20 ? 'text-yellow-400' : 'text-green-400';
    const cacheColor = cacheStats.ratio > 80 ? 'text-green-400' : cacheStats.ratio > 50 ? 'text-yellow-400' : 'text-red-400';

    return (
        <div className="space-y-4 text-gray-300">
            <h3 className="text-xl font-bold text-white">Performance Metrics</h3>

            {/* Main metrics grid */}
            <div className="grid grid-cols-2 gap-4">
                <div className="bg-gray-800 p-3 rounded">
                    <div className="text-sm text-gray-400">Execution Time</div>
                    <div className={`text-2xl font-bold ${timeColor}`}>
                        {timeMs.toFixed(1)} ms
                    </div>
                    <div className="w-full bg-gray-700 rounded-full h-2 mt-1">
                        <div className="h-2 rounded-full bg-blue-500" style={{ width: `${Math.min(100, (timeMs / 1000) * 100)}%` }} />
                    </div>
                </div>

                <div className="bg-gray-800 p-3 rounded">
                    <div className="text-sm text-gray-400">Memory Usage</div>
                    <div className={`text-2xl font-bold ${memColor}`}>
                        {memMB.toFixed(2)} MB
                    </div>
                    <div className="w-full bg-gray-700 rounded-full h-2 mt-1">
                        <div className="h-2 rounded-full bg-purple-500" style={{ width: `${Math.min(100, (memMB / 16) * 100)}%` }} />
                    </div>
                </div>

                <div className="bg-gray-800 p-3 rounded">
                    <div className="text-sm text-gray-400">Database Queries</div>
                    <div className={`text-2xl font-bold ${queryColor}`}>
                        {queryCount}
                    </div>
                    <div className="w-full bg-gray-700 rounded-full h-2 mt-1">
                        <div className="h-2 rounded-full bg-green-500" style={{ width: `${Math.min(100, (queryCount / 100) * 100)}%` }} />
                    </div>
                </div>

                <div className="bg-gray-800 p-3 rounded">
                    <div className="text-sm text-gray-400">Cache Hit Ratio</div>
                    <div className={`text-2xl font-bold ${cacheColor}`}>
                        {cacheStats.ratio}%
                    </div>
                    <div className="w-full bg-gray-700 rounded-full h-2 mt-1">
                        <div className="h-2 rounded-full bg-yellow-500" style={{ width: `${cacheStats.ratio}%` }} />
                    </div>
                    <div className="text-xs text-gray-500 mt-1">
                        {cacheStats.hits} hits / {cacheStats.misses} misses
                    </div>
                </div>
            </div>

            {/* N+1 Query Detection – always visible */}
            <div className="bg-gray-800 p-3 rounded">
                <div className="flex items-center justify-between">
                    <span className="text-sm text-gray-300">N+1 Query Detection</span>
                    {nPlusOneCount > 0 ? (
                        <span className="text-red-400 font-bold">⚠️ {nPlusOneCount} potential N+1 pattern(s)</span>
                    ) : (
                        <span className="text-green-400">✅ No N+1 queries detected</span>
                    )}
                </div>
                {nPlusOneCount > 0 && (
                    <details className="mt-2 text-sm">
                        <summary className="text-gray-400 cursor-pointer hover:text-gray-300">Show details</summary>
                        <ul className="mt-2 space-y-1">
                            {nPlusOne.map((query, idx) => (
                                <li key={idx} className="font-mono text-xs text-gray-300 border-b border-gray-700 py-1">
                                    <span className="text-cyan-300">{query.sql}</span>
                                    <span className="text-gray-500 ml-2">({query.executionMS ? (query.executionMS * 1000).toFixed(1) : '0'}ms)</span>
                                    <div className="text-gray-500 text-xs">
                                        Called from {query.caller?.file ?? 'N/A'}:{query.caller?.line ?? 'N/A'}
                                    </div>
                                </li>
                            ))}
                        </ul>
                    </details>
                )}
            </div>

            {/* Route / Controller info */}
            <div className="bg-gray-800 p-3 rounded text-sm">
                <div className="text-gray-400">Route / Controller</div>
                <div className="text-sm font-mono text-cyan-300 truncate">
                    {route?.controller ?? 'N/A'}
                    {route?.action ? `::${route.action}` : ''}
                </div>
                <div className="text-xs text-gray-500 mt-1">
                    {route?.path ?? 'No route matched'}
                </div>
                {route?.params && Object.keys(route.params).length > 0 && (
                    <div className="text-xs text-gray-400 mt-1">
                        Params: {JSON.stringify(route.params)}
                    </div>
                )}
            </div>
        </div>
    );
}