<?php
namespace App\Models;

use Rhapsody\Core\BaseModel;
use \PDO as PDO;

/**
 * @phpstan-type UserRow array{user_id: string, name: string, email: string, password: string}
 * @phpstan-type UserSummary array{user_id: string, name: string, email: string}
 */
class User extends BaseModel
{
    /**
     * Counts the total number of users.
     * @return int
     */
    public function countAll(): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(user_id) FROM users");
        $stmt->execute();
        $count = $stmt->fetchColumn();

        return is_numeric($count) ? (int) $count : 0;
    }

    /**
     * Fetches a paginated list of users.
     *
     * @param  int                 $limit  The number of users to fetch.
     * @param  int                 $offset The number of users to skip.
     * @return list<UserSummary>
     */
    public function findPaginated(int $limit, int $offset): array
    {
        $sql = "SELECT user_id, name, email FROM users ORDER BY created_at DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);

        // Bind parameters as integers
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();

        /**
         * @var list<UserSummary> $rows
         */
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $rows;
    }

    /**
     * Fetches all users from the database.
     *
     * @return list<UserSummary>
     */
    public function findAll(): array
    {
        $stmt = $this->db->prepare("SELECT user_id, name, email FROM users ORDER BY name DESC");
        $stmt->execute();

        /**
         * @var list<UserSummary> $rows
         */
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $rows;
    }

    /**
     * @param  string          $uid
     * @return UserRow|false
     */
    public function getUserById(string $uid): array | false
    {
        // Prepare and execute a simple query
        $stmt = $this->db->prepare("SELECT * FROM users WHERE user_id = :uid");
        $stmt->bindParam(':uid', $uid, PDO::PARAM_STR);
        $stmt->execute();

        /**
         * @var UserRow|false $row
         */
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row;
    }

    /**
     * @param  string          $email
     * @return UserRow|false
     */
    public function findByEmail(string $email): array | false
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        /**
         * @var UserRow|false $row
         */
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row;
    }

    /**
     * @param  array{name: string, email: string, password: string} $data
     * @return bool
     */
    public function create(array $data): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO users (user_id, name, email, password) VALUES (:user_id, :name, :email, :password)"
        );
        return $stmt->execute([
            ':user_id'  => bin2hex(random_bytes(16)), // Generate a random ID
            ':name'     => $data['name'],
            ':email'    => $data['email'],
            ':password' => password_hash($data['password'], PASSWORD_BCRYPT), // Hash the password
        ]);
    }

    /**
     * Updates a user's name and email. Returns false without writing
     * if the email is already taken by a different user, so the
     * caller can surface a validation error instead of hitting the
     * table's unique-key constraint on `email`.
     *
     * @param  string $userId
     * @param  string $name
     * @param  string $email
     * @return bool
     */
    public function updateProfile(string $userId, string $name, string $email): bool
    {
        $existing = $this->findByEmail($email);
        if ($existing && $existing['user_id'] !== $userId) {
            return false;
        }

        $stmt = $this->db->prepare(
            "UPDATE users SET name = :name, email = :email WHERE user_id = :user_id"
        );

        return $stmt->execute([
            ':name'    => $name,
            ':email'   => $email,
            ':user_id' => $userId,
        ]);
    }

    /**
     * Updates a user's password. Hashed the same way as create()
     * (bcrypt), so it stays compatible with
     * Rhapsody\Core\Controllers\AuthController::login()'s
     * password_verify() check against the same `password` column —
     * both classes read/write the same `users` table.
     *
     * @param  string $userId
     * @param  string $password Plaintext new password; hashed here.
     * @return bool
     */
    public function updatePassword(string $userId, string $password): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE users SET password = :password WHERE user_id = :user_id"
        );

        return $stmt->execute([
            ':password' => password_hash($password, PASSWORD_BCRYPT),
            ':user_id'  => $userId,
        ]);
    }

    public function isAdmin(string $userId): bool
    {
        $stmt = $this->db->prepare("SELECT is_admin FROM users WHERE user_id = :uid LIMIT 1");
        $stmt->bindParam(':uid', $userId, PDO::PARAM_STR);
        $stmt->execute();

        return (bool) $stmt->fetchColumn();
    }
}
