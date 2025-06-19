<?php 
require 'auth.php';
require 'db.php';

if ($_SESSION['user_type'] !== 'customer') {
    header("Location: index.php");
    exit;
}

// Clear cart
if (isset($_POST['clear_cart'])) {
    $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    echo json_encode(['cleared' => true]);
    exit;
}

// AJAX actions
if (isset($_POST['action'])) {
    $product_id = intval($_POST['product_id']);
    if ($_POST['action'] === 'update_quantity') {
        $new_qty = max(1, intval($_POST['quantity']));
        $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$new_qty, $_SESSION['user_id'], $product_id]);
        echo json_encode(['success' => true]);
        exit;
    } elseif ($_POST['action'] === 'remove_item') {
        $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$_SESSION['user_id'], $product_id]);
        echo json_encode(['removed' => true]);
        exit;
    }
}

$stmt = $pdo->prepare("
    SELECT c.*, p.name, p.price, p.image
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$items = $stmt->fetchAll();
$total = 0;

// Get total cart items
$stmtCart = $pdo->prepare("SELECT SUM(quantity) AS total FROM cart WHERE user_id = ?");
$stmtCart->execute([$_SESSION['user_id']]);
$cartCount = $stmtCart->fetch()['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Cart - SmartShop</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    'main-teal': '#008080',
                    'soft-teal': '#ccf2f2',
                    'muted-black': '#1a1a1a'
                },
                fontFamily: {
                    sans: ['Inter', 'sans-serif']
                }
            }
        }
    }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen font-sans">

    <!-- NAVBAR -->
    <header class="bg-white border-b border-gray-200 px-8 py-5 shadow-sm flex justify-between items-center">
        <h1 class="text-2xl font-semibold tracking-tight text-main-teal">SmartShop</h1>
        <nav class="flex space-x-8 text-base font-medium">
            <a href="index.php" class="relative group">
                <span class="transition-colors duration-300 group-hover:text-main-teal">Home</span>
                <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-main-teal transition-all duration-300 group-hover:w-full"></span>
            </a>
            <a href="products.php" class="relative group">
                <span class="transition-colors duration-300 group-hover:text-main-teal">Browse</span>
                <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-main-teal transition-all duration-300 group-hover:w-full"></span>
            </a>
            <a href="cart.php" class="relative group">
                <span class="transition-colors duration-300 group-hover:text-main-teal">Cart (<?= $cartCount ?>)</span>
                <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-main-teal transition-all duration-300 group-hover:w-full"></span>
            </a>
            <a href="customer_orders.php" class="relative group">
                <span class="transition-colors duration-300 group-hover:text-main-teal">My Orders</span>
                <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-main-teal transition-all duration-300 group-hover:w-full"></span>
            </a>
            <a href="logout.php" class="relative group text-red-600 hover:text-red-700 transition">
                Logout
            </a>
        </nav>
    </header>

    <div class="max-w-4xl mx-auto bg-white shadow-md rounded-xl px-6 py-8 mt-10">
        <h1 class="text-3xl font-bold mb-6 text-center text-teal-800">🛒 My Cart</h1>

        <?php if (!$items): ?>
            <p class="text-center text-teal-600 text-lg">Your cart is currently empty.</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full table-auto text-center border border-teal-200 bg-white rounded-md shadow-sm">
                    <thead class="bg-teal-100 text-teal-900 font-semibold">
                        <tr>
                            <th class="px-4 py-3">Product</th>
                            <th class="px-4 py-3">Image</th>
                            <th class="px-4 py-3">Qty</th>
                            <th class="px-4 py-3">Price</th>
                            <th class="px-4 py-3">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="text-teal-800">
                        <?php foreach ($items as $item): 
                            $subtotal = $item['price'] * $item['quantity'];
                            $total += $subtotal;
                        ?>
                        <tr class="border-t border-teal-100">
                            <td class="px-4 py-3"><?= htmlspecialchars($item['name']) ?></td>
                            <td class="px-4 py-3">
                                <img src="<?= file_exists($item['image']) ? htmlspecialchars($item['image']) : 'default_product.png' ?>"
                                     alt="Product" class="w-16 h-16 object-cover rounded-md mx-auto">
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-center items-center space-x-2">
                                    <button onclick="updateQuantity(<?= $item['product_id'] ?>, -1)" class="bg-teal-200 hover:bg-teal-300 text-teal-900 font-bold px-2 rounded">−</button>
                                    <input id="qty-<?= $item['product_id'] ?>" value="<?= $item['quantity'] ?>" class="w-12 text-center border border-teal-300 rounded" readonly>
                                    <button onclick="updateQuantity(<?= $item['product_id'] ?>, 1)" class="bg-teal-200 hover:bg-teal-300 text-teal-900 font-bold px-2 rounded">+</button>
                                </div>
                            </td>
                            <td class="px-4 py-3">$<?= number_format($item['price'], 2) ?></td>
                            <td class="px-4 py-3 font-semibold">$<?= number_format($subtotal, 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <tr class="bg-teal-50 font-semibold text-teal-900">
                            <td colspan="4" class="text-right px-4 py-4">Grand Total:</td>
                            <td class="px-4 py-4">$<?= number_format($total, 2) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-6 flex justify-between items-center gap-4">
                <button onclick="clearCart()" class="bg-red-500 text-white px-6 py-3 rounded hover:bg-red-600">
                    ❌ Clear Cart
                </button>
                <form action="checkout.php" method="POST">
                    <button type="submit" class="bg-teal-600 text-white px-6 py-3 rounded hover:bg-teal-700">
                        ✅ Proceed to Checkout
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function updateQuantity(productId, delta) {
            const input = document.getElementById('qty-' + productId);
            let quantity = parseInt(input.value);
            if (quantity + delta <= 0) {
                if (confirm("Remove this item?")) {
                    fetch('', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `action=remove_item&product_id=${productId}`
                    }).then(() => location.reload());
                    return;
                }
            }
            quantity += delta;
            input.value = quantity;
            fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=update_quantity&product_id=${productId}&quantity=${quantity}`
            }).then(() => location.reload());
        }

        function clearCart() {
            if (confirm("Clear all items from your cart?")) {
                fetch('', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'clear_cart=1'
                }).then(() => location.reload());
            }
        }
    </script>
</body>
</html>
