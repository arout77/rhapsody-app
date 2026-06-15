<?php

use App\Controllers\PageController;
use App\Controllers\UserController;
use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;
use Rhapsody\Core\Controllers\AuthController;
use Rhapsody\Core\Controllers\DocsController;
use Rhapsody\Core\Routing\Router;

// Define your application routes using the static Router methods.

// --- The routes below can be viewed by visitors and logged in users
// --- DOCUMENTATION ROUTES ---
Router::get('/docs/...', [DocsController::class, '...'])->middleware('docs');
Router::get('/docs', [DocsController::class, 'index']);
Router::get('/docs/caching', [DocsController::class, 'performance']);
Router::get('/docs/cli', [DocsController::class, 'cli']);
Router::get('/docs/configuration', [DocsController::class, 'configuration']);
Router::get('/docs/controllers', [DocsController::class, 'controllers']);
Router::get('/docs/debugging', [DocsController::class, 'debugging']);
Router::get('/docs/doctrine', [DocsController::class, 'doctrine']);
Router::get('/docs/error-handling', [DocsController::class, 'errorHandling']);
Router::get('/docs/events', [DocsController::class, 'events']);
Router::get('/docs/file-uploader', [DocsController::class, 'fileUploader']);
Router::get('/docs/image-processing', [DocsController::class, 'imageProcessing']);
Router::get('/docs/installation', [DocsController::class, 'installation']);
Router::get('/docs/logging', [DocsController::class, 'logging']);
Router::get('/docs/mailer', [DocsController::class, 'mailer']);
Router::get('/docs/middleware', [DocsController::class, 'middleware']);
Router::get('/docs/models', [DocsController::class, 'models']);
Router::get('/docs/pagination', [DocsController::class, 'pagination']);
Router::get('/docs/request', [DocsController::class, 'request']);
Router::get('/docs/response', [DocsController::class, 'response']);
Router::get('/docs/routing', [DocsController::class, 'routing']);
Router::get('/docs/security', [DocsController::class, 'security']);
Router::get('/docs/seo', [DocsController::class, 'seo']);
Router::get('/docs/themes', [DocsController::class, 'themes']);
Router::get('/docs/updating', [DocsController::class, 'updating']);
Router::get('/docs/validation', [DocsController::class, 'validation']);
Router::get('/docs/views', [DocsController::class, 'views']);

Router::get('/', [PageController::class, 'index']);
Router::get('/about', [PageController::class, 'about']);
Router::get('/contact', [App\Controllers\PageController::class, 'contact']);
Router::post('/contact', [App\Controllers\PageController::class, 'handleContact']);

Router::get('/sitemap.xml', [SitemapController::class, 'generate']);

Router::get('/logout', [AuthController::class, 'logout']);

// This will match URLs like /posts/hello-world or /posts/123
Router::get('/posts/{slug}', [PageController::class, 'showPost']);

// --- PROTECTED ROUTES ---
// This route should only be accessible to authenticated users.
Router::get('/dashboard', [PageController::class, 'dashboard'])->middleware('auth');
Router::get('/upload', [App\Controllers\PageController::class, 'showUploadForm'])->middleware('auth');
Router::post('/upload', [App\Controllers\PageController::class, 'handleUpload'])->middleware('auth');
Router::get('/users', [PageController::class, 'showUsers']);
Router::get('/users/{user_id}', [PageController::class, 'viewUser']);

// These routes should only be accessible to guests.
Router::get('/login', [AuthController::class, 'showLoginForm'])->middleware('guest');
Router::post('/login', [AuthController::class, 'login'])->middleware('guest');
Router::get('/register', [AuthController::class, 'showRegisterForm'])->middleware('guest');
Router::post('/register', [AuthController::class, 'register'])->middleware('guest');
