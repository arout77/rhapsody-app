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
            'source' => $sourcePath,
            'cache'  => $cachePath,
        ]);

        // Get query parameters
        $params = $request->allQueryParams();
        if (empty($params)) {
            $params = $_GET;
        }
        unset($params['route'], $params['_route']);

        try {
            // ------------------------------------------------------------
            // Step 1: Generate the image using outputImage()
            // This creates a cache file somewhere in $cachePath
            // ------------------------------------------------------------
            ob_start();
            $server->outputImage($path, $params);
            ob_end_clean();

            // ------------------------------------------------------------
            // Step 2: Recursively find the most recently created cache file
            // ------------------------------------------------------------
            $files = $this->findAllFiles($cachePath);
            if (empty($files)) {
                throw new \RuntimeException('No cache files found after generation');
            }

            // Sort by modification time descending (newest first)
            usort($files, function ($a, $b) {
                return filemtime($b) - filemtime($a);
            });
            $cachedFile = $files[0];

            // Read the cached image
            $imageContent = file_get_contents($cachedFile);
            if ($imageContent === false) {
                // If we can't read the cache, fall back to the original image
                $originalFile = $sourcePath . $path;
                if (file_exists($originalFile) && is_readable($originalFile)) {
                    $imageContent = file_get_contents($originalFile);
                    if ($imageContent === false) {
                        throw new \RuntimeException('Failed to read original image');
                    }
                    error_log("ImageController: Falling back to original for $path");
                } else {
                    throw new \RuntimeException('Original image not found');
                }
            }

            // ------------------------------------------------------------
            // Step 3: Determine the content type
            // ------------------------------------------------------------
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

            // ------------------------------------------------------------
            // Step 4: Build and return the response
            // ------------------------------------------------------------
            $response = new Response();
            $response->setContent($imageContent);
            $response->setHeader('Content-Type', $contentType);
            $response->setHeader('Content-Length', (string) strlen($imageContent));
            $response->setHeader('Cache-Control', 'public, max-age=86400');

            return $response;

        } catch (\League\Glide\Exception\FileNotFoundException $e) {
            return $this->errorResponse('Image not found: ' . $path, 404);
        } catch (\Exception $e) {
            error_log('Image processing error: ' . $e->getMessage());
            return $this->errorResponse('Unable to process image', 500);
        }
    }

    /**
     * Recursively find all files in a directory and its subdirectories.
     *
     * @param string $directory
     * @return array List of file paths
     */
    private function findAllFiles(string $directory): array
    {
        $files    = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    private function errorResponse(string $message, int $statusCode = 404): Response
    {
        $response = new Response();
        $response->setContent($message);
        $response->setStatusCode($statusCode);
        return $response;
    }
}
