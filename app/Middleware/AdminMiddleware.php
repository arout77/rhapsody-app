<?php
namespace App\Middleware;

use Rhapsody\Core\Database;
use Rhapsody\Core\Exceptions\HttpException;
use Rhapsody\Core\Middleware\Middleware;
use Rhapsody\Core\RedirectResponse;
use Rhapsody\Core\Request;
use Rhapsody\Core\Response;
use Rhapsody\Core\Routing\Route;
use Rhapsody\Core\Session;

/**
 * Gates every /admin route. Two distinct failure modes, deliberately:
 *
 *  - Not logged in at all -> redirect to /login, same as AuthMiddleware.
 *  - Logged in, but not an admin -> 404, NOT a login redirect. They
 *    already have a perfectly valid session; bouncing them back to
 *    /login would just be a confusing loop for an account that isn't
 *    broken, just not authorized for this area.
 *
 * Queries is_admin fresh on every request rather than caching it in the
 * session — it's a single indexed primary-key lookup, and this avoids
 * a revoked admin staying admin until they happen to log out.
 */
class AdminMiddleware extends Middleware
{
    public function __construct(private readonly Database $db)
    {
    }

    public function handle(Request $request, ?Route $route = null): ?Response
    {
        if (! Session::has('user_id')) {
            return new RedirectResponse(getenv('APP_BASE_URL') . '/login');
        }

        $stmt = $this->db->getConnection()->prepare(
            'SELECT is_admin FROM users WHERE user_id = :user_id LIMIT 1'
        );
        $stmt->bindValue(':user_id', Session::string('user_id'));
        $stmt->execute();

        if (! (bool) $stmt->fetchColumn()) {
            throw new HttpException(403, 'You do not have access to this area.');
        }

        return null;
    }
}
