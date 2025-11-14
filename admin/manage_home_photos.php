<?php 
require_once(__DIR__ . '/../config/session.php');
require_once(__DIR__ . '/../config/db.php');
require_role(['manager']);

$message = "";

/* ===========================================================
   UPLOAD CAROUSEL IMAGE
   =========================================================== */
if (isset($_POST['upload_carousel'])) {
    if (!empty($_FILES['carousel_image']['name'])) {

        $filename = time() . "_" . basename($_FILES['carousel_image']['name']);
        $target   = __DIR__ . '/../assets/images/carousel/' . $filename;

        if (!is_dir(__DIR__ . '/../assets/images/carousel/')) {
            mkdir(__DIR__ . '/../assets/images/carousel/', 0777, true);
        }

        if (move_uploaded_file($_FILES['carousel_image']['tmp_name'], $target)) {
            $stmt = $pdo->prepare("INSERT INTO home_carousel_images (image) VALUES (?)");
            $stmt->execute([$filename]);
            $message = "Carousel image uploaded!";
        }
    }
}

/* ===========================================================
   DELETE CAROUSEL IMAGE
   =========================================================== */
if (isset($_GET['delete_carousel'])) {
    $id = intval($_GET['delete_carousel']);

    $stmt = $pdo->prepare("SELECT image FROM home_carousel_images WHERE id = ?");
    $stmt->execute([$id]);
    $img = $stmt->fetchColumn();

    $filePath = __DIR__ . '/../assets/images/carousel/' . $img;

    if ($img && file_exists($filePath) && is_file($filePath)) {
        unlink($filePath);
    }

    $pdo->prepare("DELETE FROM home_carousel_images WHERE id=?")->execute([$id]);

    $message = "Carousel image removed!";
}

/* ===========================================================
   UPLOAD / REPLACE FEATURED CATEGORY IMAGE
   =========================================================== */
if (isset($_POST['save_featured'])) {

    $category = $_POST['category'];

    if (!empty($_FILES['featured_image']['name'])) {

        $filename = time() . "_" . basename($_FILES['featured_image']['name']);
        $target   = __DIR__ . '/../assets/images/featured/' . $filename;

        if (!is_dir(__DIR__ . '/../assets/images/featured/')) {
            mkdir(__DIR__ . '/../assets/images/featured/', 0777, true);
        }

        move_uploaded_file($_FILES['featured_image']['tmp_name'], $target);

        // Check if this category already has an image
        $stmt = $pdo->prepare("SELECT id, image FROM featured_category_images WHERE category = ?");
        $stmt->execute([$category]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            $oldFile = __DIR__ . '/../assets/images/featured/' . $existing['image'];
            if (file_exists($oldFile) && is_file($oldFile)) {
                unlink($oldFile);
            }

            $up = $pdo->prepare("UPDATE featured_category_images SET image=? WHERE id=?");
            $up->execute([$filename, $existing['id']]);

            $message = "Featured image for $category replaced!";
        } else {
            $insert = $pdo->prepare("INSERT INTO featured_category_images (category, image) VALUES (?, ?)");
            $insert->execute([$category, $filename]);
            $message = "Featured image for $category uploaded!";
        }
    }
}

/* ===========================================================
   GET RECORDS
   =========================================================== */
$carousel   = $pdo->query("SELECT * FROM home_carousel_images")->fetchAll(PDO::FETCH_ASSOC);
$featured   = $pdo->query("SELECT * FROM featured_category_images")->fetchAll(PDO::FETCH_ASSOC);
$categories = $pdo->query("SELECT DISTINCT category FROM menu_items")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
<title>Manage Home Photos</title>
<link rel="icon" type="image/png" href="/kamulan-system/assets/images/kamulan-logo.jpg">

<style>
    body { font-family: 'Poppins', sans-serif; background: #f7f7f7; margin: 0; padding: 0; }
    .container { width: 90%; margin: 30px auto; background: #fff; padding: 20px; 
                 border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }

    h2, h3 { text-align: center; color: #333; }

    .message {
        background: #4caf50;
        padding: 10px 15px;
        border-radius: 8px;
        color: white;
        width: fit-content;
        margin: 10px auto;
        font-weight: bold;
    }

    .form-box {
        background: #fff;
        padding: 20px;
        border-radius: 12px;
        width: 350px;
        margin: 0 auto;
        box-shadow: 0 3px 10px rgba(0,0,0,0.15);
    }

    input[type="file"], select {
        margin-top: 10px;
        width: 100%;
        padding: 8px;
        border: 1px solid #ccc;
        border-radius: 6px;
    }

    button {
        margin-top: 12px;
        background: #6b4f2c;
        color: white;
        padding: 10px 16px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        width: 100%;
        font-weight: 600;
    }

    .grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }

    .card {
        background: white;
        padding: 15px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.12);
        text-align: center;
    }

    .card img {
        width: 100%;
        height: 160px;
        object-fit: cover;
        border-radius: 10px;
        margin-bottom: 10px;
    }

    .delete-btn {
        display: inline-block;
        background: #dc3545;
        padding: 6px 12px;
        color: white;
        border-radius: 6px;
        text-decoration: none;
        margin-top: 5px;
    }
</style>
</head>
<body>
<?php include __DIR__ . '/../_partials/header_staff.php'; ?>

<div class="container">

<h2>Manage Home Page Photos</h2>

<?php if ($message): ?>
    <div class="message"><?= $message ?></div>
<?php endif; ?>


<!-- =================== CAROUSEL =================== -->
<h3>Carousel Images</h3>

<form method="POST" enctype="multipart/form-data" class="form-box">
    <strong>Upload New Carousel Image</strong>
    <input type="file" name="carousel_image" required>
    <button name="upload_carousel">Upload</button>
</form>

<div class="grid">
<?php foreach ($carousel as $c): ?>
    <div class="card">
        <img src="/kamulan-system/assets/images/carousel/<?= $c['image'] ?>">
        <a class="delete-btn" href="?delete_carousel=<?= $c['id'] ?>">Delete</a>
    </div>
<?php endforeach; ?>
</div>

<hr><br>

<!-- =================== FEATURED CATEGORY =================== -->
<h3>Featured Menu Images</h3>

<form method="POST" enctype="multipart/form-data" class="form-box">
    <strong>Select Category:</strong>
    <select name="category" required>
        <?php foreach ($categories as $cat): ?>
            <option><?= $cat['category'] ?></option>
        <?php endforeach; ?>
    </select>

    <input type="file" name="featured_image" required>
    <button name="save_featured">Upload / Replace</button>
</form>

<div class="grid">
<?php foreach ($featured as $f): ?>
    <div class="card">
        <strong><?= $f['category'] ?></strong>
        <img src="/kamulan-system/assets/images/featured/<?= $f['image'] ?>"
             onerror="this.src='/kamulan-system/assets/images/kamulan-cover.jpg'">
        <a class="delete-btn" href="?delete_featured=<?= $f['id'] ?>">Delete</a>
    </div>
<?php endforeach; ?>
</div>

</div>
</body>
</html>
