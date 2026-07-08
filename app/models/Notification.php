<?php
require_once APP_PATH . '/core/Model.php';

class Notification extends Model
{
    protected string $table = 'notifications';

    /** Dernières notifications d'un utilisateur */
    public function forUser(int $userId, int $limit = 10): array
    {
        return Database::query(
            "SELECT * FROM notifications WHERE user_id = :uid ORDER BY created_at DESC LIMIT " . (int)$limit,
            ['uid' => $userId]
        )->fetchAll();
    }

    /** Nombre de notifications non lues */
    public function unreadCount(int $userId): int
    {
        return $this->count(['user_id' => $userId, 'is_read' => 0]);
    }

    /** Marque toutes les notifications d'un utilisateur comme lues */
    public function markAllRead(int $userId): void
    {
        Database::query("UPDATE notifications SET is_read = 1 WHERE user_id = :uid", ['uid' => $userId]);
    }
}
