<?php

@include 'config.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
    header('location:login.php');
    exit();
}

if (isset($_POST['update_product'])) {
    $pid = filter_var($_POST['pid'], FILTER_SANITIZE_NUMBER_INT);
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $price = filter_var($_POST['price'], FILTER_VALIDATE_FLOAT);
    $category = filter_var($_POST['category'], FILTER_SANITIZE_STRING);
    $details = filter_var($_POST['details'], FILTER_SANITIZE_STRING);
    $stock = filter_var($_POST['stock'], FILTER_SANITIZE_NUMBER_INT);

    $image = $_FILES['image']['name'];
    $image_size = $_FILES['image']['size'];
    $image_tmp_name = $_FILES['image']['tmp_name'];
    $image_folder = 'uploaded_img/' . basename($image);
    $old_image = filter_var($_POST['old_image'], FILTER_SANITIZE_STRING);

    $allowed_extensions = ['jpg', 'jpeg', 'png'];
    $file_extension = pathinfo($image, PATHINFO_EXTENSION);

    try {
        $conn->beginTransaction();

        // Update product details
        $update_product = $conn->prepare("UPDATE `products` SET name = ?, description = ?, price = ?, stock = ?, updated_at = NOW() WHERE product_id = ?");
        $update_product->execute([$name, $details, $price, $stock, $pid]);

        // If a new image is uploaded
        if (!empty($image)) {
            if ($image_size > 2000000) {
                $message[] = 'Image size is too large!';
            } elseif (!in_array($file_extension, $allowed_extensions)) {
                $message[] = 'Invalid image format! Please upload JPG, JPEG, or PNG images only.';
            } else {
                $update_image = $conn->prepare("UPDATE `products` SET image = ? WHERE product_id = ?");
                $update_image->execute([$image, $pid]);

                if ($update_image) {
                    move_uploaded_file($image_tmp_name, $image_folder);
                    if (!empty($old_image)) {
                        unlink('uploaded_img/' . $old_image);
                    }
                    $message[] = 'Image updated successfully!';
                }
            }
        }

        $conn->commit();
        $message[] = 'Product updated successfully!';
    } catch (PDOException $e) {
        $conn->rollBack();
        $message[] = 'Error: ' . $e->getMessage();
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

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/admin_style.css">

</head>
<body>
   
<?php include 'admin_header.php'; ?>

<section class="update-product">

   <h1 class="title">Update Product</h1>   

   <?php
      $update_id = filter_var($_GET['update'], FILTER_SANITIZE_NUMBER_INT);
      $select_products = $conn->prepare("SELECT * FROM `products` WHERE product_id = ?");
      $select_products->execute([$update_id]);
      if ($select_products->rowCount() > 0) {
         while ($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)) { 
   ?>
   <form action="" method="post" enctype="multipart/form-data">
      <input type="hidden" name="old_image" value="<?= htmlspecialchars($fetch_products['image']); ?>">
      <input type="hidden" name="pid" value="<?= htmlspecialchars($fetch_products['product_id']); ?>">
      <img src="uploaded_img/<?= htmlspecialchars($fetch_products['image']); ?>" alt="">
      <input type="text" name="name" placeholder="Enter product name" required class="box" value="<?= htmlspecialchars($fetch_products['name']); ?>">
      <input type="number" name="price" min="0" step="0.01" placeholder="Enter product price" required class="box" value="<?= htmlspecialchars($fetch_products['price']); ?>">
      <input type="number" name="stock" min="0" placeholder="Enter product stock" required class="box" value="<?= htmlspecialchars($fetch_products['stock']); ?>">
      <select name="category" class="box" required>
         <option selected><?= htmlspecialchars($fetch_products['category']); ?></option>
         <option value="vegetables">Vegetables</option>
         <option value="fruits">Fruits</option>
         <option value="meat">Meat</option>
         <option value="fish">Fish</option>
      </select>
      <textarea name="details" required placeholder="Enter product details" class="box" cols="30" rows="10"><?= htmlspecialchars($fetch_products['description']); ?></textarea>
      <input type="file" name="image" class="box" accept="image/jpg, image/jpeg, image/png">
      <div class="flex-btn">
         <input type="submit" class="btn" value="Update Product" name="update_product">
         <a href="admin_products.php" class="option-btn">Go Back</a>
      </div>
   </form>
   <?php
         }
      } else {
         echo '<p class="empty">No products found!</p>';
      }
   ?>

</section>

<script src="js/script.js"></script>

</body>
</html>
