<?php
require_once APP_PATH . '/core/Model.php';

class User extends Model
{
    protected string $table = 'users';

    /** Recherche un utilisateur par email */
    public function findByEmail(string $email): array|false
    {
        return $this->findWhere(['email' => $email]);
    }

    /** Crée un nouvel utilisateur avec mot de passe haché */
    public function register(string $fullName, string $email, string $password, string $role): int
    {
        return $this->create([
            'full_name' => $fullName,
            'email'     => $email,
            'password'  => password_hash($password, PASSWORD_BCRYPT),
            'role'      => $role,
        ]);
    }

    /** Vérifie les identifiants de connexion */
    public function attempt(string $email, string $password): array|false
    {
        $user = $this->findByEmail($email);
        if ($user && password_verify($password, $user['password']) && $user['status'] === 'actif') {
            return $user;
        }
        return false;
    }

    /** Liste les utilisateurs par rôle */
    public function byRole(string $role): array
    {
        return $this->where(['role' => $role], 'full_name ASC');
    }

    /** Compte le nombre d'utilisateurs par rôle */
    public function countByRole(string $role): int
    {
        return $this->count(['role' => $role]);
    }

    /** Met à jour le mot de passe */
    public function changePassword(int $id, string $newPassword): bool
    {
        return $this->update($id, ['password' => password_hash($newPassword, PASSWORD_BCRYPT)]);
    }
}
