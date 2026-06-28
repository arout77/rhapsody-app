<?php

use App\Controllers\ImageController;
use App\Controllers\PageController;
use App\Controllers\UserController;
use App\Controllers\WebhookController;
use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;
use Rhapsody\Core\Controllers\AuthController;
use Rhapsody\Core\Controllers\DocsController;
use Rhapsody\Core\Controllers\SocialAuthController;
use Rhapsody\Core\Routing\Router;

// Define your application routes using the static Router methods.

// --- The routes below can be viewed by visitors and logged in users
// --- DO NOT REMOVE. This is the route for the image resizer/caching
Router::get('/img/{path}', [ImageController::class, 'show']);

// Social login routes
Router::get('/auth/{provider}', [SocialAuthController::class, 'redirectToProvider']);
Router::get('/auth/{provider}/callback', [SocialAuthController::class, 'handleProviderCallback']);
Router::get('/auth/redirect/{provider}', [SocialAuthController::class, 'redirectToProvider'])->name('auth.redirect');

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
Router::get('/login', [AuthController::class, 'showLoginForm'])
    ->middleware('usertableexists')
    ->middleware('guest');

Router::post('/login', [AuthController::class, 'login'])
    ->middleware('usertableexists')
    ->middleware('guest');
Router::get('/register', [AuthController::class, 'showRegisterForm'])
    ->middleware('usertableexists')
    ->middleware('guest');
Router::post('/register', [AuthController::class, 'register'])
    ->middleware('usertableexists')
    ->middleware('guest');

// --- Omnipay
Router::post('/payment/webhook', [WebhookController::class, 'handle']);
