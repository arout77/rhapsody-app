<?php
namespace App\Controllers;

use App\Models\MarketplaceAuthor;
use App\Models\MarketplaceItem;
use App\Models\MarketplacePurchase;
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
            'home/landing.twig',
            [], // no extra template data
            [   // meta data goes here
                'title'         => 'Welcome to Rhapsody',
                'description'   => 'A custom high-performance PHP framework.',
                'canonical_url' => $request->getCanonicalUrl(),
            ]
        );
    }

    /**
     * The post-login overview page — a greeting, a couple of quick
     * stats, and links into the real account section (/account/*).
     * Deliberately light: no purchase/listing rows here, just counts.
     *
     * Was previously '@core/home/dashboard.twig' (the framework's
     * generic default). Now that this needs marketplace-specific
     * content, it renders 'home/dashboard.twig' with no @core prefix
     * so the framework's fallback view cascade picks up this app-level
     * override instead — see Fallback View Cascade in the framework
     * conventions.
     */
    public function dashboard(Request $request): Response
    {
        $userId = Session::string('user_id');

        $purchaseCount = $userId !== '' ? (new MarketplacePurchase())->countByUser($userId) : 0;

        $author       = $userId !== '' ? (new MarketplaceAuthor())->findByUserId($userId) : false;
        $listingCount = $author ? (new MarketplaceItem())->countByAuthorId((int) $author['id']) : 0;

        return $this->view(
            'home/dashboard.twig',
            [
                'purchaseCount' => $purchaseCount,
                'listingCount'  => $listingCount,
                'isSeller'      => (bool) $author,
            ],
            [
                'title'         => 'Dashboard',
                'canonical_url' => $request->getCanonicalUrl(),
            ]
        );
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
     * @param  Request $request
     * @return mixed
     */
    public function handleUpload(Request $request): Response
    {
        $uploader = new FileUploader($request);
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
     * Show the module/theme submission guide for contributors.
     */
    public function submissionGuide(Request $request): Response
    {
        return $this->view(
            'pages/submission-guide.twig',
            [],
            [
                'title'         => 'Submission Guide — Rhapsody Marketplace',
                'description'   => 'Everything you need to know before submitting a module or theme to the Rhapsody Marketplace.',
                'canonical_url' => $request->getCanonicalUrl(),
            ]
        );
    }

    /**
     * Show the Marketplace FAQ page.
     */
    public function faq(Request $request): Response
    {
        $faqGroups = [
            'buyers'  => [
                'label' => 'For Buyers',
                'items' => [
                    [
                        'question' => "How do I install something I've purchased?",
                        'answer'   => "Every item on the Marketplace is distributed as a Composer package rather than a downloadable zip. After checkout, you'll get a package name and, for paid items, an access token for our Private Packagist mirror. Add the repository to your project's composer.json and run composer require with the package name shown on your order confirmation and account dashboard.",
                    ],
                    [
                        'question' => 'What am I actually licensing when I buy a theme or module?',
                        'answer'   => "You're licensed to use the item in your own Rhapsody applications, including client projects, for as long as your access token stays active. Redistributing the raw source as your own product isn't covered — check each listing's license terms, since sellers can set their own usage scope.",
                    ],
                    [
                        'question' => 'Do I get updates after I buy?',
                        'answer'   => 'Yes. Composer resolves version constraints the normal way, so running composer update will pull newer releases the seller publishes, as long as they fall within the version range you required.',
                    ],
                    [
                        'question' => "What if an item doesn't work with my version of Rhapsody?",
                        'answer'   => "Check the listing's compatibility notes first — sellers declare a minimum arout/rhapsody-core version in their package's composer.json. If something still looks broken, open a support ticket from your order page and we'll loop in the seller.",
                    ],
                    [
                        'question' => 'Can I get a refund?',
                        'answer'   => 'Refund requests are handled case-by-case through support, typically for items that are broken as described or misrepresented in their listing.',
                    ],
                ],
            ],
            'sellers' => [
                'label' => 'For Sellers',
                'items' => [
                    [
                        'question' => 'How do I submit a theme or module?',
                        'answer'   => "Use the submission form from your seller dashboard. You'll provide your package's repository URL, a module.json manifest (for modules) or theme metadata, category, pricing, and screenshots. Submissions enter the moderation queue after that.",
                    ],
                    [
                        'question' => 'What does the moderation review actually check?',
                        'answer'   => "Reviewers verify the package installs cleanly against a stated Rhapsody version, that modules only request permissions they use, that a theme passes ThemeValidator's required-block checks, and that the listing content matches what's in the package.",
                    ],
                    [
                        'question' => 'What can a module actually access?',
                        'answer'   => "Modules run inside a permission-checked ModuleContext. You declare the facades you need — for example database access — in your manifest, and each one is scoped: database access is prefixed to your module's own tables and can't reach the rest of the schema.",
                    ],
                    [
                        'question' => 'How and when do I get paid?',
                        'answer'   => "Payments run through Stripe. Payouts follow your Stripe Connect payout schedule once a purchase clears, minus the Marketplace's commission on paid items.",
                    ],
                    [
                        'question' => "Can I push updates to something I've already sold?",
                        'answer'   => 'Yes — tag a new release on your package and it becomes available to buyers on the version constraints they installed with. Breaking changes should bump your major version so existing installs are not disrupted.',
                    ],
                ],
            ],
            'general' => [
                'label' => 'General',
                'items' => [
                    [
                        'question' => 'What is Rhapsody?',
                        'answer'   => 'Rhapsody is a lightweight PHP MVC framework (arout/rhapsody-core) with Twig views, a custom dependency injection container, and a module system. The Marketplace is where the community shares themes and modules built on top of it.',
                    ],
                    [
                        'question' => 'Do I need Rhapsody installed to buy something?',
                        'answer'   => "You'll need a Rhapsody application to install purchases into, since everything ships as a Composer package meant to run inside one. Browsing the catalog doesn't require anything.",
                    ],
                    [
                        'question' => 'Still stuck?',
                        'answer'   => "Reach out through the support link in your account dashboard and we'll get back to you.",
                    ],
                ],
            ],
        ];

        // Flatten into Schema.org FAQPage structured data.
        $mainEntity = [];
        foreach ($faqGroups as $group) {
            foreach ($group['items'] as $item) {
                $mainEntity[] = [
                    '@type'          => 'Question',
                    'name'           => $item['question'],
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text'  => $item['answer'],
                    ],
                ];
            }
        }

        $this->schema->add('FAQPage', [
            'mainEntity' => $mainEntity,
        ]);

        return $this->view(
            'pages/faq.twig',
            ['faq_groups' => $faqGroups],
            [
                'title'         => 'FAQ — Rhapsody Marketplace',
                'description'   => 'Answers for buyers and sellers on the Rhapsody Marketplace: licensing, Composer installs, module submission, moderation, and payouts.',
                'canonical_url' => $request->getCanonicalUrl(),
            ]
        );
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
        $name    = trim($request->string('name'));
        $email   = trim($request->string('email'));
        $message = trim($request->string('message'));

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
