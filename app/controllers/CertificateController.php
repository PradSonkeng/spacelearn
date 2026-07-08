<?php
require_once APP_PATH . '/models/Certificate.php';

class CertificateController extends Controller
{
    /** Affiche un certificat (propriétaire ou promoteur uniquement) */
    public function show(int $id): void
    {
        $this->requireAuth();

        $certModel = new Certificate();
        $sql = "SELECT c.*, m.title AS module_title, m.description AS module_description,
                       u.full_name AS student_name
                FROM certificates c
                JOIN modules m ON m.id = c.module_id
                JOIN users u ON u.id = c.student_id
                WHERE c.id = :id";
        $certificate = Database::query($sql, ['id' => $id])->fetch();

        if (!$certificate) {
            $this->view('errors/404', [], 'none');
            return;
        }

        $isOwner = (int)$certificate['student_id'] === current_user_id();
        $isPromoter = current_role() === 'promoteur';

        if (!$isOwner && !$isPromoter) {
            $this->view('errors/403', [], 'main');
            return;
        }

        $verifyUrl = full_url('home/verify') . '?code=' . urlencode($certificate['code']);
        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . urlencode($verifyUrl);

        $this->view('certificate/show', [
            'title' => 'Certificat',
            'certificate' => $certificate,
            'verifyUrl' => $verifyUrl,
            'qrUrl' => $qrUrl,
        ], 'none');
    }
}
