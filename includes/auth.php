<?php
require_once __DIR__ . '/config.php';

function login_required() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['user_id'])) {
        header('Location: /index.php');
        exit;
    }
}

function hash_pw($pw) {
    return hash('sha256', $pw);
}

function login_user($email, $password) {
    $db = get_db();
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && $user['password_hash'] === hash_pw($password)) {
        return $user;
    }
    return null;
}

function register_user($username, $email, $password) {
    $db = get_db();
    try {
        $stmt = $db->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
        $stmt->execute([$username, $email, hash_pw($password)]);
        return [true, 'Compte créé avec succès'];
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) return [false, 'Email déjà utilisé'];
        return [false, $e->getMessage()];
    }
}
