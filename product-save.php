<?php

session_start();
require __DIR__ . '/src/functions.php';

if($_SERVER["REQUEST_METHOD"] !== "POST") { http_response_code(405); exit; }
require __DIR__ . '/config/database.php';

// Product Check
$id = null;
$idRec = GetPost('id');
if($idRec !== '') {
    $id = filter_var($idRec, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if($id === false){
        http_response_code(400);
        exit;
    }
}

$existingProduct = null;
if($id!==null){
    try {
        $q = $pdo->prepare('SELECT * FROM products WHERE id = ?');
        $q->execute([$id]);
        $existingProduct = $q->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Eroare.';
        redirect_to_list();
    }

    if(!$existingProduct){
        $_SESSION['error'] = 'Produsul nu a fost găsit.';
        redirect_to_list();
    }
}

// Data Validation
$data = [
    'id' => $id ?? '',
    'name' => GetPost('name'),
    'description' => GetPost('description'),
    'price' => GetPost('price'),
    'availability_date' => GetPost('availability_date'),
    'in_stock' => isset($_POST['in_stock']) ? 1 : 0,
];
$errors = [];

$nameLength = preg_match_all('/./us', $data['name']);
if($data['name'] === '' || $nameLength < 3 || $nameLength > 150){
    $errors[] = 'Numele produsului trebuie să aibă între 3 și 150 de caractere.';
}

if(preg_match_all('/./us', $data['description']) > 1000){
    $errors[] = 'Descrierea produsului nu poate depăși 1000 de caractere.';
}

if(!preg_match('/^\d+(?:\.\d{1,2})?$/D', $data['price']) || $data['price'] < 1 || $data['price'] > 10000){
    $errors[] = 'Prețul produsului trebuie să fie între 1 și 10.000.';
}

if ($data['availability_date'] !== '') {
    $date = DateTime::createFromFormat('!Y-m-d', $data['availability_date']);
    if (!$date || $date->format('Y-m-d') !== $data['availability_date'] ||
        $data['availability_date'] < '1000-01-01' ||
        $data['availability_date'] > '9999-12-31') {
        $errors[] = 'Introdu o dată validă, între anii 1000 și 9999.';
    }
}

$file = $_FILES['image'] ?? null;
$newImage = null;
$image = $existingProduct['image'] ?? null;

if ($file && (!isset($file['error']) || !is_int($file['error']))) {
    $errors[] = 'Fișierul trimis nu este valid.';
} elseif ($file && $file['error'] !== UPLOAD_ERR_NO_FILE) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Imaginea nu a putut fi încărcată. Verifică dimensiunea fișierului.';
    } elseif ($file['size'] > 2 * 1024 * 1024) {
        $errors[] = 'Imaginea poate avea maximum 2 MB.';
    } elseif (!is_uploaded_file($file['tmp_name'])) {
        $errors[] = 'Fișierul încărcat nu este valid.';
    } else {
        $types = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        if (!isset($types[$mime]) || @getimagesize($file['tmp_name']) === false) {
            $errors[] = 'Alege o imagine JPG, PNG sau WebP.';
        } else {
            $newImage = bin2hex(random_bytes(16)) . '.' . $types[$mime];
        }
    }
}

if (!$errors && $newImage !== null) {
    $uploadDirectory = __DIR__ . '/uploads';
    if (!is_dir($uploadDirectory) && !@mkdir($uploadDirectory, 0755, true) && !is_dir($uploadDirectory)) {
        $errors[] = 'Directorul pentru imagini nu a putut fi creat.';
    }
}

if (!$errors && $newImage !== null) {
    $destination = __DIR__ . '/uploads/' . $newImage;
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        $errors[] = 'Imaginea nu a putut fi salvată. Verifică directorul uploads.';
    } else {
        $image = $newImage;
    }
}

if ($errors) {
    $_SESSION['form_data'] = $data;
    $_SESSION['form_errors'] = $errors;
    redirect_to_list();
}

$params = [
    $data['name'],
    $data['description'],
    $data['price'],
    $image,
    $data['availability_date'] ?: null,
    $data['in_stock'],
];

// Product Update/Insert
try {
    if ($id === null) {
        $sql = 'INSERT INTO products
            (name, description, price, image, availability_date, in_stock)
            VALUES (?, ?, ?, ?, ?, ?)';
    } else {
        $sql = 'UPDATE products SET name = ?, description = ?,
            price = ?, image = ?, availability_date = ?,
            in_stock = ? WHERE id = ?';
        $params[] = $id;
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
} catch (PDOException $exception) {
    if ($newImage !== null) {
        delete_product_image($newImage);
    }
    error_log($exception->getMessage());
    $_SESSION['form_data'] = $data;
    $_SESSION['form_errors'] = ['Produsul nu a putut fi salvat. Încearcă din nou.'];
    redirect_to_list();
}

if ($newImage !== null && !empty($existingProduct['image'])) {
    delete_product_image($existingProduct['image']);
}

unset($_SESSION['form_data'], $_SESSION['form_errors']);
$_SESSION['success'] = $id === null ? 'Produsul a fost adăugat.' : 'Produsul a fost actualizat.';
redirect_to_list();
