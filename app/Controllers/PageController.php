<?php
namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;
use App\Models\User;
use Core\BaseController;
use Core\FileUploader;
use Core\Mailer;
use Core\Pagination;
use Core\Request;
use Core\Response;
use Core\Session;
use Core\Validator;
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
    public function index(): Response
    {
        return $this->view('@core/home/landing.twig');
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
