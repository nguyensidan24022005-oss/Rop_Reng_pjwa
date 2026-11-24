let adminToppingsCache = [];

function escapeAdminToppingHtml(value) {

    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');

}

function formatAdminToppingPrice(price) {

    return Number(price || 0)
        .toLocaleString('vi-VN') + ' VNĐ';

}

async function loadAdminToppings() {

    const tableWrapper =
        document.getElementById(
            'adminToppingsTableWrapper'
        );

    const toppingCount =
        document.getElementById(
            'adminToppingCount'
        );

    if (!tableWrapper) {
        return;
    }

    tableWrapper.innerHTML = `
        <div class="admin-loading">
            ĐANG TẢI TOPPING...
        </div>
    `;

    try {

        const response = await fetch(
            'api/admin-toppings.php'
        );

        const result =
            await response.json();

        if (
            !response.ok ||
            !result.success
        ) {
            throw new Error(
                result.message ||
                'Không thể tải topping.'
            );
        }

        adminToppingsCache =
            Array.isArray(result.data)
                ? result.data
                : [];

        const toppings =
            adminToppingsCache;

        if (toppingCount) {

            toppingCount.textContent =
                toppings.length;

        }

        if (toppings.length === 0) {

            tableWrapper.innerHTML = `
                <div class="admin-empty">
                    CHƯA CÓ TOPPING
                </div>
            `;

            return;
        }

        tableWrapper.innerHTML = `
            <table class="admin-products-table">

                <thead>

                    <tr>
                        <th>ẢNH</th>
                        <th>TOPPING</th>
                        <th>MÔ TẢ</th>
                        <th>GIÁ</th>
                        <th>TRẠNG THÁI</th>
                        <th>THAO TÁC</th>
                    </tr>

                </thead>

                <tbody>

                    ${toppings.map(topping => {

                        const image =
                            topping.image
                                ? `
                                    <img
                                        src="${escapeAdminToppingHtml(
                                            topping.image
                                        )}"
                                        alt="${escapeAdminToppingHtml(
                                            topping.name
                                        )}"
                                        class="admin-product-image"
                                    >
                                `
                                : `
                                    <span
                                        class="admin-product-image-placeholder"
                                    >
                                        —
                                    </span>
                                `;

                        const status =
                            Number(
                                topping.is_active
                            ) === 1;

                        return `
                            <tr>

                                <td>
                                    ${image}
                                </td>

                                <td>
                                    <strong>
                                        ${escapeAdminToppingHtml(
                                            topping.name
                                        )}
                                    </strong>
                                </td>

                                <td>
                                    ${
                                        topping.description
                                            ? escapeAdminToppingHtml(
                                                topping.description
                                            )
                                            : '—'
                                    }
                                </td>

                                <td>
                                    <strong>
                                        ${formatAdminToppingPrice(
                                            topping.price
                                        )}
                                    </strong>
                                </td>

                                <td>

                                    <span
                                        class="${
                                            status
                                                ? 'admin-status-active'
                                                : 'admin-status-inactive'
                                        }"
                                    >
                                        ${
                                            status
                                                ? 'ĐANG BÁN'
                                                : 'ĐANG ẨN'
                                        }
                                    </span>

                                </td>

                                <td>

                                    <div class="admin-product-actions">

                                        <button
                                            type="button"
                                            class="admin-product-edit-button"
                                            data-topping-edit="${topping.id}"
                                        >
                                            SỬA
                                        </button>

                                        <button
                                            type="button"
                                            class="admin-product-toggle-button"
                                            data-topping-toggle="${topping.id}"
                                        >
                                            ${
                                                status
                                                    ? 'ẨN'
                                                    : 'HIỆN'
                                            }
                                        </button>

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
            'Load toppings error:',
            error
        );

        tableWrapper.innerHTML = `
            <div class="admin-error">
                KHÔNG THỂ TẢI DANH SÁCH TOPPING
            </div>
        `;

    }

}

window.loadAdminToppings =
    loadAdminToppings;


/*
|--------------------------------------------------------------------------
| CREATE TOPPING
|--------------------------------------------------------------------------
*/

