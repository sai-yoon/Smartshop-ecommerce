<?php
require 'auth.php';
require 'db.php';

if ($_SESSION['user_type'] !== 'seller') {
    header("Location: index.php");
    exit;
}

// Fetch all orders that include this seller's products, with customer details
$stmt = $pdo->prepare("
    SELECT DISTINCT o.*, u.full_name, u.email
    FROM orders o
    JOIN users u ON o.user_id = u.id
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    WHERE p.seller_id = ?
    ORDER BY o.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Seller Orders - SmartShop</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen py-10">
    <div class="max-w-5xl mx-auto px-4">
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-teal-800 mb-2">📦 Orders for Your Products</h1>
            <a href="seller_dashboard.php" class="text-teal-600 hover:underline">← Back to Dashboard</a>
        </div>

        <?php if (!$orders): ?>
            <div class="text-center bg-white p-6 rounded shadow-md">
                <p class="text-gray-600 text-lg">No one has ordered your products yet.</p>
            </div>
        <?php else: ?>
            <?php
            $isAlt = false;
            foreach ($orders as $order):
                $bgClass = $isAlt ? 'bg-teal-100' : 'bg-teal-50';
                $isAlt = !$isAlt;
            ?>
                <div class="<?= $bgClass ?> rounded-lg shadow-md mb-6 p-5">
                    <div class="mb-3">
                        <h2 class="text-lg font-semibold text-teal-900 mb-1">
                            🧾 Order #<?= $order['id'] ?> 
                            <span class="text-sm text-teal-700">(<?= date("F j, Y, g:i A", strtotime($order['created_at'])) ?>)</span>
                        </h2>
                        <p class="text-teal-800"><strong>Customer:</strong> <?= htmlspecialchars($order['full_name']) ?> (<?= htmlspecialchars($order['email']) ?>)</p>
                        <p class="text-teal-800 text-sm">
                            <strong>Shipping Address:</strong><br>
                            <?= htmlspecialchars($order['shipping_address']) ?><br>
                            <?= htmlspecialchars($order['city']) ?>, <?= htmlspecialchars($order['zip_code']) ?>
                        </p>
                        <p class="text-teal-800 mt-1"><strong>Total:</strong> $<?= number_format($order['total'], 2) ?></p>
                    </div>

                    <div class="overflow-x-auto rounded">
                        <table class="w-full table-auto border border-teal-200 text-sm text-teal-900 bg-white shadow-sm">
                            <thead class="bg-teal-200 text-teal-900 font-semibold">
                                <tr>
                                    <th class="px-3 py-2 text-left">Product</th>
                                    <th class="px-3 py-2 text-center">Qty</th>
                                    <th class="px-3 py-2 text-center">Price</th>
                                    <th class="px-3 py-2 text-center">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $stmt_items = $pdo->prepare("
                                    SELECT oi.*, p.name 
                                    FROM order_items oi
                                    JOIN products p ON oi.product_id = p.id
                                    WHERE oi.order_id = ? AND p.seller_id = ?
                                ");
                                $stmt_items->execute([$order['id'], $_SESSION['user_id']]);
                                $items = $stmt_items->fetchAll();

                                foreach ($items as $item):
                                ?>
                                    <tr class="border-t border-teal-100">
                                        <td class="px-3 py-2"><?= htmlspecialchars($item['name']) ?></td>
                                        <td class="px-3 py-2 text-center"><?= $item['quantity'] ?></td>
                                        <td class="px-3 py-2 text-center">$<?= number_format($item['price'], 2) ?></td>
                                        <td class="px-3 py-2 text-center font-semibold">
                                            $<?= number_format($item['price'] * $item['quantity'], 2) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>

