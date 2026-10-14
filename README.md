# Rhapsody Framework

> **High-Performance PHP. Enterprise Tooling. Zero Infrastructure Hassle.**  
> An elegant, type-safe PHP framework built for digital agencies, contractors, and content-rich applications—deployable anywhere without background queue workers or server daemons.

---

## Why Rhapsody?

For digital agencies, freelancers, and contractors, client hosting budgets rarely accommodate managed Redis clusters, persistent Supervisor daemons, or complex server orchestration. Yet client projects still require rapid request latencies, background task processing (image optimization, email notifications, cache warming), and modern front-end dynamic features.

**Rhapsody bridges this gap.** Built specifically for content- and media-rich web applications, Rhapsody brings enterprise-grade developer tooling, secure-by-default standards, and strict static type safety to affordable, universal hosting environments.

---

## Key Pillars & Architectural Features

### ⚡ Zero-Worker Background Tasks (`defer()`)
Offload time-consuming tasks seamlessly after the HTTP response using Rhapsody's native `defer()` layer. Powered by FastCGI connection flushes, user response times stay well under 100ms **without requiring Redis, Supervisor, or background queue worker daemons**.

### 🚀 Deploy Anywhere (Shared Hosting & Low-Cost Servers)
Zero-friction installation across any hosting setup:
- Standard cPanel / Shared Web Hosting
- Budget VPS & Bare-Metal Cloud Servers
- Shared Apache, Nginx, or LiteSpeed environments
- WAMP / LAMP / Docker environments

No root access or complex server daemons required—ship high-performance client applications on inexpensive shared hosting without compromising execution speed or system stability.

### 🔒 Secure by Default
Rhapsody prioritizes application security out of the box so client projects remain hardened without tedious configuration:
- **CSRF Protection:** Built-in safeguards across request handlers and form submissions.
- **Core Encryption:** Standardized encryption mechanisms for sensitive payloads and application data.
- **Built-In Authentication & Role Middleware:** Out-of-the-box authentication flows and configurable admin role middleware for straightforward access control.
- **Signed URLs:** Easily generate tamper-proof, time-sensitive signed links for downloads or temporary actions.
- **Encrypted Database Columns:** Simple column-level encryption support to safeguard sensitive data at rest.
- **Strict Release Quality Assurance:** Core framework code and updates are required to pass strict **PHPStan Level 9** static analysis checks prior to release.

### 🖼️ Integrated Media & Content Engine
Stop re-inventing asset pipelines for every client site. Rhapsody comes equipped with:
- Automated WebP/AVIF image generation & responsive sizing
- Signed upload handlers & CSRF-protected file management
- Built-in SEO metadata and schema generators
- Flexible ORM options: Doctrine ORM or lightweight PDO `BaseModel`

### 🏝️ Hybrid View Engine: Twig SSR + React Islands
Render lightning-fast server-side views using Twig, and seamlessly mount interactive **React Islands** directly inside your templates—or build full Single Page Applications (SPAs) without complex build setups or front-end fragmentation.

### 🛡️ Compile-Time Speed & Type Safety Assurance
Experience sub-millisecond container resolving powered by a compiled dependency injection container, backed by enforced **PHPStan Level 9** static analysis guarantees across core framework updates to catch potential bugs in development before code reaches production.

---

## Built for Digital Agencies & Contractors

When delivering client projects, long-term maintainability and low operational tax are paramount:

- **Zero Ongoing Infrastructure Tax:** Client hosting bills remain low, and support calls regarding crashed background queue daemons disappear entirely.
- **Built-In Security & Peace of Mind:** Protect client applications with enterprise-grade security primitives and sensible defaults from day one.
- **Rapid Developer Onboarding:** Expressive, readable syntax without hidden "magic" makes codebases transparent and easy for junior developers or incoming contractors to maintain.
- **Predictable Client Deployments:** Standard Git push or automated FTP/SSH deployments work right out of the box on any server.

---

## Requirements

- **PHP 8.4.1 or newer** (with standard FastCGI/FPM enabled)
- **Web Server:** Apache 2.4+, Nginx, LiteSpeed, or Caddy
- **Database:** Any PDO-compatible database (MySQL, PostgreSQL, SQLite, SQL Server)
- **Composer** ([getcomposer.org](https://getcomposer.org/))

---

## Quick Start & Zero-Friction Installation

Scaffold a new project in your terminal:

```bash
composer create-project arout/rhapsody-app my-awesome-app
```

### Environment Configuration

Once Composer finishes, edit the `.env` file in your root directory with your database credentials, environment, app key (a hash used by cookies and encryption) and website URL:

```env
# development or production
APP_ENV=production

# Generate your own unique 32 or 64 character key using:
# php -r "echo bin2hex(random_bytes(64));"
APP_KEY=your-key-goes-here

# Website URL
APP_URL=https://your-client-domain.com

# Database credentials
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=client_app_db
DB_USERNAME=db_user
DB_PASSWORD=db_password
DB_DRIVER="" # Supported databases: mysql, pgsql, sqlite, or sqlsrv
```

That is all that is absolutely required to launch your new site, but you'll want to <a href="https://rhapsodyframework.com/docs/installation">check the documentation</a> for other optional steps that you may find useful before going live (installing the React bridge, warming the cache and proxies, etc).

---

## Expressive Code Example

A simple controller that displays a specific user:

```php
<?php
namespace App\Controllers;

use App\Models\User;
use Rhapsody\Core\BaseController;

// Your controller with automatic dependency injection
class UserController extends BaseController
{
    public function __construct(protected User $userModel) {}

    public function show(Request $request, string $user_id): Response
    {
        // Find the user
        $user = $this->userModel->findById($user_id);

        // Tell the controller which view file to render, and pass
        // the user's data to the view
        return $this->view('users/show.twig', ['user' => $user]);
    }
}
```

Register the route in **routes/web.php**:

```php
<?php
// Note that '{user_id}' is an example of how to create dynamic ($_GET) routes.
// We chain the ->middleware('auth') middleware to the route in order to ensure
// only logged in users can view this page (replace 'auth' with 'admin' to do the
// same to restrict pages to only administrators
Router::get('/users/{user_id}', [UserController::class, 'show'])
    ->middleware('auth');
```

Finally, the Twig view file:

```twig
{% extends "layouts/main.twig" %}

{% block content %}
<div class="max-w-3xl mx-auto py-12 px-4">
    <h1 class="text-3xl font-bold mb-4">About</h1>
    <p class="text-gray-600 dark:text-gray-400">
        Welcome, {{ user.name }}. We sent a verification email to you at {{ user.email_address )).
        Please respond in order to activate your account
    </p>
</div>
{% endblock %}

```

---

## License

The Rhapsody Framework is open-source software licensed under the [MIT License](LICENSE).
