<?php
/**
 * =========================================================
 *  Model — Classe de base pour tous les modèles
 * =========================================================
 *  Fournit des opérations CRUD génériques basées sur des
 *  requêtes préparées. Chaque modèle enfant définit sa
 *  propre table ($table) et clé primaire ($primaryKey).
 */

abstract class Model
{
    protected string $table;
    protected string $primaryKey = 'id';
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /** Récupère un enregistrement par son id */
    public function find(int $id): array|false
    {
        $stmt = Database::query(
            "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id LIMIT 1",
            ['id' => $id]
        );
        return $stmt->fetch();
    }

    /** Récupère tous les enregistrements (avec tri optionnel) */
    public function all(string $orderBy = ''): array
    {
        $sql = "SELECT * FROM {$this->table}";
        if ($orderBy !== '') {
            $sql .= " ORDER BY {$orderBy}";
        }
        return Database::query($sql)->fetchAll();
    }

    /** Récupère les enregistrements correspondant à des conditions simples (égalité) */
    public function where(array $conditions, string $orderBy = '', ?int $limit = null): array
    {
        [$where, $params] = $this->buildWhere($conditions);
        $sql = "SELECT * FROM {$this->table} WHERE {$where}";
        if ($orderBy !== '') {
            $sql .= " ORDER BY {$orderBy}";
        }
        if ($limit !== null) {
            $sql .= " LIMIT " . (int)$limit;
        }
        return Database::query($sql, $params)->fetchAll();
    }

    /** Récupère un seul enregistrement correspondant aux conditions */
    public function findWhere(array $conditions): array|false
    {
        $rows = $this->where($conditions, '', 1);
        return $rows[0] ?? false;
    }

    /** Insère un enregistrement et retourne son id */
    public function create(array $data): int
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        Database::query($sql, $data);
        return (int)$this->db->lastInsertId();
    }

    /** Met à jour un enregistrement par id */
    public function update(int $id, array $data): bool
    {
        $set = [];
        foreach (array_keys($data) as $col) {
            $set[] = "{$col} = :{$col}";
        }
        $sql = "UPDATE {$this->table} SET " . implode(', ', $set) . " WHERE {$this->primaryKey} = :id";
        $data['id'] = $id;
        $stmt = Database::query($sql, $data);
        return $stmt->rowCount() >= 0;
    }

    /** Supprime un enregistrement par id */
    public function delete(int $id): bool
    {
        $stmt = Database::query("DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id", ['id' => $id]);
        return $stmt->rowCount() > 0;
    }

    /** Compte le nombre d'enregistrements correspondant à des conditions */
    public function count(array $conditions = []): int
    {
        if (empty($conditions)) {
            return (int)Database::query("SELECT COUNT(*) AS c FROM {$this->table}")->fetch()['c'];
        }
        [$where, $params] = $this->buildWhere($conditions);
        $sql = "SELECT COUNT(*) AS c FROM {$this->table} WHERE {$where}";
        return (int)Database::query($sql, $params)->fetch()['c'];
    }

    /** Construit une clause WHERE simple (égalité, combinée par AND) */
    protected function buildWhere(array $conditions): array
    {
        $parts = [];
        $params = [];
        foreach ($conditions as $col => $val) {
            $paramKey = str_replace('.', '_', $col);
            $parts[] = "{$col} = :{$paramKey}";
            $params[$paramKey] = $val;
        }
        return [implode(' AND ', $parts), $params];
    }
}
