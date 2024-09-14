<?php

@include 'config.php';

session_start();

$vendor_id = $_SESSION['vendor_id'] ?? null;

if (!$vendor_id) {
    header('location: login.php');
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendor Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="css/admin_style.css">
</head>
<body>

<?php include 'vendor_header.php'; ?>

<section class="dashboard">
    <h1 class="title">Dashboard</h1>

    <div class="box-container">

        <div class="box">
            <?php
            $total_pendings = 0;
            $select_pendings = $conn->prepare("SELECT total_amount FROM orders WHERE order_status = ?");
            $select_pendings->execute(['pending']);
            while ($fetch_pendings = $select_pendings->fetch(PDO::FETCH_ASSOC)) {
                $total_pendings += $fetch_pendings['total_amount'];
            }
            ?>
            <h3>Rs. <?= htmlspecialchars($total_pendings); ?>/-</h3>
            <p>Total Pendings</p>
            <a href="vendor_order.php" class="btn">See Orders</a>
        </div>

        <div class="box">
            <?php
            $total_completed = 0;
            $select_completed = $conn->prepare("SELECT total_amount FROM orders WHERE order_status = ?");
            $select_completed->execute(['completed']);
            while ($fetch_completed = $select_completed->fetch(PDO::FETCH_ASSOC)) {
                $total_completed += $fetch_completed['total_price'];
            }
            ?>
            <h3>Rs. <?= htmlspecialchars($total_completed); ?>/-</h3>
            <p>Completed Orders</p>
            <a href="vendor_order.php" class="btn">See Orders</a>
        </div>

        <div class="box">
            <?php
            $select_orders = $conn->prepare("SELECT COUNT(*) FROM orders");
            $select_orders->execute();
            $number_of_orders = $select_orders->fetchColumn();
            ?>
            <h3><?= htmlspecialchars($number_of_orders); ?></h3>
            <p>Orders Placed</p>
            <a href="vendor_order.php" class="btn">See Orders</a>
        </div>

        <div class="box">
            <?php
            $select_products = $conn->prepare("SELECT COUNT(*) FROM products");
            $select_products->execute();
            $number_of_products = $select_products->fetchColumn();
            ?>
            <h3><?= htmlspecialchars($number_of_products); ?></h3>
            <p>Products Added</p>
            <a href="vendor_product.php" class="btn">See Products</a>
        </div>

      
    </div>

</section>

<script src="js/script.js"></script>

</body>
</html>
