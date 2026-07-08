<?php
require_once APP_PATH . '/models/Course.php';
require_once APP_PATH . '/models/ModuleModel.php';

class HomeController extends Controller
{
    public function index(): void
    {
        $courseModel = new Course();
        $moduleModel = new ModuleModel();

        $featuredCourses = array_slice($courseModel->catalog(), 0, 6);
        $modules = $moduleModel->allWithStats();

        $this->view('home/index', [
            'title' => 'Accueil',
            'featuredCourses' => $featuredCourses,
            'modules' => $modules,
        ], 'guest');
    }

    /** Page de vérification publique d'un certificat */
    public function verify(string $code = ''): void
    {
        require_once APP_PATH . '/models/Certificate.php';
        $certModel = new Certificate();
        $certificate = $code !== '' ? $certModel->verify($code) : false;

        $this->view('home/verify', [
            'title' => 'Vérification de certificat',
            'certificate' => $certificate,
            'code' => $code,
        ], 'guest');
    }
}
