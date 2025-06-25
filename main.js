// Main JavaScript functionality for Food Ordering System

// DOM Content Loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

// Initialize application
function initializeApp() {
    // Initialize search functionality
    initializeSearch();
    
    // Initialize cart functionality
    initializeCart();
    
    // Initialize form validation
    initializeFormValidation();
    
    // Initialize animations
    initializeAnimations();
    
    // Initialize modals
    initializeModals();
    
    // Initialize filters
    initializeFilters();
    
    // Update cart count on page load
    updateCartCount();
}

// Search functionality
function initializeSearch() {
    const searchInput = document.getElementById('searchInput');
    const searchBtn = document.getElementById('searchBtn');
    
    if (searchInput) {
        searchInput.addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                performSearch();
            }
        });
    }
    
    if (searchBtn) {
        searchBtn.addEventListener('click', performSearch);
    }
}

function performSearch() {
    const searchInput = document.getElementById('searchInput');
    const query = searchInput ? searchInput.value.trim() : '';
    
    if (query.length > 0) {
        // Check current page and redirect accordingly
        const currentPage = window.location.pathname.split('/').pop();
        
        if (currentPage === 'dishes.php' || currentPage === '') {
            window.location.href = `dishes.php?search=${encodeURIComponent(query)}`;
        } else {
            window.location.href = `restaurants.php?search=${encodeURIComponent(query)}`;
        }
    }
}

// Filter functionality
function initializeFilters() {
    // Cuisine filter
    const cuisineFilter = document.getElementById('cuisineFilter');
    if (cuisineFilter) {
        cuisineFilter.addEventListener('change', function() {
            applyFilters();
        });
    }
    
    // Category filter
    const categoryFilter = document.getElementById('categoryFilter');
    if (categoryFilter) {
        categoryFilter.addEventListener('change', function() {
            applyFilters();
        });
    }
    
    // Price range filter
    const priceFilter = document.getElementById('priceFilter');
    if (priceFilter) {
        priceFilter.addEventListener('change', function() {
            applyFilters();
        });
    }
    
    // Sort filter
    const sortFilter = document.getElementById('sortFilter');
    if (sortFilter) {
        sortFilter.addEventListener('change', function() {
            applyFilters();
        });
    }
}

function applyFilters() {
    const form = document.getElementById('filterForm');
    if (form) {
        form.submit();
    }
}

// Cart functionality
function initializeCart() {
    // Add to cart buttons
    const addToCartBtns = document.querySelectorAll('.add-to-cart');
    addToCartBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const dishId = this.getAttribute('data-dish-id');
            const dishName = this.getAttribute('data-dish-name');
            addToCart(dishId, dishName);
        });
    });
    
    // Quantity update buttons
    const quantityBtns = document.querySelectorAll('.quantity-btn');
    quantityBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const action = this.getAttribute('data-action');
            const dishId = this.getAttribute('data-dish-id');
            updateQuantity(dishId, action);
        });
    });
    
    // Remove from cart buttons
    const removeBtns = document.querySelectorAll('.remove-from-cart');
    removeBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const dishId = this.getAttribute('data-dish-id');
            removeFromCart(dishId);
        });
    });
}

function addToCart(dishId, dishName) {
    // Show loading state
    const btn = document.querySelector(`[data-dish-id="${dishId}"]`);
    const originalText = btn.textContent;
    btn.textContent = 'Adding...';
    btn.disabled = true;
    
    // Send AJAX request to add item to cart
    fetch('ajax/add_to_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `dish_id=${dishId}&quantity=1`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(`${dishName} added to cart!`, 'success');
            updateCartCount();
            updateCartTotal();
            
            // Update button text temporarily
            btn.textContent = 'Added!';
            setTimeout(() => {
                btn.textContent = originalText;
                btn.disabled = false;
            }, 1000);
        } else {
            showNotification(data.message || 'Error adding item to cart', 'error');
            btn.textContent = originalText;
            btn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error adding item to cart', 'error');
        btn.textContent = originalText;
        btn.disabled = false;
    });
}

