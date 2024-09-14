<?php
@include 'config.php'; // Database connection

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$message = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_to_wishlist'])) {
        $pid = filter_var($_POST['product_id'], FILTER_SANITIZE_STRING);
        $p_name = filter_var($_POST['p_name'], FILTER_SANITIZE_STRING);
        $p_price = filter_var($_POST['p_price'], FILTER_SANITIZE_STRING);
        $p_image = filter_var($_POST['p_image'], FILTER_SANITIZE_STRING);

        // Check if the product is already in the wishlist
        $stmt = $conn->prepare("SELECT * FROM `wishlist` WHERE product_id = ? AND user_id = ?");
        $stmt->execute([$pid, $user_id]);

        // Check if the product is already in the cart
        $stmt2 = $conn->prepare("SELECT * FROM `carts` WHERE product_id = ? AND user_id = ?");
        $stmt2->execute([$pid, $user_id]);

        if ($stmt->rowCount() > 0) {
            $message[] = 'Already added to wishlist!';
        } elseif ($stmt2->rowCount() > 0) {
            $message[] = 'Already added to cart!';
        } else {
            $stmt3 = $conn->prepare("INSERT INTO `wishlist` (user_id, product_id, name, price, image) VALUES (?, ?, ?, ?, ?)");
            $stmt3->execute([$user_id, $pid, $p_name, $p_price, $p_image]);
            $message[] = 'Added to wishlist!';
        }
    }

    if (isset($_POST['add_to_cart'])) {
        $pid = filter_var($_POST['pid'], FILTER_SANITIZE_STRING);
        $p_name = filter_var($_POST['p_name'], FILTER_SANITIZE_STRING);
        $p_price = filter_var($_POST['p_price'], FILTER_SANITIZE_STRING);
        $p_image = filter_var($_POST['p_image'], FILTER_SANITIZE_STRING);
        $p_qty = filter_var($_POST['p_qty'], FILTER_SANITIZE_NUMBER_INT);

        // Check if the product is already in the cart
        $stmt = $conn->prepare("SELECT * FROM `carts` WHERE product_id = ? AND user_id = ?");
        $stmt->execute([$pid, $user_id]);

        if ($stmt->rowCount() > 0) {
            $message[] = 'Already added to cart!';
        } else {
            // Remove from wishlist if present
            $stmt2 = $conn->prepare("SELECT * FROM `wishlist` WHERE product_id = ? AND user_id = ?");
            $stmt2->execute([$pid, $user_id]);

            if ($stmt2->rowCount() > 0) {
                $stmt3 = $conn->prepare("DELETE FROM `wishlist` WHERE product_id = ? AND user_id = ?");
                $stmt3->execute([$pid, $user_id]);
            }

            $stmt4 = $conn->prepare("INSERT INTO `carts` (user_id, product_id, name, price, quantity, image) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt4->execute([$user_id, $pid, $p_name, $p_price, $p_qty, $p_image]);
            $message[] = 'Added to cart!';
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
    <title>Category</title>

    <!-- Font Awesome CDN link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <!-- Custom CSS file link -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
   
<?php include 'header.php'; ?>

<section class="products">
    <h1 class="title">Products Categories</h1>

    <div class="box-container">
        <?php
        // Check if the 'category' parameter exists in the URL
        if (isset($_GET['category'])) {
            $category_name = filter_var($_GET['category'], FILTER_SANITIZE_STRING);
            $stmt = $conn->prepare("SELECT * FROM `products` WHERE category = ?");
            $stmt->execute([$category_name]);

            if ($stmt->rowCount() > 0) {
                while ($product = $stmt->fetch(PDO::FETCH_ASSOC)) { 
        ?>
        <form action="" class="box" method="POST">
            <div class="price">Rs.<span><?= htmlspecialchars($product['price']); ?></span>/-</div>
            <a href="view_page.php?product_id=<?= htmlspecialchars($product['product_id']); ?>" class="fas fa-eye"></a>
            <img src="uploaded_img/<?= htmlspecialchars($product['image']); ?>" alt="">
            <div class="name"><?= htmlspecialchars($product['name']); ?></div>
            <input type="hidden" name="product_id" value="<?= htmlspecialchars($product['product_id']); ?>">
            <input type="hidden" name="p_name" value="<?= htmlspecialchars($product['name']); ?>">
            <input type="hidden" name="p_price" value="<?= htmlspecialchars($product['price']); ?>">
            <input type="hidden" name="p_image" value="<?= htmlspecialchars($product['image']); ?>">
            <input type="number" min="1" value="1" name="p_qty" class="qty">
            <input type="submit" value="Add to Wishlist" class="option-btn" name="add_to_wishlist">
            <input type="submit" value="Add to Cart" class="btn" name="add_to_cart">
        </form>
        <?php
                }
            } else {
                echo '<p class="empty">No products available!</p>';
            }
        } else {
            echo '<p class="empty">Category not specified!</p>';
        }
        ?>
    </div>
</section>

<?php include 'footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>
