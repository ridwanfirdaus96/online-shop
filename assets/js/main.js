// Main JavaScript

// Confirm delete
function confirmDelete(message) {
    return confirm(message || 'Apakah Anda yakin ingin menghapus item ini?');
}

// Auto-hide alerts
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });
});

// Update cart quantity
function updateQuantity(productId, quantity) {
    if (quantity < 1) {
        if (!confirm('Hapus produk dari keranjang?')) {
            return;
        }
    }
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.innerHTML = `
        <input type="hidden" name="product_id" value="${productId}">
        <input type="hidden" name="quantity" value="${quantity}">
        <input type="hidden" name="update_cart" value="1">
    `;
    document.body.appendChild(form);
    form.submit();
}
