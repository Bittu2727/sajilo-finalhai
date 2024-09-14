<?php
@include 'config.php';

session_start();

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders</title>

    <!-- Font Awesome CDN link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <!-- Custom CSS file link -->
    <link rel="stylesheet" href="css/style.css">

</head>
<body>

<?php include 'header.php'; ?>

<section class="placed-orders">

    <h1 class="title">Placed Orders</h1>

    <div class="box-container">

    <?php
    $select_orders = $conn->prepare("SELECT * FROM `orders` WHERE user_id = ?");
    $select_orders->execute([$user_id]);

    if ($select_orders->rowCount() > 0) {
        while ($fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC)) {
    ?>
    <div class="box">
        <p>Placed on: <span><?= htmlspecialchars($fetch_orders['placed_on']); ?></span></p>
        <p>Name: <span><?= htmlspecialchars($fetch_orders['name']); ?></span></p>
        <p>Number: <span><?= htmlspecialchars($fetch_orders['number']); ?></span></p>
        <p>Email: <span><?= htmlspecialchars($fetch_orders['email']); ?></span></p>
        <p>Address: <span><?= htmlspecialchars($fetch_orders['address']); ?></span></p>
        <p>Payment Method: <span><?= htmlspecialchars($fetch_orders['method']); ?></span></p>
        <p>Your Orders: <span><?= htmlspecialchars($fetch_orders['total_products']); ?></span></p>
        <p>Total Price: <span>Rs.<?= htmlspecialchars($fetch_orders['total_amount']); ?>/-</span></p>
        <p> Order status : 
    <span style="color:
        <?php 
            if ($fetch_orders['order_status'] == 'Pending') {
                echo 'orange';
            } elseif ($fetch_orders['order_status'] == 'Cancelled') {
                echo 'red'; // Red for cancelled orders
            } else {
                echo 'green'; // Green for other statuses (e.g., Completed)
            } 
        ?>;">
        <?php echo $fetch_orders['order_status']; ?>
    </span>
</p>    </div>
    <?php
        }
    } else {
        echo '<p class="empty">No orders placed yet!</p>';
    }
    ?>

    </div>

</section>

<?php include 'footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>
