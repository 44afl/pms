<?php

session_start();
require __DIR__ . '/src/functions.php';

if($_SERVER["REQUEST_METHOD"] !== "POST") { http_response_code(405); redirect_to_list(); }
require __DIR__ . '/config/database.php';

if(!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    http_response_code(400);
    exit;
}

try {
    $id = (int) $_POST['id'];

    $q1 = $pdo->prepare('SELECT * FROM products WHERE id = ?');
    $q1->execute([$id]);
    $product = $q1->fetch(PDO::FETCH_ASSOC);
    if(!$product) {
        $_SESSION['error'] = 'Produsul nu a fost găsit.';
        redirect_to_list();
    }

    $q = $pdo->prepare('DELETE FROM products WHERE id = ?');
    $q->execute([$id]);

    delete_product_image($product['image']);
} catch(PDOException $e) {
    error_log($e->getMessage());
    $_SESSION['error'] = 'Produsul nu a putut fi șters. Încearcă din nou.';
    redirect_to_list();
}
$_SESSION['success'] = 'Produsul a fost șters.';
redirect_to_list();
