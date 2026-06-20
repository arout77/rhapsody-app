<?php
namespace App\Controllers;

use League\Glide\ServerFactory;
use Rhapsody\Core\BaseController;
use Rhapsody\Core\Request;
use Rhapsody\Core\Response;
use Twig\Environment;

class ImageController extends BaseController
{
    public function __construct(Environment $twig)
    {
        parent::__construct($twig);
    }

    public function show(Request $request, string $path)
    {
        // Security: prevent directory traversal
        if (strpos($path, '..') !== false || strpos($path, './') !== false) {
            return $this->errorResponse('Invalid image path', 404);
        }

        $rootPath   = dirname(__DIR__, 2);
        $sourcePath = $rootPath . '/public/images/';
        $cachePath  = $rootPath . '/storage/cache/images/';

        // Ensure cache directory exists
        if (! is_dir($cachePath)) {
            mkdir($cachePath, 0755, true);
        }

        $server = ServerFactory::create([
            'source'   => $sourcePath,
            'cache'    => $cachePath,
            'security' => false, // simplifies cache key generation
        ]);

        // Get query parameters
        $params = $request->allQueryParams();
        if (empty($params)) {
            $params = $_GET;
        }
        unset($params['route'], $params['_route']);

        // ------------------------------------------------------------
        // Determine the cache subdirectory (mirrors the source path)
        // ------------------------------------------------------------
        // e.g., "cena.webp" or "tagteams/team.webp"
        $cacheSubdir = $path;

        // Full path to the subdirectory
        $subdirPath = $cachePath . $cacheSubdir;

        // ------------------------------------------------------------
        // Check if any cached file already exists in this subdirectory
        // ------------------------------------------------------------
        $cachedFile = null;
        if (is_dir($subdirPath)) {
            $files = glob($subdirPath . '/*');
            if (! empty($files)) {
                // Sort by modification time descending (newest first)
                usort($files, function ($a, $b) {
                    return filemtime($b) - filemtime($a);
                });
                $cachedFile = $files[0];
            }
        }

        if (! $cachedFile) {
            // ------------------------------------------------------------
            // No cache – generate the image using outputImage()
            // ------------------------------------------------------------
            ob_start();
            $server->outputImage($path, $params);
            ob_end_clean();

            // Now re‑scan the subdirectory for the newly created file
            if (is_dir($subdirPath)) {
                $files = glob($subdirPath . '/*');
                if (! empty($files)) {
                    usort($files, function ($a, $b) {
                        return filemtime($b) - filemtime($a);
                    });
                    $cachedFile = $files[0];
                }
            }
        }

        if (! $cachedFile) {
            throw new \RuntimeException('Cache file not found after generation');
        }

        // ------------------------------------------------------------
        // Read the cached file and serve it
        // ------------------------------------------------------------
        $imageContent = file_get_contents($cachedFile);
        if ($imageContent === false) {
            throw new \RuntimeException('Failed to read cached image');
        }

        // Determine MIME type from the file extension
        $ext     = pathinfo($path, PATHINFO_EXTENSION);
        $mimeMap = [
            'webp' => 'image/webp',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png'  => 'image/png',
            'gif'  => 'image/gif',
            'svg'  => 'image/svg+xml',
        ];
        $contentType = $mimeMap[strtolower($ext)] ?? 'image/jpeg';

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
