/**
 * AiPrompt — a self-contained "type a prompt, get a response" widget backed
 * by BaseAiController::generate() (see Rhapsody\Core\Controllers\BaseAiController).
 *
 * Usage as an island (in any Twig template):
 *   {{ react_component('AiPrompt', { endpoint: '/ai/generate' }) }}
 *
 * Requires a CSRF meta tag somewhere on the page (same convention
 * BaseController::react() uses for full-SPA mode):
 *   <meta name="csrf-token" content="{{ csrf_token() }}">
 *
 * Props:
 *   - endpoint: string, the route to POST to (default '/ai/generate')
 *   - placeholder: string, textarea placeholder
 *   - buttonLabel: string, submit button text
 */
import React, { useState, useRef, useEffect } from 'react';

const SLOW_RESPONSE_HINT_MS = 8000;

function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

export default function AiPrompt({
    endpoint = '/ai/generate',
    placeholder = 'Ask me anything...',
    buttonLabel = 'Generate',
}) {
    const [prompt, setPrompt] = useState('');
    const [status, setStatus] = useState('idle'); // idle | loading | slow | done | error
    const [result, setResult] = useState(null);   // { text, truncated, blocked, usage }
    const [error, setError] = useState(null);     // { message, retryAfter? }

    const abortRef = useRef(null);
    const slowTimerRef = useRef(null);

    // Abort any in-flight request if the component unmounts mid-request
    // (e.g. the user navigates away or the island is removed from the page).
    useEffect(() => {
        return () => {
            abortRef.current?.abort();
            clearTimeout(slowTimerRef.current);
        };
    }, []);

    async function handleSubmit(e) {
        e.preventDefault();

        const trimmed = prompt.trim();
        if (!trimmed || status === 'loading' || status === 'slow') {
            return;
        }

        setStatus('loading');
        setResult(null);
        setError(null);

        const controller = new AbortController();
        abortRef.current = controller;

        // Generation can genuinely take a while — if it's taking longer than
        // usual, let the user know it's still working rather than leaving
        // them staring at a spinner wondering if something broke.
        slowTimerRef.current = setTimeout(() => setStatus('slow'), SLOW_RESPONSE_HINT_MS);

        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                signal: controller.signal,
                body: JSON.stringify({
                    prompt: trimmed,
                    _token: getCsrfToken(),
                }),
            });

            const data = await response.json().catch(() => ({}));

            if (!response.ok) {
                setError({
                    message: data.error || 'Something went wrong. Please try again.',
                    status: response.status,
                    retryAfter: response.headers.get('Retry-After'),
                });
                setStatus('error');
                return;
            }

            setResult(data);
            setStatus('done');
        } catch (err) {
            if (err.name === 'AbortError') {
                // Cancelled deliberately (unmount or user-initiated) — not an error.
                return;
            }
            setError({ message: 'Could not reach the server. Check your connection and try again.' });
            setStatus('error');
        } finally {
            clearTimeout(slowTimerRef.current);
        }
    }

    function handleCancel() {
        abortRef.current?.abort();
        clearTimeout(slowTimerRef.current);
        setStatus('idle');
    }

    const isBusy = status === 'loading' || status === 'slow';

    return (
        <div className="max-w-2xl mx-auto space-y-3">
            <form onSubmit={handleSubmit} className="space-y-3">
                <textarea
                    value={prompt}
                    onChange={(e) => setPrompt(e.target.value)}
                    placeholder={placeholder}
                    disabled={isBusy}
                    rows={4}
                    className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                               focus:outline-none focus:ring-2 focus:ring-indigo-500
                               disabled:bg-gray-100 disabled:text-gray-500"
                />

                <div className="flex items-center gap-3">
                    <button
                        type="submit"
                        disabled={isBusy || !prompt.trim()}
                        className="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-medium
                                   hover:bg-indigo-700 disabled:bg-indigo-300 disabled:cursor-not-allowed"
                    >
                        {isBusy ? 'Generating…' : buttonLabel}
                    </button>

                    {isBusy && (
                        <button
                            type="button"
                            onClick={handleCancel}
                            className="px-3 py-2 text-sm text-gray-600 hover:text-gray-900"
                        >
                            Cancel
                        </button>
                    )}

                    {status === 'slow' && (
                        <span className="text-sm text-gray-500">
                            Still working — longer prompts can take a little while…
                        </span>
                    )}
                </div>
            </form>

            {status === 'error' && error && (
                <div className="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    {error.status === 429 ? (
                        <>
                            You're sending requests a bit too quickly.
                            {error.retryAfter ? ` Please wait about ${error.retryAfter}s and try again.` : ' Please wait a moment and try again.'}
                        </>
                    ) : error.status === 504 ? (
                        <>The response took too long. Please try again — shorter prompts usually respond faster.</>
                    ) : error.status === 502 ? (
                        <>The AI service is temporarily unavailable. Please try again shortly.</>
                    ) : (
                        error.message
                    )}
                </div>
            )}

            {status === 'done' && result && (
                <div className="rounded-lg border border-gray-200 bg-white px-4 py-3 space-y-2">
                    {result.blocked ? (
                        <div className="text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded px-3 py-2">
                            This prompt was blocked by content safety filters. Try rephrasing it.
                        </div>
                    ) : (
                        <>
                            <p className="text-sm text-gray-800 whitespace-pre-wrap">{result.text}</p>
                            {result.truncated && (
                                <div className="text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded px-3 py-2">
                                    This response was cut off due to length. Try a more specific prompt for a complete answer.
                                </div>
                            )}
                        </>
                    )}
                </div>
            )}
        </div>
    );
}
