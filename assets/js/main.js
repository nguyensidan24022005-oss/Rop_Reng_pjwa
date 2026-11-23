/* =====================================================
   1. CHUNG: THANH ĐIỀU HƯỚNG & NĂM HIỆN TẠI
===================================================== */

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

/* =====================================================
   2. TIỆN ÍCH CHUNG
===================================================== */

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

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function saveCart(cart) {
    localStorage.setItem(
        CART_STORAGE_KEY,
        JSON.stringify(cart)
    );
}

/* =====================================================
   3. GIỎ HÀNG: THÔNG BÁO & THÊM SẢN PHẨM
===================================================== */

function showCartToast(message) {
    let toast = document.getElementById('cartToast');

    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'cartToast';

        toast.style.position = 'fixed';
        toast.style.right = '24px';
        toast.style.bottom = '24px';
        toast.style.zIndex = '9999';
        toast.style.pointerEvents = 'none';
        toast.style.padding = '14px 20px';
        toast.style.borderRadius = '10px';
        toast.style.background = '#D9E75B';
        toast.style.color = '#293706';
        toast.style.fontSize = '13px';
        toast.style.fontWeight = '800';
        toast.style.boxShadow = '0 10px 30px rgba(0, 0, 0, 0.5)';
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(10px)';
        toast.style.transition = 'all 0.25s ease';

        document.body.appendChild(toast);
    }

    toast.textContent = message;

    requestAnimationFrame(() => {
        toast.style.opacity = '1';
        toast.style.transform = 'translateY(0)';
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
            quantity: 1,
            note: ''
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

    const productCard = button.closest(
        '.menu-product-card, .product-card'
    );

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

/* =====================================================
   4. GIỎ HÀNG: KHỞI TẠO & HIỂN THỊ
===================================================== */

document.addEventListener('DOMContentLoaded', () => {
    updateCartCount();
    renderCheckout();
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

        const quantity = Number(item.quantity) || 0;
        const price = Number(item.price) || 0;
        const cartKey = escapeHtml(item.cartKey);
        const itemTotal = price * quantity;

        subtotal += itemTotal;

        return `
            <article class="cart-item">

                <div class="cart-item-info">

                    <h3>
                        ${escapeHtml(item.productName)}
                    </h3>

                    <span>
                        ${escapeHtml(item.variantName)}
                    </span>

                    <strong>
                        ${price.toLocaleString('vi-VN')}đ
                    </strong>
                </div>

                <div class="cart-item-note">
                    <label for="cart-note-${cartKey}">
                        GHI CHÚ
                    </label>

                    <textarea
                        id="cart-note-${cartKey}"
                        class="cart-note-input"
                        data-cart-note="${cartKey}"
                        rows="2"
                        placeholder="Ví dụ: ít đá, ít ngọt,..."
                    >${escapeHtml(item.note)}</textarea>
                </div>

                <div class="cart-item-quantity">

                    <button
                        type="button"
                        class="cart-quantity-button"
                        data-cart-action="decrease"
                        data-cart-key="${cartKey}"
                    >
                        −
                    </button>

                    <span>
                        ${quantity}
                    </span>

                    <button
                        type="button"
                        class="cart-quantity-button"
                        data-cart-action="increase"
                        data-cart-key="${cartKey}"
                    >
                        +
                    </button>

                </div>

                <button
                    type="button"
                    class="cart-item-remove"
                    data-cart-action="remove"
                    data-cart-key="${cartKey}"
                >
                    XÓA
                </button>

            </article>
        `;

    }).join('');

    cartSubtotal.textContent =
        `${subtotal.toLocaleString('vi-VN')}đ`;
}

/* =====================================================
   5. GIỎ HÀNG: CẬP NHẬT & GHI CHÚ
===================================================== */

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

document.addEventListener('input', event => {
    const textarea = event.target.closest(
        '[data-cart-note]'
    );

    if (!textarea) return;

    const cartKey = textarea.dataset.cartNote;
    const cart = getCart();
    const item = cart.find(
        item => item.cartKey === cartKey
    );

    if (!item) return;

    item.note = textarea.value;
    saveCart(cart);
});

/* =====================================================
   6. GIỎ HÀNG: DRAWER
===================================================== */

const cartToggle = document.getElementById('cartToggle');
const cartDrawer = document.getElementById('cartDrawer');
const cartClose = document.getElementById('cartClose');
const cartOverlay = document.getElementById('cartOverlay');
const cartCheckout = document.getElementById('cartCheckout');

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

cartCheckout?.addEventListener('click', () => {
    window.location.href = 'checkout.php';
});

document.addEventListener('keydown', event => {

    if (event.key === 'Escape') {
        closeCartDrawer();
    }
});

/* =====================================================
   7. THANH TOÁN
===================================================== */

function renderCheckout() {
    const checkoutItems = document.getElementById('checkoutOrderItems');
    const checkoutTotal = document.getElementById('checkoutTotal');
    const checkoutSubmit = document.getElementById('checkoutSubmit');

    if (!checkoutItems || !checkoutTotal) return;

    const cart = getCart();

    if (cart.length === 0) {
        checkoutItems.innerHTML = `
            <div class="checkout-empty">
                <strong>GIỎ HÀNG ĐANG TRỐNG</strong>
                <span>Hãy thêm sản phẩm trước khi tạo đơn.</span>
            </div>
        `;

        checkoutTotal.textContent = '0đ';

        if (checkoutSubmit) {
            checkoutSubmit.disabled = true;
        }

        return;
    }

    if (checkoutSubmit) {
        checkoutSubmit.disabled = false;
    }

    let total = 0;

    checkoutItems.innerHTML = cart.map(item => {
        const quantity = Number(item.quantity) || 0;
        const price = Number(item.price) || 0;
        const itemTotal = price * quantity;

        total += itemTotal;

        return `
            <article class="checkout-order-item">

                <div class="checkout-order-item-info">
                    <h3>${escapeHtml(item.productName)}</h3>

                    <span>
                        ${escapeHtml(item.variantName)}
                        · SL ${quantity}
                    </span>

                    ${
                        item.note
                            ? `<span>Ghi chú: ${escapeHtml(item.note)}</span>`
                            : ''
                    }
                </div>

                <div class="checkout-order-item-meta">
                    <span>
                        ${price.toLocaleString('vi-VN')}đ
                    </span>

                    <strong>
                        ${itemTotal.toLocaleString('vi-VN')}đ
                    </strong>
                </div>

            </article>
        `;
    }).join('');

    checkoutTotal.textContent =
        `${total.toLocaleString('vi-VN')}đ`;
}

async function submitOrder() {
    const checkoutSubmit = document.getElementById('checkoutSubmit');

    if (!checkoutSubmit) return;

    const cart = getCart();

    if (cart.length === 0) {
        alert('Giỏ hàng đang trống.');
        return;
    }

    const customerNameInput =
        document.getElementById('customerName');

    const customerName =
        customerNameInput?.value.trim() || '';

    const items = cart.map(item => ({
        product_id: item.productId,
        variant_id: item.variantId,
        quantity: item.quantity,
        note: item.note || ''
    }));

    checkoutSubmit.disabled = true;
    checkoutSubmit.textContent = 'ĐANG TẠO ĐƠN...';
    
    try {

        const response = await fetch(
            'api/create-order.php',
            {
                method: 'POST',

                headers: {
                    'Content-Type': 'application/json'
                },

                body: JSON.stringify({
                    customer_name: customerName,
                    items: items
                })
            }
        );

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(
                result.message ||
                'Không thể tạo đơn hàng.'
            );
        }

        saveCart([]);
        updateCartCount();
        renderCheckout();
        renderCart();

        console.log(
            'Order created:',
            result.data
        );

        alert(
            `Tạo đơn thành công!\nMã đơn: ${result.data.order_code}`
        );

        setTimeout(() => {
            window.location.href = 'index.php'
        }, 500);

    } catch (error) {

        console.error(
            'Create order error:',
            error
        );

        alert(
            error.message ||
            'Có lỗi xảy ra khi tạo đơn.'
        );

    } finally {

        checkoutSubmit.disabled = false;

        checkoutSubmit.innerHTML = `
            XÁC NHẬN ĐƠN
            <span>→</span>
        `;
    }
}

