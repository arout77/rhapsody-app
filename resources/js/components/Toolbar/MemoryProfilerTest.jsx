import React from 'react';

/**
 * MemoryProfilerTest
 *
 * Props are passed from PHP via $this->react('MemoryProfilerTest', $props).
 * Destructure whatever keys your controller provides.
 */
export default function MemoryProfilerTest(props) {
    return (
        <div className="MemoryProfilerTest-page">
            <h1>MemoryProfilerTest</h1>
            {/* Remove this debug block once you're wired up */}
            <pre style={{ fontFamily: 'monospace', fontSize: '0.85rem', opacity: 0.6 }}>
                {JSON.stringify(props, null, 2)}
            </pre>
        </div>
    );
}
