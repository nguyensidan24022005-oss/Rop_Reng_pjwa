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