document.addEventListener('click', event => {

    const button = event.target.closest(
        '#checkoutSubmit'
    );

    if (!button) return;

    submitOrder();
});

/* =====================================================
   8. QUẢN TRỊ: DANH SÁCH ĐƠN HÀNG
===================================================== */

async function loadAdminOrders() {

    const tableWrapper =
        document.getElementById(
            'adminOrdersTableWrapper'
        );

    const orderCount =
        document.getElementById(
            'adminOrderCount'
        );

    if (!tableWrapper) return;

    tableWrapper.innerHTML = `
        <div class="admin-loading">
            ĐANG TẢI ĐƠN HÀNG...
        </div>
    `;

    try {

        const response = await fetch(
            'api/orders.php'
        );

        const result = await response.json();

        if (
            !response.ok ||
            !result.success
        ) {
            throw new Error(
                result.message ||
                'Không thể tải đơn hàng.'
            );
        }

        const orders = Array.isArray(result.data)
            ? result.data
            : [];

        if (orderCount) {
            orderCount.textContent =
                orders.length;
        }

        if (orders.length === 0) {

            tableWrapper.innerHTML = `
                <div class="admin-empty">
                    CHƯA CÓ ĐƠN HÀNG
                </div>
            `;

            return;
        }

        tableWrapper.innerHTML = `
            <table class="admin-orders-table">
            
                <thead>
                    <tr>
                        <th>MÃ ĐƠN</th>
                        <th>KHÁCH HÀNG</th>
                        <th>TỔNG TIỀN</th>
                        <th>TRẠNG THÁI</th>
                        <th>THỜI GIAN</th>
                        <th></th>
                    </tr>
                </thead>
                
                <tbody>
                
                    ${orders.map(order => `
                        
                        <tr>
                        
                            <td>
                                <span class="admin-order-code">
                                    ${escapeHtml(order.order_code)}
                                </span>
                            </td>

                            <td>
                                <span class="admin-order-customer">
                                    ${
                                        escapeHtml(
                                            order.customer_name || '—'
                                        )
                                    }
                                </span>
                            </td>
                            
                            <td>
                                <span class="admin-order-total">
                                    ${
                                        Number(order.total)
                                            .toLocaleString('vi-VN')
                                    }đ
                                </span>
                            </td>
                            
                            <td>
                                <span class="admin-order-status">
                                ${escapeHtml(getOrderStatusLabel(
                                    order.status
                                ))}
                                </span>
                            </td>
                            
                            <td>
                                <span class="admin-order-date">
                                    ${escapeHtml(formatOrderDate(
                                        order.created_at
                                    ))}
                                </span>
                            </td>
                            
                            <td>
                                <button
                                    type="button"
                                    class="admin-order-view"
                                    data-order-id="${escapeHtml(order.id)}"
                                >
                                    XEM
                                </button>
                            </td>
                            
                        </tr>
                        
                    `).join('')}
                    
                </tbody>
                
            </table>
        `;
    } catch (error) {

        console.error(
            'Load admin orders error:',
            error
        );

        tableWrapper.innerHTML = `
            <div class="admin-error">
                KHÔNG THỂ TẢI DANH SÁCH ĐƠN HÀNG
            </div>
        `;
    }
}

