<?php

@include 'config.php';

session_start();

$user_id = $_SESSION['user_id'];

if (!isset($user_id)) {
    header('location:login.php');
    exit();
}

if (isset($_GET['delete'])) {
    $delete_id = filter_var($_GET['delete'], FILTER_VALIDATE_INT);
    if ($delete_id !== false) {
        $delete_cart_item = $conn->prepare("DELETE FROM `carts` WHERE cart_id = ?");
        $delete_cart_item->execute([$delete_id]);
        header('location:cart.php');
        exit();
    }
}

if (isset($_GET['delete_all'])) {
    $delete_cart_item = $conn->prepare("DELETE FROM `carts` WHERE user_id = ?");
    $delete_cart_item->execute([$user_id]);
    header('location:cart.php');
    exit();
}

if (isset($_POST['update_qty'])) {
    $cart_id = filter_var($_POST['cart_id'], FILTER_VALIDATE_INT);
    $p_qty = filter_var($_POST['p_qty'], FILTER_VALIDATE_INT);
    if ($p_qty !== false && $p_qty > 0) {
        $update_qty = $conn->prepare("UPDATE `carts` SET quantity = ? WHERE cart_id = ?");
        $update_qty->execute([$p_qty, $cart_id]);
        $message[] = 'Cart quantity updated';
    } else {
        $message[] = 'Invalid quantity';
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Shopping Cart</title>

   <!-- Font Awesome CDN link -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- Custom CSS file link -->
   <link rel="stylesheet" href="css/style.css">
</head>
<body>
   
<?php include 'header.php'; ?>

<section class="shopping-cart">
   <h1 class="title">Products Added</h1>

   <div class="box-container">
   <?php
      $grand_total = 0;
      $select_cart = $conn->prepare("SELECT * FROM `carts` WHERE user_id = ?");
      $select_cart->execute([$user_id]);
      if ($select_cart->rowCount() > 0) {
         while ($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)) { 
   ?>
   <form action="" method="POST" class="box">
      <a href="cart.php?delete=<?= htmlspecialchars($fetch_cart['cart_id']); ?>" class="fas fa-times" onclick="return confirm('Delete this from cart?');"></a>
      <a href="view_page.php?product_id=<?= htmlspecialchars($fetch_cart['product_id']); ?>" class="fas fa-eye"></a>
      <img src="uploaded_img/<?= htmlspecialchars($fetch_cart['image']); ?>" alt="">
      <div class="name"><?= htmlspecialchars($fetch_cart['name']); ?></div>
      <div class="price">Rs.<?= htmlspecialchars($fetch_cart['price']); ?>/-</div>
      <input type="hidden" name="cart_id" value="<?= htmlspecialchars($fetch_cart['cart_id']); ?>">
      <div class="flex-btn">
         <input type="number" min="1" value="<?= htmlspecialchars($fetch_cart['quantity']); ?>" class="qty" name="p_qty">
         <input type="submit" value="Update" name="update_qty" class="option-btn">
      </div>
      <div class="sub-total"> Sub Total: <span>Rs.<?= $sub_total = ($fetch_cart['price'] * $fetch_cart['quantity']); ?>/-</span> </div>
   </form>
   <?php
      $grand_total += $sub_total;
      }
   } else {
      echo '<p class="empty">Your cart is empty</p>';
   }
   ?>
   </div>

   <div class="cart-total">
      <p>Grand Total: <span>Rs.<?= htmlspecialchars($grand_total); ?>/-</span></p>
      <a href="shop.php" class="option-btn">Continue Shopping</a>
      <a href="cart.php?delete_all" class="delete-btn <?= ($grand_total > 0) ? '' : 'disabled'; ?>">Delete All</a>
      <a href="checkout.php" class="btn <?= ($grand_total > 0) ? '' : 'disabled'; ?>">Proceed to Checkout</a>
   </div>
</section>

<?php include 'footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>
