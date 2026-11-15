document.addEventListener('DOMContentLoaded', () => {

    const menuToggle = document.getElementById('menuToggle');
    const mainNavigation = document.getElementById('mainNavigation');

    if (menuToggle && mainNavigation) {

        menuToggle.addEventListener('click', () => {

            const isOpen = mainNavigation.classList.toggle('is-open');

            menuToggle.setAttribute('aria-expanded', isOpen);

        });

        const navLinks = mainNavigation.querySelectorAll('a');

        navLinks.forEach(link => {

            link.addEventListener('click', () => {

                mainNavigation.classList.remove('is-open');

                menuToggle.setAttribute('aria-expanded', 'false');

            });

        });

    }

    const currentYear = document.getElementById('currentYear');

    if (currentYear) {
        currentYear.textContent = new Date().getFullYear();
    }

});

const CART_STORAGE_KEY = 'ropRengCart';

function getCart() {
    try {
        const cart = JSON.parse(
            localStorage.getItem(CART_STORAGE_KEY)
        );

        return Array.isArray(cart) ? cart : [];
    } catch (error) {
        return [];
    }
}

function saveCart(cart) {
    localStorage.setItem(
        CART_STORAGE_KEY,
        JSON.stringify(cart)
    );
}

function showCartToast(message) {
    let toast = document.getElementById('cartToast');

    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'cartToast';

        toast.style.position = 'fixed';
        toast.style.right = '24px';
        toast.style.bottom = '24px';
        toast.style.zIndex = '9999';
        toast.style.padding = '14px 20px';
        toast.style.borderRadius = '10px';
        toast.style.background = '#D9E75B';
        toast.style.color = '#293706';
        toast.style.fontSize = '13px';
        toast.style.fontWeight = '800';
        toast.style.boxShadow = '0, 10px 30px rgba(0, 0, 0, 0.5)';
        toast.style.opacity = '0';
        toast.style.transform = 'translate(10px)';
        toast.style.transition = 'all o.25s ease';

            document.body.appendChild(toast);
        }

    toast.textContent = message;

    requestAnimationFrame(() => {
        toast.style.opacity = '1';
        toast.style.transform = 'translate(0)';
    });

    clearTimeout(toast.hidenTimer);

    toast.hidenTimer = setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(10px)';
    }, 1800);

}

function addToCart(product) {

    const cart = getCart();

    const existingItem = cart.find(
        item => item.cartKey === product.cartKey
    );

    if (existingItem) {

        existingItem.quantity += 1;

    } else {

        cart.push({
            cartKey: product.cartKey,
            productId: product.productId,
            productName: product.productName,
            variantId: product.variantId,
            variantName: product.variantName,
            price: product.price,
            quantity: 1
        });

    }

    saveCart(cart);

    updateCartCount();

    showCartToast(
        `${product.productName} đã được thêm vào giỏ hàng`
    );
}

function updateCartCount() {

    const cart = getCart();

    const totalQuantity = cart.reduce(
        (total, item) => total + item.quantity,
        0
    );

    const cartCount = document.getElementById('cartCount');

    if (!cartCount) {
        return;
    }

    cartCount.textContent = totalQuantity;
}

document.addEventListener('click', event => {

    const button = event.target.closest('.menu-add-to-cart');

    if (!button) {
        return;
    }

    const productId = button.dataset.productId;
    const productName = button.dataset.productName;

    let variantId = button.dataset.variantId;
    let variantName = button.dataset.variantName;
    let price = Number(button.dataset.price);

    const productCard = button.closest('.menu-product-card');

    const variantSelect = productCard?.querySelector(
        '.menu-variant-select'
    );

    if (variantSelect) {

        const selectedOption =
            variantSelect.options[variantSelect.selectedIndex];

        variantId = selectedOption.value;
        variantName = selectedOption.dataset.variantName;
        price = Number(selectedOption.dataset.price);

    }

    if (!productId || !variantId || !price) {
        return;
    }

    addToCart({
        cartKey: `${productId}-${variantId}`,
        productId: Number(productId),
        productName,
        variantId: Number(variantId),
        variantName,
        price,
    });

});

document.addEventListener('DOMContentLoaded', () => {
    updateCartCount();
});

