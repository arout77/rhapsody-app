<?php
namespace App\Controllers;

use App\Models\User;
use Rhapsody\Core\BaseController;
use Rhapsody\Core\FileUploader;
use Rhapsody\Core\Mailer;
use Rhapsody\Core\Pagination;
use Rhapsody\Core\RedirectResponse;
use Rhapsody\Core\Request;
use Rhapsody\Core\Response;
use Rhapsody\Core\Session;
use Rhapsody\Core\Validator;
use Twig\Environment;

class PageController extends BaseController
{
    /**
     * @param Environment $twig
     */
    public function __construct(Environment $twig)
    {
        parent::__construct($twig);
    }

    /**
     * Show the home page.
     *
     * @return Response
     */
    public function index(Request $request): Response
    {
        return $this->view(
            '@core/home/landing.twig',
            [], // no extra template data
            [   // meta data goes here
                'title'         => 'Welcome to Rhapsody',
                'description'   => 'A custom high-performance PHP framework.',
                'canonical_url' => $request->getCanonicalUrl(),
            ]
        );
    }

    /**
     * @return mixed
     */
    public function dashboard(): Response
    {
        // The AuthMiddleware now handles protection for this route.
        // The controller's only job is to render the view.
        return $this->view('@core/home/dashboard.twig');
    }

    /**
     * @return mixed
     */
    public function showRegisterForm(): Response
    {
        return $this->view('register.twig', ['old' => [], 'errors' => []]);
    }

    /**
     * @return mixed
     */
    public function showUploadForm(): Response
    {
        return $this->view('upload.twig');
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function handleUpload(Request $request): Response
    {
        $uploader = new FileUploader();
        $uploader->setAllowedMimes(['image/jpeg', 'image/png', 'application/pdf'])
            ->setMaxSize(5 * 1024 * 1024); // 5 MB

        if ($uploader->handle('documents')) {
            return $this->json([
                'success' => true,
                'files'   => $uploader->getUploadedFiles(),
            ]);
        } else {
            return $this->json([
                'success' => false,
                'errors'  => $uploader->getErrors(),
            ], 400);
        }
    }

    /**
     * Show the "About" page.
     */
    public function about(Request $request): Response
    {
        return $this->view(
            'pages/about.twig',
            [],
            [
                'title'         => 'About',
                'description'   => 'Learn more about this Rhapsody application.',
                'canonical_url' => $request->getCanonicalUrl(),
            ]
        );
    }

    /**
     * Show the contact form.
     */
    public function contact(Request $request): Response
    {
        return $this->view(
            'pages/contact.twig',
            ['old' => [], 'errors' => []],
            [
                'title'         => 'Contact',
                'description'   => 'Get in touch.',
                'canonical_url' => $request->getCanonicalUrl(),
            ]
        );
    }

    /**
     * Handle the contact form submission.
     */
    public function handleContact(Request $request): Response
    {
        $name    = trim((string) $request->post('name'));
        $email   = trim((string) $request->post('email'));
        $message = trim((string) $request->post('message'));

        $errors = [];
        if ($name === '') {
            $errors['name'] = 'Please enter your name.';
        }
        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address.';
        }
        if ($message === '') {
            $errors['message'] = 'Please enter a message.';
        }

        if (! empty($errors)) {
            return $this->view(
                'pages/contact.twig',
                ['old' => ['name' => $name, 'email' => $email, 'message' => $message], 'errors' => $errors],
                ['title' => 'Contact']
            );
        }

        // NOTE: wire this up to Mailer/a Contact model as needed. For now we just
        // acknowledge receipt so the route doesn't fatal on a fresh install.
        return (new RedirectResponse(getenv('APP_BASE_URL') . '/contact'))
            ->with('success', "Thanks {$name}, your message has been received.");
    }

    /**
     * Show a single blog post by slug.
     */
    public function showPost(Request $request, string $slug): Response
    {
        return $this->view(
            'pages/post.twig',
            ['slug' => $slug],
            [
                'title'         => 'Post: ' . $slug,
                'canonical_url' => $request->getCanonicalUrl(),
            ]
        );
    }

    /**
     * Show a paginated list of users.
     */
    public function showUsers(Request $request): Response
    {
        try {
            $users = (new User())->findAll();
            $error = null;
        } catch (\Throwable $e) {
            // Fresh installs may not have a database configured/migrated yet;
            // degrade gracefully instead of a fatal error.
            $users = [];
            $error = 'Unable to load users right now. Is your database configured and migrated?';
        }

        return $this->view(
            'pages/users.twig',
            ['users' => $users, 'error' => $error],
            ['title' => 'Users']
        );
    }

    /**
     * Show a single user's profile.
     */
    public function viewUser(Request $request, string $user_id): Response
    {
        try {
            $user  = (new User())->getUserById($user_id);
            $error = null;
        } catch (\Throwable $e) {
            $user  = false;
            $error = 'Unable to load this user right now. Is your database configured and migrated?';
        }

        return $this->view(
            'pages/user.twig',
            ['user' => $user ?: null, 'error' => $error],
            ['title' => 'User Profile']
        );
    }
}