function getOrderStatusLabel(status) {

    const labels = {
        pending: 'ĐANG XỬ LÝ',
        confirmed: 'ĐANG THỰC HIỆN MÓN',
        completed: 'HOÀN THÀNH',
        cancelled: 'ĐÃ HỦY'
    };

    return labels[status] || status;
}

function formatOrderDate(dateString) {

    if (!dateString) {
        return '—';
    }

    const date = new Date(
        dateString.replace(' ', 'T')
    );

    if (Number.isNaN(date.getTime())) {
        return dateString;
    }

    return date.toLocaleString(
        'vi-VN',
        {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        }
    );
}

/* =====================================================
   9. QUẢN TRỊ: CHUYỂN TRANG & CẬP NHẬT TRẠNG THÁI
===================================================== */

document.addEventListener(
    'click',
    event => {

        const refreshButton =
            event.target.closest(
                '#adminOrdersRefresh'
            );

        if (!refreshButton) return;

        loadAdminOrders();
    }
);

document.addEventListener(
    'DOMContentLoaded',
    () => {

        if (
            document.getElementById(
                'adminOrdersTableWrapper'
            )
        ) {
            loadAdminOrders();
        }

    }
);

document.addEventListener('click', event => {

    const button = event.target.closest(
        '.admin-order-view'
    );

    if (!button) return;

    const orderId = button.dataset.orderId;

    if (!orderId) return;

    window.location.href =
        `admin/order-detail.php?id=${encodeURIComponent(orderId)}`;
});

async function updateOrderStatus() {

    const statusSelect =
        document.getElementById('orderStatus');

    const updateButton =
        document.getElementById('updateOrderStatus');

    if (!statusSelect || !updateButton) {
        return;
    }

    const urlParams =
        new URLSearchParams(
            window.location.search
        );

    const orderId =
        urlParams.get('id');

    const status =
        statusSelect.value;

    if (!orderId || !status) {
        return;
    }

    updateButton.disabled = true;
    updateButton.innerHTML =
        'ĐANG CẬP NHẬT...';

    try {

        const response = await fetch(
            'api/update-order-status.php',
            {
                method: 'POST',

                headers: {
                    'Content-Type': 'application/json'
                },

                body: JSON.stringify({
                    order_id: Number(orderId),
                    status: status
                })
            }
        );

        const result =
            await response.json();

        if (
            !response.ok ||
            !result.success
        ) {
            throw new Error(
                result.message ||
                'Không thể cập nhật trạng thái.'
            );
        }

        alert(
            'Cập nhật trạng thái thành công.'
        );

        window.location.reload();

    } catch (error) {

        console.error(
            'Update order status error:',
            error
        );

        alert(
            error.message ||
            'Có lỗi xảy ra khi cập nhật trạng thái.'
        );

    } finally {

        updateButton.disabled = false;

        updateButton.innerHTML = `
            CẬP NHẬT TRẠNG THÁI
            <span>→</span>
        `;
    }
}

document.addEventListener(
    'click',
    event => {

        const button =
            event.target.closest(
                '#updateOrderStatus'
            );

        if (!button) {
            return;
        }

        updateOrderStatus();
    }
);

async function loadAdminDashboardStats() {

    const dashboard =
        document.querySelector('.admin-dashboard-grid');

    if (!dashboard) {
        return;
    }

    try {

        const response = await fetch(
            'api/dashboard-stats.php'
        );

        const result = await response.json();

        if (
            !response.ok ||
            !result.success
        ) {
            throw new Error(
                result.message ||
                'Không thể tải thống kê.'
            );
        }

        const stats = result.data;

        const setText = (id, value) => {

            const element =
                document.getElementById(id);

            if (element) {
                element.textContent = value;
            }
        };

        const formatNumber = value => {
            return Number(value || 0)
                .toLocaleString('vi-VN');
        };

        const formatCurrency = value => {
            return `${formatNumber(value)}đ`;
        };

        setText(
            'dashboardTotalOrders',
            formatNumber(stats.orders.total)
        );

        setText(
            'dashboardPendingOrders',
            formatNumber(stats.orders.pending)
        );

        setText(
            'dashboardConfirmedOrders',
            formatNumber(stats.orders.confirmed)
        );

        setText(
            'dashboardCompletedOrders',
            formatNumber(stats.orders.completed)
        );

        setText(
            'dashboardCancelledOrders',
            formatNumber(stats.orders.cancelled)
        );

        setText(
            'dashboardTodayRevenue',
            formatCurrency(stats.revenue.today)
        );

        setText(
            'dashboardMonthRevenue',
            formatCurrency(stats.revenue.month)
        );

        setText(
            'dashboardTotalRevenue',
            formatCurrency(stats.revenue.total)
        );

        setText(
            'dashboardTotalProducts',
            formatNumber(stats.products.total)
        );

        setText(
            'dashboardActiveProducts',
            formatNumber(stats.products.active)
        );

        setText(
            'dashboardInactiveProducts',
            formatNumber(stats.products.inactive)
        );

    } catch (error) {

        console.error(
            'load dashboard stats error:',
            error
        );

    }
}

