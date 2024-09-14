<?php
@include 'config.php';

session_start();

if (isset($_POST['submit'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $pass = $_POST['pass'];

    $sql = "SELECT * FROM `users` WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$email]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row && password_verify($pass, $row['password'])) {
        switch ($row['user_type']) {
            case 'Admin':
                $_SESSION['admin_id'] = $row['user_id'];
                header('Location: admin_page.php');
                exit;
            case 'User':
                $_SESSION['user_id'] = $row['user_id'];
                header('Location: home.php');
                exit;
            case 'Vendor':
                $_SESSION['vendor_id'] = $row['user_id'];
                header('Location: vendor_page.php');
                exit;
            default:
                $message[] = 'No user type found!';
                break;
        }
    } else {
        $message[] = 'Incorrect email or password!';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>

    <!-- Font Awesome link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <!-- Custom CSS file link -->
    <link rel="stylesheet" href="css/components.css">
</head>
<body>

<?php
if (isset($message)) {
    foreach ($message as $msg) {
        echo '
        <div class="message">
            <span>' . htmlspecialchars($msg) . '</span>
            <i class="fas fa-times" onclick="this.parentElement.remove();"></i> 
        </div>
        ';
    }
}
?>

<section class="form-container">
    <form action="" method="POST">
        <h3>Login Now</h3>
        <input type="email" name="email" class="box" placeholder="Enter your email" required>
        <input type="password" name="pass" class="box" placeholder="Enter your password" required>
        <input type="submit" value="Login Now" class="btn" name="submit">
        <p>Don't have an account? <a href="register.php">Register now</a></p>
    </form>
</section>

</body>
</html>
