<?php
/**
 * =========================================================
 *  Database — Singleton de connexion PDO
 * =========================================================
 *  - Une seule instance de connexion par requête HTTP
 *    (réduction du coût d'ouverture de connexion).
 *  - Connexions persistantes désactivées par défaut mais
 *    configurables (utile en cas de forte charge).
 *  - Toutes les requêtes passent par des requêtes préparées
 *    (protection contre les injections SQL).
 *  - Lève des PDOException en cas d'erreur (mode strict).
 */

class Database
{
    private static ?PDO $instance = null;

    /**
     * Retourne l'instance unique de connexion PDO.
     */
    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false, // vraies requêtes préparées côté serveur
                // Connexions persistantes : limite le nombre d'ouvertures/fermetures
                // de connexions TCP lors de pics de trafic.
                PDO::ATTR_PERSISTENT         => false,
            ];

            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                // On journalise l'erreur sans exposer les identifiants à l'utilisateur
                error_log('[DB] Connexion impossible : ' . $e->getMessage());
                http_response_code(500);
                die('Erreur serveur : impossible de se connecter à la base de données.');
            }
        }

        return self::$instance;
    }

    /**
     * Exécute une requête préparée et retourne le PDOStatement.
     *
     * @param string $sql
     * @param array  $params
     * @return PDOStatement
     */
    public static function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /** Empêche l'instanciation directe */
    private function __construct() {}
    private function __clone() {}
}
