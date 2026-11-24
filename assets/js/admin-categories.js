document.addEventListener('DOMContentLoaded', () => {

    const tableBody = document.getElementById('categoriesTableBody');
    const modal = document.getElementById('categoryModal');
    const form = document.getElementById('categoryForm');

    const categoryId = document.getElementById('categoryId');
    const categoryName = document.getElementById('categoryName');
    const categorySlug = document.getElementById('categorySlug');

    const modalTitle = document.getElementById('categoryModalTitle');
    const modalEyebrow = document.getElementById('categoryModalEyebrow');

    const openCreateButton =
        document.getElementById('openCreateCategoryModal');

    const API_URL = 'api/admin-categories.php';

    let categories = [];

    function escapeHtml(value) {

        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function openModal(category = null) {

        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');

        if (category) {

            modalTitle.textContent = 'Chỉnh sửa danh mục';
            modalEyebrow.textContent = 'CẬP NHẬT DANH MỤC';

            categoryId.value = category.id;
            categoryName.value = category.name;
            categorySlug.value = category.slug;

        } else {

            modalTitle.textContent = 'Thêm danh mục';
            modalEyebrow.textContent = 'DANH MỤC MỚI';

            categoryId.value = '';
            categoryName.value = '';
            categorySlug.value = '';
        }

        categoryName.focus();
    }

    function closeModal() {

        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');

        form.reset();
        categoryId.value = '';
    }

    function renderCategories() {

        if (!categories.length) {

            tableBody.innerHTML = `
                <tr>
                    <td colspan="5">
                        Chưa có danh mục.
                    </td>
                </tr>
            `;

            return;
        }

        tableBody.innerHTML = categories.map(category => {

            const active = Number(category.is_active) === 1;

            return `
                <tr>

                    <td>${escapeHtml(category.id)}</td>

                    <td>
                        <strong>
                            ${escapeHtml(category.name)}
                        </strong>
                    </td>

                    <td>
                        <code>
                            ${escapeHtml(category.slug)}
                        </code>
                    </td>

                    <td>
                        <span class="admin-status ${active ? 'is-active' : 'is-inactive'}">
                            ${active ? 'ĐANG BÁN' : 'ĐANG ẨN'}
                        </span>
                    </td>

                    <td>

                        <div class="admin-table-actions">

                            <button
                                type="button"
                                class="admin-action-button"
                                data-action="edit"
                                data-id="${category.id}"
                            >
                                SỬA
                            </button>

                            <button
                                type="button"
                                class="admin-action-button"
                                data-action="toggle"
                                data-id="${category.id}"
                            >
                                ${active ? 'ẨN' : 'HIỆN'}
                            </button>

                        </div>

                    </td>

                </tr>
            `;

        }).join('');
    }

    async function loadCategories() {

        try {

            tableBody.innerHTML = `
                <tr>
                    <td colspan="5">
                        Đang tải danh mục...
                    </td>
                </tr>
            `;

            const response = await fetch(API_URL);

            const data = await response.json();

            if (!response.ok || !data.success) {
                throw new Error(
                    data.message || 'Không thể tải danh mục.'
                );
            }

            categories = data.categories || [];

            renderCategories();

        } catch (error) {

            tableBody.innerHTML = `
                <tr>
                    <td colspan="5">
                        Không thể tải danh mục.
                    </td>
                </tr>
            `;

            console.error(error);
        }
    }

    async function saveCategory(event) {

        event.preventDefault();

        const id = categoryId.value.trim();

        const payload = {
            name: categoryName.value.trim(),
            slug: categorySlug.value.trim()
        };

        const method = id ? 'PUT' : 'POST';

        if (id) {
            payload.id = Number(id);
        }

        try {

            const response = await fetch(API_URL, {
                method,
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                throw new Error(
                    data.message || 'Không thể lưu danh mục.'
                );
            }

            closeModal();

            await loadCategories();

        } catch (error) {

            alert(error.message);

        }
    }

    async function toggleCategory(id) {

        try {

            const response = await fetch(API_URL, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id: Number(id)
                })
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                throw new Error(
                    data.message || 'Không thể thay đổi trạng thái.'
                );
            }

            await loadCategories();

        } catch (error) {

            alert(error.message);

        }
    }

    openCreateButton.addEventListener(
        'click',
        () => openModal()
    );

    document.querySelectorAll(
        '[data-close-category-modal]'
    ).forEach(button => {

        button.addEventListener(
            'click',
            closeModal
        );

    });

    form.addEventListener(
        'submit',
        saveCategory
    );

    tableBody.addEventListener(
        'click',
        event => {

            const button =
                event.target.closest('button[data-action]');

            if (!button) {
                return;
            }

            const id = Number(button.dataset.id);

            const category =
                categories.find(item => Number(item.id) === id);

            if (!category) {
                return;
            }

            if (button.dataset.action === 'edit') {

                openModal(category);

                return;
            }

            if (button.dataset.action === 'toggle') {

                const action =
                    Number(category.is_active) === 1
                        ? 'ẩn'
                        : 'hiện';

                if (
                    !confirm(
                        `Bạn có chắc muốn ${action} danh mục "${category.name}"?`
                    )
                ) {
                    return;
                }

                toggleCategory(id);
            }

        }
    );

    loadCategories();

});