document.addEventListener(
    'DOMContentLoaded',
    () => {

        const openButton =
            document.getElementById(
                'adminToppingCreateOpen'
            );

        const modal =
            document.getElementById(
                'adminToppingCreateModal'
            );

        const form =
            document.getElementById(
                'adminToppingCreateForm'
            );

        const closeButton =
            document.getElementById(
                'adminToppingCreateClose'
            );

        const cancelButton =
            document.getElementById(
                'adminToppingCreateCancel'
            );

        const overlay =
            document.querySelector(
                '[data-close-topping-modal]'
            );

        if (
            !openButton ||
            !modal ||
            !form
        ) {
            return;
        }

        const closeModal = () => {

            modal.hidden = true;

            modal.classList.remove(
                'is-open'
            );

        };

        const openModal = () => {

            form.reset();

            modal.hidden = false;

            modal.classList.add(
                'is-open'
            );

        };

        openButton.addEventListener(
            'click',
            openModal
        );

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

        form.addEventListener(
            'submit',
            async event => {

                event.preventDefault();

                const nameInput =
                    document.getElementById(
                        'adminCreateToppingName'
                    );

                const priceInput =
                    document.getElementById(
                        'adminCreateToppingPrice'
                    );

                const descriptionInput =
                    document.getElementById(
                        'adminCreateToppingDescription'
                    );

                const imageInput =
                    document.getElementById(
                        'adminCreateToppingImage'
                    );

                const submitButton =
                    document.getElementById(
                        'adminToppingCreateSubmit'
                    );

                const name =
                    nameInput.value.trim();

                const price =
                    Number(
                        priceInput.value
                    );

                const description =
                    descriptionInput.value.trim();

                const image =
                    imageInput.value.trim();

                if (
                    !name ||
                    !Number.isFinite(price) ||
                    price < 0
                ) {

                    alert(
                        'Vui lòng kiểm tra tên và giá topping.'
                    );

                    return;

                }

                try {

                    submitButton.disabled = true;

                    submitButton.textContent =
                        'ĐANG THÊM...';

                    const response =
                        await fetch(
                            'api/admin-toppings.php',
                            {
                                method: 'POST',

                                headers: {
                                    'Content-Type':
                                        'application/json'
                                },

                                body: JSON.stringify({
                                    name,
                                    description:
                                        description || null,
                                    price,
                                    image:
                                        image || null
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
                            'Không thể thêm topping.'
                        );

                    }

                    alert(
                        'Đã thêm topping thành công.'
                    );

                    closeModal();

                    await loadAdminToppings();

                } catch (error) {

                    console.error(
                        'Create topping error:',
                        error
                    );

                    alert(
                        error.message ||
                        'Có lỗi xảy ra khi thêm topping.'
                    );

                } finally {

                    submitButton.disabled =
                        false;

                    submitButton.textContent =
                        'THÊM TOPPING';

                }

            }
        );

        /*
        |--------------------------------------------------------------------------
        | LOAD INITIAL DATA
        |--------------------------------------------------------------------------
        */

        loadAdminToppings();

    }
);

document.addEventListener(
    'click',
    async event => {

        const editButton =
            event.target.closest(
                '[data-topping-edit]'
            );

        const toggleButton =
            event.target.closest(
                '[data-topping-toggle]'
            );

        /*
        |--------------------------------------------------------------------------
        | EDIT
        |--------------------------------------------------------------------------
        */

        if (editButton) {

            const id =
                Number(
                    editButton.dataset.toppingEdit
                );

            const topping =
                adminToppingsCache.find(
                    item =>
                        Number(item.id) === id
                );

            if (!topping) {

                alert(
                    'Không tìm thấy topping.'
                );

                return;

            }

            document.getElementById(
                'adminEditToppingId'
            ).value = topping.id;

            document.getElementById(
                'adminEditToppingName'
            ).value = topping.name || '';

            document.getElementById(
                'adminEditToppingPrice'
            ).value = topping.price ?? 0;

            document.getElementById(
                'adminEditToppingDescription'
            ).value =
                topping.description || '';

            document.getElementById(
                'adminEditToppingImage'
            ).value =
                topping.image || '';

            const modal =
                document.getElementById(
                    'adminToppingEditModal'
                );

            modal.hidden = false;

            modal.classList.add(
                'is-open'
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | TOGGLE ACTIVE
        |--------------------------------------------------------------------------
        */

        if (toggleButton) {

            const id =
                Number(
                    toggleButton.dataset.toppingToggle
                );

            const topping =
                adminToppingsCache.find(
                    item =>
                        Number(item.id) === id
                );

            if (!topping) {

                alert(
                    'Không tìm thấy topping.'
                );

                return;

            }

            const currentStatus =
                Number(
                    topping.is_active
                ) === 1;

            const actionText =
                currentStatus
                    ? 'ẩn'
                    : 'hiện';

            const confirmed =
                window.confirm(
                    `Bạn có chắc muốn ${actionText} topping "${topping.name}"?`
                );

            if (!confirmed) {
                return;
            }

            try {

                toggleButton.disabled = true;

                const response =
                    await fetch(
                        'api/admin-toppings.php',
                        {
                            method: 'PATCH',

                            headers: {
                                'Content-Type':
                                    'application/json'
                            },

                            body: JSON.stringify({
                                id
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
                        'Không thể thay đổi trạng thái topping.'
                    );

                }

                await loadAdminToppings();

            } catch (error) {

                console.error(
                    'Toggle topping error:',
                    error
                );

                alert(
                    error.message ||
                    'Không thể thay đổi trạng thái topping.'
                );

                toggleButton.disabled =
                    false;

            }

        }

    }
);

document.addEventListener(
    'DOMContentLoaded',
    () => {

        const form =
            document.getElementById(
                'adminToppingEditForm'
            );

        const modal =
            document.getElementById(
                'adminToppingEditModal'
            );

        const closeButton =
            document.getElementById(
                'adminToppingEditClose'
            );

        const cancelButton =
            document.getElementById(
                'adminToppingEditCancel'
            );

        const overlay =
            document.querySelector(
                '[data-close-topping-edit-modal]'
            );

        if (!form || !modal) {
            return;
        }

        const closeModal = () => {

            modal.hidden = true;

            modal.classList.remove(
                'is-open'
            );

        };

        closeButton?.addEventListener(
            'click',
            closeModal
        );

        cancelButton?.addEventListener(
            'click',
            closeModal
        );

        overlay?.addEventListener(
            'click',
            closeModal
        );

        form.addEventListener(
            'submit',
            async event => {

                event.preventDefault();

                const submitButton =
                    document.getElementById(
                        'adminToppingEditSubmit'
                    );

                const id =
                    Number(
                        document.getElementById(
                            'adminEditToppingId'
                        ).value
                    );

                const name =
                    document.getElementById(
                        'adminEditToppingName'
                    ).value.trim();

                const price =
                    Number(
                        document.getElementById(
                            'adminEditToppingPrice'
                        ).value
                    );

                const description =
                    document.getElementById(
                        'adminEditToppingDescription'
                    ).value.trim();

                const image =
                    document.getElementById(
                        'adminEditToppingImage'
                    ).value.trim();

                if (
                    !id ||
                    !name ||
                    !Number.isFinite(price) ||
                    price < 0
                ) {

                    alert(
                        'Vui lòng kiểm tra dữ liệu topping.'
                    );

                    return;

                }

                try {

                    submitButton.disabled = true;

                    submitButton.textContent =
                        'ĐANG LƯU...';

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
                                    id,
                                    name,
                                    price,
                                    description:
                                        description || null,
                                    image:
                                        image || null
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

                    alert(
                        'Đã cập nhật topping.'
                    );

                    closeModal();

                    await loadAdminToppings();

                } catch (error) {

                    console.error(
                        'Edit topping error:',
                        error
                    );

                    alert(
                        error.message ||
                        'Không thể cập nhật topping.'
                    );

                } finally {

                    submitButton.disabled =
                        false;

                    submitButton.textContent =
                        'LƯU THAY ĐỔI';

                }

            }
        );

    }
);

loadAdminToppings();

const refreshButton = document.getElementById('adminToppingRefresh');

if (refreshButton) {
    refreshButton.addEventListener('click', () => {
        loadAdminToppings();
    });
}