function renderCart() {

    const cartBody = document.getElementById('cartDrawerBody');
    const cartSubtotal = document.getElementById('cartSubtotal');
    const cartCheckout = document.getElementById('cartCheckout');

    if (!cartBody || !cartSubtotal) {
        return;
    }

    const cart = getCart();

    if (cart.length === 0) {

        cartBody.innerHTML = `
            <div class="cart-empty">
                <p>GIỎ HÀNG ĐANG TRỐNG</p>
                <span>Hãy chọn một món.</span>
            </div>
        `;

        cartSubtotal.textContent = '0đ';

        if (cartCheckout) {
            cartCheckout.disabled= true;
        }

        return;
    }

    if (cartCheckout) {
        cartCheckout.disabled = false;
    }

    let subtotal = 0;

    cartBody.innerHTML = cart.map(item => {

        const itemTotal = item.price * item.quantity;

        subtotal += itemTotal;

        return `
            <article class="cart-item">

                <div class="cart-item-info">

                    <h3>
                        ${item.productName}
                    </h3>

                    <span>
                        ${item.variantName}
                    </span>

                    <strong>
                        ${item.price.toLocaleString('vi-VN')}đ
                    </strong>

                </div>

                <div class="cart-item-quantity">

                    <button
                        type="button"
                        class="cart-quantity-button"
                        data-cart-action="decrease"
                        data-cart-key="${item.cartKey}"
                    >
                        −
                    </button>

                    <span>
                        ${item.quantity}
                    </span>

                    <button
                        type="button"
                        class="cart-quantity-button"
                        data-cart-action="increase"
                        data-cart-key="${item.cartKey}"
                    >
                        +
                    </button>

                </div>

                <button
                    type="button"
                    class="cart-item-remove"
                    data-cart-action="remove"
                    data-cart-key="${item.cartKey}"
                >
                    XÓA
                </button>

            </article>
        `;

    }).join('');

    cartSubtotal.textContent =
        `${subtotal.toLocaleString('vi-VN')}đ`;
}

function updateCartItemQuantity(cartKey, action) {

    const cart = getCart();

    const item = cart.find(
        item => item.cartKey === cartKey
    );

    if (!item) {
        return;
    }

    if (action === 'increase') {
        item.quantity += 1;
    }

    if (action === 'decrease') {
        item.quantity -= 1;
    }

    if (action === 'remove') {

        const updatedCart = cart.filter(
            item => item.cartKey !== cartKey
        );

        saveCart(updatedCart);

        updateCartCount();

        renderCart();

        return;
    }

    const updatedCart = cart.filter(
        item => item.quantity > 0
    );

    saveCart(updatedCart);

    updateCartCount();

    renderCart();
}

document.addEventListener('click', event => {

    const button = event.target.closest(
        '[data-cart-action]'
    );

    if (!button) {
        return;
    }

    const action = button.dataset.cartAction;
    const cartKey = button.dataset.cartKey;

    if (!action || !cartKey) {
        return;
    }

    updateCartItemQuantity(
        cartKey,
        action
    );

});


const cartToggle = document.getElementById('cartToggle');
const cartDrawer = document.getElementById('cartDrawer');
const cartClose = document.getElementById('cartClose');
const cartOverlay = document.getElementById('cartOverlay');

function openCartDrawer() {

    if (!cartDrawer || !cartOverlay) {
        return;
    }

    renderCart();

    cartDrawer.classList.add('is-open');
    cartOverlay.classList.add('is-open');

    cartDrawer.setAttribute('aria-hidden', 'false');

    document.body.style.overflow = 'hidden';
}

function closeCartDrawer() {

    if (!cartDrawer || !cartOverlay) {
        return;
    }

    cartDrawer.classList.remove('is-open');
    cartOverlay.classList.remove('is-open');

    cartDrawer.setAttribute('aria-hidden', 'true');

    document.body.style.overflow = '';
}

cartToggle?.addEventListener('click', openCartDrawer);

cartClose?.addEventListener('click', closeCartDrawer);

cartOverlay?.addEventListener('click', closeCartDrawer);

document.addEventListener('keydown', event => {

    if (event.key === 'Escape') {
        closeCartDrawer();
    }

});