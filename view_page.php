<?php

@include 'config.php';

session_start();

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    header('Location: login.php');
    exit();
}

// Initialize message array
$message = [];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add to wishlist
    if (isset($_POST['add_to_wishlist'])) {
        $product_id = filter_input(INPUT_POST, 'pid', FILTER_SANITIZE_NUMBER_INT);
        $p_name = filter_input(INPUT_POST, 'p_name', FILTER_SANITIZE_STRING);
        $p_price = filter_input(INPUT_POST, 'p_price', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $p_image = filter_input(INPUT_POST, 'p_image', FILTER_SANITIZE_STRING);

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
                $insert_wishlist = $conn->prepare("INSERT INTO `wishlist` (user_id, product_id, name, price, image) VALUES (?, ?, ?, ?, ?)");
                $insert_wishlist->execute([$user_id, $product_id, $p_name, $p_price, $p_image]);
                $message[] = 'Added to wishlist!';
            }
        } else {
            $message[] = 'Failed to add to wishlist. Invalid data.';
        }
    }

    // Add to cart
    if (isset($_POST['add_to_cart'])) {
        $product_id = filter_input(INPUT_POST, 'pid', FILTER_SANITIZE_NUMBER_INT);
        $p_name = filter_input(INPUT_POST, 'p_name', FILTER_SANITIZE_STRING);
        $p_price = filter_input(INPUT_POST, 'p_price', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $p_image = filter_input(INPUT_POST, 'p_image', FILTER_SANITIZE_STRING);
        $p_qty = filter_input(INPUT_POST, 'p_qty', FILTER_SANITIZE_NUMBER_INT);

        if ($product_id && $p_name && $p_price && $p_image && $p_qty) {
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
            $message[] = 'Failed to add to cart. Invalid data.';
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
    <title>Quick View</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .product-box {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        .product-box img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
        }
        .product-box .vendor {
            font-size: 1.8em; /* Vendor size */
            margin: 10px 0;
        }
        .product-box .details {
            font-size: 2em; /* Details size */
            margin: 10px 0;
        }
        .product-box .price {
            font-size: 2em; /* Price size */
            color: #333;
        }
        .product-box .name {
            font-size: 3em; /* Product name size */
            font-weight: bold;
        }
        .product-box input.qty {
            font-size: 1.5em; /* Quantity input size */
            width: 80px; /* Fixed width */
            text-align: center;
        }
        .product-box .stock {
            font-size: 1.8em; /* Stock size */
            margin: 10px 0;
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<section class="quick-view">
    <h1 class="title">Quick View</h1>

    <?php
    // Ensure 'product_id' parameter is set and valid
    $product_id = filter_input(INPUT_GET, 'product_id', FILTER_VALIDATE_INT);

    if ($product_id === null) {
        echo '<p class="empty">Product ID is not set or is invalid.</p>';
    } elseif ($product_id === false) {
        echo '<p class="empty">Product ID is not an integer.</p>';
    } else {
        // Fetch product details
        $select_products = $conn->prepare("SELECT product_id, name, price, image, description, stock, vendor_name 
            FROM `products` 
            WHERE product_id = ?");
        $select_products->execute([$product_id]);

        if ($select_products->rowCount() > 0) {
            $fetch_products = $select_products->fetch(PDO::FETCH_ASSOC);
    ?>
    <div class="product-box">
        <img src="uploaded_img/<?= htmlspecialchars($fetch_products['image']); ?>" alt="<?= htmlspecialchars($fetch_products['name']); ?>">
        <div class="name"><?= htmlspecialchars($fetch_products['name']); ?></div>
        <div class="price">Rs.<span><?= htmlspecialchars($fetch_products['price']); ?></span>/-</div>
        <div class="vendor">Vendor: <?= htmlspecialchars($fetch_products['vendor_name']); ?></div>
        <div class="details">Details: <?= htmlspecialchars($fetch_products['description']); ?></div>
        <div class="stock">Quantity in Stock: <?= htmlspecialchars($fetch_products['stock']); ?></div>
        <form action="" class="box" method="POST">
            <input type="hidden" name="pid" value="<?= htmlspecialchars($fetch_products['product_id']); ?>">
            <input type="hidden" name="p_name" value="<?= htmlspecialchars($fetch_products['name']); ?>">
            <input type="hidden" name="p_price" value="<?= htmlspecialchars($fetch_products['price']); ?>">
            <input type="hidden" name="p_image" value="<?= htmlspecialchars($fetch_products['image']); ?>">
            <input type="number" min="1" value="1" name="p_qty" class="qty">
            <input type="submit" value="Add to Wishlist" class="option-btn" name="add_to_wishlist">
            <input type="submit" value="Add to Cart" class="btn" name="add_to_cart">
        </form>
    </div>
    <?php
        } else {
            echo '<p class="empty">No products found!</p>';
        }
    }
    ?>
</section>

<?php include 'footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>
