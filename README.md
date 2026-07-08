# NovaLearn — Plateforme LMS (Learning Management System)

Plateforme web de gestion de l'apprentissage développée en **PHP natif (MVC) + MySQL**,
avec **Bootstrap 5**, **FontAwesome** et **AJAX** pour les interactions dynamiques.

---

## 1. Fonctionnalités

### 👨‍🏫 Enseignant
- Création de cours rattachés à un module défini par le promoteur
- Ajout de leçons en **PDF ou vidéo**, organisées et réordonnables
- Création d'évaluations (QCM / Vrai-Faux à choix multiples) par leçon, avec
  note de passage configurable
- Suivi des étudiants inscrits et de leur progression
- Tableau de bord avec statistiques (Chart.js)

### 🎓 Étudiant
- Catalogue de cours avec recherche AJAX et filtres par module
- Inscription en un clic
- Lecteur de leçon (PDF intégré / lecteur vidéo HTML5)
- **Déblocage progressif** : la leçon suivante n'est accessible qu'après
  validation de la précédente (drip content)
- Évaluations interactives corrigées automatiquement par AJAX (sans
  rechargement de page)
- Suivi de la progression (%) par cours, calculé automatiquement
- Avis et notation des cours (étoiles)
- Certificats téléchargeables/imprimables (PDF via impression navigateur)
  avec QR code de vérification

### 🏢 Promoteur (Administrateur)
- Création et gestion des **modules de formation**
- Vue d'ensemble de tous les cours et utilisateurs
- Activation/désactivation des comptes
- Délivrance automatique des certificats lorsque tous les cours d'un module
  sont validés à 100%
- Statistiques globales (répartition des utilisateurs, notes, progression)

### ⚙️ Transversal
- Authentification sécurisée (mots de passe hachés avec bcrypt, jetons CSRF)
- Notifications en temps réel (cloche, AJAX polling)
- Mode sombre / clair (persisté côté navigateur)
- Vérification publique d'un certificat via son code unique
- Connexions à la base de données via **PDO + requêtes préparées**
  (protection contre les injections SQL, connexions persistantes pour
  une bonne tenue en charge)

---

## 2. Architecture (MVC)

```
lms/
├── config/                 # Configuration (BDD, constantes, chemins)
├── database/
│   ├── schema.sql           # Structure de la base de données
│   └── seed.php             # Données de démonstration
├── app/
│   ├── core/                 # Router, Controller, Model, Database (PDO)
│   ├── helpers/functions.php # Fonctions utilitaires globales
│   ├── controllers/          # AuthController, AdminController, TeacherController,
│   │                          # StudentController, ApiController, CertificateController...
│   ├── models/                # User, Course, Lesson, Evaluation, Enrollment, ...
│   └── views/                 # Vues organisées par rôle (admin/, teacher/, student/...)
└── public/                  # DocumentRoot (point d'entrée index.php)
    ├── index.php             # Front controller
    ├── .htaccess              # Réécriture d'URL (routing propre)
    ├── assets/css, js, img
    └── uploads/               # Fichiers PDF, vidéos, avatars, images
```

### Routage
Toutes les requêtes passent par `public/index.php`.
Format des URL : `/controleur/action/parametre1/parametre2`
Exemple : `/teacher/courseManage/3` → `TeacherController::courseManage(3)`

---

## 3. Installation (XAMPP sous Kali Linux)

1. **Copier le projet** dans le dossier des sites web de XAMPP :
   ```bash
   sudo cp -r lms /opt/lampp/htdocs/
   sudo chmod -R 775 /opt/lampp/htdocs/lms/public/uploads
   sudo chown -R daemon:daemon /opt/lampp/htdocs/lms/public/uploads
   ```

2. **Démarrer Apache et MySQL** :
   ```bash
   sudo /opt/lampp/lampp start
   ```

3. **Créer la base de données** :
   - Ouvrir phpMyAdmin : `http://localhost/phpmyadmin`
   - Importer le fichier `database/schema.sql` (cela crée la base `lms_db`
     et toutes les tables)

4. **Charger les données de démonstration** (comptes + module + cours d'exemple) :
   - Dans le navigateur : `http://localhost/lms/database/seed.php`
   - (ou en ligne de commande : `php database/seed.php`)

5. **Configurer l'accès à la base de données** si besoin
   (fichier `config/config.php`) :
   ```php
   define('DB_HOST', '127.0.0.1');
   define('DB_NAME', 'lms_db');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```

6. **Activer mod_rewrite** (généralement actif par défaut sur XAMPP) et
   s'assurer que `AllowOverride All` est défini pour le dossier `htdocs`
   dans `httpd.conf`.

7. **Accéder à la plateforme** :
   ```
   http://localhost/lms/public/
   ```

### Comptes de démonstration (mot de passe : `password123`)
| Rôle       | Email                  |
|------------|------------------------|
| Promoteur  | admin@lms.test         |
| Enseignant | enseignant@lms.test    |
| Étudiant   | etudiant@lms.test      |

---

## 4. Notes techniques

- **Sécurité** : toutes les requêtes SQL utilisent des requêtes préparées
  (`PDO::prepare`). Les formulaires sensibles sont protégés par un jeton
  CSRF. Les mots de passe sont hachés avec `password_hash()` (bcrypt).
  Le dossier `public/uploads` interdit l'exécution de scripts PHP.
- **Performance / forte charge** : connexion PDO en singleton (une seule
  connexion réutilisée par requête, mode persistant activé), requêtes
  optimisées avec index et `FULLTEXT` pour la recherche.
- **QR Code des certificats** : généré via l'API publique
  `api.qrserver.com` (nécessite une connexion Internet côté serveur/navigateur
  lors de l'affichage du certificat). Pour un fonctionnement 100% hors-ligne,
  remplacez l'URL du QR code dans `app/controllers/CertificateController.php`
  par une bibliothèque PHP de génération de QR code locale.
- **Limites d'upload** : PDF 20 Mo max, vidéos 200 Mo max (modifiable dans
  `config/config.php`, et penser à ajuster `upload_max_filesize` /
  `post_max_size` dans `php.ini` de XAMPP).
