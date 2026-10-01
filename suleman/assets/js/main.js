/* ========================================================
   AFRIDI PHARMACY - Interactive Frontend JavaScript
   ======================================================== */

document.addEventListener('DOMContentLoaded', function() {
    initAddToCartButtons();
    initQuantityControls();
    initPrescriptionUploadPreview();
});

/**
 * Global Toast Notification Helper
 */
function showToast(message, type = 'success') {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        document.body.appendChild(container);
    }

    const toastId = 'toast-' + Date.now();
    const bgClass = type === 'success' ? 'bg-success' : (type === 'danger' ? 'bg-danger' : 'bg-warning');
    const icon = type === 'success' ? 'bi-check-circle-fill' : (type === 'danger' ? 'bi-exclamation-triangle-fill' : 'bi-info-circle-fill');

    const html = `
    <div id="${toastId}" class="toast align-items-center text-white ${bgClass} border-0 shadow-lg mb-2" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body d-flex align-items-center gap-2 font-weight-bold">
          <i class="bi ${icon} fs-5"></i>
          <span>${message}</span>
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
    </div>`;

    container.insertAdjacentHTML('beforeend', html);
    const toastElem = document.getElementById(toastId);
    const bsToast = new bootstrap.Toast(toastElem, { delay: 3500 });
    bsToast.show();

    toastElem.addEventListener('hidden.bs.toast', function() {
        toastElem.remove();
    });
}

/**
 * Handle AJAX Add To Cart
 */
function initAddToCartButtons() {
    document.querySelectorAll('.btn-add-to-cart').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = this.dataset.productId;
            const qtyInput = document.getElementById(`qty-${productId}`);
            const qty = qtyInput ? parseInt(qtyInput.value) : 1;

            const originalHTML = this.innerHTML;
            this.disabled = true;
            this.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Adding...`;

            const formData = new FormData();
            formData.append('action', 'add_to_cart');
            formData.append('product_id', productId);
            formData.append('quantity', qty);

            fetch('cart-action.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                this.disabled = false;
                this.innerHTML = originalHTML;

                if (data.status === 'success') {
                    showToast(data.message, 'success');
                    // Update Cart badge
                    const badge = document.querySelector('.cart-badge-counter');
                    if (badge) {
                        badge.textContent = data.cart_count;
                        badge.classList.remove('d-none');
                    }
                } else {
                    showToast(data.message, 'danger');
                }
            })
            .catch(err => {
                this.disabled = false;
                this.innerHTML = originalHTML;
                showToast('Unable to connect to server. Please try again.', 'danger');
            });
        });
    });
}

/**
 * Cart Page Quantity +/- Controls
 */
function initQuantityControls() {
    document.querySelectorAll('.btn-qty-minus').forEach(btn => {
        btn.addEventListener('click', function() {
            const input = this.nextElementSibling;
            let current = parseInt(input.value);
            if (current > 1) {
                input.value = current - 1;
                triggerCartUpdate(input.dataset.cartId, input.value);
            }
        });
    });

    document.querySelectorAll('.btn-qty-plus').forEach(btn => {
        btn.addEventListener('click', function() {
            const input = this.previousElementSibling;
            let current = parseInt(input.value);
            input.value = current + 1;
            triggerCartUpdate(input.dataset.cartId, input.value);
        });
    });
}

function triggerCartUpdate(cartId, newQty) {
    if (!cartId) return;
    const formData = new FormData();
    formData.append('action', 'update_cart');
    formData.append('cart_id', cartId);
    formData.append('quantity', newQty);

    fetch('cart-action.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            window.location.reload();
        } else {
            showToast(data.message, 'danger');
        }
    });
}

/**
 * File Input Preview for Prescription Uploads
 */
function initPrescriptionUploadPreview() {
    const rxInput = document.getElementById('prescription_file');
    const previewContainer = document.getElementById('rx_file_preview');
    if (rxInput && previewContainer) {
        rxInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                previewContainer.classList.remove('d-none');
                previewContainer.innerHTML = `
                <div class="alert alert-info d-flex align-items-center gap-2 mb-0">
                  <i class="bi bi-file-earmark-medical fs-4"></i>
                  <div>
                    <strong>${file.name}</strong> (${(file.size / 1024).toFixed(1)} KB)
                    <br><small class="text-muted">File selected. Ready to upload with order.</small>
                  </div>
                </div>`;
            }
        });
    }
}
