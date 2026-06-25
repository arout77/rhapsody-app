import React from 'react';

/**
 * PerformancePanel – shows execution time, memory, queries, and route info.
 * Props are passed from Toolbar.php via the React island.
 */
export default function PerformancePanel({ time, memory, queries, route }) {
    const timeMs = time ?? 0;
    const memMB = memory ?? 0;
    const queryCount = queries ?? 0;

    // Determine colour based on thresholds
    const timeColor = timeMs > 500 ? 'text-red-400' : timeMs > 200 ? 'text-yellow-400' : 'text-green-400';
    const memColor = memMB > 8 ? 'text-red-400' : memMB > 4 ? 'text-yellow-400' : 'text-green-400';
    const queryColor = queryCount > 50 ? 'text-red-400' : queryCount > 20 ? 'text-yellow-400' : 'text-green-400';

    return (
        <div className="space-y-4 text-gray-300">
            <h3 className="text-xl font-bold text-white">Performance Metrics</h3>

            <div className="grid grid-cols-2 gap-4">
                <div className="bg-gray-800 p-3 rounded">
                    <div className="text-sm text-gray-400">Execution Time</div>
                    <div className={`text-2xl font-bold ${timeColor}`}>
                        {timeMs.toFixed(1)} ms
                    </div>
                    <div className="w-full bg-gray-700 rounded-full h-2 mt-1">
                        <div
                            className="h-2 rounded-full bg-blue-500"
                            style={{ width: `${Math.min(100, (timeMs / 1000) * 100)}%` }}
                        />
                    </div>
                </div>

                <div className="bg-gray-800 p-3 rounded">
                    <div className="text-sm text-gray-400">Memory Usage</div>
                    <div className={`text-2xl font-bold ${memColor}`}>
                        {memMB.toFixed(2)} MB
                    </div>
                    <div className="w-full bg-gray-700 rounded-full h-2 mt-1">
                        <div
                            className="h-2 rounded-full bg-purple-500"
                            style={{ width: `${Math.min(100, (memMB / 16) * 100)}%` }}
                        />
                    </div>
                </div>

                <div className="bg-gray-800 p-3 rounded">
                    <div className="text-sm text-gray-400">Database Queries</div>
                    <div className={`text-2xl font-bold ${queryColor}`}>
                        {queryCount}
                    </div>
                    <div className="w-full bg-gray-700 rounded-full h-2 mt-1">
                        <div
                            className="h-2 rounded-full bg-green-500"
                            style={{ width: `${Math.min(100, (queryCount / 100) * 100)}%` }}
                        />
                    </div>
                </div>

                <div className="bg-gray-800 p-3 rounded">
                    <div className="text-sm text-gray-400">Route / Controller</div>
                    <div className="text-sm font-mono text-cyan-300 truncate">
                        {route?.controller ?? 'N/A'}
                        {route?.action ? `::${route.action}` : ''}
                    </div>
                    <div className="text-xs text-gray-500 mt-1">
                        {route?.path ?? 'No route matched'}
                    </div>
                </div>
            </div>

            <div className="bg-gray-800 p-3 rounded text-sm">
                <div className="text-gray-400">System Info</div>
                <div className="grid grid-cols-2 gap-2 mt-1 font-mono text-xs text-gray-300">
                    <span>PHP Version: {window?.Rhapsody?.phpVersion ?? 'N/A'}</span>
                </div>
            </div>
        </div>
    );
}