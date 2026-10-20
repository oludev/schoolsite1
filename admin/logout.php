<?php require '../config.php'; $_SESSION = []; session_destroy(); header('Location: ' . BASE_URL . '/admin/login.php');
