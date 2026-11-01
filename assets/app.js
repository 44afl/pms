let productModal = document.getElementById('product-modal');
let productForm = document.getElementById('product-form');
let deleteModal = document.getElementById('delete-modal');
let imageInput = document.getElementById('product-image');
let preview = document.getElementById('image-preview');
let imageUrl = '';

function clearPreview() {
    if (imageUrl) {
        URL.revokeObjectURL(imageUrl);
    }
    imageUrl = '';
    preview.hidden = true;
    preview.removeAttribute('src');
}

function openProduct(product) {
    productForm.reset();
    clearPreview();
    document.getElementById('product-errors').hidden = true;
    imageInput.value = '';
    imageInput.setCustomValidity('');

    if (product) {
        productForm.elements['id'].value = product.id;
        productForm.elements['name'].value = product.name;
        productForm.elements['description'].value = product.description || '';
        productForm.elements['price'].value = product.price;
        productForm.elements['availability_date'].value = product.availability_date || '';
        productForm.elements['in_stock'].checked = Number(product.in_stock) === 1;
        document.getElementById('product-title').textContent = 'Editează produsul';
        document.getElementById('save-product').textContent = 'Salvează modificările';

        if (product.image) {
            preview.src = 'uploads/' + encodeURIComponent(product.image);
            preview.hidden = false;
        }
    } else {
        productForm.elements['id'].value = '';
        productForm.elements['name'].value = '';
        productForm.elements['description'].value = '';
        productForm.elements['price'].value = '';
        productForm.elements['availability_date'].value = '';
        productForm.elements['in_stock'].checked = false;
        document.getElementById('product-title').textContent = 'Adaugă produs';
        document.getElementById('save-product').textContent = 'Salvează produsul';
    }

    productModal.showModal();
    document.body.style.overflow = 'hidden';
}

document.getElementById('add-product').onclick = function () {
    openProduct(null);
};

let editButtons = document.querySelectorAll('.edit-product');
for (let i = 0; i < editButtons.length; i++) {
    editButtons[i].onclick = function () {
        let product = JSON.parse(this.getAttribute('data-product'));
        openProduct(product);
    };
}

let closeButtons = document.querySelectorAll('[data-close]');
for (let i = 0; i < closeButtons.length; i++) {
    closeButtons[i].onclick = function () {
        let modalId = this.getAttribute('data-close');
        document.getElementById(modalId).close();
    };
}

let deleteButtons = document.querySelectorAll('.delete-product');
for (let i = 0; i < deleteButtons.length; i++) {
    deleteButtons[i].onclick = function () {
        document.getElementById('delete-id').value = this.getAttribute('data-id');
        document.getElementById('delete-message').textContent =
            'Ștergi produsul „' + this.getAttribute('data-name') + '”?';
        deleteModal.showModal();
        document.body.style.overflow = 'hidden';
    };
}

imageInput.onchange = function () {
    clearPreview();
    imageInput.setCustomValidity('');
    let file = imageInput.files[0];
    if (!file) {
        return;
    }

    if (file.size > 2 * 1024 * 1024) {
        imageInput.setCustomValidity('Imaginea poate avea maximum 2 MB.');
    } else if (file.type !== 'image/jpeg' && file.type !== 'image/png' && file.type !== 'image/webp') {
        imageInput.setCustomValidity('Alege o imagine JPG, PNG sau WebP.');
    } else {
        imageUrl = URL.createObjectURL(file);
        preview.src = imageUrl;
        preview.hidden = false;
    }
    imageInput.reportValidity();
};

productModal.onclose = function () {
    clearPreview();
    document.body.style.overflow = '';
};

deleteModal.onclose = function () {
    document.body.style.overflow = '';
};
if (productModal.getAttribute('data-open') === '1') {
    productModal.showModal();
    document.body.style.overflow = 'hidden';
}
