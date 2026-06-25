import React from 'react';

/**
 * Welcome
 *
 * Props are passed from PHP via $this->react('Welcome', $props).
 * Destructure whatever keys your controller provides.
 */
export default function Welcome(props) {
    return (
        <div className="Welcome-page">
            <h1>Welcome</h1>
            {/* Remove this debug block once you're wired up */}
            <pre style={{ fontFamily: 'monospace', fontSize: '0.85rem', opacity: 0.6 }}>
                {JSON.stringify(props, null, 2)}
            </pre>
        </div>
    );
}
