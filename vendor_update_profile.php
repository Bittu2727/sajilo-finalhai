<?php

@include 'config.php';

session_start();

// Correct session variable name
$vendor_id = $_SESSION['vendor_id'] ?? null;

if (!$vendor_id) {
    header('location: login.php');
    exit(); // Ensure no further code is executed after redirect
}

$message = []; // Initialize the message array

if (isset($_POST['update_profile'])) {
    // Sanitize and validate input data
    $username = filter_var($_POST['username'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $old_image = filter_var($_POST['old_image'], FILTER_SANITIZE_STRING);

    // Update profile details
    $update_profile = $conn->prepare("UPDATE `users` SET username = ?, email = ? WHERE user_id = ?");
    $update_profile->execute([$username, $email, $vendor_id]);

    if ($update_profile->rowCount() > 0) {
        $message[] = 'Profile updated successfully!';
    } else {
        $message[] = 'Failed to update username or email!';
    }

    $image = $_FILES['image'];
    $image_name = filter_var($image['name'], FILTER_SANITIZE_STRING);
    $image_size = $image['size'];
    $image_tmp_name = $image['tmp_name'];
    $image_folder = 'uploaded_img/' . $image_name;

    // Handle image upload
    if (!empty($image_name)) {
        if ($image_size > 2000000) {
            $message[] = 'Image size is too large!';
        } else {
            // Update image in database
            $update_image = $conn->prepare("UPDATE `users` SET image = ? WHERE user_id = ?");
            $update_image->execute([$image_name, $vendor_id]);

            if ($update_image) {
                move_uploaded_file($image_tmp_name, $image_folder);
                if (file_exists('uploaded_img/' . $old_image)) {
                    unlink('uploaded_img/' . $old_image);
                }
                $message[] = 'Image updated successfully!';
            } else {
                $message[] = 'Failed to update image!';
            }
        }
    }

    // Password update
    $old_pass = filter_var($_POST['old_pass'], FILTER_SANITIZE_STRING);
    $new_pass = filter_var($_POST['new_pass'], FILTER_SANITIZE_STRING);
    $confirm_pass = filter_var($_POST['confirm_pass'], FILTER_SANITIZE_STRING);

    if (!empty($old_pass) && !empty($new_pass) && !empty($confirm_pass)) {
        $select_user = $conn->prepare("SELECT password FROM `users` WHERE user_id = ?");
        $select_user->execute([$vendor_id]);
        $fetch_user = $select_user->fetch(PDO::FETCH_ASSOC);

        if (md5($old_pass) != $fetch_user['password']) {
            $message[] = 'Old password does not match!';
        } elseif ($new_pass != $confirm_pass) {
            $message[] = 'Confirm password does not match!';
        } else {
            $update_pass_query = $conn->prepare("UPDATE `users` SET password = ? WHERE user_id = ?");
            $update_pass_query->execute([md5($new_pass), $vendor_id]);
            $message[] = 'Password updated successfully!';
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
    <title>Update Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="css/components.css">
</head>
<body>

<?php include 'vendor_header.php'; ?>

<section class="update-profile">
    <h1 class="title">Update Profile</h1>

    <?php
    // Display messages if any
    $message=[];
    if (!empty($message)) {
        foreach ($message as $msg) {
            echo '<p class="message">' . htmlspecialchars($msg) . '</p>';
        }
    }

    // Fetch the current user profile data
    $select_profile = $conn->prepare("SELECT * FROM `users` WHERE user_id = ?");
    $select_profile->execute([$vendor_id]);
    if ($select_profile->rowCount() > 0) {
        $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
    ?>
    <form action="" method="POST" enctype="multipart/form-data">
        <img src="uploaded_img/<?= htmlspecialchars($fetch_profile['image']); ?>" alt="Profile Picture" class="profile-img">
        <div class="flex">
            <div class="inputBox">
                <span>Username :</span>
                <input type="text" name="username" value="<?= htmlspecialchars($fetch_profile['username']); ?>" placeholder="Update username" required class="box">
                <span>Email :</span>
                <input type="email" name="email" value="<?= htmlspecialchars($fetch_profile['email']); ?>" placeholder="Update email" required class="box">
                <span>Update Picture :</span>
                <input type="file" name="image" accept="image/jpg, image/jpeg, image/png" class="box">
                <input type="hidden" name="old_image" value="<?= htmlspecialchars($fetch_profile['image']); ?>">
            </div>
            <div class="inputBox">
                <span>Old Password :</span>
                <input type="password" name="old_pass" placeholder="Enter previous password" class="box">
                <span>New Password :</span>
                <input type="password" name="new_pass" placeholder="Enter new password" class="box">
                <span>Confirm Password :</span>
                <input type="password" name="confirm_pass" placeholder="Confirm new password" class="box">
            </div>
        </div>
        <div class="flex-btn">
            <input type="submit" class="btn" value="Update Profile" name="update_profile">
            <a href="vendor_page.php" class="option-btn">Go Back</a>
        </div>
    </form>
    <?php
    } else {
        echo '<p class="empty">No profile found!</p>';
    }
    ?>

</section>

<script src="js/script.js"></script>

</body>
</html>
