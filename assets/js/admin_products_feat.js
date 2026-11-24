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

        const productItems =
            Array.isArray(result.data)
                ? result.data
                : [];

        const toppingItems =
            Array.isArray(result.toppings)
                ? result.toppings
                : [];

        const products = [
            ...productItems,
            ...toppingItems
        ];

        adminProductsCache = products;

        adminCategoriesCache = Array.isArray(result.categories)
            ? result.categories
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

    const bestSellerInput = document.getElementById(
        'adminEditProductBestSeller'
    );

    const isNewInput = document.getElementById(
        'adminEditProductIsNew'
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
        !activeInput ||
        !bestSellerInput ||
        !isNewInput
    ) {
        console.error(
            'Không tìm thấy Product Edit Modal.'
        );
        return;
    }

    const isTopping =
        product.is_topping === 1 ||
        product.is_topping === '1';

    idInput.value = product.id ?? '';
    nameInput.value = product.name ?? '';

    priceInput.value = product.price ?? '';
    priceMInput.value = '';
    priceLInput.value = '';

    /*
     * TOPPING
     */
    if (isTopping) {

        typeInput.value = 'topping';

        variantTypeInput.value = 'default';

        variantTypeInput.disabled = true;

        priceInput.value = product.price ?? '';

        priceMInput.value = '';
        priceLInput.value = '';

        activeInput.value =
            Number(product.is_active) === 1
                ? '1'
                : '0';

        bestSellerInput.checked = false;
        isNewInput.checked = false;

        syncAdminProductEditType();
        syncAdminProductEditVariant();

        modal.hidden = false;
        modal.classList.add('is-open');

        return;
    }

    /*
     * DRINK / SEASON
     */

    variantTypeInput.disabled = false;

    const activeVariants =
        Array.isArray(product.variants)
            ? product.variants.filter(
                variant =>
                    Number(variant.is_active) === 1
            )
            : [];

    const defaultVariant =
        activeVariants.find(
            variant => variant.name === 'Default'
        );

    const mVariant =
        activeVariants.find(
            variant => variant.name === 'M'
        );

    const lVariant =
        activeVariants.find(
            variant => variant.name === 'L'
        );

    if (
        activeVariants.length === 1 &&
        defaultVariant
    ) {

        variantTypeInput.value = 'default';

        priceInput.value =
            defaultVariant.price;

    } else if (
        activeVariants.length === 2 &&
        mVariant &&
        lVariant
    ) {

        variantTypeInput.value = 'size';

        priceMInput.value =
            mVariant.price;

        priceLInput.value =
            lVariant.price;

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

    activeInput.value =
        Number(product.is_active) === 1
            ? '1'
            : '0';

    bestSellerInput.checked =
        Number(product.is_best_seller) === 1;

    isNewInput.checked =
        Number(product.is_new) === 1;

    const isSeasonal =
        Number(product.is_seasonal) === 1;

    typeInput.value =
        isSeasonal
            ? 'season'
            : 'drink';

    syncAdminProductEditType();
    syncAdminProductEditVariant();

    const categoryInput =
        document.getElementById(
            'adminEditProductCategory'
        );

    const seasonStartInput =
        document.getElementById(
            'adminEditSeasonStart'
        );

    const seasonEndInput =
        document.getElementById(
            'adminEditSeasonEnd'
        );

    if (categoryInput) {

        populateAdminCategorySelect(
            categoryInput
        );

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
    modal.classList.add('is-open');
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

    const variantGroup = document.getElementById(
        'adminEditVariantTypeGroup'
    );

    const variantInput = document.getElementById(
        'adminEditVariantType'
    );

    if (
        !typeInput ||
        !categoryGroup ||
        !seasonStartGroup ||
        !seasonEndGroup ||
        !variantGroup ||
        !variantInput
    ) {
        return;
    }

    const isSeason =
        typeInput.value === 'season';

    const isTopping =
        typeInput.value === 'topping';

    categoryGroup.hidden =
        isSeason || isTopping;

    seasonStartGroup.hidden =
        !isSeason;

    seasonEndGroup.hidden =
        !isSeason;

    variantGroup.hidden =
        isTopping;

    variantInput.disabled =
        isTopping;

    if (isTopping) {
        variantInput.value = 'default';
    }
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

    if (variantTypeInput.disabled) {

        defaultGroup.hidden = false;
        priceMGroup.hidden = true;
        priceLGroup.hidden = true;

        defaultPriceInput.required = true;
        priceMInput.required = false;
        priceLInput.required = false;

        return;
    }

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

    const variantGroup = document.getElementById(
        'adminCreateVariantTypeGroup'
    );

    const defaultPriceGroup = document.getElementById(
        'adminCreateDefaultPriceGroup'
    );

    const priceMGroup = document.getElementById(
        'adminCreatePriceMGroup'
    );

    const priceLGroup = document.getElementById(
        'adminCreatePriceLGroup'
    );

    const variantInput = document.getElementById(
        'adminCreateVariantType'
    );

    const priceInput = document.getElementById(
        'adminCreatePrice'
    );

    const priceMInput = document.getElementById(
        'adminCreatePriceM'
    );

    const priceLInput = document.getElementById(
        'adminCreatePriceL'
    );

    const flagsGroup = document.getElementById(
        'adminCreateFlagsGroup'
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

    const type = typeInput.value;

    const isDrink = type === 'drink';
    const isSeason = type === 'season';
    const isTopping = type === 'topping';

    categoryGroup.hidden = !isDrink;

    categoryInput.required = isDrink;
    categoryInput.disabled = !isDrink;

    if (!isDrink) {
        categoryInput.value = '';
    }

    startGroup.hidden = !isSeason;
    endGroup.hidden = !isSeason;

    startInput.required = isSeason;
    endInput.required = isSeason;

    startInput.disabled = !isSeason;
    endInput.disabled = !isSeason;

    if (!isSeason) {
        startInput.value = '';
        endInput.value = '';
    }

    if (variantGroup) {
        variantGroup.hidden = isTopping;
    }

    if (variantInput) {
        variantInput.disabled = isTopping;

        if (isTopping) {
            variantInput.value = 'default';
        }
    }

    if (isTopping) {

        // Topping chỉ có 1 giá
        defaultPriceGroup.hidden = false;

        priceMGroup.hidden = true;
        priceLGroup.hidden = true;

        priceInput.required = true;
        priceInput.disabled = false;

        priceMInput.required = false;
        priceMInput.disabled = true;

        priceLInput.required = false;
        priceLInput.disabled = true;

    } else {

        syncAdminProductCreateVariant();
    }

    if (flagsGroup) {
        flagsGroup.hidden = isTopping;

        flagsGroup
            .querySelectorAll(
                'input[type="checkbox"]'
            )
            .forEach(input => {

                input.disabled = isTopping;

                if (isTopping) {
                    input.checked = false;
                }

            });
    }
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
            modal.classList.remove('is-open');
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

                const bestSellerInput = document.getElementById(
                    'adminEditProductBestSeller'
                );

                const isNewInput = document.getElementById(
                    'adminEditProductIsNew'
                );

                const submitButton =
                    editForm.querySelector(
                        'button[type="submit"]'
                    );

                const isSeason =
                    typeInput.value === 'season';

                const isTopping =
                    typeInput.value === 'topping';

                const isSize =
                    variantTypeInput.value === 'size';

                if (isTopping) {

                    const currentItem =
                        adminProductsCache.find(
                            item =>
                                String(item.id) ===
                                String(idInput.value)
                        );

                    if (!currentItem) {

                        alert(
                            'Không tìm thấy topping hiện tại.'
                        );

                        return;
                    }

                    const rawId =
                        String(idInput.value)
                            .replace('topping-', '');

                    const toppingId =
                        Number(rawId);

                    const toppingPrice =
                        Number(priceInput.value);

                    if (
                        !Number.isInteger(toppingId) ||
                        toppingId <= 0 ||
                        !nameInput.value.trim() ||
                        !Number.isFinite(toppingPrice) ||
                        toppingPrice <= 0
                    ) {
                        alert(
                            'Vui lòng kiểm tra tên và giá topping.'
                        );
                        return;
                    }

                    try {

                        if (submitButton) {
                            submitButton.disabled = true;
                            submitButton.textContent =
                                'ĐANG LƯU...';
                        }

                        const response =
                            await fetch(
                                'api/admin-toppings.php',
                                {
                                    method: 'PUT',
                                    headers: {
                                        'Content-Type':
                                            'application/json'
                                    },
                                    body: JSON.stringify({
                                        id: toppingId,
                                        name: nameInput.value.trim(),
                                        price: toppingPrice,

                                        description:
                                            currentItem.description || null,

                                        image:
                                            currentItem.image || null
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
                                'Không thể cập nhật topping.'
                            );
                        }

                        /*
                        * Cập nhật trạng thái nếu người dùng
                        * thay đổi Đang bán / Ngừng bán.
                        */
                        const currentProduct =
                            adminProductsCache.find(
                                item =>
                                    String(item.id) ===
                                    String(idInput.value)
                            );

                        const currentActive =
                            currentProduct
                                ? Number(
                                    currentProduct.is_active
                                )
                                : null;

                        const nextActive =
                            Number(activeInput.value);

                        if (
                            currentActive !== null &&
                            currentActive !== nextActive
                        ) {

                            const toggleResponse =
                                await fetch(
                                    'api/admin-toppings.php',
                                    {
                                        method: 'PATCH',
                                        headers: {
                                            'Content-Type':
                                                'application/json'
                                        },
                                        body: JSON.stringify({
                                            id: toppingId
                                        })
                                    }
                                );

                            const toggleResult =
                                await toggleResponse.json();

                            if (
                                !toggleResponse.ok ||
                                !toggleResult.success
                            ) {
                                throw new Error(
                                    toggleResult.message ||
                                    'Không thể cập nhật trạng thái topping.'
                                );
                            }
                        }

                        alert(
                            'Cập nhật topping thành công.'
                        );

                        closeModal();

                        await loadAdminProducts();

                    } catch (error) {

                        console.error(
                            'Update topping error:',
                            error
                        );

                        alert(
                            error.message ||
                            'Có lỗi xảy ra khi cập nhật topping.'
                        );

                    } finally {

                        if (submitButton) {
                            submitButton.disabled = false;
                            submitButton.textContent =
                                'LƯU';
                        }
                    }

                    return;
                }

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
                    is_best_seller: bestSellerInput.checked ? 1 : 0,
                    is_new: isNewInput.checked ? 1 : 0,
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

document.addEventListener(
    'click',
    async event => {

        const button = event.target.closest(
            '.admin-product-toggle'
        );

        if (!button) {
            return;
        }

        const rawId =
            String(button.dataset.productId || '');

        const nextActive =
            Number(button.dataset.nextActive);

        const isTopping =
            rawId.startsWith('topping-');

        const toppingId =
            isTopping
                ? Number(
                    rawId.replace(
                        'topping-',
                        ''
                    )
                )
                : null;

        const productId =
            isTopping
                ? null
                : Number(rawId);

        /*
         * Kiểm tra ID
         */
        if (isTopping) {

            if (
                !Number.isInteger(toppingId) ||
                toppingId <= 0
            ) {
                console.error(
                    'Topping ID không hợp lệ:',
                    rawId
                );
                return;
            }

        } else {

            if (
                !Number.isInteger(productId) ||
                productId <= 0
            ) {
                console.error(
                    'Product ID không hợp lệ:',
                    rawId
                );
                return;
            }

        }

        if (![0, 1].includes(nextActive)) {
            return;
        }

        /*
         * Tìm item trong cache
         */
        const item =
            adminProductsCache.find(
                currentItem =>
                    String(currentItem.id) ===
                    rawId
            );

        const itemName =
            item
                ? item.name
                : (
                    isTopping
                        ? `#${toppingId}`
                        : `#${productId}`
                );

        const action =
            nextActive === 0
                ? 'ẩn'
                : 'hiện';

        const confirmed =
            window.confirm(
                `Bạn có chắc muốn ${action} ${
                    isTopping
                        ? 'topping'
                        : 'sản phẩm'
                } "${itemName}"?`
            );

        if (!confirmed) {
            return;
        }

        const originalText =
            button.textContent;

        button.disabled = true;
        button.textContent =
            'ĐANG CẬP NHẬT...';

        try {

            let response;

            /*
             * TOPPING
             * → dùng PATCH admin-toppings.php
             */
            if (isTopping) {

                response = await fetch(
                    'api/admin-toppings.php',
                    {
                        method: 'PATCH',
                        headers: {
                            'Content-Type':
                                'application/json'
                        },
                        body: JSON.stringify({
                            id: toppingId
                        })
                    }
                );

            /*
             * DRINK / SEASON
             * → dùng API Product hiện tại
             */
            } else {

                response = await fetch(
                    'api/admin-toggle-product.php',
                    {
                        method: 'POST',
                        headers: {
                            'Content-Type':
                                'application/json'
                        },
                        body: JSON.stringify({
                            id: productId,
                            is_active:
                                nextActive
                        })
                    }
                );
            }

            const result =
                await response.json();

            if (
                !response.ok ||
                !result.success
            ) {
                throw new Error(
                    result.message ||
                    `Không thể ${action} ${
                        isTopping
                            ? 'topping'
                            : 'sản phẩm'
                    }.`
                );
            }

            await loadAdminProducts();

        } catch (error) {

            console.error(
                'Toggle item error:',
                error
            );

            alert(
                error.message ||
                'Có lỗi xảy ra khi cập nhật trạng thái.'
            );

            button.disabled = false;
            button.textContent =
                originalText;
        }

    }
);

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
        modal.classList.remove('is-open');
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
            modal.classList.add('is-open');
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

        const isSeason = typeInput.value === 'season';
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
                modal.classList.remove('is-open');
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