document.addEventListener(
    'DOMContentLoaded',
    () => {

        if (
            document.querySelector(
                '.admin-dashboard-grid'
            )
        ) {
            loadAdminDashboardStats();
        }

    }
);

let adminProductsCache = [];
let adminCategoriesCache = [];

function populateAdminCategorySelect(
    selectElement,
    placeholder = '-- Chọn danh mục --'
) {
    if (!selectElement) {
        return;
    }

    selectElement.innerHTML = '';

    const placeholderOption = document.createElement('option');
    placeholderOption.value = '';
    placeholderOption.textContent = placeholder;

    selectElement.append(placeholderOption);

    adminCategoriesCache.forEach(category => {
        const option = document.createElement('option');

        option.value = String(category.id);
        option.textContent = category.name;

        selectElement.append(option);
    });
}

async function loadAdminProducts() {

    const tableWrapper =
        document.getElementById(
            'adminProductsTableWrapper'
        );

    const productCount =
        document.getElementById(
            'adminProductCount'
        );

    if (!tableWrapper) {
        return;
    }

    tableWrapper.innerHTML = `
        <div class="admin-loading">
            ĐANG TẢI SẢN PHẨM...
        </div>
    `;

    try {

        const response = await fetch(
            'api/admin-products.php'
        );

        const result =
            await response.json();

        if (
            !response.ok ||
            !result.success
        ) {
            throw new Error(
                result.message ||
                'Không thể tải sản phẩm.'
            );
        }

        adminProductsCache = result.data || [];
        adminCategoriesCache = Array.isArray(result.categories)
            ? result.categories
            : [];

        const products =
            Array.isArray(result.data)
                ? result.data
                : [];

        if (productCount) {

            productCount.textContent =
                products.length;
        }

        if (products.length === 0) {

            tableWrapper.innerHTML = `
                <div class="admin-empty">
                    CHƯA CÓ SẢN PHẨM
                </div>
            `;

            return;
        }

        tableWrapper.innerHTML = `
            <table class="admin-products-table">
            
                <thead>
                
                    <tr>
                        <th>Ảnh</th>
                        <th>SẢN PHẨM</th>
                        <th>DANH MỤC</th>
                        <th>GIÁ</th>
                        <th>SIZE</th>
                        <th>TRẠNG THÁI</th>
                        <th>NHÃN</th>
                        <th>THAO TÁC</th>
                    </tr>

                </thead>

                <tbody>

                    ${products.map(product => {

                        const variants =
                            Array.isArray(product.variants)
                                ? product.variants
                                : [];

                        const activeVariants =
                            variants.filter(
                                variant =>
                                    Number(
                                        variant.is_active
                                    ) === 1
                            );

                        const variantHtml =
                            activeVariants.length > 0
                                ? `
                                    <div class="admin-product-variants">
                                    
                                        ${
                                            activeVariants
                                                .map(variant => `
                                                    <span class="admin-product-variant">
                                                    
                                                        <span>
                                                            ${escapeHtml(
                                                                variant.name
                                                            )}
                                                        </span>
                                                        
                                                        <strong>
                                                            ${Number(
                                                                variant.price
                                                            ).toLocaleString(
                                                                'vi-VN'
                                                            )}đ
                                                        </strong>
                                                        
                                                    </span>
                                                `)
                                                .join('')
                                        }
                                        
                                    </div>
                                `
                                : '-';

                        const flags = [];

                        if (
                            Number(
                                product.is_best_seller
                            ) === 1
                        ) {
                            flags.push(
                                'BEST SELLER'
                            );
                        }

                        if (
                            Number(
                                product.is_new
                            ) === 1
                        ) {
                            flags.push(
                                'MỚI'
                            );
                        }

                        if (
                            Number(
                                product.is_seasonal
                            ) === 1
                        ) {
                            flags.push(
                                'SẢN PHẨM MÙA'
                            );
                        }

                        const flagsHtml =
                            flags.length > 0
                                ? `
                                    <div class="admin-product-flags">
                                    
                                        ${
                                            flags
                                                .map(flag => `
                                                    <span class="admin-product-flag">
                                                        ${escapeHtml(flag)}
                                                    </span>
                                                `)
                                                .join('')
                                        }
                                        
                                    </div>
                                `
                                : '-';

                        const imageHtml =
                            product.image
                                ? `
                                    <div class="admin-product-image">
                                    
                                        <img
                                            src="${escapeHtml(
                                                product.image
                                            )}"
                                            alt="${escapeHtml(
                                                product.name
                                            )}"
                                            loading="lazy"
                                        >
                                        
                                    </div>
                                `
                                : `
                                    <div class="admin-product-image">
                                    
                                        <div class="admin-product-image-placeholder">
                                            RỐP RẺNG
                                        </div>
                                        
                                    </div>
                                `;

                        const statusHtml =
                            Number(
                                product.is_active
                            ) === 1
                                ? `
                                    <span class="admin-product-status admin-product-status-active">
                                        ĐANG BÁN
                                    </span>
                                `
                                : `
                                    <span class="admin-product-status admin-product-status-inactive">
                                        NGỪNG BÁN
                                    </span>
                                `;

                        const nextActive =
                            Number(product.is_active) === 1 ? 0 : 1;

                        const toggleButtonHtml =
                            nextActive === 0
                                ? `
                                    <button
                                        type="button"
                                        class="admin-product-toggle admin-product-toggle-hide"
                                        data-product-id="${escapeHtml(product.id)}"
                                        data-next-active="0"
                                    >
                                        ẨN
                                    </button>
                                `
                                : `
                                    <button
                                        type="button"
                                        class="admin-product-toggle admin-product-toggle-show"
                                        data-product-id="${escapeHtml(product.id)}"
                                        data-next-active="1"
                                    >
                                        HIỆN
                                    </button>
                                `;

                        return `
                            <tr>
                            
                                <td>
                                    ${imageHtml}
                                </td>
                                
                                <td>
                                
                                    <div class="admin-product-name">
                                        ${escapeHtml(
                                            product.name
                                        )}
                                    </div>
                                    
                                </td>
                                
                                <td>
                                
                                    <span class="admin-product-category">
                                        ${escapeHtml(
                                            product.category_name
                                        )}
                                    </span>
                                    
                                </td>
                                
                                <td>
                                
                                    <span class="admin-product-price">
                                        ${Number(
                                            product.price
                                        ).toLocaleString(
                                            'vi-VN'
                                        )}đ
                                    </span>
                                    
                                </td>
                                
                                <td>
                                    ${variantHtml}
                                </td>
                                
                                <td>
                                    ${statusHtml}
                                </td>
                                
                                <td>
                                    ${flagsHtml}
                                </td>

                                <td>
                                    <div class="admin-product-actions">
                                        <button
                                            type="button"
                                            class="admin-product-edit"
                                            data-product-id="${escapeHtml(product.id)}"
                                        >
                                            SỬA
                                        </button>
                                        ${toggleButtonHtml}
                                    </div>
                                </td>
                                
                            </tr>
                        `;

                    }).join('')}

                                        </tbody>

                                    </table>
                                `;
                        
    } catch (error) {

        console.error(
            'Load admin products error:',
            error
        );

        tableWrapper.innerHTML = `
            <div class="admin-error">
                KHÔNG THỂ TẢI DANH SÁCH SẢN PHẨM
            </div>
        `;

    }
}

