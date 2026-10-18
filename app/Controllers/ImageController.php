<?php
namespace App\Controllers;

use League\Glide\Filesystem\FileNotFoundException;
use League\Glide\ServerFactory;
use League\Glide\Signatures\SignatureException;
use Rhapsody\Core\BaseController;
use Rhapsody\Core\Helpers\ImageSigner;
use Rhapsody\Core\Helpers\Path;
use Rhapsody\Core\Request;
use Rhapsody\Core\Response;
use Twig\Environment;

class ImageController extends BaseController
{
    /**
     * Largest image Glide will generate, in pixels (width x height).
     * 4,000,000 = 2000 x 2000. Sources above this are scaled down to fit.
     */
    private const MAX_IMAGE_PIXELS = 4_000_000;

    /**
     * Fallback MIME types, keyed by source file extension.
     */
    private const MIME_TYPES = [
        'webp' => 'image/webp',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'gif'  => 'image/gif',
        'svg'  => 'image/svg+xml',
    ];

    public function __construct(Environment $twig)
    {
        parent::__construct($twig);
    }

    public function show(Request $request, string $path): Response
    {
        // Security: prevent directory traversal
        if (str_contains($path, '..') || str_contains($path, './')) {
            return $this->errorResponse('Invalid image path', 404);
        }

        // Get query parameters (w, h, fit, q, fm, s, ...)
        $params = $request->allQueryParams();
        if ($params === []) {
            $params = $_GET;
        }
        unset($params['route'], $params['_route']);

        // Any query string means "generate a variant", and every distinct query
        // string is a separate cached file. So variants must be signed by the
        // application (see image_url() / ImageSigner::url()); the plain
        // original (no parameters at all) stays public.
        if ($params !== []) {
            try {
                ImageSigner::validate($path, $params);
            } catch (SignatureException) {
                return $this->errorResponse('Invalid image signature', 403);
            }

            // The signature isn't a manipulation, keep it out of Glide's cache key.
            unset($params['s']);
        }

        $cachePath = Path::storage('cache/images');
        if (! is_dir($cachePath) && ! mkdir($cachePath, 0755, true) && ! is_dir($cachePath)) {
            throw new \RuntimeException('Unable to create the image cache directory.');
        }

        $server = ServerFactory::create([
            'source'         => Path::public('images'),
            'cache'          => $cachePath,
            'max_image_size' => self::MAX_IMAGE_PIXELS,
        ]);

        // Glide keys its cache by source path + the exact manipulation params.
        // makeImage() returns the cache path of the variant matching THESE
        // params, generating and caching it first if it doesn't exist yet.
        try {
            $cacheKey     = $server->makeImage($path, $params);
            $imageContent = $server->getCache()->read($cacheKey);
        } catch (FileNotFoundException) {
            return $this->errorResponse('Image not found', 404);
        }

        // Prefer the real output type (fm=webp changes it), then fall back to
        // the source extension.
        $detected    = (new \finfo(FILEINFO_MIME_TYPE))->buffer($imageContent);
        $contentType = is_string($detected) && str_starts_with($detected, 'image/')
            ? $detected
            : (self::MIME_TYPES[strtolower(pathinfo($path, PATHINFO_EXTENSION))] ?? 'image/jpeg');

        $response = new Response();
        $response->setContent($imageContent);
        $response->setHeader('Content-Type', $contentType);
        $response->setHeader('Content-Length', (string) strlen($imageContent));
        $response->setHeader('Cache-Control', 'public, max-age=86400');

        return $response;
    }

    private function errorResponse(string $message, int $statusCode = 404): Response
    {
        $response = new Response();
        $response->setContent($message);
        $response->setStatusCode($statusCode);
        return $response;
    }
}
