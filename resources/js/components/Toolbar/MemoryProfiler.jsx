// resources/js/components/Toolbar/MemoryProfiler.jsx
import React from 'react';

/**
 * Real-Time Memory Profiling Widget
 * @param {Object} memory - Extracted from the Debug Toolbar payload
 */
export default function MemoryProfiler({ memory }) {
    if (!memory) return null;

    const { used_mb, limit_mb, percent, status } = memory;

    // Default: Safe/Ok (Green)
    let textStyle = 'text-green-400';
    let barColor = 'bg-green-500';

    // Threshold state highlights
    if (status === 'warning') {
        textStyle = 'text-amber-400';
        barColor = 'bg-amber-500';
    } else if (status === 'critical') {
        textStyle = 'text-red-400';
        barColor = 'bg-red-500';
    }

    return (
        <div className="flex items-center px-4 py-2 border-r border-gray-700 hover:bg-gray-800 transition-colors cursor-pointer">
            <div className="flex flex-col mr-3">
                <span className="text-[10px] font-bold tracking-wider text-gray-400 uppercase">
                    Memory Peak
                </span>
                <span className={`font-mono text-sm font-semibold ${textStyle}`}>
                    {used_mb} <span className="text-gray-500 text-xs font-normal">MB</span>
                </span>
            </div>
            
            {/* Visual Capacity Bar */}
            <div className="w-24 h-2 bg-gray-900 rounded-full overflow-hidden border border-gray-700" title={`Limit: ${limit_mb} MB`}>
                <div
                    className={`h-full ${barColor} transition-all duration-500 ease-out`}
                    style={{ width: `${Math.min(percent, 100)}%` }}
                ></div>
            </div>
            
            <span className="ml-2 font-mono text-xs text-gray-400">{percent}%</span>
        </div>
    );
}