<?php

@include 'config.php';

session_start();

$user_id = $_SESSION['user_id'] ?? 0;

if (!$user_id) {
    header('Location: login.php');
    exit;
}

$message = [];

if (isset($_POST['add_to_wishlist'])) {
    $product_id = filter_input(INPUT_POST, 'product_id', FILTER_SANITIZE_STRING);
    $p_name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $p_price = filter_input(INPUT_POST, 'price', FILTER_SANITIZE_STRING);
    $p_image = filter_input(INPUT_POST, 'image', FILTER_SANITIZE_STRING);

    if ($product_id && $p_name && $p_price && $p_image) {
        $check_wishlist = $conn->prepare("SELECT * FROM `wishlist` WHERE product_id = ? AND user_id = ?");
        $check_wishlist->execute([$product_id, $user_id]);

        $check_cart = $conn->prepare("SELECT * FROM `carts` WHERE product_id = ? AND user_id = ?");
        $check_cart->execute([$product_id, $user_id]);

        if ($check_wishlist->rowCount() > 0) {
            $message[] = 'Already added to wishlist!';
        } elseif ($check_cart->rowCount() > 0) {
            $message[] = 'Already added to cart!';
        } else {
            $insert_wishlist = $conn->prepare("INSERT INTO `wishlist` (user_id, product_id) VALUES (?, ?)");
            $insert_wishlist->execute([$user_id, $product_id]);
            $message[] = 'Added to wishlist!';
        }
    } else {
        $message[] = 'Invalid product data!';
    }
}

if (isset($_POST['add_to_cart'])) {
    $product_id = filter_input(INPUT_POST, 'product_id', FILTER_SANITIZE_STRING);
    $p_name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $p_price = filter_input(INPUT_POST, 'price', FILTER_SANITIZE_STRING);
    $p_image = filter_input(INPUT_POST, 'image', FILTER_SANITIZE_STRING);
    $p_qty = filter_input(INPUT_POST, 'quantity', FILTER_SANITIZE_NUMBER_INT);

    if ($product_id && $p_name && $p_price && $p_image && $p_qty > 0) {
        $check_cart = $conn->prepare("SELECT * FROM `carts` WHERE product_id = ? AND user_id = ?");
        $check_cart->execute([$product_id, $user_id]);

        if ($check_cart->rowCount() > 0) {
            $message[] = 'Already added to cart!';
        } else {
            $check_wishlist = $conn->prepare("SELECT * FROM `wishlist` WHERE product_id = ? AND user_id = ?");
            $check_wishlist->execute([$product_id, $user_id]);

            if ($check_wishlist->rowCount() > 0) {
                $delete_wishlist = $conn->prepare("DELETE FROM `wishlist` WHERE product_id = ? AND user_id = ?");
                $delete_wishlist->execute([$product_id, $user_id]);
            }

            $insert_cart = $conn->prepare("INSERT INTO `carts` (user_id, product_id, name, price, quantity, image) VALUES (?, ?, ?, ?, ?, ?)");
            $insert_cart->execute([$user_id, $product_id, $p_name, $p_price, $p_qty, $p_image]);
            $message[] = 'Added to cart!';
        }
    } else {
        $message[] = 'Invalid product data or quantity!';
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop</title>

    <!-- Font Awesome CDN link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <!-- Custom CSS file link -->
    <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<?php include 'header.php'; ?>

<section class="p-category">
    <a href="category.php?category=Stationery">Stationery</a>
    <a href="category.php?category=Instant Foods">Instant Foods</a>
    <a href="category.php?category=Bakery">Bakery</a>
    <a href="category.php?category=Drinks">Drinks</a>
</section>

<section class="products">
    <h1 class="title">All Products</h1>
    <div class="box-container">
        <?php
        $select_products = $conn->prepare("SELECT * FROM `products`");
        $select_products->execute();
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
            <input type="hidden" name="name" value="<?= htmlspecialchars($fetch_products['name']); ?>">
            <input type="hidden" name="price" value="<?= htmlspecialchars($fetch_products['price']); ?>">
            <input type="hidden" name="image" value="<?= htmlspecialchars($fetch_products['image']); ?>">
            <input type="number" min="1" value="1" name="quantity" class="qty">
            <input type="submit" value="Add to Wishlist" class="option-btn" name="add_to_wishlist">
            <input type="submit" value="Add to Cart" class="btn" name="add_to_cart">
        </form>
        <?php
            }
        } else {
            echo '<p class="empty">No products added yet!</p>';
        }
        ?>
    </div>
</section>

<?php include 'footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>