function updateQuantity(dishId, action) {
    const quantitySpan = document.querySelector(`[data-quantity-for="${dishId}"]`);
    let currentQuantity = parseInt(quantitySpan.textContent);
    let newQuantity = action === 'increase' ? currentQuantity + 1 : currentQuantity - 1;
    
    if (newQuantity < 1) {
        removeFromCart(dishId);
        return;
    }
    
    // Send AJAX request to update quantity
    fetch('ajax/update_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `dish_id=${dishId}&quantity=${newQuantity}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            quantitySpan.textContent = newQuantity;
            updateCartTotal();
            updateCartCount();
        } else {
            showNotification(data.message || 'Error updating cart', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error updating cart', 'error');
    });
}

function removeFromCart(dishId) {
    if (confirm('Remove this item from cart?')) {
        // Send AJAX request to remove item
        fetch('ajax/remove_from_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `dish_id=${dishId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove item from DOM
                const cartItem = document.querySelector(`[data-cart-item="${dishId}"]`);
                if (cartItem) {
                    cartItem.style.animation = 'fadeOut 0.3s ease-out';
                    setTimeout(() => {
                        cartItem.remove();
                        // Check if cart is empty
                        const remainingItems = document.querySelectorAll('[data-cart-item]');
                        if (remainingItems.length === 0) {
                            location.reload(); // Reload to show empty cart message
                        }
                    }, 300);
                }
                updateCartTotal();
                updateCartCount();
                showNotification('Item removed from cart', 'success');
            } else {
                showNotification(data.message || 'Error removing item', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error removing item', 'error');
        });
    }
}

function updateCartCount() {
    const cartCountElements = document.querySelectorAll('#cartCount, .cart-count');
    if (cartCountElements.length > 0) {
        fetch('ajax/get_cart_count.php')
            .then(response => response.json())
            .then(data => {
                cartCountElements.forEach(element => {
                    if (data.count > 0) {
                        element.textContent = `(${data.count})`;
                        element.style.display = 'inline';
                    } else {
                        element.style.display = 'none';
                    }
                });
            })
            .catch(error => {
                console.error('Error updating cart count:', error);
            });
    }
}

function updateCartTotal() {
    fetch('ajax/get_cart_total.php')
        .then(response => response.json())
        .then(data => {
            const totalElements = document.querySelectorAll('#cartTotal, .cart-total');
            totalElements.forEach(element => {
                element.textContent = data.total || '$0.00';
            });
        })
        .catch(error => {
            console.error('Error updating cart total:', error);
        });
}

// Form validation
function initializeFormValidation() {
    const forms = document.querySelectorAll('.needs-validation');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
                e.stopPropagation();
            }
        });
    });
}

function validateForm(form) {
    let isValid = true;
    const inputs = form.querySelectorAll('.form-control[required]');
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            showFieldError(input, 'This field is required');
            isValid = false;
        } else {
            clearFieldError(input);
            
            // Specific validations
            if (input.type === 'email' && !isValidEmail(input.value)) {
                showFieldError(input, 'Please enter a valid email address');
                isValid = false;
            }
            
            if (input.type === 'password' && input.value.length < 6) {
                showFieldError(input, 'Password must be at least 6 characters');
                isValid = false;
            }
        }
    });
    
    return isValid;
}

function showFieldError(input, message) {
    input.classList.add('error');
    
    // Remove existing error message
    const existingError = input.parentNode.querySelector('.form-error');
    if (existingError) {
        existingError.remove();
    }
    
    // Add new error message
    const errorDiv = document.createElement('div');
    errorDiv.className = 'form-error';
    errorDiv.textContent = message;
    input.parentNode.appendChild(errorDiv);
}

function clearFieldError(input) {
    input.classList.remove('error');
    const errorDiv = input.parentNode.querySelector('.form-error');
    if (errorDiv) {
        errorDiv.remove();
    }
}

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Animations
function initializeAnimations() {
    // Fade in elements on scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in');
            }
        });
    }, observerOptions);
    
    // Observe cards and other elements
    const animateElements = document.querySelectorAll('.card, .restaurant-card, .dish-card');
    animateElements.forEach(el => observer.observe(el));
}

