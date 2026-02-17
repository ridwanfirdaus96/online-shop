<?php
$page_title = 'Tambah Produk';

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
            preview.innerHTML = \'<span class="image-preview-text">📷 Preview gambar akan muncul di sini</span>\';
        }
    });
</script>';

include 'includes/header.php';

// Get categories
$categories = $pdo->query("SELECT * FROM categories")->fetchAll();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && checkCsrf()) {
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);
    $price = (float) $_POST['price'];
    $stock = (int) $_POST['stock'];
    $category_id = (int) $_POST['category_id'];
    $image_name = null;

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
            $image_name = uniqid('product_') . '.' . $extension;
            $upload_path = __DIR__ . '/../uploads/' . $image_name;

            if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $errors[] = 'Gagal mengupload gambar!';
                $image_name = null;
            }
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO products (name, description, price, stock, category_id, image) 
                               VALUES (?, ?, ?, ?, ?, ?)");

        if ($stmt->execute([$name, $description, $price, $stock, $category_id, $image_name])) {
            setFlash('success', 'Produk berhasil ditambahkan!');
            redirect('products.php');
        } else {
            $errors[] = 'Gagal menambahkan produk!';
        }
    }
}
?>

<div class="container" style="margin-top: 2rem;">
    <a href="products.php"
        style="color: var(--primary); text-decoration: none; margin-bottom: 1rem; display: inline-block;">
        ← Kembali
    </a>

    <h1 class="section-title">➕ Tambah Produk Baru</h1>

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
                <div class="image-preview" id="imagePreview">
                    <span class="image-preview-text">📷 Preview gambar akan muncul di sini</span>
                </div>
                <input type="file" name="image" id="imageInput" class="form-control" accept="image/*">
                <small style="color: var(--gray);">Format: JPG, PNG, GIF, WEBP. Maksimal 5MB</small>
            </div>

            <div class="form-group">
                <label class="form-label">Nama Produk *</label>
                <input type="text" name="name" class="form-control" required
                    value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
            </div>

            <div class="form-group">
                <label class="form-label">Deskripsi</label>
                <textarea name="description" class="form-control"
                    rows="4"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label class="form-label">Harga *</label>
                    <input type="number" name="price" class="form-control" step="0.01" required
                        value="<?php echo isset($_POST['price']) ? $_POST['price'] : ''; ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Stok *</label>
                    <input type="number" name="stock" class="form-control" required
                        value="<?php echo isset($_POST['stock']) ? $_POST['stock'] : ''; ?>">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Kategori</label>
                <select name="category_id" class="form-control">
                    <option value="">Pilih Kategori</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary">Simpan Produk</button>
                <a href="products.php" class="btn btn-outline">Batal</a>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>