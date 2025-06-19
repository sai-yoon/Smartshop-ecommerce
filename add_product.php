<?php 
require 'auth.php';
require 'db.php';

if ($_SESSION['user_type'] !== 'seller') {
    header("Location: index.php");
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $desc = $_POST['description'];
    $price = $_POST['price'];

    $imagePath = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['image']['tmp_name'];
        $fileName = basename($_FILES['image']['name']);
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExts = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($fileExt, $allowedExts)) {
            $newName = uniqid("img_") . '.' . $fileExt;
            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $uploadPath = $uploadDir . $newName;
            if (move_uploaded_file($fileTmp, $uploadPath)) {
                $imagePath = $uploadPath;
            } else {
                $message = "❌ Failed to move uploaded file.";
            }
        } else {
            $message = "❌ Invalid image format. Only JPG, PNG, and GIF are allowed.";
        }
    }

    if (!$message) {
        $stmt = $pdo->prepare("INSERT INTO products (seller_id, name, description, price, image)
                               VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $name, $desc, $price, $imagePath]);
        $message = "✅ Product added successfully!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Product - SmartShop</title>
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
    <h1 class="text-2xl font-semibold tracking-tight">SmartShop</h1>
    <nav class="flex space-x-8 text-base font-medium">
        <a href="index.php" class="relative group">
            <span class="transition-colors duration-300 group-hover:text-main-teal">Home</span>
            <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-main-teal transition-all duration-300 group-hover:w-full"></span>
        </a>
        <a href="products.php" class="relative group">
            <span class="transition-colors duration-300 group-hover:text-main-teal">Browse</span>
            <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-main-teal transition-all duration-300 group-hover:w-full"></span>
        </a>
        <a href="my_products.php" class="relative group">
            <span class="transition-colors duration-300 group-hover:text-main-teal">My Products</span>
            <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-main-teal transition-all duration-300 group-hover:w-full"></span>
        </a>
        <a href="logout.php" class="relative group text-red-600 hover:text-red-700 transition">
            Logout
        </a>
    </nav>
</header>

    <!-- MAIN FORM -->
    <main class="flex items-center justify-center py-12">
        <div class="w-full max-w-2xl p-8 bg-white shadow-xl rounded-xl">
            <h1 class="text-2xl md:text-3xl font-bold text-teal-700 mb-4">➕ Add a New Product</h1>

            <?php if ($message): ?>
                <div class="mb-4 p-4 rounded-md text-white font-semibold
                    <?= str_starts_with($message, '✅') ? 'bg-green-500' : 'bg-red-500' ?>">
                    <?= $message ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" class="space-y-6">
                <div>
                    <label for="name" class="block font-medium text-gray-700">Name of the Product</label>
                    <input type="text" id="name" name="name" placeholder="e.g., Wireless Headphones"
                        class="w-full mt-1 px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-teal-500"
                        required>
                </div>

                <div>
                    <label for="description" class="block font-medium text-gray-700">Short Description</label>
                    <textarea id="description" name="description" rows="4"
                        class="w-full mt-1 px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-teal-500"
                        placeholder="Briefly describe your product..." required></textarea>
                </div>

                <div>
                    <label for="price" class="block font-medium text-gray-700">Price (in $)</label>
                    <input type="number" step="0.01" id="price" name="price" placeholder="e.g., 49.99"
                        class="w-full mt-1 px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-teal-500"
                        required>
                </div>

                <div>
                    <label for="image" class="block font-medium text-gray-700">Upload Product Image</label>
                    <input type="file" id="image" name="image" accept="image/*"
                        class="w-full mt-1 text-sm text-gray-600 file:mr-4 file:py-2 file:px-4
                               file:rounded-full file:border-0 file:text-sm file:font-semibold
                               file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100"
                        onchange="previewImage(event)">

                    <div id="imagePreviewContainer" class="mt-4 hidden">
                        <p class="text-sm text-gray-600 mb-1">📸 Image Preview:</p>
                        <img id="imagePreview" src="#" alt="Image Preview"
                            class="w-48 h-48 object-cover rounded-lg shadow-md border border-gray-300">
                    </div>
                </div>

                <button type="submit"
                    class="w-full py-2 px-4 bg-teal-600 hover:bg-teal-700 text-white font-semibold rounded-md shadow">
                    ➕ Add Product
                </button>
            </form>

            <div class="mt-6">
                <a href="seller_dashboard.php" class="text-teal-600 hover:underline">← Back to Dashboard</a>
            </div>
        </div>
    </main>

    <script>
        function previewImage(event) {
            const file = event.target.files[0];
            const previewContainer = document.getElementById('imagePreviewContainer');
            const preview = document.getElementById('imagePreview');

            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    previewContainer.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            } else {
                preview.src = '#';
                previewContainer.classList.add('hidden');
            }
        }
    </script>
</body>

</html>
