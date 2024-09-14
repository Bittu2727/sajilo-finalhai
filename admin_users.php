<?php

@include 'config.php';

session_start();

$admin_id = $_SESSION['admin_id'] ?? null;

if (!$admin_id) {
    header('location:login.php');
    exit();
}

if (isset($_GET['delete'])) {
    $delete_id = filter_var($_GET['delete'], FILTER_SANITIZE_NUMBER_INT);

    if ($delete_id) {
        try {
            // Prepare and execute delete query
            $delete_users = $conn->prepare("DELETE FROM `users` WHERE user_id = ?");
            $delete_users->execute([$delete_id]);

            // Redirect to the users page
            header('location:admin_users.php');
            exit();
        } catch (PDOException $e) {
            $message[] = 'Error: ' . $e->getMessage();
        }
    } else {
        $message[] = 'Invalid user ID!';
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Users</title>

   <!-- Font Awesome CDN link -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- Custom CSS file link -->
   <link rel="stylesheet" href="css/admin_style.css">
</head>
<body>
   
<?php include 'admin_header.php'; ?>

<section class="user-accounts">
   <h1 class="title">User Accounts</h1>

   <div class="box-container">
      <?php
         // Fetch users from the database
         $select_users = $conn->prepare("SELECT * FROM `users`");
         $select_users->execute();

         while ($fetch_users = $select_users->fetch(PDO::FETCH_ASSOC)) {
      ?>
      <div class="box <?= $fetch_users['user_id'] == $admin_id ? 'hidden' : ''; ?>">
         <!-- Adjust image display jaba tyo column exists -->
         <?php if (!empty($fetch_users['image'])): ?>
            <img src="uploaded_img/<?= htmlspecialchars($fetch_users['image']); ?>" alt="User Image">
         <?php endif; ?>
         <p>User ID: <span><?= htmlspecialchars($fetch_users['user_id']); ?></span></p>
         <p>Username: <span><?= htmlspecialchars($fetch_users['username']); ?></span></p>
         <p>Email: <span><?= htmlspecialchars($fetch_users['email']); ?></span></p>
         <p>User Type: <span style="color: <?= $fetch_users['user_type'] === 'Admin' ? 'orange' : 'inherit'; ?>"><?= htmlspecialchars($fetch_users['user_type']); ?></span></p>
         <a href="admin_users.php?delete=<?= htmlspecialchars($fetch_users['user_id']); ?>" onclick="return confirm('Delete this user?');" class="delete-btn">Delete</a>
      </div>
      <?php
         }
      ?>
   </div>
</section>

<script src="js/script.js"></script>

</body>
</html>
