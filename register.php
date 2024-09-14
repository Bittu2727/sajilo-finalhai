<?php

@include 'config.php';

if (isset($_POST['submit'])) {

    $username = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $pass = $_POST['pass'];
    $cpass = $_POST['cpass'];
    $user_type = filter_var($_POST['user_type'] ?? '', FILTER_SANITIZE_STRING);
    $image = $_FILES['image']['name'];
    $image_size = $_FILES['image']['size'];
    $image_tmp_name = $_FILES['image']['tmp_name'];
    $image_folder = 'uploaded_img/' . $image;

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message[] = 'Invalid email format!';
    }

    // Check if passwords match
    if ($pass !== $cpass) {
        $message[] = 'Confirm password does not match!';
    }

    // Check if email already exists
    $select = $conn->prepare("SELECT * FROM `users` WHERE email = ?");
    $select->execute([$email]);

    if ($select->rowCount() > 0) {
        $message[] = 'User email already exists!';
    } else {
        // Hash the password securely
        $hashed_pass = password_hash($pass, PASSWORD_BCRYPT);

        // Insert user data into database
        $insert = $conn->prepare("INSERT INTO `users` (username, email, password, image, user_type) VALUES (?, ?, ?, ?, ?)");
        $insert->execute([$username, $email, $hashed_pass, $image, $user_type]);

        if ($insert) {
            // Validate image size
            if ($image_size > 2000000) {
                $message[] = 'Image size is too large!';
            } else {
                move_uploaded_file($image_tmp_name, $image_folder);
                $message[] = 'Registered successfully!';
                header('Location: login.php');
                exit;
            }
        } else {
            $message[] = 'Registration failed!';
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
    <title>Register</title>

    <!-- Font Awesome CDN link -->
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
    <form action="" enctype="multipart/form-data" method="POST">
        <h3>Register now</h3>
        <input type="text" name="name" class="box" placeholder="Enter your name" required>
        <input type="email" name="email" class="box" placeholder="Enter your email" required>
        <input type="password" name="pass" class="box" placeholder="Enter your password" required>
        <input type="password" name="cpass" class="box" placeholder="Confirm your password" required>
        <input type="file" name="image" class="box" required accept="image/jpg, image/jpeg, image/png">
        <select name="user_type" class="box" required>
            <option value="" disabled selected>Select your user type</option>
            <option value="user">User</option>
            <option value="vendor">Vendor</option>
        </select>
        <input type="submit" value="Register now" class="btn" name="submit">
        <p>Already have an account? <a href="login.php">Login now</a></p>
    </form>
</section>

</body>
</html>