function openAdminProductEditModal(product) {
    const modal = document.getElementById(
        'adminProductEditModal'
    );

    const idInput = document.getElementById(
        'adminEditProductId'
    );

    const nameInput = document.getElementById(
        'adminEditProductName'
    );

    const priceInput = document.getElementById(
        'adminEditProductPrice'
    );

    const variantTypeInput = document.getElementById(
        'adminEditVariantType'
    );

    const priceMInput = document.getElementById(
        'adminEditPriceM'
    );

    const priceLInput = document.getElementById(
        'adminEditPriceL'
    );

    const typeInput = document.getElementById(
        'adminEditProductType'
    );

    const activeInput = document.getElementById(
        'adminEditProductActive'
    );

    if (
        !modal ||
        !idInput ||
        !nameInput ||
        !priceInput ||
        !variantTypeInput ||
        !priceMInput ||
        !priceLInput ||
        !typeInput ||
        !activeInput
    ) {
        console.error(
            'Không tìm thấy Product Edit Modal hoặc các trường Variant.'
        );
        return;
    }

    idInput.value = product.id ?? '';
    nameInput.value = product.name ?? '';

    priceInput.value = product.price ?? '';
    priceMInput.value = '';
    priceLInput.value = '';

    const activeVariants = Array.isArray(product.variants)
        ? product.variants.filter(
            variant => Number(variant.is_active) === 1
        )
        : [];

    const defaultVariant = activeVariants.find(
        variant => variant.name === 'Default'
    );

    const mVariant = activeVariants.find(
        variant => variant.name === 'M'
    );

    const lVariant = activeVariants.find(
        variant => variant.name === 'L'
    );

    if (
        activeVariants.length === 1 &&
        defaultVariant
    ) {
        variantTypeInput.value = 'default'
        priceInput.value = defaultVariant.price;
    } else if (
        activeVariants.length === 2 &&
        mVariant &&
        lVariant
    ) {
        variantTypeInput.value = 'size'

        priceMInput.value = mVariant.price;
        priceLInput.value = lVariant.price;
    } else {
        console.error(
            'Cấu trúc variant không hợp lệ:',
            product
        );
        alert(
            'Sản phẩm có nhóm phân loại không hợp lệ. Vui lòng kiểm tra dữ liệu.'
        );
        return;
    }

    syncAdminProductEditVariant();

    activeInput.value =
        Number(product.is_active) === 1
            ? '1'
            : '0';

    const isSeasonal =
        Number(product.is_seasonal) === 1;

    typeInput.value = isSeasonal
        ? 'season'
        : 'drink';

    syncAdminProductEditType();

    const categoryInput = document.getElementById(
        'adminEditProductCategory'
    );

    const seasonStartInput = document.getElementById(
        'adminEditSeasonStart'
    );

    const seasonEndInput = document.getElementById(
        'adminEditSeasonEnd'
    );

    if (categoryInput) {
        populateAdminCategorySelect(categoryInput);
        categoryInput.value =
            product.category_id !== null &&
            product.category_id !== undefined
                ? String(product.category_id)
                : '';
    }

    if (seasonStartInput) {
        seasonStartInput.value =
            product.season_start ?? '';
    }

    if (seasonEndInput) {
        seasonEndInput.value =
            product.season_end ?? '';
    }

    modal.hidden = false;
}

