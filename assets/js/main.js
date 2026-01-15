/**
 * PharmaCare - Main JavaScript File
 *
 * Handles cart functionality, form validation, and UI interactions
 */

// Get base URL from script location
const BASE_URL = (function() {
    const scripts = document.getElementsByTagName('script');
    for (let script of scripts) {
        if (script.src.includes('main.js')) {
            return script.src.replace('/assets/js/main.js', '');
        }
    }
    return '';
})();

// =====================================================
// CART FUNCTIONALITY
// =====================================================

/**
 * Add product to cart
 */
function addToCart(productId, quantity = 1) {
    const formData = new FormData();
    formData.append('action', 'add');
    formData.append('product_id', productId);
    formData.append('quantity', quantity);

    fetch(`${BASE_URL}/actions/cart-actions.php`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartCount(data.cart_count);
            showNotification(data.message, 'success');
        } else {
            showNotification(data.message || 'Error adding to cart', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error adding to cart', 'error');
    });
}

/**
 * Update cart item quantity
 */
function updateCartQuantity(cartId, quantity) {
    if (quantity < 0) return;

    const formData = new FormData();
    formData.append('action', 'update');
    formData.append('cart_id', cartId);
    formData.append('quantity', quantity);

    fetch(`${BASE_URL}/actions/cart-actions.php`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartCount(data.cart_count);
            // Reload page to update totals
            window.location.reload();
        } else {
            showNotification(data.message || 'Error updating cart', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error updating cart', 'error');
    });
}

/**
 * Remove item from cart
 */
function removeFromCart(cartId) {
    if (!confirm('Remove this item from cart?')) return;

    const formData = new FormData();
    formData.append('action', 'remove');
    formData.append('cart_id', cartId);

    fetch(`${BASE_URL}/actions/cart-actions.php`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartCount(data.cart_count);
            // Remove item from DOM or reload
            const item = document.querySelector(`[data-cart-id="${cartId}"]`);
            if (item) {
                item.remove();
            }
            window.location.reload();
        } else {
            showNotification(data.message || 'Error removing item', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error removing item', 'error');
    });
}

/**
 * Update cart count in header
 */
function updateCartCount(count) {
    const cartCountElements = document.querySelectorAll('.cart-count, #cart-count');
    cartCountElements.forEach(el => {
        el.textContent = count;
    });
}

// =====================================================
// EVENT LISTENERS
// =====================================================

document.addEventListener('DOMContentLoaded', function() {
    // Add to Cart buttons
    document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            // Skip if this is the detail page button (handled separately)
            if (this.id === 'add-to-cart-detail') return;

            e.preventDefault();
            const productId = this.dataset.productId;
            const isPrescription = this.dataset.prescription === 'true';

            if (isPrescription) {
                if (!confirm('This product requires a prescription. You will need to upload your prescription during checkout. Continue?')) {
                    return;
                }
            }

            // Add loading state
            const originalText = this.innerHTML;
            this.innerHTML = '<span class="spinner"></span>';
            this.disabled = true;

            // Simulate slight delay for UX
            setTimeout(() => {
                addToCart(productId);
                this.innerHTML = originalText;
                this.disabled = false;
            }, 300);
        });
    });

    // Mobile menu toggle
    const mobileToggle = document.getElementById('mobile-menu-toggle');
    const categoryNav = document.getElementById('category-nav');

    if (mobileToggle && categoryNav) {
        mobileToggle.addEventListener('click', function() {
            categoryNav.classList.toggle('active');
        });
    }

    // User dropdown (keyboard accessibility)
    const userDropdown = document.querySelector('.user-dropdown');
    if (userDropdown) {
        const userBtn = userDropdown.querySelector('.user-btn');
        const dropdownMenu = userDropdown.querySelector('.dropdown-menu');

        userBtn.addEventListener('click', function() {
            dropdownMenu.classList.toggle('show');
        });

        // Close on outside click
        document.addEventListener('click', function(e) {
            if (!userDropdown.contains(e.target)) {
                dropdownMenu.classList.remove('show');
            }
        });
    }

    // Form validation
    initFormValidation();

    // Image preview for admin product upload
    const productImageInput = document.querySelector('input[name="image"]');
    if (productImageInput) {
        productImageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    let preview = productImageInput.parentElement.querySelector('img');
                    if (!preview) {
                        preview = document.createElement('img');
                        preview.style.cssText = 'width: 100px; height: 100px; object-fit: cover; border-radius: 4px; margin-top: 0.5rem;';
                        productImageInput.parentElement.appendChild(document.createElement('div')).appendChild(preview);
                    }
                    preview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Confirm delete actions
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', function(e) {
            if (!confirm(this.dataset.confirm || 'Are you sure?')) {
                e.preventDefault();
            }
        });
    });
});

// =====================================================
// FORM VALIDATION
// =====================================================

function initFormValidation() {
    // Registration form
    const registerForm = document.getElementById('register-form');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            const password = this.querySelector('[name="password"]');
            const confirmPassword = this.querySelector('[name="confirm_password"]');

            if (password && confirmPassword) {
                if (password.value !== confirmPassword.value) {
                    e.preventDefault();
                    showNotification('Passwords do not match', 'error');
                    confirmPassword.focus();
                }
            }
        });
    }

    // Checkout form
    const checkoutForm = document.getElementById('checkout-form');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function(e) {
            const requiredFields = this.querySelectorAll('[required]');
            let isValid = true;

            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('is-invalid');
                } else {
                    field.classList.remove('is-invalid');
                }
            });

            if (!isValid) {
                e.preventDefault();
                showNotification('Please fill in all required fields', 'error');
            }
        });
    }
}

// =====================================================
// NOTIFICATIONS
// =====================================================

function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existing = document.querySelector('.notification-toast');
    if (existing) {
        existing.remove();
    }

    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification-toast notification-${type}`;
    notification.innerHTML = `
        <span>${message}</span>
        <button onclick="this.parentElement.remove()" style="background: none; border: none; color: inherit; cursor: pointer; padding: 0; margin-left: 1rem;">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>
    `;

    // Add styles
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        background-color: ${type === 'success' ? '#28a745' : type === 'error' ? '#dc3545' : '#007bff'};
        color: white;
        display: flex;
        align-items: center;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 9999;
        animation: slideIn 0.3s ease;
    `;

    // Add animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
    `;
    document.head.appendChild(style);

    document.body.appendChild(notification);

    // Auto remove after 5 seconds
    setTimeout(() => {
        notification.style.animation = 'slideIn 0.3s ease reverse';
        setTimeout(() => notification.remove(), 300);
    }, 5000);
}

// =====================================================
// UTILITY FUNCTIONS
// =====================================================

/**
 * Debounce function for search/filter inputs
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Format price for display
 */
function formatPrice(amount) {
    return '$' + parseFloat(amount).toFixed(2);
}

/**
 * Scroll to top
 */
function scrollToTop() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// =====================================================
// ADMIN FUNCTIONALITY
// =====================================================

// Admin sidebar toggle for mobile
const adminSidebarToggle = document.getElementById('admin-sidebar-toggle');
const adminSidebar = document.querySelector('.admin-sidebar');

if (adminSidebarToggle && adminSidebar) {
    adminSidebarToggle.addEventListener('click', function() {
        adminSidebar.classList.toggle('active');
    });
}

// Close admin sidebar on outside click (mobile)
document.addEventListener('click', function(e) {
    if (adminSidebar && adminSidebar.classList.contains('active')) {
        if (!adminSidebar.contains(e.target) && !adminSidebarToggle?.contains(e.target)) {
            adminSidebar.classList.remove('active');
        }
    }
});
