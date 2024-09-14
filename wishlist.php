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

// Handle adding to cart
if (isset($_POST['add_to_cart'])) {
    $product_id = filter_input(INPUT_POST, 'product_id', FILTER_SANITIZE_STRING);
    $p_name = filter_input(INPUT_POST, 'p_name', FILTER_SANITIZE_STRING);
    $p_price = filter_input(INPUT_POST, 'p_price', FILTER_SANITIZE_STRING);
    $p_image = filter_input(INPUT_POST, 'p_image', FILTER_SANITIZE_STRING);
    $p_qty = filter_input(INPUT_POST, 'p_qty', FILTER_SANITIZE_NUMBER_INT);

    $check_cart = $conn->prepare("SELECT * FROM `carts` WHERE name = ? AND user_id = ?");
    $check_cart->execute([$p_name, $user_id]);

    if ($check_cart->rowCount() > 0) {
        $message[] = 'Already added to cart!';
    } else {
        $check_wishlist = $conn->prepare("SELECT * FROM `wishlist` WHERE name = ? AND user_id = ?");
        $check_wishlist->execute([$p_name, $user_id]);

        if ($check_wishlist->rowCount() > 0) {
            $delete_wishlist = $conn->prepare("DELETE FROM `wishlist` WHERE name = ? AND user_id = ?");
            $delete_wishlist->execute([$p_name, $user_id]);
        }

        $insert_cart = $conn->prepare("INSERT INTO `carts` (user_id, product_id, name, price, quantity, image) VALUES (?, ?, ?, ?, ?, ?)");
        $insert_cart->execute([$user_id, $product_id, $p_name, $p_price, $p_qty, $p_image]);
        $message[] = 'Added to cart!';
    }
}

// Handle adding to wishlist
if (isset($_POST['add_to_wishlist'])) {
    $product_id = filter_input(INPUT_POST, 'product_id', FILTER_SANITIZE_STRING);
    $p_name = filter_input(INPUT_POST, 'p_name', FILTER_SANITIZE_STRING);
    $p_price = filter_input(INPUT_POST, 'p_price', FILTER_SANITIZE_STRING);
    $p_image = filter_input(INPUT_POST, 'p_image', FILTER_SANITIZE_STRING);

    // Check if the product is already in the wishlist
    $check_wishlist = $conn->prepare("SELECT * FROM `wishlist` WHERE product_id = ? AND user_id = ?");
    $check_wishlist->execute([$product_id, $user_id]);

    if ($check_wishlist->rowCount() > 0) {
        $message[] = 'Already in wishlist!';
    } else {
        // Insert into the wishlist table with all necessary columns
        $insert_wishlist = $conn->prepare("INSERT INTO `wishlist` (user_id, product_id, name, price, image) VALUES (?, ?, ?, ?, ?)");
        $insert_wishlist->execute([$user_id, $product_id, $p_name, $p_price, $p_image]);
        $message[] = 'Added to wishlist!';
    }
}

// Handle single item deletion
if (isset($_GET['delete'])) {
    $delete_id = filter_input(INPUT_GET, 'delete', FILTER_SANITIZE_NUMBER_INT);
    if ($delete_id) {
        $delete_wishlist_item = $conn->prepare("DELETE FROM `wishlist` WHERE product_id = ?");
        $delete_wishlist_item->execute([$delete_id]);
    }
    header('Location: wishlist.php');
    exit();
}

// Handle all items deletion
if (isset($_GET['delete_all'])) {
    $delete_wishlist_item = $conn->prepare("DELETE FROM `wishlist` WHERE user_id = ?");
    $delete_wishlist_item->execute([$user_id]);
    header('Location: wishlist.php');
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wishlist</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'header.php'; ?>

<section class="wishlist">
    <h1 class="title">Products Added</h1>

    <div class="box-container">
        <?php
        $grand_total = 0;
        $select_wishlist = $conn->prepare("SELECT 
                w.wishlist_id AS wishlist_id, 
                u.username AS name, 
                p.product_id AS product_id, 
                p.name AS name, 
                p.price AS price, 
                p.image AS image
            FROM 
                wishlist w
            JOIN 
                users u ON w.user_id = u.user_id
            JOIN 
                products p ON w.product_id = p.product_id
            WHERE 
                w.user_id = ?
        ");
        $select_wishlist->execute([$user_id]);

        if ($select_wishlist->rowCount() > 0) {
            while ($fetch_wishlist = $select_wishlist->fetch(PDO::FETCH_ASSOC)) {
        ?>
        <form action="" method="POST" class="box">
            <a href="wishlist.php?delete=<?= htmlspecialchars($fetch_wishlist['product_id']); ?>" class="fas fa-times" onclick="return confirm('Delete this from wishlist?');"></a>
            <a href="view_page.php?pid=<?= htmlspecialchars($fetch_wishlist['product_id']); ?>" class="fas fa-eye"></a>
            <img src="uploaded_img/<?= htmlspecialchars($fetch_wishlist['image']); ?>" alt="">
            <div class="name"><?= htmlspecialchars($fetch_wishlist['name']); ?></div>
            <div class="price">Rs.<?= htmlspecialchars($fetch_wishlist['price']); ?>/-</div>
            <input type="number" min="1" value="1" class="qty" name="p_qty">
            <input type="hidden" name="pid" value="<?= htmlspecialchars($fetch_wishlist['product_id']); ?>">
            <input type="hidden" name="p_name" value="<?= htmlspecialchars($fetch_wishlist['name']); ?>">
            <input type="hidden" name="p_price" value="<?= htmlspecialchars($fetch_wishlist['price']); ?>">
            <input type="hidden" name="p_image" value="<?= htmlspecialchars($fetch_wishlist['image']); ?>">
            <input type="submit" value="Add to Cart" name="add_to_cart" class="btn">
        </form>
        <?php
                $grand_total += $fetch_wishlist['price'];
            }
        } else {
            echo '<p class="empty">Your wishlist is empty</p>';
        }
        ?>
    </div>

    <div class="wishlist-total">
        <p>Grand Total : <span>Rs.<?= htmlspecialchars($grand_total); ?>/-</span></p>
        <a href="shop.php" class="option-btn">Continue Shopping</a>
        <a href="wishlist.php?delete_all" class="delete-btn <?= ($grand_total > 0) ? '' : 'disabled'; ?>">Delete All</a>
    </div>
</section>

<?php include 'footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>