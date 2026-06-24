<?php

use App\Controllers\ApiController;
use Rhapsody\Core\Request;
use Rhapsody\Core\Response;
use Rhapsody\Core\Routing\Router;

// API routes can be prefixed for versioning, e.g., /api/v1
Router::get('/api/users', [ApiController::class, 'getUsers']);
Router::get('/api/users/{id}', [ApiController::class, 'getUser']);

// --- DEBUGGING

// Test JSON endpoints
// Router::get('/api/test/success', function (Request $request) {
//     $response = new Rhapsody\Core\Response();
//     $response->setStatusCode(200);
//     $response->setHeader('Content-Type', 'application/json');
//     $response->setContent(json_encode([
//         'success' => true,
//         'message' => 'API is working!',
//         'data'    => [
//             'timestamp' => date('Y-m-d H:i:s'),
//             'method'    => $request->getMethod(),
//             'path'      => $request->getPath(),
//         ],
//     ]));
//     return $response;
// });

// Router::post('/api/test/echo', function (Request $request) {
//     $body     = $request->getBody();
//     $response = new Rhapsody\Core\Response();
//     $response->setStatusCode(200);
//     $response->setHeader('Content-Type', 'application/json');
//     $response->setContent(json_encode([
//         'success' => true,
//         'message' => 'Echo received',
//         'data'    => [
//             'received'  => $body,
//             'method'    => $request->getMethod(),
//             'timestamp' => date('Y-m-d H:i:s'),
//         ],
//     ]));
//     return $response;
// });

// // Route that intentionally throws an exception
// Router::get('/api/test/error', function () {
//     throw new \Exception('This is a test error for JSON handling.');
// });

// --- End debugging
