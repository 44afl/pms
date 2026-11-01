<?php
session_start();
require __DIR__ . '/config/database.php';

require __DIR__ . '/src/functions.php';

$q = '';
if (isset($_GET['q']) && is_string($_GET['q'])) {
    $q = trim($_GET['q']);
}

$sql = 'SELECT COUNT(*) FROM products WHERE LOCATE(?, name) > 0';
$stmt = $pdo->prepare($sql);
$stmt->execute([$q]);
$total = (int) $stmt->fetchColumn();

$perPage = 10;
$pages = (int) ceil($total / $perPage);
if ($pages < 1) {
    $pages = 1;
}

$page = 1;
if (isset($_GET['page']) && is_string($_GET['page'])) {
    $page = filter_var($_GET['page'], FILTER_VALIDATE_INT);
}
if ($page === false || $page < 1) {
    $page = 1;
}
if ($page > $pages) {
    $page = $pages;
}
$offset = ($page - 1) * $perPage;

$sql = "SELECT * FROM products WHERE LOCATE(?, name) > 0
        ORDER BY id DESC LIMIT $perPage OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute([$q]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$success = '';
if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}

$error = '';
if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}
?>
<!doctype html>
<html lang="ro">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Administrare produse</title>
        <link rel="stylesheet" href="assets/style.css">
        <script src="assets/app.js" defer></script>
    </head>
    <body>
        <main class="container">
            <header class="page-header">
                <div>
                    <h1>Produse</h1>
                    <p class="muted">Gestionează produsele, prețurile și disponibilitatea.</p>
                </div>
                <button type="button" class="button primary" id="add-product">Adaugă produs</button>
            </header>
            <?php if ($success): ?>
                <p class="notice success" role="status"><?= e($success) ?></p>
            <?php endif; ?>
            <?php if ($error): ?>
                <p class="notice error" role="alert"><?= e($error) ?></p>
            <?php endif; ?>
            <section class="panel" aria-label="Lista produselor">
                <div class="list-toolbar">
                    <form method="get" action="index.php" class="search-form" role="search">
                        <label class="sr-only" for="search">Caută după nume</label>
                        <input id="search" type="search" name="q" placeholder="Caută după nume…" value="<?= e($q) ?>">
                        <button type="submit" class="button">Caută</button>
                        <?php if ($q !== ''): ?>
                            <a href="index.php">Resetează</a>
                        <?php endif; ?>
                    </form>
                    <span class="muted"><?= $total ?> <?= $total === 1 ? 'produs' : 'produse' ?></span>
                </div>
                <div class="table-wrap" tabindex="0" role="region" aria-label="Tabel produse, derulabil pe ecrane mici">
                    <table>
                        <thead>
                            <tr>
                                <th scope="col">Nume</th>
                                <th scope="col">Descriere</th>
                                <th scope="col">Preț</th>
                                <th scope="col">Imagine</th>
                                <th scope="col">Disponibil din</th>
                                <th scope="col">Stoc</th>
                                <th scope="col">Acțiuni</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($products)): ?>
                                <tr>
                                    <td colspan="7" class="empty-state">
                                        <strong><?= $q !== '' ? 'Niciun produs găsit' : 'Nu există produse' ?></strong>
                                        <p><?= $q !== '' ? 'Încearcă alt nume sau resetează căutarea.' : 'Adaugă primul produs folosind butonul de mai sus.' ?></p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($products as $product): ?>
                                    <tr>
                                        <td class="product-name"><?= e($product['name']) ?></td>
                                        <td class="description"><?= e($product['description'] ?: '—') ?></td>
                                        <td class="nowrap"><?= e(number_format((float) $product['price'], 2, ',', '.')) ?> lei</td>
                                        <td>
                                            <?php if ($product['image']): ?>
                                                <img class="product-image" src="uploads/<?= e($product['image']) ?>" alt="<?= e($product['name']) ?>" loading="lazy">
                                            <?php else: ?>
                                                <span class="no-image">Fără imagine</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="nowrap"><?= e($product['availability_date'] ?: '—') ?></td>
                                        <td><span class="stock <?= $product['in_stock'] ? 'available' : 'unavailable' ?>"><?= $product['in_stock'] ? 'În stoc' : 'Indisponibil' ?></span></td>
                                        <td>
                                            <div class="row-actions">
                                                <button type="button" class="button small edit-product" data-product="<?= e(json_encode($product, JSON_INVALID_UTF8_SUBSTITUTE)) ?>">Editează</button>
                                                <button type="button" class="button small danger-outline delete-product" data-id="<?= (int) $product['id'] ?>" data-name="<?= e($product['name']) ?>">Șterge</button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <footer class="list-footer">
                    <span class="muted">Pagina <?= $page ?> din <?= $pages ?></span>
                    <nav class="pagination" aria-label="Paginarea produselor">
                        <?php if ($page > 1): ?>
                            <a class="button small" href="?<?= e(http_build_query(['q' => $q, 'page' => $page - 1])) ?>">Înapoi</a>
                        <?php else: ?>
                            <span class="button small disabled" aria-disabled="true">Înapoi</span>
                        <?php endif; ?>
                        <?php for ($i = 1; $i <= $pages; $i++): ?>
                            <?php if ($i === $page): ?>
                                <span class="button small current" aria-current="page"><?= $i ?></span>
                            <?php else: ?>
                                <a class="button small" href="?<?= e(http_build_query(['q' => $q, 'page' => $i])) ?>"><?= $i ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        <?php if ($page < $pages): ?>
                            <a class="button small" href="?<?= e(http_build_query(['q' => $q, 'page' => $page + 1])) ?>">Înainte</a>
                        <?php else: ?>
                            <span class="button small disabled" aria-disabled="true">Înainte</span>
                        <?php endif; ?>
                    </nav>
                </footer>
            </section>
            <noscript><p class="notice error">Activează JavaScript pentru a folosi formularele.</p></noscript>
        </main>
        <?php require __DIR__ . '/product-form.php'; ?>
        <dialog id="delete-modal" class="modal confirm-modal" aria-labelledby="delete-title">
            <div class="modal-header">
                <h2 id="delete-title">Șterge produsul</h2>
                <button type="button" class="close-button" data-close="delete-modal" aria-label="Închide">&times;</button>
            </div>
            <p id="delete-message"></p>
            <p class="muted">Această acțiune este definitivă.</p>
            <form action="product-delete.php" method="post">
                <input type="hidden" name="id" id="delete-id">
                <div class="modal-actions">
                    <button type="button" class="button" data-close="delete-modal">Anulează</button>
                    <button type="submit" class="button danger">Șterge definitiv</button>
                </div>
            </form>
        </dialog>
    </body>
</html>
