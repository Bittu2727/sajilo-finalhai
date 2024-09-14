<?php
@include 'config.php';

session_start();

// Ensure database connection is established
if (!isset($conn)) {
    die("Database connection failed.");
}

$admin_id = $_SESSION['admin_id'] ?? null;

if (!$admin_id) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_order'])) {
    $order_id = filter_var($_POST['order_id'], FILTER_SANITIZE_NUMBER_INT);
    $update_payment = filter_var($_POST['update_payment'], FILTER_SANITIZE_STRING);
    
    if ($order_id && $update_payment) {
        $update_orders = $conn->prepare("UPDATE `orders` SET order_status = ? WHERE order_id = ?");
        $update_orders->execute([$update_payment, $order_id]);
        
        $message[] = 'Order status has been updated!';
    } else {
        $message[] = 'Invalid input.';
    }
}

if (isset($_GET['delete'])) {
    $delete_id = filter_var($_GET['delete'], FILTER_SANITIZE_NUMBER_INT);
    
    if ($delete_id) {
        $delete_orders = $conn->prepare("DELETE FROM `orders` WHERE order_id = ?");
        $delete_orders->execute([$delete_id]);
        
        header('Location: admin_orders.php');
        exit();
    }
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
    <link rel="stylesheet" href="css/admin_style.css">
</head>
<body>
   
<?php include 'admin_header.php'; ?>

<section class="placed-orders">
    <h1 class="title">Orders Placed</h1>

    <div class="box-container">

        <?php
            $select_orders = $conn->prepare("SELECT * FROM `orders`");
            $select_orders->execute();
            if ($select_orders->rowCount() > 0) {
                while ($fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC)) {
                    
                    $placed_on = isset($fetch_orders['placed_on']) ? htmlspecialchars($fetch_orders['placed_on']) : 'Unknown';
$formatted_date = ($placed_on === '0000-00-00 00:00:00' || $placed_on === 'Unknown') ? 'Date not available' : date('Y-m-d H:i:s', strtotime($placed_on));     ?>
                    <div class="box">
                        <p> Order ID : <span><?= htmlspecialchars($fetch_orders['order_id']); ?></span> </p>
                        <p> User ID : <span><?= htmlspecialchars($fetch_orders['user_id']); ?></span> </p>
                        <p> Vendor ID : <span><?= htmlspecialchars($fetch_orders['vendor_id']); ?></span> </p>
                        <p> Name : <span><?= htmlspecialchars($fetch_orders['name']); ?></span> </p>
                        <p> Email : <span><?= htmlspecialchars($fetch_orders['email']); ?></span> </p>
                        <p> Number : <span><?= htmlspecialchars($fetch_orders['number']); ?></span> </p>
                        <p> Payment Method : <span><?= htmlspecialchars($fetch_orders['method']); ?></span> </p>
                        <p> Placed On : <span><?= $formatted_date; ?></span> </p>
                        <p> Address : <span><?= htmlspecialchars($fetch_orders['address']); ?></span> </p>
                        <form action="" method="POST">
                            <input type="hidden" name="order_id" value="<?= htmlspecialchars($fetch_orders['order_id']); ?>">
                            <select name="update_payment" class="drop-down">
                                <option value="" selected disabled><?= htmlspecialchars($fetch_orders['order_status'] ?? 'Unknown'); ?></option>
                                <option value="Pending">Pending</option>
                                <option value="Completed">Completed</option>
                                <option value="Cancelled">Cancelled</option>
                            </select>
                            <div class="flex-btn">
                                <input type="submit" name="update_order" class="option-btn" value="Update">
                                <a href="admin_orders.php?delete=<?= htmlspecialchars($fetch_orders['order_id']); ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this order?');">Delete</a>
                            </div>
                        </form>
                    </div>
                    <?php
                }
            } else {
                echo '<p class="empty">No orders placed yet!</p>';
            }
        ?>

    </div>
</section>

<script src="js/script.js"></script>

</body>
</html>
