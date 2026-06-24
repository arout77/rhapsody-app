<?php
namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;
use App\Models\User;
use Rhapsody\Core\BaseController;
use Rhapsody\Core\FileUploader;
use Rhapsody\Core\Mailer;
use Rhapsody\Core\Pagination;
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
}
