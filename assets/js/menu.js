// Create new file or replace existing at: /food-ordering-system/assets/js/menu.js
document.addEventListener('DOMContentLoaded', function() {
    // Initialize cart if not exists
    if (!sessionStorage.getItem('cart')) {
        sessionStorage.setItem('cart', JSON.stringify({}));
    }

    // Quantity controls
    document.querySelectorAll('.plus-btn').forEach(button => {
        button.addEventListener('click', function() {
            const input = this.parentNode.querySelector('.quantity-input');
            input.value = parseInt(input.value) + 1;
        });
    });
    
    document.querySelectorAll('.minus-btn').forEach(button => {
        button.addEventListener('click', function() {
            const input = this.parentNode.querySelector('.quantity-input');
            if (parseInt(input.value) > 1) {
                input.value = parseInt(input.value) - 1;
            }
        });
    });
    
    // Add to cart functionality
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', function() {
            const itemId = this.getAttribute('data-item-id');
            const itemName = this.getAttribute('data-item-name');
            const itemPrice = parseFloat(this.getAttribute('data-item-price'));
            const quantity = parseInt(this.closest('.card-body').querySelector('.quantity-input').value);
            
            // Get current cart
            const cart = JSON.parse(sessionStorage.getItem('cart') || {});
            
            // Add or update item
            if (cart[itemId]) {
                cart[itemId].quantity += quantity;
            } else {
                cart[itemId] = {
                    name: itemName,
                    price: itemPrice,
                    quantity: quantity
                };
            }
            
            // Save cart
            sessionStorage.setItem('cart', JSON.stringify(cart));
            
            // Update UI
            updateCartCount();
            // Show Bootstrap modal instead of alert
        showAddToCartModal(`${quantity} ${itemName} added to your cart!`);
            // showToast(`${quantity} ${itemName} added to cart!`);
            
            // Sync with server
            syncCartWithServer(cart);
        });
    });
    
    // Initialize cart count
    updateCartCount();
});

// Helper functions
function updateCartCount() {
    const cart = JSON.parse(sessionStorage.getItem('cart') || {});
    const count = Object.values(cart).reduce((sum, item) => sum + item.quantity, 0);
    
    const cartCountElement = document.getElementById('cartCount');
    if (cartCountElement) {
        cartCountElement.textContent = count;
        cartCountElement.style.display = count > 0 ? 'block' : 'none';
    }
}

function showAddToCartModal(message) {
    const modal = new bootstrap.Modal(document.getElementById('addToCartModal'));
    document.getElementById('addToCartMessage').textContent = message;
    modal.show();
}

function showToast(message) {
    // Create toast container if needed
    let container = document.getElementById('toastContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toastContainer';
        container.style.position = 'fixed';
        container.style.top = '20px';
        container.style.right = '20px';
        container.style.zIndex = '1000';
        document.body.appendChild(container);
    }
    
    // Create toast
    const toast = document.createElement('div');
    toast.className = 'toast show bg-success text-white';
    toast.innerHTML = `
        <div class="toast-body">
            ${message}
            <button type="button" class="btn-close btn-close-white float-end" data-bs-dismiss="toast"></button>
        </div>
    `;
    container.appendChild(toast);
    
    // Auto-remove after 3 seconds
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

function syncCartWithServer(cart) {
    fetch('api/update_cart_session.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ cart: cart })
    });
}

function addToCart(itemId, quantity) {
    fetch(`api/check_stock.php?id=${itemId}&qty=${quantity}`)
    .then(response => response.json())
    .then(data => {
        if (data.available) {
            // Proceed with add to cart
        } else {
            showStockError(data.message);
        }
    });
}