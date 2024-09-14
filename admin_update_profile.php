<?php

@include 'config.php';

session_start();

$admin_id = $_SESSION['admin_id'] ?? null;

if (!$admin_id) {
    header('Location: login.php');
    exit();
}

// Initialize message array
$message = [];

// Handle profile update
if (isset($_POST['update_profile'])) {
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

    // Update profile information
    $update_profile = $conn->prepare("UPDATE `users` SET name = ?, email = ? WHERE id = ?");
    $update_profile->execute([$name, $email, $admin_id]);

    // Handle image upload
    $image = $_FILES['image']['name'] ?? null;
    $image_size = $_FILES['image']['size'] ?? 0;
    $image_tmp_name = $_FILES['image']['tmp_name'] ?? null;
    $image_folder = 'uploaded_img/' . basename($image);
    $old_image = filter_input(INPUT_POST, 'old_image', FILTER_SANITIZE_STRING);

    if ($image) {
        $allowed_extensions = ['jpg', 'jpeg', 'png'];
        $file_extension = pathinfo($image, PATHINFO_EXTENSION);

        if ($image_size > 2000000) {
            $message[] = 'Image size is too large!';
        } elseif (!in_array($file_extension, $allowed_extensions)) {
            $message[] = 'Invalid image format! Please upload JPG, JPEG, or PNG images only.';
        } else {
            $update_image = $conn->prepare("UPDATE `users` SET image = ? WHERE id = ?");
            $update_image->execute([$image, $admin_id]);

            if ($update_image) {
                move_uploaded_file($image_tmp_name, $image_folder);

                // Delete old image if it exists
                if ($old_image && file_exists('uploaded_img/' . $old_image)) {
                    unlink('uploaded_img/' . $old_image);
                }

                $message[] = 'Image updated successfully!';
            }
        }
    }

    // Handle password update
    $old_pass = filter_input(INPUT_POST, 'old_pass', FILTER_SANITIZE_STRING);
    $update_pass = filter_input(INPUT_POST, 'update_pass', FILTER_SANITIZE_STRING);
    $new_pass = filter_input(INPUT_POST, 'new_pass', FILTER_SANITIZE_STRING);
    $confirm_pass = filter_input(INPUT_POST, 'confirm_pass', FILTER_SANITIZE_STRING);

    if ($update_pass && $new_pass && $confirm_pass) {
        $stmt = $conn->prepare("SELECT password FROM `users` WHERE id = ?");
        $stmt->execute([$admin_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result && password_verify($update_pass, $result['password'])) {
            if ($new_pass !== $confirm_pass) {
                $message[] = 'New password and confirm password do not match!';
            } else {
                $new_pass_hashed = password_hash($new_pass, PASSWORD_DEFAULT);
                $update_pass_query = $conn->prepare("UPDATE `users` SET password = ? WHERE id = ?");
                $update_pass_query->execute([$new_pass_hashed, $admin_id]);
                $message[] = 'Password updated successfully!';
            }
        } else {
            $message[] = 'Old password does not match!';
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
    <title>Update Admin Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="css/components.css">
</head>
<body>

<?php include 'admin_header.php'; ?>

<section class="update-profile">
    <h1 class="title">Update Profile</h1>

    <form action="" method="POST" enctype="multipart/form-data">
        <?php
        // Fetch the current profile details
        $select_profile = $conn->prepare("SELECT * FROM `users` WHERE user_id = ?");
        $select_profile->execute([$admin_id]);
        $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);

        if ($fetch_profile) {
        ?>
        <img src="uploaded_img/<?= htmlspecialchars($fetch_profile['image']); ?>" alt="">
        <div class="flex">
            <div class="inputBox">
                <span>Username :</span>
                <input type="text" name="name" value="<?= htmlspecialchars($fetch_profile['name']); ?>" placeholder="Update username" required class="box">
                <span>Email :</span>
                <input type="email" name="email" value="<?= htmlspecialchars($fetch_profile['email']); ?>" placeholder="Update email" required class="box">
                <span>Update Pic :</span>
                <input type="file" name="image" accept="image/jpg, image/jpeg, image/png" class="box">
                <input type="hidden" name="old_image" value="<?= htmlspecialchars($fetch_profile['image']); ?>">
            </div>
            <div class="inputBox">
                <span>Old Password :</span>
                <input type="password" name="update_pass" placeholder="Enter previous password" class="box">
                <span>New Password :</span>
                <input type="password" name="new_pass" placeholder="Enter new password" class="box">
                <span>Confirm Password :</span>
                <input type="password" name="confirm_pass" placeholder="Confirm new password" class="box">
            </div>
        </div>
        <div class="flex-btn">
            <input type="submit" class="btn" value="Update Profile" name="update_profile">
            <a href="admin_page.php" class="option-btn">Go Back</a>
        </div>
        <?php
        } else {
            echo '<p class="empty">Profile not found!</p>';
        }
        ?>
    </form>
</section>

<script src="js/script.js"></script>

</body>
</html>
