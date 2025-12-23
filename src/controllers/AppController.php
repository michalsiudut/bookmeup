<?php
require_once 'src/controllers/ProfileController.php';


class AppController {

    //checking inf user is logged in, if not go to login

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $urlPath = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
        $publicPaths = ['login', 'register'];

        if ($urlPath === "") {
            if ($this->isLoggedIn()) {
                header("Location: /dashboard");
            } else {
                header("Location: /login");
            }
            exit();
        }

        if (!$this->isLoggedIn() && !in_array($urlPath, $publicPaths)) {
            header("Location: /login");
            exit();
        }

        if ($this->isLoggedIn() && in_array($urlPath, $publicPaths)) {
            header("Location: /dashboard");
            exit();
        }
    }

    protected function isLoggedIn(): bool {
        return isset($_SESSION['user_id']);
    }

    protected function render(string $template = null, array $variables = [])
    {
        $templatePath = 'public/views/'. $template.'.html';
        $templatePath404 = 'public/views/404.html';
        $output = "";
                 
        if(file_exists($templatePath)){
            extract($variables);
            
            ob_start();
            include $templatePath;
            $output = ob_get_clean();
        } else {
            ob_start();
            include $templatePath404;
            $output = ob_get_clean();
        }
        echo $output;
    }

        protected function isGet(): bool
    {
        return $_SERVER["REQUEST_METHOD"] === 'GET';
    }

    protected function isPost(): bool
    {
        return $_SERVER["REQUEST_METHOD"] === 'POST';
    }
 
}