function syncAdminProductEditType() {
    const typeInput = document.getElementById(
        'adminEditProductType'
    );
    const categoryGroup = document.getElementById(
        'adminEditCategoryGroup'
    );
    const seasonStartGroup = document.getElementById(
        'adminEditSeasonStartGroup'
    );
    const seasonEndGroup = document.getElementById(
        'adminEditSeasonEndGroup'
    );

    if (
        !typeInput ||
        !categoryGroup ||
        !seasonStartGroup ||
        !seasonEndGroup
    ) {
        return;
    }

    const isSeason =
        typeInput.value === 'season';

    categoryGroup.hidden = isSeason;
    seasonStartGroup.hidden = !isSeason;
    seasonEndGroup.hidden = !isSeason;
}

function syncAdminProductEditVariant() {
    const variantTypeInput = document.getElementById(
        'adminEditVariantType'
    );

    const defaultGroup = document.getElementById(
        'adminEditDefaultPriceGroup'
    );

    const priceMGroup = document.getElementById(
        'adminEditPriceMGroup'
    );

    const priceLGroup = document.getElementById(
        'adminEditPriceLGroup'
    );

    const defaultPriceInput = document.getElementById(
        'adminEditProductPrice'
    );

    const priceMInput = document.getElementById(
        'adminEditPriceM'
    );

    const priceLInput = document.getElementById(
        'adminEditPriceL'
    );
    if (
        !variantTypeInput ||
        !defaultGroup ||
        !priceMGroup ||
        !priceLGroup ||
        !defaultPriceInput ||
        !priceMInput ||
        !priceLInput
    ) {
        return;
    }
    const isSize = variantTypeInput.value === 'size';

    defaultGroup.hidden = isSize;
    priceMGroup.hidden = !isSize;
    priceLGroup.hidden = !isSize;

    defaultPriceInput.required = !isSize;
    priceMInput.required = isSize;
    priceLInput.required = isSize;
}

function syncAdminProductCreateType() {
    const typeInput = document.getElementById(
        'adminCreateProductType'
    );

    const categoryGroup = document.getElementById(
        'adminCreateCategoryGroup'
    );

    const categoryInput = document.getElementById(
        'adminCreateProductCategory'
    );

    const startGroup = document.getElementById(
        'adminCreateSeasonStartGroup'
    );

    const endGroup = document.getElementById(
        'adminCreateSeasonEndGroup'
    );

    const startInput = document.getElementById(
        'adminCreateSeasonStart'
    );

    const endInput = document.getElementById(
        'adminCreateSeasonEnd'
    );

    if (
        !typeInput ||
        !categoryGroup ||
        !categoryInput ||
        !startGroup ||
        !endGroup ||
        !startInput ||
        !endInput
    ) {
        return;
    }

    const isSeason = typeInput.value === '1';

    categoryGroup.hidden = isSeason;
    startGroup.hidden = !isSeason;
    endGroup.hidden = !isSeason;

    categoryInput.required = !isSeason;
    categoryInput.disabled = isSeason;

    startInput.required = isSeason;
    endInput.required = isSeason;

    startInput.disabled = !isSeason;
    endInput.disabled = !isSeason;
}

function syncAdminProductCreateVariant() {
    const variantInput = document.getElementById(
        'adminCreateVariantType'
    );

    const defaultGroup = document.getElementById(
        'adminCreateDefaultPriceGroup'
    );

    const priceMGroup = document.getElementById(
        'adminCreatePriceMGroup'
    );

    const priceLGroup = document.getElementById(
        'adminCreatePriceLGroup'
    );

    const defaultInput = document.getElementById(
        'adminCreatePrice'
    );

    const priceMInput = document.getElementById(
        'adminCreatePriceM'
    );

    const priceLInput = document.getElementById(
        'adminCreatePriceL'
    );

    if (
        !variantInput ||
        !defaultGroup ||
        !priceMGroup ||
        !priceLGroup ||
        !defaultInput ||
        !priceMInput ||
        !priceLInput
    ) {
        return;
    }

    const isSize = variantInput.value === 'size';

    defaultGroup.hidden = isSize;
    priceMGroup.hidden = !isSize;
    priceLGroup.hidden = !isSize;

    defaultInput.required = !isSize;
    priceMInput.required = isSize;
    priceLInput.required = isSize;

    defaultInput.disabled = isSize;
    priceMInput.disabled = !isSize;
    priceLInput.disabled = !isSize;
}

