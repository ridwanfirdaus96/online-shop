<?php
$page_title = 'Edit Produk';

$extra_css = '
<style>
    .image-preview {
        width: 200px;
        height: 200px;
        border: 2px dashed var(--border);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        background: var(--light);
        margin-bottom: 1rem;
        position: relative;
    }
    .image-preview img {
        max-width: 100%;
        max-height: 100%;
        object-fit: cover;
    }
    .image-preview-text {
        color: var(--gray);
        font-size: 0.875rem;
        text-align: center;
        padding: 1rem;
    }
    .current-image {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1rem;
        padding: 1rem;
        background: var(--light);
        border-radius: 8px;
    }
    .current-image img {
        width: 100px;
        height: 100px;
        object-fit: cover;
        border-radius: 8px;
    }
    .delete-image-btn {
        color: var(--danger);
        cursor: pointer;
        font-size: 0.875rem;
    }
</style>';

$extra_js = '
<script>
    // Image preview
    document.getElementById("imageInput").addEventListener("change", function(e) {
        const preview = document.getElementById("imagePreview");
        const file = e.target.files[0];
        
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = \'<img src="\' + e.target.result + \'" alt="Preview">\';
            }
            reader.readAsDataURL(file);
        } else {
            preview.innerHTML = \'<span class="image-preview-text">📷 Upload gambar baru (opsional)</span>\';
        }
    });
</script>';

// We need to get product data before including header to check if product exists
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Check if user is admin
if (!isAdmin()) {
    setFlash('danger', 'Akses ditolak!');
    redirect('../login.php');
}

$product_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Get product
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    setFlash('danger', 'Produk tidak ditemukan!');
    redirect('products.php');
}

// Get categories
$categories = $pdo->query("SELECT * FROM categories")->fetchAll();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && checkCsrf()) {
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);
    $price = (float) $_POST['price'];
    $stock = (int) $_POST['stock'];
    $category_id = (int) $_POST['category_id'];
    $image_name = $product['image']; // Keep existing image by default

    $errors = [];

    if (empty($name))
        $errors[] = 'Nama produk harus diisi';
    if ($price <= 0)
        $errors[] = 'Harga harus lebih dari 0';
    if ($stock < 0)
        $errors[] = 'Stok tidak boleh negatif';

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $max_size = 5 * 1024 * 1024; // 5MB

        $file_type = $_FILES['image']['type'];
        $file_size = $_FILES['image']['size'];

        if (!in_array($file_type, $allowed_types)) {
            $errors[] = 'Format gambar tidak valid! Gunakan JPG, PNG, GIF, atau WEBP';
        } elseif ($file_size > $max_size) {
            $errors[] = 'Ukuran gambar terlalu besar! Maksimal 5MB';
        } else {
            // Generate unique filename
            $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $new_image_name = uniqid('product_') . '.' . $extension;
            $upload_path = __DIR__ . '/../uploads/' . $new_image_name;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                // Delete old image if exists
                if ($product['image']) {
                    $old_image_path = __DIR__ . '/../uploads/' . $product['image'];
                    if (file_exists($old_image_path)) {
                        unlink($old_image_path);
                    }
                }
                $image_name = $new_image_name;
            } else {
                $errors[] = 'Gagal mengupload gambar!';
            }
        }
    }

    // Handle image deletion
    if (isset($_POST['delete_image']) && $_POST['delete_image'] === '1') {
        if ($product['image']) {
            $old_image_path = __DIR__ . '/../uploads/' . $product['image'];
            if (file_exists($old_image_path)) {
                unlink($old_image_path);
            }
        }
        $image_name = null;
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE products 
                               SET name = ?, description = ?, price = ?, stock = ?, category_id = ?, image = ? 
                               WHERE id = ?");

        if ($stmt->execute([$name, $description, $price, $stock, $category_id, $image_name, $product_id])) {
            setFlash('success', 'Produk berhasil diupdate!');
            redirect('products.php');
        } else {
            $errors[] = 'Gagal mengupdate produk!';
        }
    }
}

$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Admin - Shop Online</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/shop-online/assets/css/style.css">
    <?php echo $extra_css; ?>
</head>

<body>
    <nav class="navbar">
        <div class="container">
            <a href="/shop-online/admin/" class="logo">🛒 Admin Panel</a>
            <nav>
                <ul>
                    <li><a href="/shop-online/admin/">Dashboard</a></li>
                    <li><a href="/shop-online/admin/products.php">Produk</a></li>
                    <li><a href="/shop-online/admin/orders.php">Pesanan</a></li>
                    <li><a href="/shop-online/">Lihat Toko</a></li>
                    <li><a href="/shop-online/logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </nav>

    <?php if ($flash): ?>
        <div class="container" style="margin-top: 1.5rem;">
            <div class="alert alert-<?php echo $flash['type']; ?>">
                <?php echo $flash['message']; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="container" style="margin-top: 2rem;">
        <a href="products.php"
            style="color: var(--primary); text-decoration: none; margin-bottom: 1rem; display: inline-block;">
            ← Kembali
        </a>

        <h1 class="section-title">✏️ Edit Produk</h1>

        <?php if (isset($errors) && !empty($errors)): ?>
            <div class="alert alert-danger">
                <ul style="margin: 0; padding-left: 1.5rem;">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="card">
            <form method="POST" enctype="multipart/form-data">
                <?php echo getCsrfInput(); ?>
                <div class="form-group">
                    <label class="form-label">Gambar Produk</label>

                    <?php if ($product['image']): ?>
                        <div class="current-image" id="currentImage">
                            <img src="/shop-online/uploads/<?php echo htmlspecialchars($product['image']); ?>"
                                alt="Current Image">
                            <div>
                                <p style="margin: 0; font-weight: 500;">Gambar saat ini</p>
                                <p style="margin: 0.25rem 0 0; font-size: 0.875rem; color: var(--gray);">
                                    <?php echo htmlspecialchars($product['image']); ?></p>
                                <label class="delete-image-btn">
                                    <input type="checkbox" name="delete_image" value="1" id="deleteImageCheck"
                                        style="margin-right: 0.25rem;">
                                    Hapus gambar ini
                                </label>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="image-preview" id="imagePreview">
                        <span class="image-preview-text">📷 Upload gambar baru (opsional)</span>
                    </div>
                    <input type="file" name="image" id="imageInput" class="form-control" accept="image/*">
                    <small style="color: var(--gray);">Format: JPG, PNG, GIF, WEBP. Maksimal 5MB</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Nama Produk *</label>
                    <input type="text" name="name" class="form-control" required
                        value="<?php echo htmlspecialchars($product['name']); ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" class="form-control"
                        rows="4"><?php echo htmlspecialchars($product['description']); ?></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Harga *</label>
                        <input type="number" name="price" class="form-control" step="0.01" required
                            value="<?php echo $product['price']; ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Stok *</label>
                        <input type="number" name="stock" class="form-control" required
                            value="<?php echo $product['stock']; ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Kategori</label>
                    <select name="category_id" class="form-control">
                        <option value="">Pilih Kategori</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo $product['category_id'] == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="display: flex; gap: 1rem;">
                    <button type="submit" class="btn btn-primary">Update Produk</button>
                    <a href="products.php" class="btn btn-outline">Batal</a>
                </div>
            </form>
        </div>
    </div>

    <script src="/shop-online/assets/js/main.js"></script>
    <?php echo $extra_js; ?>
</body>

</html>