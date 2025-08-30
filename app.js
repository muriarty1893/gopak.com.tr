// Gopak Website JavaScript Functions

// Lazy Loading Implementation
class LazyLoader {
    constructor() {
        this.imageObserver = null;
        this.modelObserver = null;
        this.init();
    }

    init() {
        // Check for Intersection Observer support
        if ('IntersectionObserver' in window) {
            this.setupImageObserver();
            this.setupModelObserver();
            this.observeImages();
            this.observeModels();
        } else {
            // Fallback for older browsers
            this.loadAllImages();
            this.loadAllModels();
        }
    }

    setupImageObserver() {
        const options = {
            root: null,
            rootMargin: '50px',
            threshold: 0.1
        };

        this.imageObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    this.loadImage(entry.target);
                    this.imageObserver.unobserve(entry.target);
                }
            });
        }, options);
    }

    setupModelObserver() {
        const options = {
            root: null,
            rootMargin: '100px',
            threshold: 0.1
        };

        this.modelObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    this.load3DModel(entry.target);
                    this.modelObserver.unobserve(entry.target);
                }
            });
        }, options);
    }

    observeImages() {
        const lazyImages = document.querySelectorAll('.lazy-image');
        lazyImages.forEach(img => {
            this.imageObserver.observe(img);
        });
    }

    observeModels() {
        const lazyModels = document.querySelectorAll('.lazy-3d-model');
        lazyModels.forEach(model => {
            this.modelObserver.observe(model);
        });
    }

    loadImage(img) {
        const src = img.getAttribute('data-src');
        if (!src) return;

        img.onload = () => {
            img.classList.add('loaded');
        };

        img.onerror = () => {
            img.classList.add('loaded');
            console.warn(`Failed to load image: ${src}`);
        };

        img.src = src;
        img.removeAttribute('data-src');
    }

    load3DModel(model) {
        const src = model.getAttribute('data-src');
        if (!src) return;

        model.addEventListener('load', () => {
            model.classList.add('loaded');
        });

        model.addEventListener('error', () => {
            model.classList.add('loaded');
            console.warn(`Failed to load 3D model: ${src}`);
        });

        model.src = src;
        model.removeAttribute('data-src');
    }

    loadAllImages() {
        // Fallback for browsers without Intersection Observer
        const lazyImages = document.querySelectorAll('.lazy-image');
        lazyImages.forEach(img => this.loadImage(img));
    }

    loadAllModels() {
        // Fallback for browsers without Intersection Observer
        const lazyModels = document.querySelectorAll('.lazy-3d-model');
        lazyModels.forEach(model => this.load3DModel(model));
    }
}

// Initialize Lazy Loading
const lazyLoader = new LazyLoader();

// Optimize slider images (prevent duplicate loading)
function optimizeSliderImages() {
    const sliderImages = document.querySelectorAll('.slider .item img');
    let masterImage = null;
    
    sliderImages.forEach((img, index) => {
        if (index === 0) {
            // First image becomes the master
            masterImage = img;
        } else {
            // Other images will clone from master when it loads
            img.style.display = 'none';
            
            if (masterImage.complete && masterImage.src) {
                // Master already loaded
                img.src = masterImage.src;
                img.style.display = 'block';
                img.classList.add('loaded');
            } else {
                // Wait for master to load
                masterImage.addEventListener('load', () => {
                    img.src = masterImage.src;
                    img.style.display = 'block';
                    img.classList.add('loaded');
                });
            }
        }
    });
}

// Call optimization after DOM is loaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', optimizeSliderImages);
} else {
    optimizeSliderImages();
}

// Scroll indicator click - updated to point to sales section
const scrollIndicator = document.querySelector('.scroll-indicator');
if (scrollIndicator) {
    scrollIndicator.addEventListener('click', function() {
        document.getElementById('sales').scrollIntoView({
            behavior: 'smooth'
        });
    });
}