document.addEventListener('DOMContentLoaded', () => {
    const typeInput = document.getElementById(
        'adminEditProductType'
    );

    const variantTypeInput = document.getElementById(
        'adminEditVariantType'
    );

    if (variantTypeInput) {
        variantTypeInput.addEventListener(
            'change',
            syncAdminProductEditVariant
        );
    }

    const closeButton = document.getElementById(
        'adminProductEditClose'
    );

    const cancelButton = document.getElementById(
        'adminProductEditCancel'
    );

    const modal = document.getElementById(
        'adminProductEditModal'
    );

    const overlay = document.querySelector(
        '[data-close-product-modal]'
    );

    if (typeInput) {
        typeInput.addEventListener(
            'change',
            syncAdminProductEditType
        );
    }

    const closeModal = () => {
        if (modal) {
            modal.hidden = true;
        }
    };

    if (closeButton) {
        closeButton.addEventListener(
            'click',
            closeModal
        );
    }

    if (cancelButton) {
        cancelButton.addEventListener(
            'click',
            closeModal
        );
    }

    if (overlay) {
        overlay.addEventListener(
            'click',
            closeModal
        );
    }

    const editForm = document.getElementById(
        'adminProductEditForm'
    );

    if (editForm) {
        editForm.addEventListener(
            'submit',
            async event => {
                event.preventDefault();

                const idInput = document.getElementById(
                    'adminEditProductId'
                );

                const nameInput = document.getElementById(
                    'adminEditProductName'
                );

                const priceInput = document.getElementById(
                    'adminEditProductPrice'
                );

                const variantTypeInput = document.getElementById(
                    'adminEditVariantType'
                );

                const priceMInput = document.getElementById(
                    'adminEditPriceM'
                );

                const priceLInput = document.getElementById(
                    'adminEditPriceL'
                );

                const typeInput = document.getElementById(
                    'adminEditProductType'
                );

                const categoryInput = document.getElementById(
                    'adminEditProductCategory'
                );

                const seasonStartInput = document.getElementById(
                    'adminEditSeasonStart'
                );

                const seasonEndInput = document.getElementById(
                    'adminEditSeasonEnd'
                );

                const activeInput = document.getElementById(
                    'adminEditProductActive'
                );

                const submitButton =
                    editForm.querySelector(
                        'button[type="submit"]'
                    );

                const isSeason = typeInput.value === 'season';
                const isSize = variantTypeInput.value === 'size';

                const price = isSize
                    ? Number(priceMInput.value)
                    : Number(priceInput.value);

                const payload = {
                    id: Number(idInput.value),
                    name: nameInput.value.trim(),

                    price: price,
                    variant_type: isSize ? 'size' : 'default',

                    ...(isSize
                        ? {
                            price_m: Number(priceMInput.value),
                            price_l: Number(priceLInput.value)
                        }
                        : {}
                    ),

                    is_active: Number(activeInput.value),
                    is_seasonal: isSeason ? 1 : 0,

                    category_id: isSeason
                        ? null
                        : (
                            categoryInput.value
                                ? Number(categoryInput.value)
                                : null
                        ),

                    season_start: isSeason
                        ? (seasonStartInput.value || null)
                        : null,

                    season_end: isSeason
                        ? (seasonEndInput.value || null)
                        : null
                };

                const validPrices = isSize
                    ? (
                        Number.isFinite(payload.price_m) &&
                        Number.isFinite(payload.price_l) &&
                        payload.price_m > 0 &&
                        payload.price_l > 0
                    )
                    : (
                        Number.isFinite(payload.price) &&
                        payload.price > 0
                    );

                if (
                    !payload.id ||
                    !payload.name ||
                    !validPrices
                ) {
                    alert(
                        'Vui lòng kiểm tra tên sản phẩm và giá biến thể.'
                    );
                    return;
                }
                try {
                    if (submitButton) {
                        submitButton.disabled = true;
                        submitButton.textContent =
                            'ĐANG LƯU...'
                    }
                    const response = await fetch(
                        'api/admin-update-product.php',
                        {
                            method: 'POST',
                            headers: {
                                'Content-Type':
                                    'application/json'
                            },
                            body: JSON.stringify(payload)
                        }
                    );
                    const result =
                        await response.json();
                    if (!response.ok || !result.success) {
                        throw new Error(
                            result.message ||
                            'Không thể cập nhật sản phẩm.'
                        );
                    }
                    alert(
                        'Cập nhật sản phẩm thành công.'
                    );
                    closeModal();
                    await loadAdminProducts();
                } catch (error) {
                    console.error(
                        'Update product error:',
                        error
                    );
                    alert(
                        error.message ||
                        'Có lỗi xảy ra khi cập nhật.'
                    );
                } finally {
                    if (submitButton) {
                        submitButton.disabled = false;
                        submitButton.textContent =
                            'LƯU';
                    }
                }
            }
        );
    }
});

document.addEventListener(
    'click',
    event => {

        const refreshButton =
            event.target.closest(
                '#adminProductRefresh'
            );

        if (!refreshButton) {
            return;
        }

        loadAdminProducts();

    }
);

document.addEventListener('click', event => {
    const button = event.target.closest(
        '.admin-product-edit'
    );
    if (!button) {
        return;
    }

    const productId = button.dataset.productId;
    if (!productId) {
        return;
    }

    const row = button.closest('tr');
    if (!row) {
        return;
    }

    const product = adminProductsCache.find(
        item => String(item.id) === String(productId)
    );
    if (!product) {
        console.error(
            'Không tìm thấy sản phẩm:',
            productId
        );
        return;
    }

    openAdminProductEditModal(product);
});

document.addEventListener('click', async event => {

    const button = event.target.closest(
        '.admin-product-toggle'
    );

    if (!button) {
        return;
    }

    const productId = Number(
        button.dataset.productId
    );

    const nextActive = Number(
        button.dataset.nextActive
    );

    if (
        !Number.isInteger(productId) ||
        productId <= 0 ||
        ![0, 1].includes(nextActive)
    ) {
        return;
    }

    const product = adminProductsCache.find(
        item => Number(item.id) === productId
    );

    const productName = product
        ? product.name
        : `#${productId}`;

    const action = nextActive === 0
        ? 'ẩn'
        : 'hiện';

    const confirmed = window.confirm(
        `Bạn có chắc muốn ${action} sản phẩm "${productName}"?`
    );

    if (!confirmed) {
        return;
    }

    const originalText = button.textContent;

    button.disabled = true;
    button.textContent = 'ĐANG CẬP NHẬT...';

    try {

        const response = await fetch(
            'api/admin-toggle-product.php',
            {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id: productId,
                    is_active: nextActive
                })
            }
        );

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(
                result.message ||
                'Không thể cập nhật trạng thái sản phẩm.'
            );
        }

        await loadAdminProducts();

    } catch (error) {

        console.error(
            'Toggle product status error:',
            error
        );

        alert(
            error.message ||
            'Có lỗi xảy ra khi cập nhật trạng thái.'
        );

        button.disabled = false;
        button.textContent = originalText;

    }

});

