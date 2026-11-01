<?php
$form = $_SESSION['form_data'] ?? [
    'id' => '', 'name' => '', 'description' => '', 'price' => '',
    'availability_date' => '', 'in_stock' => 0
];
$errors = $_SESSION['form_errors'] ?? [];
$openForm = !empty($errors);
unset($_SESSION['form_data'], $_SESSION['form_errors']);
?>
<dialog id="product-modal" class="modal" aria-labelledby="product-title" data-open="<?= $openForm ? '1' : '0' ?>">
    <div class="modal-header">
        <div>
            <h2 id="product-title"><?= $form['id'] ? 'Editează produsul' : 'Adaugă produs' ?></h2>
            <p class="muted">Câmpurile marcate cu * sunt obligatorii.</p>
        </div>
        <button type="button" class="close-button" data-close="product-modal" aria-label="Închide">&times;</button>
    </div>
    <div id="product-errors" class="notice error" role="alert" <?= !$errors ? 'hidden' : '' ?>>
        <?php foreach ($errors as $message): ?>
            <p><?= e($message) ?></p>
        <?php endforeach; ?>
    </div>
    <form action="product-save.php" method="post" enctype="multipart/form-data" id="product-form" class="form-grid">
        <input type="hidden" name="id" value="<?= e($form['id']) ?>">
        <div class="field full-width">
            <label for="product-name">Nume *</label>
            <input id="product-name" name="name" minlength="3" maxlength="150" required value="<?= e($form['name']) ?>" placeholder="Ex.: Mouse wireless" autofocus>
        </div>
        <div class="field full-width">
            <label for="product-description">Descriere</label>
            <textarea id="product-description" name="description" rows="3" maxlength="1000" placeholder="O scurtă descriere a produsului"><?= e($form['description']) ?></textarea>
        </div>
        <div class="field">
            <label for="product-price">Preț (lei) *</label>
            <input id="product-price" name="price" type="number" min="1" max="10000" step="0.01" required value="<?= e($form['price']) ?>" placeholder="1.00">
        </div>
        <div class="field">
            <label for="product-date">Data disponibilității</label>
            <input id="product-date" name="availability_date" type="date" min="1000-01-01" max="9999-12-31" value="<?= e($form['availability_date']) ?>">
        </div>
        <div class="field full-width">
            <label for="product-image">Imagine</label>
            <input id="product-image" name="image" type="file" accept="image/jpeg,image/png,image/webp" aria-describedby="image-help">
            <small class="muted" id="image-help">JPG, PNG sau WebP, maximum 2 MB.</small>
            <img id="image-preview" class="image-preview" alt="Previzualizarea imaginii produsului" hidden>
        </div>
        <label class="checkbox-label full-width" for="product-stock">
            <input id="product-stock" name="in_stock" type="checkbox" value="1" <?= $form['in_stock'] ? 'checked' : '' ?>> Produs în stoc
        </label>
        <div class="modal-actions full-width">
            <button type="button" class="button" data-close="product-modal">Anulează</button>
            <button type="submit" class="button primary" id="save-product">Salvează produsul</button>
        </div>
    </form>
</dialog>