// Contact form submission (check if form exists)
const contactForm = document.querySelector('.contact-form form');
if (contactForm) {
    contactForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Get form data
        const formData = new FormData(contactForm);
        
        // Show loading state
        const submitBtn = contactForm.querySelector('.submit-btn');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Gönderiliyor...';
        submitBtn.disabled = true;
        
        // Send to PHP backend
        fetch('api/contact.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                contactForm.reset();
            } else {
                alert(data.message || 'Bir hata oluştu. Lütfen tekrar deneyiniz.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Bir hata oluştu. Lütfen tekrar deneyiniz.');
        })
        .finally(() => {
            // Reset button state
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        });
    });
}

// Scroll animations
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver(function(entries) {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, observerOptions);

// Observe all sections for scroll animations
document.querySelectorAll('.impact-banner, .sales-section, .testimonials-section, .contact-section').forEach(section => {
    section.style.opacity = '0';
    section.style.transform = 'translateY(50px)';
    section.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
    observer.observe(section);
});

// Sales Functionality
class SalesManager {
    constructor() {
        this.config = {
            type: 'unprinted',
            basePrice: 2.5,
            color: 'white',
            colorName: 'Beyaz',
            size: '30x40',
            sizeMultiplier: 1.0,
            quantity: 100
        };
        this.sizeIndex = 0;
        this.init();
    }

    init() {
        this.setupTypeSelection();
        this.setupColorSelection();
        this.setupSizeSlider();
        this.setupQuantityControls();
        // Initial config update from first card
        this.updateConfigFromActiveCard();
        this.updatePreview();
        this.updatePricing();
    }

    setupTypeSelection() {
        const typeOptions = document.querySelectorAll('.type-option');
        typeOptions.forEach(option => {
            option.addEventListener('click', () => {
                typeOptions.forEach(opt => opt.classList.remove('active'));
                option.classList.add('active');
                this.config.type = option.dataset.type;
                this.config.basePrice = parseFloat(option.dataset.price);
                this.updatePreview();
                this.updatePricing();
            });
        });
    }

    setupColorSelection() {
        const colorOptions = document.querySelectorAll('.color-option');
        const customColorPicker = document.getElementById('customColorPicker');
        const customColorOption = document.querySelector('.custom-color-option');
        
        colorOptions.forEach(option => {
            option.addEventListener('click', () => {
                if (option.classList.contains('custom-color-option')) {
                    // Custom color option clicked
                    customColorPicker.click();
                    return;
                }
                
                // Regular color option
                colorOptions.forEach(opt => opt.classList.remove('active'));
                option.classList.add('active');
                this.config.color = option.dataset.color;
                this.config.colorName = option.dataset.name;
                this.updatePreview();
                this.updatePricing();
            });
        });

        // Custom color picker functionality
        if (customColorPicker) {
            customColorPicker.addEventListener('change', (e) => {
                const selectedColor = e.target.value;
                const colorHex = selectedColor.toUpperCase();
                
                // Update custom color circle to show selected color
                const customCircle = document.querySelector('.custom-color-circle');
                customCircle.style.background = selectedColor;
                
                // Set as active color
                colorOptions.forEach(opt => opt.classList.remove('active'));
                customColorOption.classList.add('active');
                
                // Update config with custom color
                this.config.color = 'custom';
                this.config.colorName = `Özel Renk (${colorHex})`;
                this.config.customColor = selectedColor;
                
                this.updatePreview();
                this.updatePricing();
                
                // Show success message
                this.showCustomColorSuccess(colorHex);
            });
        }
    }

    showCustomColorSuccess(colorHex) {
        // Simple notification for custom color selection
        const notification = document.createElement('div');
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(135deg, #90EE90, #32CD32);
            color: #000;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: bold;
            z-index: 1000;
            animation: slideDown 0.3s ease;
        `;
        notification.textContent = `🎨 Özel renk seçildi: ${colorHex}`;

        document.body.appendChild(notification);

        // Remove after 2 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 2000);

        // Add animation styles if not already present
        if (!document.getElementById('custom-color-animations')) {
            const style = document.createElement('style');
            style.id = 'custom-color-animations';
            style.textContent = `
                @keyframes slideDown {
                    from { transform: translateX(-50%) translateY(-100%); opacity: 0; }
                    to { transform: translateX(-50%) translateY(0); opacity: 1; }
                }
            `;
            document.head.appendChild(style);
        }
    }

    setupSizeSlider() {
        this.sizeCards = document.querySelectorAll('.size-card');
        this.sizeActive = Math.min(1, this.sizeCards.length - 1); // Start with second card if available
        const prevBtn = document.querySelector('.size-prev');
        const nextBtn = document.querySelector('.size-next');

        // Initialize all cards first
        this.sizeCards.forEach((card, index) => {
            card.classList.remove('active');
        });

        // Initialize the slider
        this.loadSizeShow();

        // Add click events to cards
        this.sizeCards.forEach((card, index) => {
            card.addEventListener('click', () => {
                this.sizeActive = index;
                this.loadSizeShow();
                this.updateConfigFromCard(card);
            });
        });

        // Navigation buttons
        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                this.sizeActive = this.sizeActive + 1 < this.sizeCards.length ? this.sizeActive + 1 : this.sizeActive;
                this.loadSizeShow();
                this.updateConfigFromActiveCard();
            });
        }

        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                this.sizeActive = this.sizeActive - 1 >= 0 ? this.sizeActive - 1 : this.sizeActive;
                this.loadSizeShow();
                this.updateConfigFromActiveCard();
            });
        }
    }

    loadSizeShow() {
        // Reset all cards first
        this.sizeCards.forEach(card => {
            card.classList.remove('active');
        });

        // Active card styling
        this.sizeCards[this.sizeActive].style.transform = `translateX(-50%)`;
        this.sizeCards[this.sizeActive].style.zIndex = 1;
        this.sizeCards[this.sizeActive].style.filter = 'none';
        this.sizeCards[this.sizeActive].style.opacity = 1;
        this.sizeCards[this.sizeActive].classList.add('active');

        // Cards after active (right side)
        let stt = 0;
        for(let i = this.sizeActive + 1; i < this.sizeCards.length; i++) {
            stt++;
            this.sizeCards[i].style.transform = `translateX(calc(-50% + ${60*stt}px)) scale(${1 - 0.15*stt}) perspective(16px) rotateY(-1deg)`;
            this.sizeCards[i].style.zIndex = -stt;
            this.sizeCards[i].style.filter = 'blur(3px)';
            this.sizeCards[i].style.opacity = stt > 2 ? 0 : 0.7;
        }

        // Cards before active (left side)
        stt = 0;
        for(let i = (this.sizeActive - 1); i >= 0; i--) {
            stt++;
            this.sizeCards[i].style.transform = `translateX(calc(-50% + ${-60*stt}px)) scale(${1 - 0.15*stt}) perspective(16px) rotateY(1deg)`;
            this.sizeCards[i].style.zIndex = -stt;
            this.sizeCards[i].style.filter = 'blur(3px)';
            this.sizeCards[i].style.opacity = stt > 2 ? 0 : 0.7;
        }
    }

    updateConfigFromActiveCard() {
        const activeCard = this.sizeCards[this.sizeActive];
        this.updateConfigFromCard(activeCard);
    }

    updateConfigFromCard(card) {
        this.config.size = card.dataset.size;
        this.config.sizeMultiplier = parseFloat(card.dataset.multiplier);
        this.updatePreview();
        this.updatePricing();
    }

    setupQuantityControls() {
        const quantityInput = document.getElementById('quantity');
        const minusBtn = document.querySelector('.qty-btn.minus');
        const plusBtn = document.querySelector('.qty-btn.plus');
        const presets = document.querySelectorAll('.qty-preset');

        quantityInput.addEventListener('input', () => {
            this.config.quantity = parseInt(quantityInput.value) || 100;
            this.updatePricing();
        });

        minusBtn.addEventListener('click', () => {
            const currentQty = parseInt(quantityInput.value) || 100;
            const newQty = Math.max(50, currentQty - 50);
            quantityInput.value = newQty;
            this.config.quantity = newQty;
            this.updatePricing();
        });

        plusBtn.addEventListener('click', () => {
            const currentQty = parseInt(quantityInput.value) || 100;
            const newQty = Math.min(10000, currentQty + 50);
            quantityInput.value = newQty;
            this.config.quantity = newQty;
            this.updatePricing();
        });

        presets.forEach(preset => {
            preset.addEventListener('click', () => {
                const qty = parseInt(preset.dataset.qty);
                quantityInput.value = qty;
                this.config.quantity = qty;
                this.updatePricing();
            });
        });
    }

    updatePreview() {
        const selectedType = document.getElementById('selectedType');
        const selectedSpecs = document.getElementById('selectedSpecs');

        // Update text only (bag-shape removed)
        selectedType.textContent = this.config.type === 'printed' ? 'Baskılı Çanta' : 'Baskısız Çanta';
        selectedSpecs.textContent = `${this.config.colorName} - ${this.config.size} cm`;
    }

    updatePricing() {
        const unitPrice = this.config.basePrice * this.config.sizeMultiplier;
        const subtotal = unitPrice * this.config.quantity;
        const discount = this.config.quantity >= 500 ? 0.1 : 0;
        const total = subtotal * (1 - discount);

        document.getElementById('unitPrice').textContent = `${unitPrice.toFixed(2)}₺`;
        document.getElementById('totalQty').textContent = this.config.quantity.toString();
        document.getElementById('sizeMultiplier').textContent = `x${this.config.sizeMultiplier}`;
        document.getElementById('totalPrice').textContent = `${total.toFixed(0)}₺`;

        // Update discount info visibility
        const discountInfo = document.querySelector('.discount-info');
        if (this.config.quantity >= 500) {
            discountInfo.style.background = 'rgba(144, 238, 144, 0.2)';
            discountInfo.style.borderColor = 'rgba(144, 238, 144, 0.4)';
        } else {
            discountInfo.style.background = 'rgba(144, 238, 144, 0.1)';
            discountInfo.style.borderColor = 'rgba(144, 238, 144, 0.2)';
        }
    }
}

// Cart Management System
class CartManager {
    constructor() {
        this.cart = this.loadCart();
        this.init();
    }

    init() {
        this.setupAddToCartButton();
        this.updateCartDisplay();
    }

    setupAddToCartButton() {
        const addToCartBtn = document.querySelector('.add-to-cart-btn');
        if (addToCartBtn) {
            addToCartBtn.addEventListener('click', () => {
                this.addToCart();
            });
        }
    }

    addToCart() {
        const salesManager = window.salesManagerInstance;
        if (!salesManager) {
            console.error('Sales manager not initialized');
            return;
        }

        const config = salesManager.config;
        const unitPrice = config.basePrice * config.sizeMultiplier;
        const subtotal = unitPrice * config.quantity;
        const discount = config.quantity >= 500 ? 0.1 : 0;
        const total = subtotal * (1 - discount);

        const cartItem = {
            id: Date.now(), // Simple ID for now
            type: config.type,
            typeName: config.type === 'printed' ? 'Baskılı Çanta' : 'Baskısız Çanta',
            color: config.color,
            colorName: config.colorName,
            size: config.size,
            quantity: config.quantity,
            unitPrice: unitPrice,
            sizeMultiplier: config.sizeMultiplier,
            subtotal: subtotal,
            discount: discount,
            total: total,
            addedAt: new Date().toISOString()
        };

        // Add to cart directly
        this.cart.push(cartItem);
        this.saveCart();
        this.showAddToCartSuccess(cartItem);
        this.updateCartDisplay();
    }



    showAddToCartSuccess(item) {
        // Calculate safe position for notification (avoid cart button)
        const cartButton = document.querySelector('.cart-button-wrapper');
        const isCartVisible = cartButton && cartButton.classList.contains('visible');
        
        // Determine notification position based on screen size and cart button visibility
        const isMobile = window.innerWidth <= 768;
        const isSmallMobile = window.innerWidth <= 480;
        let topPosition = '20px';
        
        if (isCartVisible) {
            // If cart button is visible, position notification below it
            if (isSmallMobile) {
                topPosition = '90px'; // Smaller gap for very small screens
            } else if (isMobile) {
                topPosition = '100px';
            } else {
                topPosition = '110px';
            }
        } else {
            // If cart button is not visible, use top position
            topPosition = '20px';
        }
        
        // Show temporary success notification
        const notification = document.createElement('div');
        notification.style.cssText = `
            position: fixed;
            top: ${topPosition};
            right: 20px;
            background: linear-gradient(135deg, #90EE90, #32CD32);
            color: #000;
            padding: 15px 20px;
            border-radius: 10px;
            box-shadow: 0 10px 20px rgba(144, 238, 144, 0.3);
            z-index: 999;
            font-weight: bold;
            max-width: ${isMobile ? '280px' : '300px'};
            animation: slideInRight 0.3s ease;
        `;
        notification.innerHTML = `
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 1.5em;">🛒</span>
                <div>
                    <div style="font-size: 1.1em; margin-bottom: 5px;">Sepete Eklendi!</div>
                    <div style="font-size: 0.9em; opacity: 0.8;">
                        ${item.quantity} adet ${item.typeName}<br>
                        ${item.colorName} - ${item.size} cm<br>
                        <strong>${item.total.toFixed(0)}₺</strong>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(notification);

        // Remove notification after 4 seconds
        setTimeout(() => {
            notification.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 4000);

        // Add animation styles if not already present
        if (!document.getElementById('cart-animations')) {
            const style = document.createElement('style');
            style.id = 'cart-animations';
            style.textContent = `
                @keyframes slideInRight {
                    from { transform: translateX(100%); opacity: 0; }
                    to { transform: translateX(0); opacity: 1; }
                }
                @keyframes slideOutRight {
                    from { transform: translateX(0); opacity: 1; }
                    to { transform: translateX(100%); opacity: 0; }
                }
            `;
            document.head.appendChild(style);
        }
    }

    updateCartDisplay() {
        // Update cart count in the UI
        const cartCountElement = document.getElementById('cartCount');
        if (cartCountElement) {
            const itemCount = this.getCartItemsCount();
            cartCountElement.textContent = itemCount;
            cartCountElement.style.display = itemCount > 0 ? 'flex' : 'none';
        }

        // Update cart display
    }

    getCartTotal() {
        return this.cart.reduce((total, item) => total + item.total, 0);
    }

    getCartItemsCount() {
        return this.cart.reduce((count, item) => count + item.quantity, 0);
    }

    removeFromCart(itemId) {
        this.cart = this.cart.filter(item => item.id !== itemId);
        this.saveCart();
        this.updateCartDisplay();
    }

    clearCart() {
        this.cart = [];
        this.saveCart();
        this.updateCartDisplay();
    }

    loadCart() {
        try {
            const savedCart = localStorage.getItem('gopak_cart');
            return savedCart ? JSON.parse(savedCart) : [];
        } catch (error) {
            console.error('Error loading cart:', error);
            return [];
        }
    }

    saveCart() {
        try {
            localStorage.setItem('gopak_cart', JSON.stringify(this.cart));
        } catch (error) {
            console.error('Error saving cart:', error);
        }
    }
}

// Cart Button Visibility Controller
class CartButtonController {
    constructor() {
        this.cartButton = document.querySelector('.cart-button-wrapper');
        this.impactSection = document.querySelector('.impact-banner');
        this.init();
    }

    init() {
        if (!this.cartButton || !this.impactSection) return;
        
        // Hide cart button initially
        this.cartButton.classList.remove('visible');
        
        // Setup scroll listener
        this.setupScrollListener();
        
        // Check initial position
        this.checkScrollPosition();
    }

    setupScrollListener() {
        let ticking = false;
        
        const handleScroll = () => {
            if (!ticking) {
                requestAnimationFrame(() => {
                    this.checkScrollPosition();
                    ticking = false;
                });
                ticking = true;
            }
        };

        window.addEventListener('scroll', handleScroll, { passive: true });
    }

    checkScrollPosition() {
        if (!this.impactSection) return;

        const impactSectionTop = this.impactSection.offsetTop;
        const scrollPosition = window.pageYOffset || document.documentElement.scrollTop;
        
        // Show cart button when user scrolls to the impact section (second section)
        if (scrollPosition >= impactSectionTop - 100) {
            this.cartButton.classList.add('visible');
        } else {
            this.cartButton.classList.remove('visible');
        }
    }
}

// Header Navigation Controller
class HeaderController {
    constructor() {
        this.navToggle = document.getElementById('navToggle');
        this.navMenu = document.getElementById('navMenu');
        this.navLinks = document.querySelectorAll('.nav-link');
        this.customerCountElement = document.getElementById('customerCount');
        this.visitorCountElement = document.getElementById('visitorCount');
        this.init();
    }

    init() {
        this.setupMobileNavigation();
        this.setupSmoothScrolling();
        this.setupActiveSection();
        this.startCountAnimations();
    }

    setupMobileNavigation() {
        if (!this.navToggle || !this.navMenu) return;

        // Toggle mobile menu
        this.navToggle.addEventListener('click', () => {
            this.navToggle.classList.toggle('active');
            this.navMenu.classList.toggle('active');
            
            // Prevent body scrolling when menu is open
            if (this.navMenu.classList.contains('active')) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        });

        // Close menu when clicking on a link
        this.navLinks.forEach(link => {
            link.addEventListener('click', () => {
                this.navToggle.classList.remove('active');
                this.navMenu.classList.remove('active');
                document.body.style.overflow = '';
            });
        });

        // Close menu when clicking outside
        document.addEventListener('click', (e) => {
            if (!this.navToggle.contains(e.target) && !this.navMenu.contains(e.target)) {
                this.navToggle.classList.remove('active');
                this.navMenu.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    }

    setupSmoothScrolling() {
        this.navLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const targetId = link.getAttribute('href').substring(1);
                const targetSection = document.getElementById(targetId);
                
                if (targetSection) {
                    const headerHeight = document.querySelector('.main-header').offsetHeight;
                    const targetPosition = targetSection.offsetTop - headerHeight - 20;
                    
                    window.scrollTo({
                        top: targetPosition,
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Logo click to scroll to top
        const headerBrand = document.querySelector('.header-brand h1');
        if (headerBrand) {
            headerBrand.addEventListener('click', () => {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        }
    }

    setupActiveSection() {
        // Track which section is currently in view
        const sections = document.querySelectorAll('section, .banner');
        const options = {
            rootMargin: '-80px 0px -50% 0px',
            threshold: 0.1
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const id = entry.target.id;
                    if (id) {
                        this.updateActiveLink(id);
                    }
                }
            });
        }, options);

        sections.forEach(section => {
            if (section.id) {
                observer.observe(section);
            }
        });
    }

    updateActiveLink(activeId) {
        this.navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === `#${activeId}`) {
                link.classList.add('active');
            }
        });
    }

    startCountAnimations() {
        // Animate customer count
        if (this.customerCountElement) {
            this.animateNumber(this.customerCountElement, 2847, 2000);
        }

        // Animate visitor count
        if (this.visitorCountElement) {
            this.animateNumber(this.visitorCountElement, 156, 1500);
            
            // Update visitor count every 30 seconds with random increments
            setInterval(() => {
                const currentCount = parseInt(this.visitorCountElement.textContent) || 156;
                const increment = Math.floor(Math.random() * 5) + 1; // 1-5 increment
                this.animateNumber(this.visitorCountElement, currentCount + increment, 800);
            }, 30000);
        }
    }

    animateNumber(element, targetValue, duration) {
        const startValue = parseInt(element.textContent) || 0;
        const difference = targetValue - startValue;
        const startTime = Date.now();

        const updateNumber = () => {
            const currentTime = Date.now();
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            // Easing function for smooth animation
            const easeOutQuart = 1 - Math.pow(1 - progress, 4);
            const currentValue = Math.floor(startValue + (difference * easeOutQuart));
            
            element.textContent = currentValue.toLocaleString();
            
            if (progress < 1) {
                requestAnimationFrame(updateNumber);
            } else {
                element.textContent = targetValue.toLocaleString();
            }
        };

        requestAnimationFrame(updateNumber);
    }
}

// Order Modal Controller
class OrderModalController {
    constructor() {
        this.init();
    }

    init() {
        this.setupModalEvents();
    }

    setupModalEvents() {
        const modal = document.getElementById('orderModal');
        const closeModal = document.getElementById('closeModal');
        const cancelOrder = document.getElementById('cancelOrder');
        const orderForm = document.getElementById('orderForm');

        // Close modal events
        [closeModal, cancelOrder].forEach(element => {
            if (element) {
                element.addEventListener('click', () => this.closeModal());
            }
        });

        // Close modal when clicking outside
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                this.closeModal();
            }
        });

        // Handle form submission
        if (orderForm) {
            orderForm.addEventListener('submit', (e) => this.handleOrderSubmit(e));
        }
    }

    closeModal() {
        const modal = document.getElementById('orderModal');
        modal.classList.remove('show');
        document.body.style.overflow = '';
        
        // Clear form
        const form = document.getElementById('orderForm');
        if (form) form.reset();
    }

    async handleOrderSubmit(e) {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        const orderData = {
            firstName: formData.get('firstName'),
            lastName: formData.get('lastName'),
            phone: formData.get('phone'),
            email: formData.get('email'),
            address: formData.get('address'),
            cartItem: window.currentCartItem
        };

        try {
            // Show loading state
            const submitBtn = e.target.querySelector('.btn-submit');
            const originalText = submitBtn.textContent;
            submitBtn.textContent = 'Gönderiliyor...';
            submitBtn.disabled = true;

            // Send to backend
            const response = await fetch('api/orders.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(orderData)
            });

            const result = await response.json();

            if (result.success) {
                // Add to cart after successful order
                window.cartManagerInstance.cart.push(window.currentCartItem);
                window.cartManagerInstance.saveCart();
                window.cartManagerInstance.updateCartDisplay();

                // Show success message
                alert('Siparişiniz başarıyla alındı! En kısa sürede sizinle iletişime geçeceğiz.');
                this.closeModal();
            } else {
                alert(result.message || 'Sipariş gönderilemedi. Lütfen tekrar deneyiniz.');
            }
        } catch (error) {
            console.error('Order submission error:', error);
            alert('Bir hata oluştu. Lütfen tekrar deneyiniz.');
        } finally {
            // Reset button state
            const submitBtn = e.target.querySelector('.btn-submit');
            submitBtn.textContent = 'Siparişi Tamamla';
            submitBtn.disabled = false;
        }
    }
}

// Mobile Size Button Handler
class MobileSizeButtonHandler {
    constructor() {
        this.init();
    }

    init() {
        this.setupMobileSizeButtons();
    }

    setupMobileSizeButtons() {
        const mobileSizeButtons = document.querySelectorAll('.size-btn');
        
        mobileSizeButtons.forEach(button => {
            button.addEventListener('click', () => {
                // Remove active class from all buttons
                mobileSizeButtons.forEach(btn => btn.classList.remove('active'));
                
                // Add active class to clicked button
                button.classList.add('active');
                
                // Update configuration
                const size = button.dataset.size;
                const multiplier = parseFloat(button.dataset.multiplier);
                
                // Update sales manager config if it exists
                if (window.salesManagerInstance) {
                    window.salesManagerInstance.config.size = size;
                    window.salesManagerInstance.config.sizeMultiplier = multiplier;
                    window.salesManagerInstance.updatePreview();
                    window.salesManagerInstance.updatePricing();
                }
                
                // Also update desktop size cards to keep them in sync
                const desktopCards = document.querySelectorAll('.size-card');
                desktopCards.forEach((card, index) => {
                    if (card.dataset.size === size) {
                        // Remove active from all cards
                        desktopCards.forEach(c => c.classList.remove('active'));
                        // Add active to matching card
                        card.classList.add('active');
                    }
                });
            });
        });
    }
}

// Initialize sales manager and cart system
document.addEventListener('DOMContentLoaded', () => {
    // Initialize header controller
    window.headerController = new HeaderController();
    
    // Initialize sales manager first
    window.salesManagerInstance = new SalesManager();
    
    // Initialize cart manager
    window.cartManagerInstance = new CartManager();
    
    // Initialize cart button controller
    window.cartButtonController = new CartButtonController();
    
    // Initialize order modal controller
    window.orderModalController = new OrderModalController();
    
    // Initialize mobile size button handler
    window.mobileSizeButtonHandler = new MobileSizeButtonHandler();
});
