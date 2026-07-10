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
    
    /** Vérifie si l'email existe déjà */
	public function emailExists(string $email): bool
	{
    		return (bool)$this->findByEmail($email);
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
    
    /** Crée un utilisateur avec token de vérification */
	public function registerWithVerification(string $fullName, string $email, string $password, string $role): array
	{
    		$token = bin2hex(random_bytes(32));
    
    		$id = $this->create([
        		'full_name'          => $fullName,
        		'email'              => $email,
        		'password'           => password_hash($password, PASSWORD_BCRYPT),
        		'role'               => $role,
        		'verification_token' => $token,
        		'email_verified'     => 0
    		]);

    		return ['id' => $id, 'token' => $token];
	}
	
	/** Vérifie un token et active le compte */
	public function verifyEmail(string $token): bool
	{
    		$user = Database::query(
        		"SELECT id FROM users 
        		WHERE verification_token = :token 
        		AND email_verified = 0 
        		AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)",
        		['token' => $token]
    		)->fetch();

    		if ($user) {
        		Database::query(
            		"UPDATE users SET email_verified = 1, verification_token = NULL WHERE id = :id",
            		['id' => $user['id']]
        		);
        		return true;
    		}
    		return false;
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
