<?php
@include 'config.php';

session_start();

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;


if (isset($_POST['add_to_wishlist'])) {
    $product_id = filter_var($_POST['product_id'], FILTER_SANITIZE_STRING);
    $p_name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $p_price = filter_var($_POST['price'], FILTER_SANITIZE_STRING);
    $p_image = filter_var($_POST['image'], FILTER_SANITIZE_STRING);

    $check_wishlist_numbers = $conn->prepare("SELECT * FROM `wishlist` WHERE name = ? AND user_id = ?");
    $check_wishlist_numbers->execute([$p_name, $user_id]);

    $check_cart_numbers = $conn->prepare("SELECT * FROM `carts` WHERE name = ? AND user_id = ?");
    $check_cart_numbers->execute([$p_name, $user_id]);

    if ($check_wishlist_numbers->rowCount() > 0) {
        $message[] = 'Already added to wishlist!';
    } elseif ($check_cart_numbers->rowCount() > 0) {
        $message[] = 'Already added to cart!';
    } else {
        // Corrected the number of arguments in the execute function
        $insert_wishlist = $conn->prepare("INSERT INTO `wishlist` (user_id, product_id, name, price, image) VALUES (?, ?, ?, ?, ?)");
        $insert_wishlist->execute([$user_id, $product_id, $p_name, $p_price, $p_image]);
        $message[] = 'Added to wishlist!';
    }
}
if(isset($_POST['add_to_cart'])){

    $product_id = filter_var($_POST['product_id'], FILTER_SANITIZE_STRING);
    $p_name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $p_price = filter_var($_POST['price'], FILTER_SANITIZE_STRING);
    $p_qty = filter_var($_POST['p_qty'], FILTER_SANITIZE_STRING);
    $p_image = filter_var($_POST['image'], FILTER_SANITIZE_STRING);
    
    $check_cart_numbers = $conn->prepare("SELECT * FROM `carts` WHERE name = ? AND user_id = ?");
    $check_cart_numbers->execute([$p_name, $user_id]);
 
    if($check_cart_numbers->rowCount() > 0){
       $message[] = 'Already added to cart!';
    }else{
 
       $check_wishlist_numbers = $conn->prepare("SELECT * FROM `wishlist` WHERE name = ? AND user_id = ?");
       $check_wishlist_numbers->execute([$p_name, $user_id]);
 
       if($check_wishlist_numbers->rowCount() > 0){
          $delete_wishlist = $conn->prepare("DELETE FROM `wishlist` WHERE name = ? AND user_id = ?");
          $delete_wishlist->execute([$p_name, $user_id]);
       }
 
       $insert_cart = $conn->prepare("INSERT INTO `carts`(user_id, product_id, name, price, quantity, image) VALUES(?,?,?,?,?,?)");
       $insert_cart->execute([$user_id, $product_id, $p_name, $p_price, $p_qty, $p_image]);
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
    <title>Home Page</title>

    <!-- Font Awesome icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <!-- Custom CSS file -->
    <link rel="stylesheet" href="css/style.css">

</head>
<body>

<?php include 'header.php'; ?>

<div class="home-bg">
    <section class="home">
        <div class="content">
            <span>BUILDING RELATIONS!</span>
            <h3>For Easy Reach of Your Daily Groceries</h3>
            <p>Where you get the best of both</p>
            <a href="about.php" class="btn">About Us</a>
        </div>
    </section>
</div>

<section class="home-category">
    <h1 class="title">Shop by Category</h1>
    <div class="box-container">
        <div class="box">
            <img src="images/cat-1.png" alt="Stationery">
            <h3>Stationery</h3>
            <p>Spark creativity and stay organized with stylish stationery.</p>
            <a href="category.php?category=Stationery" class="btn">Stationery</a>
        </div>

        <div class="box">
            <img src="images/cat-2.png" alt="Instant Foods">
            <h3>Instant Foods</h3>
            <p>Enjoy convenience and flavor with delicious instant food options.</p>
            <a href="category.php?category=Instant Foods" class="btn">Instant Foods</a>
        </div>

        <div class="box">
            <img src="images/cat-3.png" alt="Bakery">
            <h3>Bakery</h3>
            <p>Indulge in fresh, mouthwatering delights with our delicious bakery treats.</p>
            <a href="category.php?category=Bakery" class="btn">Bakery</a>
        </div>

        <div class="box">
            <img src="images/cat-4.png" alt="Drinks">
            <h3>Drinks</h3>
            <p>Refresh and savor every sip with our selection of delightful drinks.</p>
            <a href="category.php?category=Drinks" class="btn">Drinks</a>
        </div>
    </div>
</section>

<section class="products">
    <h1 class="title">Latest Products</h1>
    <div class="box-container">
    <?php
        $select_products = $conn->prepare("SELECT * FROM `products` LIMIT 3");
        $select_products->execute();
        if ($select_products->rowCount() > 0) {
            while ($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)) {
    ?>
    <form action="" method="POST" class="box">
        <div class="price">Rs.<span><?= htmlspecialchars($fetch_products['price']); ?></span>/-</div>
        <a href="view_page.php?product_id=<?= htmlspecialchars($fetch_products['product_id']); ?>" class="fas fa-eye"></a>
        <img src="uploaded_img/<?= htmlspecialchars($fetch_products['image']); ?>" alt="<?= htmlspecialchars($fetch_products['name']); ?>">
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
        <input type="number" min="1" value="1" name="p_qty" class="qty">
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
