<?php

@include 'config.php';

session_start();

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    header('Location: login.php');
    exit;
}

if (isset($_POST['add_to_wishlist'])) {
    $pid = filter_var($_POST['product_id'], FILTER_SANITIZE_STRING);
    $p_name = filter_var($_POST['p_name'], FILTER_SANITIZE_STRING);
    $p_price = filter_var($_POST['p_price'], FILTER_SANITIZE_STRING);
    $p_image = filter_var($_POST['p_image'], FILTER_SANITIZE_STRING);

    $check_wishlist_numbers = $conn->prepare("SELECT * FROM `wishlist` WHERE name = ? AND user_id = ?");
    $check_wishlist_numbers->execute([$p_name, $user_id]);

    $check_cart_numbers = $conn->prepare("SELECT * FROM `carts` WHERE name = ? AND user_id = ?");
    $check_cart_numbers->execute([$p_name, $user_id]);

    if ($check_wishlist_numbers->rowCount() > 0) {
        $message[] = 'Already added to wishlist!';
    } elseif ($check_cart_numbers->rowCount() > 0) {
        $message[] = 'Already added to cart!';
    } else {
        $insert_wishlist = $conn->prepare("INSERT INTO `wishlist` (user_id, product_id, name, price, image) VALUES (?, ?, ?, ?, ?)");
        $insert_wishlist->execute([$user_id, $pid, $p_name, $p_price, $p_image]);
        $message[] = 'Added to wishlist!';
    }
}

if (isset($_POST['add_to_cart'])) {
    $pid = filter_var($_POST['product_id'], FILTER_SANITIZE_STRING);
    $p_name = filter_var($_POST['p_name'], FILTER_SANITIZE_STRING);
    $p_price = filter_var($_POST['p_price'], FILTER_SANITIZE_STRING);
    $p_image = filter_var($_POST['p_image'], FILTER_SANITIZE_STRING);
    $p_qty = filter_var($_POST['p_qty'], FILTER_SANITIZE_STRING);

    $check_cart_numbers = $conn->prepare("SELECT * FROM `carts` WHERE name = ? AND user_id = ?");
    $check_cart_numbers->execute([$p_name, $user_id]);

    if ($check_cart_numbers->rowCount() > 0) {
        $message[] = 'Already added to cart!';
    } else {
        $check_wishlist_numbers = $conn->prepare("SELECT * FROM `wishlist` WHERE name = ? AND user_id = ?");
        $check_wishlist_numbers->execute([$p_name, $user_id]);

        if ($check_wishlist_numbers->rowCount() > 0) {
            $delete_wishlist = $conn->prepare("DELETE FROM `wishlist` WHERE name = ? AND user_id = ?");
            $delete_wishlist->execute([$p_name, $user_id]);
        }

        $insert_cart = $conn->prepare("INSERT INTO `carts` (user_id, product_id, name, price, quantity, image) VALUES (?, ?, ?, ?, ?, ?)");
        $insert_cart->execute([$user_id, $pid, $p_name, $p_price, $p_qty, $p_image]);
        $message[] = 'Added to cart!';
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Page</title>

    <!-- Font Awesome CDN link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <!-- Custom CSS file link -->
    <link rel="stylesheet" href="css/style.css">

</head>
<body>

<?php include 'header.php'; ?>

<section class="search-form">
    <form action="" method="POST">
        <input type="text" class="box" name="search_box" placeholder="Search products...">
        <input type="submit" name="search_btn" value="Search" class="btn">
    </form>
</section>

<section class="products" style="padding-top: 0; min-height: 100vh;">
    <div class="box-container">

    <?php
    if (isset($_POST['search_btn'])) {
        $search_box = filter_var($_POST['search_box'], FILTER_SANITIZE_STRING);
        $select_products = $conn->prepare("SELECT * FROM `products` WHERE name LIKE ? OR category LIKE ?");
        $select_products->execute(["%{$search_box}%", "%{$search_box}%"]);
        if ($select_products->rowCount() > 0) {
            while ($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)) {
    ?>
    <form action="" class="box" method="POST">
        <div class="price">Rs.<span><?= htmlspecialchars($fetch_products['price']); ?></span>/-</div>
        <a href="view_page.php?product_id=<?= htmlspecialchars($fetch_products['product_id']); ?>" class="fas fa-eye"></a>
        <img src="uploaded_img/<?= htmlspecialchars($fetch_products['image']); ?>" alt="">
        <div class="product-details" style="padding: 10px;">
                <div class="name" style="font-size: 2.1em; margin-bottom: 10px;">
                    <?= htmlspecialchars($fetch_products['name']); ?>
                </div>
                <div class="details" style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 1.7em; color: #666; margin-right: 20px;">
                        Quantity: <?= htmlspecialchars($fetch_products['stock']); ?>
                    </span>
                    <span style="font-size: 1.7em; color: #006400;">
                        Vendor: <?= htmlspecialchars($fetch_products['vendor_name']); ?>
                    </span>
                </div>
            </div>
        <input type="hidden" name="product_id" value="<?= htmlspecialchars($fetch_products['product_id']); ?>">
        <input type="hidden" name="p_name" value="<?= htmlspecialchars($fetch_products['name']); ?>">
        <input type="hidden" name="p_price" value="<?= htmlspecialchars($fetch_products['price']); ?>">
        <input type="hidden" name="p_image" value="<?= htmlspecialchars($fetch_products['image']); ?>">
        <input type="number" min="1" value="1" name="p_qty" class="qty">
        <input type="submit" value="Add to wishlist" class="option-btn" name="add_to_wishlist">
        <input type="submit" value="Add to cart" class="btn" name="add_to_cart">
    </form>
    <?php
            }
        } else {
            echo '<p class="empty">No results found!</p>';
        }
    }
    ?>

    </div>
</section>

<?php include 'footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>