document.addEventListener(
    'DOMContentLoaded',
    () => {

        if (
            document.getElementById(
                'adminProductsTableWrapper'
            )
        ) {
            loadAdminProducts();
        }

    }
);

document.addEventListener('DOMContentLoaded', () => {
    const openButton = document.getElementById(
        'adminProductCreateOpen'
    );

    const modal = document.getElementById(
        'adminProductCreateModal'
    );

    const form = document.getElementById(
        'adminProductCreateForm'
    );

    const closeButton = document.getElementById(
        'adminProductCreateClose'
    );

    const cancelButton = document.getElementById(
        'adminProductCreateCancel'
    );

    const overlay = document.querySelector(
        '[data-close-create-product-modal]'
    );

    const typeInput = document.getElementById(
        'adminCreateProductType'
    );

    const variantInput = document.getElementById(
        'adminCreateVariantType'
    );

    const categoryInput = document.getElementById(
        'adminCreateProductCategory'
    );
    if (
        !openButton ||
        !modal ||
        !form ||
        !typeInput ||
        !variantInput ||
        !categoryInput
    ) {
        return;
    }
    const closeModal = () => {
        modal.hidden = true;
    };
    const loadCategories = async () => {
        const response = await fetch(
            'api/admin-products.php'
        );
        const result = await response.json();
        if (!response.ok || !result.success) {
            throw new Error(
                result.message || 'Không thể tải danh mục.'
            );
        }
        if (!Array.isArray(result.categories)) {
            throw new Error(
                'API chưa trả về danh sách Category.'
            );
        }
        adminCategoriesCache = result.categories;
        populateAdminCategorySelect(categoryInput);
    };
    openButton.addEventListener('click', async () => {
        try {
            form.reset();

            syncAdminProductCreateType();
            syncAdminProductCreateVariant();

            openButton.disabled = true;

            await loadCategories();

            modal.hidden = false;
        } catch (error) {
            console.error(
                'Load create product categories error:',
                error
            );
            alert(
                error.message ||
                'Không thể mở form tạo sản phẩm.'
            );
        } finally {
            openButton.disabled = false;
        }
    });

    if (closeButton) {
        closeButton.addEventListener(
            'click',
            closeModal
        );
    }
    if (cancelButton) {
        cancelButton.addEventListener(
            'click',
            closeModal
        );
    }
    if (overlay) {
        overlay.addEventListener(
            'click',
            closeModal
        );
    }
    typeInput.addEventListener(
        'change',
        syncAdminProductCreateType
    );
    variantInput.addEventListener(
        'change',
        syncAdminProductCreateVariant
    );

    syncAdminProductCreateType();
    syncAdminProductCreateVariant();
});

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById(
        'adminProductCreateForm'
    );
    if (!form) {
        return;
    }
    const submitButton = form.querySelector(
        'button[type="submit"]'
    );
    const modal = document.getElementById(
        'adminProductCreateModal'
    );
    const imageInput = document.getElementById(
        'adminCreateProductImage'
    );
    form.addEventListener('submit', async event => {
        event.preventDefault();

        const typeInput = document.getElementById(
            'adminCreateProductType'
        );

        const variantInput = document.getElementById(
            'adminCreateVariantType'
        );

        const categoryInput = document.getElementById(
            'adminCreateProductCategory'
        );

        const startInput = document.getElementById(
            'adminCreateSeasonStart'
        );

        const endInput = document.getElementById(
            'adminCreateSeasonEnd'
        );

        const isSeason = typeInput.value === '1';
        const isSize = variantInput.value === 'size';

        if (
            isSeason &&
            startInput.value > endInput.value
        ) {
            alert(
                'Ngày bắt đầu phải trước hoặc bằng ngày kết thúc.'
            );
            return;
        }
        const imageFile = imageInput.files[0];
        if (imageFile) {
            const allowedTypes = [
                'image/jpeg',
                'image/png',
                'image/webp'
            ];
            if (!allowedTypes.includes(imageFile.type)) {
                alert(
                    'Chỉ hỗ trợ ảnh JPG, PNG hoặc WEBP.'
                );
                return;
            }
            if (imageFile.size > 5 * 1024 * 1024) {
                alert(
                    'Ảnh không được vượt quá 5MB.'
                );
                return;
            }
        }
        const formData = new FormData(form);
        formData.set(
            'is_seasonal',
            isSeason ? '1' : '0'
        );
        try {
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.textContent = 'ĐANG TẠO...';
            }
            const response = await fetch(
                'api/admin-create-product.php',
                {
                    method: 'POST',
                    body: formData
                }
            );
            const result = await response.json();
            if (!response.ok || !result.success) {
                throw new Error(
                    result.message ||
                    'Không thể tạo sản phẩm.'
                );
            }
            alert(
                `Tạo sản phẩm thành công. Mã sản phẩm: ${result.data?.id ?? ''}`
            );
            form.reset();

            syncAdminProductCreateType();
            syncAdminProductCreateVariant();

            if (modal) {
                modal.hidden = true;
            }
            await loadAdminProducts();
        } catch (error) {
            console.error(
                'Create product error:',
                error
            );
            alert(
                error.message ||
                'Có lỗi xảy ra khi tạo sản phẩm.'
            );
        } finally {
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.textContent = 'TẠO SẢN PHẨM'
            }
        }
    });
});