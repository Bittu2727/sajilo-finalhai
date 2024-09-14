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
    if (isset($_POST['order'])) {
        $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
        $number = filter_var($_POST['number'], FILTER_SANITIZE_STRING);
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $method = filter_var($_POST['method'], FILTER_SANITIZE_STRING);
        $address = 'flat no. ' . filter_var($_POST['flat'], FILTER_SANITIZE_STRING) . ' ' . filter_var($_POST['street'], FILTER_SANITIZE_STRING) . ' ' . filter_var($_POST['city'], FILTER_SANITIZE_STRING) . ' ' . filter_var($_POST['province'], FILTER_SANITIZE_STRING) . ' ' . filter_var($_POST['country'], FILTER_SANITIZE_STRING) . ' - ' . filter_var($_POST['pin_code'], FILTER_SANITIZE_STRING);
        $placed_on = date('d-M-Y');

        $cart_total = 0;
        $cart_products = [];
        $cart_items = [];

        // Retrieve cart items
        $cart_query = $conn->prepare("SELECT * FROM `carts` WHERE user_id = ?");
        $cart_query->execute([$user_id]);
        if ($cart_query->rowCount() > 0) {
            while ($cart_item = $cart_query->fetch(PDO::FETCH_ASSOC)) {
                $cart_products[] = $cart_item['name'] . ' (' . $cart_item['quantity'] . ')';
                $sub_total = ($cart_item['price'] * $cart_item['quantity']);
                $cart_total += $sub_total;

                // Store cart items for stock update
                $cart_items[] = [
                    'product_id' => $cart_item['product_id'],
                    'quantity' => $cart_item['quantity']
                ];
            }
        }

        $total_products = implode(', ', $cart_products);

        // Check if order already exists
        $order_query = $conn->prepare("SELECT * FROM `orders` WHERE name = ? AND number = ? AND email = ? AND method = ? AND address = ? AND total_products = ? AND total_amount = ?");
        $order_query->execute([$name, $number, $email, $method, $address, $total_products, $cart_total]);

        if ($cart_total == 0) {
            $message[] = 'Your cart is empty';
        } elseif ($order_query->rowCount() > 0) {
            $message[] = 'Order already placed!';
        } else {
            try {
                $conn->beginTransaction();

                // Insert order
                $insert_order = $conn->prepare("INSERT INTO `orders` (user_id, name, number, email, method, address, total_products, total_amount, placed_on) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $insert_order->execute([$user_id, $name, $number, $email, $method, $address, $total_products, $cart_total, $placed_on]);

                // Update product stock
                foreach ($cart_items as $item) {
                    $product_id = $item['product_id'];
                    $quantity = $item['quantity'];

                    // Fetch current stock
                    $stock_query = $conn->prepare("SELECT stock FROM `products` WHERE product_id = ?");
                    $stock_query->execute([$product_id]);
                    $product = $stock_query->fetch(PDO::FETCH_ASSOC);

                    if ($product) {
                        $new_stock = $product['stock'] - $quantity;

                        if ($new_stock < 0) {
                            throw new Exception('Insufficient stock for product ID ' . $product_id);
                        }

                        // Update stock
                        $update_stock = $conn->prepare("UPDATE `products` SET stock = ? WHERE product_id = ?");
                        $update_stock->execute([$new_stock, $product_id]);
                    } else {
                        throw new Exception('Product not found for product ID ' . $product_id);
                    }
                }

                // Delete cart items
                $delete_cart = $conn->prepare("DELETE FROM `carts` WHERE user_id = ?");
                $delete_cart->execute([$user_id]);

                $conn->commit();
                $message[] = 'Order placed successfully!';
            } catch (Exception $e) {
                $conn->rollBack();
                $message[] = 'Error placing order: ' . $e->getMessage();
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
    <title>Checkout</title>

    <!-- Font Awesome CDN link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <!-- Custom CSS file link -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
   
<?php include 'header.php'; ?>

<section class="display-orders">
    <?php
    $cart_grand_total = 0;
    $select_cart_items = $conn->prepare("SELECT * FROM `carts` WHERE user_id = ?");
    $select_cart_items->execute([$user_id]);
    if ($select_cart_items->rowCount() > 0) {
        while ($fetch_cart_items = $select_cart_items->fetch(PDO::FETCH_ASSOC)) {
            $cart_total_price = ($fetch_cart_items['price'] * $fetch_cart_items['quantity']);
            $cart_grand_total += $cart_total_price;
    ?>
    <p><?= htmlspecialchars($fetch_cart_items['name']); ?> <span>(<?= 'Rs.' . htmlspecialchars($fetch_cart_items['price']) . '/- x ' . htmlspecialchars($fetch_cart_items['quantity']); ?>)</span></p>
    <?php
        }
    } else {
        echo '<p class="empty">Your cart is empty!</p>';
    }
    ?>
    <div class="grand-total">Grand total : <span>Rs.<?= $cart_grand_total; ?>/-</span></div>
</section>

<section class="checkout-orders">
    <form action="" method="POST">
        <h3>Place Your Order</h3>
        <div class="flex">
            <div class="inputBox">
                <span>Your Name :</span>
                <input type="text" name="name" placeholder="Enter your name" class="box" required>
            </div>
            <div class="inputBox">
                <span>Your Number :</span>
                <input type="number" name="number" placeholder="Enter your number" class="box" required>
            </div>
            <div class="inputBox">
                <span>Your Email :</span>
                <input type="email" name="email" placeholder="Enter your email" class="box" required>
            </div>
            <div class="inputBox">
                <span>Payment Method :</span>
                <select name="method" class="box" required>
                    <option value="cash on delivery">Cash on Delivery</option>
                </select>
            </div>
            <div class="inputBox">
                <span>Address Line 01 :</span>
                <input type="text" name="flat" placeholder="e.g. flat number" class="box" required>
            </div>
            <div class="inputBox">
                <span>Address Line 02 :</span>
                <input type="text" name="street" placeholder="e.g. street name" class="box" required>
            </div>
            <div class="inputBox">
                <span>City :</span>
                <input type="text" name="city" placeholder="e.g. Pokhara" class="box" required>
            </div>
            <div class="inputBox">
                <span>Province :</span>
                <input type="text" name="province" placeholder="e.g. Gandaki" class="box" required>
            </div>
            <div class="inputBox">
                <span>Country :</span>
                <input type="text" name="country" placeholder="e.g. Nepal" class="box" required>
            </div>
            <div class="inputBox">
                <span>Pin Code :</span>
                <input type="number" min="0" name="pin_code" placeholder="e.g. 123456" class="box" required>
            </div>
        </div>
        <input type="submit" name="order" class="btn <?= ($cart_grand_total > 1) ? '' : 'disabled'; ?>" value="Place Order">
    </form>
</section>

<?php include 'footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>
