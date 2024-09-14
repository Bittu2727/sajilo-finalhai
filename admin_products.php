<?php

@include 'config.php';

session_start();

$admin_id = isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : null;

if (!$admin_id) {
   header('location:login.php');
   exit();
}

if (isset($_GET['delete'])) {

   $delete_id = $_GET['delete'];
   $select_delete_image = $conn->prepare("SELECT image FROM `products` WHERE product_id = ?");
   $select_delete_image->execute([$delete_id]);
   $fetch_delete_image = $select_delete_image->fetch(PDO::FETCH_ASSOC);
   
   if ($fetch_delete_image) {
      unlink('uploaded_img/' . $fetch_delete_image['image']);
      $delete_products = $conn->prepare("DELETE FROM `products` WHERE product_id = ?");
      $delete_products->execute([$delete_id]);
      $delete_wishlist = $conn->prepare("DELETE FROM `wishlist` WHERE product_id = ?");
      $delete_wishlist->execute([$delete_id]);
      $delete_cart = $conn->prepare("DELETE FROM `carts` WHERE product_id = ?");
      $delete_cart->execute([$delete_id]);
   }
   
   header('location:admin_products.php');
   exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Products</title>

   <!-- Font Awesome CDN link -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- Custom CSS file link -->
   <link rel="stylesheet" href="css/admin_style.css">

</head>
<body>
   
<?php include 'admin_header.php'; ?>

<section class="show-products">

   <h1 class="title">Products Added</h1>

   <div class="box-container">

   <?php
      $show_products = $conn->prepare("SELECT * FROM `products`");
      $show_products->execute();
      if ($show_products->rowCount() > 0) {
         while ($fetch_products = $show_products->fetch(PDO::FETCH_ASSOC)) {
   ?>
   <div class="box">
      <div class="price">Rs. <?= htmlspecialchars($fetch_products['price']); ?>/-</div>
      <img src="uploaded_img/<?= htmlspecialchars($fetch_products['image']); ?>" alt="">
      <div class="name"><?= htmlspecialchars($fetch_products['name']); ?></div>
      <div class="cat"><?= htmlspecialchars($fetch_products['category']); ?></div>
      <div class="description"><?= htmlspecialchars($fetch_products['description']); ?></div>
      <div class="flex-btn">
         <a href="admin_update_product.php?update=<?= htmlspecialchars($fetch_products['product_id']); ?>" class="option-btn">Update</a>
         <a href="admin_products.php?delete=<?= htmlspecialchars($fetch_products['product_id']); ?>" class="delete-btn" onclick="return confirm('Delete this product?');">Delete</a>
      </div>
   </div>
   <?php
         }
      } else {
         echo '<p class="empty">No products added yet!</p>';
      }
   ?>

   </div>

</section>

<script src="js/script.js"></script>

</body>
</html>