// Modals
function initializeModals() {
    // Modal triggers
    const modalTriggers = document.querySelectorAll('[data-modal-target]');
    modalTriggers.forEach(trigger => {
        trigger.addEventListener('click', function() {
            const modalId = this.getAttribute('data-modal-target');
            openModal(modalId);
        });
    });
    
    // Modal close buttons
    const modalCloseBtns = document.querySelectorAll('[data-modal-close]');
    modalCloseBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const modal = this.closest('.modal');
            if (modal) {
                closeModal(modal.id);
            }
        });
    });
    
    // Close modal on backdrop click
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal(this.id);
            }
        });
    });
}

function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

// Notifications
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <span>${message}</span>
        <button class="notification-close">&times;</button>
    `;
    
    // Add styles if not already added
    if (!document.getElementById('notification-styles')) {
        const styles = document.createElement('style');
        styles.id = 'notification-styles';
        styles.textContent = `
            .notification {
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 1rem 1.5rem;
                border-radius: 8px;
                color: white;
                font-weight: 500;
                z-index: 1000;
                display: flex;
                align-items: center;
                gap: 1rem;
                animation: slideInRight 0.3s ease-out;
                box-shadow: 0 4px 12px rgba(0,0,0,0.2);
                max-width: 400px;
            }
            
            .notification-success { background: var(--success-color); }
            .notification-error { background: var(--error-color); }
            .notification-warning { background: var(--warning-color); }
            .notification-info { background: var(--secondary-color); }
            
            .notification-close {
                background: none;
                border: none;
                color: white;
                font-size: 1.2rem;
                cursor: pointer;
                opacity: 0.8;
                padding: 0;
                margin-left: auto;
            }
            
            .notification-close:hover {
                opacity: 1;
            }
            
            @keyframes slideInRight {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            
            @keyframes fadeOut {
                from { opacity: 1; transform: scale(1); }
                to { opacity: 0; transform: scale(0.9); }
            }
        `;
        document.head.appendChild(styles);
    }
    
    // Add to page
    document.body.appendChild(notification);
    
    // Close button functionality
    const closeBtn = notification.querySelector('.notification-close');
    closeBtn.addEventListener('click', () => {
        notification.remove();
    });
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.style.animation = 'fadeOut 0.3s ease-out';
            setTimeout(() => {
                notification.remove();
            }, 300);
        }
    }, 5000);
}

// Live search functionality
function initializeLiveSearch() {
    const searchInput = document.getElementById('liveSearchInput');
    if (searchInput) {
        searchInput.addEventListener('input', debounce(function() {
            const query = this.value.trim();
            if (query.length >= 2) {
                performLiveSearch(query);
            } else {
                clearLiveSearchResults();
            }
        }, 300));
    }
}

function performLiveSearch(query) {
    fetch(`ajax/live_search.php?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            displayLiveSearchResults(data);
        })
        .catch(error => {
            console.error('Live search error:', error);
        });
}

function displayLiveSearchResults(results) {
    const resultsContainer = document.getElementById('liveSearchResults');
    if (!resultsContainer) return;
    
    if (results.length === 0) {
        resultsContainer.innerHTML = '<div class="live-search-item">No results found</div>';
    } else {
        resultsContainer.innerHTML = results.map(item => `
            <div class="live-search-item" onclick="selectSearchResult('${item.type}', ${item.id})">
                <div class="live-search-icon">${item.type === 'restaurant' ? '🏪' : '🍽️'}</div>
                <div class="live-search-content">
                    <div class="live-search-title">${item.name}</div>
                    <div class="live-search-subtitle">${item.subtitle}</div>
                </div>
            </div>
        `).join('');
    }
    
    resultsContainer.style.display = 'block';
}

function selectSearchResult(type, id) {
    if (type === 'restaurant') {
        window.location.href = `restaurant_menu.php?id=${id}`;
    } else {
        window.location.href = `dishes.php?dish_id=${id}`;
    }
}

function clearLiveSearchResults() {
    const resultsContainer = document.getElementById('liveSearchResults');
    if (resultsContainer) {
        resultsContainer.style.display = 'none';
    }
}

// Utility functions
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
}

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

// Export functions for use in other scripts
window.FoodOrderingSystem = {
    addToCart,
    updateQuantity,
    removeFromCart,
    showNotification,
    openModal,
    closeModal,
    formatCurrency,
    updateCartCount,
    updateCartTotal
};