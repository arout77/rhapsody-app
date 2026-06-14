# Upgrade Guide for Rhapsody Framework v1.4.6

This guide helps you update your application to the latest framework version, incorporating all recent security and bug fixes.

## Breaking Changes

None. All changes are backwards-compatible.

## New Features & Improvements

### 1. CSRF Middleware Now Checks Token Correctly
The `VerifyCsrfTokenMiddleware` now aborts requests (419) when the token is invalid. Previously it did nothing.

**Action required:** None – your existing forms with `{{ csrf_field() }}` will continue to work. JSON APIs must include `_token` in the request body.

### 2. Request::getBody() Now Handles PUT/PATCH/DELETE
The method no longer ignores non-POST requests. It now correctly parses JSON and form data for any HTTP method.

**Action required:** If you manually read `php://input` in controllers, you can replace that logic with `$request->getBody()`.

### 3. Mailer Throws Exception When Unconfigured
If `MAIL_HOST` is empty in `.env`, calling `send()` will now throw a `RuntimeException`. Previously the mailer was silently unusable.

**Action required:** Ensure your `.env` contains valid mail settings before using the mailer in production.

### 4. Middleware Can Short‑Circuit Requests
Middleware classes now return `?Response`. Returning a `Response` object (e.g., a redirect) stops further processing and sends that response.

**Action required:** If you have custom middleware, update the `handle` method signature to `handle(Request $request): ?Response` and return `null` when the request should proceed. See `middleware.twig` for example.

### 5. QueryLogger Singleton
The `QueryLogger` is now bound as a singleton, ensuring all Doctrine queries are logged to the same instance. The debug toolbar will show all queries correctly.

**Action required:** None.

### 6. Route Cache Command Detects Closures
`php rhapsody route:cache` will now fail if any route uses a closure callback, preventing an invalid cache file from being generated.

**Action required:** Replace closure routes with controller/action pairs if you intend to cache routes.

### 7. FileUploader Creates Directory Automatically
The upload directory is created if it doesn’t exist – no more “failed to move uploaded file” errors due to missing folder.

**Action required:** None.

### 8. Global Exception Handler in Production
Uncaught exceptions are now logged and a generic 500 error page is shown (no stack trace exposure).

**Action required:** None, but ensure your server error logs are monitored.

## How to Upgrade

1. Pull the latest framework changes (via Git or download ZIP).
2. Run `composer install` to update dependencies.
3. Clear caches: `php rhapsody cache:clear` and `php rhapsody route:clear` (if you use route caching).
4. Test critical parts: form submissions (CSRF), API endpoints (JSON body reading), and file uploads.

If you encounter issues, check the updated documentation files for the correct usage.