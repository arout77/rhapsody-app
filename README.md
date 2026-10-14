Rhapsody
==========
Rhapsody is a modern PHP framework for developers who find full-stack frameworks like Laravel too heavy for their needs, but find micro-frameworks like Slim too bare-bones. It gives you the modern tooling you love—like a powerful CLI, dependency injection, and an ORM—in a simple, performant, and elegant package. It's the perfect choice for building fast, maintainable web applications and APIs without the overhead.

### Stop reaching for a sledgehammer to crack a nut.
Laravel is fantastic for large, complex applications. But for your agency's client brochure sites, a fast JSON API, or your next startup MVP, you don't need the overhead. Rhapsody gives you the modern features you need to be productive without the bloat.
You could start with a micro-framework and spend hours wiring up your own ORM, container, and console commands. Or, you can start with Rhapsody, which provides a cohesive, pre-configured toolkit with Doctrine, a powerful CLI, and a multi-driver cache system right out of the box. You get the simplicity of a small framework with the developer experience of a large one.

### Learn how modern PHP frameworks *actually* work under the hood.
Frameworks like Laravel are powerful, but their 'magic' can hide how things work. Rhapsody is transparent. Its core is so simple and well-documented that you can read through it and finally understand core concepts like the Service Container, Middleware, and the Request Lifecycle. It's the best way to learn the patterns used by all modern frameworks.
Coming from an older framework like CodeIgniter or just raw PHP? Rhapsody is the perfect next step. It will introduce you to essential modern concepts like dependency injection and database migrations in a friendly and approachable way.

### Faster development, faster websites, and easier maintenance.
Rhapsody's simple structure and powerful command-line tools mean our developers can build and launch your project faster, saving you time and money.
With a lightweight core and built-in caching for routes and data, websites built with Rhapsody are incredibly fast, leading to better user engagement and improved SEO.
The framework's clean, decoupled architecture (thanks to features like the Event Dispatcher) makes the codebase easy to understand and maintain. This reduces the long-term cost of adding features or bringing on new developers.

### Requirements
- Apache Server 2.4+
- PHP 8.4.1 or newer
- Any PDO compatible database
- Composer package manager (https://getcomposer.org/)
- SSH access to your server (optional, but recommended)

### Installation

In your terminal:

#### <span style="color:green">composer create-project arout/rhapsody-app my-awesome-app</span>

After composer is finished running, open the .env file in your newly created project's root directory. In it, enter your database credentials, and your website URL (and sub-directory if applicable). That's it, installation is complete! Now you can open your URL in the web browser and you should see the welcome page!
