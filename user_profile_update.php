<?php
@include 'config.php';

session_start();

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    header('Location: login.php');
    exit();
}

$message = [];

// Handle Profile Update
if (isset($_POST['update_profile'])) {
    $username = filter_var($_POST['username'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message[] = 'Invalid email format!';
    } else {
        // Update profile information
        $update_profile = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE user_id = ?");
        $update_profile->execute([$username, $email, $user_id]);

        if (isset($_FILES['image']) && !empty($_FILES['image']['name'])) {
            $image = filter_var($_FILES['image']['name'], FILTER_SANITIZE_STRING);
            $image_size = $_FILES['image']['size'];
            $image_tmp_name = $_FILES['image']['tmp_name'];
            $image_folder = 'uploaded_img/' . $image;
            $old_image = filter_var($_POST['old_image'], FILTER_SANITIZE_STRING);

            if ($image_size > 2000000) {
                $message[] = 'Image size is too large!';
            } else {
                // Update image information
                $update_image = $conn->prepare("UPDATE users SET image = ? WHERE user_id = ?");
                $update_image->execute([$image, $user_id]);
                move_uploaded_file($image_tmp_name, $image_folder);

                if (!empty($old_image) && file_exists('uploaded_img/' . $old_image)) {
                    unlink('uploaded_img/' . $old_image);
                }
                $message[] = 'Image updated successfully!';
            }
        }
    }
}

// Handle Password Update
if (isset($_POST['update_pass']) && !empty($_POST['update_pass']) && !empty($_POST['new_pass']) && !empty($_POST['confirm_pass'])) {
    $old_pass = $_POST['update_pass'];
    $new_pass = $_POST['new_pass'];
    $confirm_pass = $_POST['confirm_pass'];

    $stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if ($user && !password_verify($old_pass, $user['password'])) {
        $message[] = 'Old password does not match!';
    } elseif ($new_pass !== $confirm_pass) {
        $message[] = 'New password and confirm password do not match!';
    } else {
        $new_pass_hash = password_hash($new_pass, PASSWORD_BCRYPT);
        $update_pass_query = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $update_pass_query->execute([$new_pass_hash, $user_id]);
        $message[] = 'Password updated successfully!';
    }
} elseif (isset($_POST['update_pass'])) {
    $message[] = 'Please fill in all fields for password update!';
}

// Fetch user profile
$fetch_profile = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$fetch_profile->execute([$user_id]);
$profile = $fetch_profile->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update User Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="css/components.css">
</head>
<body>

<?php include 'header.php'; ?>

<section class="update-profile">
    <h1 class="title">Update Profile</h1>

    <?php
    // Display messages
    if (!empty($message)) {
        foreach ($message as $msg) {
            echo "<p>$msg</p>";
        }
    }
    ?>

    <form action="" method="POST" enctype="multipart/form-data">
        <img src="uploaded_img/<?= htmlspecialchars($profile['image']); ?>" alt="Profile Image">
        <div class="flex">
            <div class="inputBox">
                <span>Username:</span>
                <input type="text" name="username" value="<?= htmlspecialchars($profile['username']); ?>" placeholder="Update username" required class="box">
                <span>Email:</span>
                <input type="email" name="email" value="<?= htmlspecialchars($profile['email']); ?>" placeholder="Update email" required class="box">
                <span>Update Pic:</span>
                <input type="file" name="image" accept="image/jpg, image/jpeg, image/png" class="box">
                <input type="hidden" name="old_image" value="<?= htmlspecialchars($profile['image']); ?>">
            </div>
            <div class="inputBox">
                <span>Old Password:</span>
                <input type="password" name="update_pass" placeholder="Enter previous password" class="box">
                <span>New Password:</span>
                <input type="password" name="new_pass" placeholder="Enter new password" class="box">
                <span>Confirm Password:</span>
                <input type="password" name="confirm_pass" placeholder="Confirm new password" class="box">
            </div>
        </div>
        <div class="flex-btn">
            <input type="submit" class="btn" value="Update Profile" name="update_profile">
            <a href="home.php" class="option-btn">Go Back</a>
        </div>
    </form>
</section>

<?php include 'footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>
