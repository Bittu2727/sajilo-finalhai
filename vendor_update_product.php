<?php

@include 'config.php';

session_start();

$vendor_id = $_SESSION['vendor_id'] ?? null;

if (!$vendor_id) {
    header('location:login.php');
    exit();
}

if (isset($_POST['update_product'])) {

    $product_id = filter_var($_POST['product_id'], FILTER_SANITIZE_NUMBER_INT);
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $price = filter_var($_POST['price'], FILTER_SANITIZE_STRING);
    $category = filter_var($_POST['category'], FILTER_SANITIZE_STRING);
    $description = filter_var($_POST['description'], FILTER_SANITIZE_STRING);
    $stock=filter_var($_POST['stock'], FILTER_SANITIZE_STRING);
    $image = $_FILES['image'];
    $image_name = filter_var($image['name'], FILTER_SANITIZE_STRING);
    $image_size = $image['size'];
    $image_tmp_name = $image['tmp_name'];
    $image_folder = 'uploaded_img/' . $image_name;
    $old_image = filter_var($_POST['old_image'], FILTER_SANITIZE_STRING);

    $update_product = $conn->prepare("UPDATE `products` SET name = ?, category = ?, description = ?, price = ?, stock =? WHERE product_id = ?");
    $update_product->execute([$name, $category, $description, $price,$stock, $product_id]);

    $message[] = 'Product updated successfully!';

    if (!empty($image_name)) {
        if ($image_size > 2000000) {
            $message[] = 'Image size is too large!';
        } else {
            $update_image = $conn->prepare("UPDATE `products` SET image = ? WHERE product_id = ?");
            $update_image->execute([$image_name, $product_id]);

            if ($update_image) {
                move_uploaded_file($image_tmp_name, $image_folder);
                if (file_exists('uploaded_img/' . $old_image)) {
                    unlink('uploaded_img/' . $old_image);
                }
                $message[] = 'Image updated successfully!';
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Product</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="css/admin_style.css">
</head>
<body>

<?php include 'vendor_header.php'; ?>

<section class="update-product">
    <h1 class="title">Update Product</h1>

    <?php
    if (isset($_GET['update'])) {
        $update_id = filter_var($_GET['update'], FILTER_SANITIZE_NUMBER_INT);
        $select_products = $conn->prepare("SELECT * FROM `products` WHERE product_id = ? AND vendor_id = ?");
        $select_products->execute([$update_id, $vendor_id]);

        if ($select_products->rowCount() > 0) {
            $fetch_products = $select_products->fetch(PDO::FETCH_ASSOC);
    ?>
    <form action="" method="post" enctype="multipart/form-data">
        <input type="hidden" name="old_image" value="<?= htmlspecialchars($fetch_products['image']); ?>">
        <input type="hidden" name="product_id" value="<?= htmlspecialchars($fetch_products['product_id']); ?>">
        <img src="uploaded_img/<?= htmlspecialchars($fetch_products['image']); ?>" alt="">
        <input type="text" name="name" placeholder="Enter product name" required class="box" value="<?= htmlspecialchars($fetch_products['name']); ?>">
        <input type="number" name="price" min="0" placeholder="Enter product price" required class="box" value="<?= htmlspecialchars($fetch_products['price']); ?>">
        <select name="category" class="box" required>
            <option selected><?= htmlspecialchars($fetch_products['category']); ?></option>
            <option value="stationery">Stationery</option>
            <option value="instant food">Instant Food</option>
            <option value="bakery">Bakery</option>
            <option value="drinks">Drinks</option>
        </select>
    <input type="number" id="stock" name="stock" class="box" value="<?= htmlspecialchars($fetch_products['stock']); ?>" min="0" required>
            <textarea name="description" required placeholder="Enter product description" class="box" cols="30" rows="10"><?= htmlspecialchars($fetch_products['description']); ?></textarea>
        <input type="file" name="image" class="box" accept="image/jpg, image/jpeg, image/png">
        <div class="flex-btn">
            <input type="submit" class="btn" value="Update Product" name="update_product">
            <a href="vendor_product.php" class="option-btn">Go Back</a>
        </div>
    </form>
    <?php
        } else {
            echo '<p class="empty">No product found!</p>';
        }
    } else {
        echo '<p class="empty">No product ID specified!</p>';
    }
    ?>

</section>

<script src="js/script.js"></script>

</body>
</html>
