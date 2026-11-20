<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/db.php';

try {

    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);

        echo json_encode([
            'success' => false,
            'message' => 'Method not allowed.'
        ]);

        exit;
    }

    $orderStatsQuery = $pdo->query(
        "SELECT
            COUNT(*) AS total_orders,
            
            SUM(
                CASE
                    WHEN status = 'pending' THEN 1
                    ELSE 0
                END
            ) AS pending_orders,
            
            SUM(
                CASE
                    WHEN status = 'confirmed' THEN 1
                    ELSE 0
                END
            ) AS confirmed_orders,
            
            SUM(
                CASE
                    WHEN status = 'completed' THEN 1
                    ELSE 0
                END
            ) AS completed_orders,
            
            SUM(
                CASE
                    WHEN status = 'cancelled' THEN 1
                    ELSE 0
                END
            ) AS cancelled_orders
            
        FROM orders"
    );

    $orderStats = $orderStatsQuery->fetch();

    $revenueStatsQuery = $pdo->query(
        "SELECT
        
            COALESCE(
                SUM(
                    CASE
                        WHEN status = 'completed'
                        AND DATE(created_at) = CURDATE()
                        THEN total
                        ELSE 0
                    END
                ),
                0
            ) AS today_revenue,
            
            COALESCE(
                SUM(
                    CASE
                        WHEN status = 'completed'
                        AND YEAR(created_at) = YEAR(CURDATE())
                        AND MONTH(created_at) = MONTH(CURDATE())
                        THEN total
                        ELSE 0
                    END
                ),
                0
            ) AS month_revenue,
            
            COALESCE(
                SUM(
                    CASE
                        WHEN status = 'completed'
                        THEN total
                        ELSE 0
                    END
                ),
                0
            ) AS total_revenue
            
        FROM orders"
    );

    $revenueStats = $revenueStatsQuery->fetch();

    $productStatsQuery = $pdo->query(
        "SELECT
        
            COUNT(*) AS total_products,
            
            SUM(
                CASE
                    WHEN is_active = 1 then 1
                    ELSE 0
                END
            ) AS active_products,
            
            SUM(
                CASE
                    WHEN is_active = 0 THEN 1
                    ELSE 0
                END
            ) AS inactive_products
            
        FROM products"
    );

    $productStats = $productStatsQuery->fetch();

    echo json_encode([
        'success' => true,

        'data' => [
            'orders' => [
                'total' => (int) ($orderStats['total_orders'] ?? 0),
                'pending' => (int) ($orderStats['pending_orders'] ?? 0),
                'confirmed' => (int) ($orderStats['confirmed_orders'] ?? 0),
                'completed' => (int) ($orderStats['completed_orders'] ?? 0),
                'cancelled' => (int) ($orderStats['cancelled_orders'] ?? 0)
            ],

            'revenue' => [
                'today' => (float) ($revenueStats['today_revenue'] ?? 0),
                'month' => (float) ($revenueStats['month_revenue'] ?? 0),
                'total' => (float) ($revenueStats['total_revenue'] ?? 0)
            ],

            'products' => [
                'total' => (int) ($productStats['total_products'] ?? 0),
                'active' => (int) ($productStats['active_products'] ?? 0),
                'inactive' => (int) ($productStats['inactive_products'] ?? 0)
            ]
        ]
    ]);

} catch (Throwable $e) {

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Không thể tải thống kê Dashboard.'
    ]);
}