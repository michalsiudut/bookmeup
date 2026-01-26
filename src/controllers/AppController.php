<?php


abstract class AppController
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    protected function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id']);
    }

    protected function render(?string $template = null, array $variables = [])
    {
        $templatePath = 'public/views/' . $template . '.html';

        if (file_exists($templatePath)) {
            extract($variables);
            include $templatePath;
        } else {
            include 'public/views/404.html';
        }
    }

    protected function isGet(): bool
    {
        return $_SERVER["REQUEST_METHOD"] === 'GET';
    }
    protected function isPost(): bool
    {
        return $_SERVER["REQUEST_METHOD"] === 'POST';
    }

    protected function validatePassword(string $password): bool
    {
        return strlen($password) >= 8 &&
            preg_match('/[A-Z]/', $password) &&
            preg_match('/[0-9]/', $password) &&
            preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password);
    }
}