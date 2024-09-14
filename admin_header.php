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

<header class="header">

   <div class="flex">

      <a href="admin_page.php" class="logo">Admin<span>Panel</span></a>

      <nav class="navbar">
         <a href="admin_page.php">Home</a>
         <a href="admin_products.php">Products</a>
         <a href="admin_orders.php">Orders</a>
         <a href="admin_users.php">Users</a>
         <a href="admin_contacts.php">Messages</a>
      </nav>

      <div class="icons">
         <div id="menu-btn" class="fas fa-bars"></div>
         <div id="user-btn" class="fas fa-user"></div>
      </div>

      <div class="profile">
         <?php //ensure session started only once and fetch admin ko profile//
            $admin_id = isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : 0;

            if($admin_id == 0){
               header('location:login.php');
               exit;
            }
               if(isset($conn)){
                  $select_profile = $conn->prepare("SELECT * FROM `users` WHERE user_id = ?");
                  $select_profile->execute([$admin_id]);
               }
            if ($select_profile->rowCount() > 0) {
                $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
         ?>
         <img src="uploaded_img/<?= htmlspecialchars($fetch_profile['image']); ?>" alt="">
         <p><?= htmlspecialchars($fetch_profile['username']); ?></p>
         <a href="admin_update_profile.php" class="btn">Update Profile</a>
         <a href="logout.php" class="delete-btn">Logout</a>
         <div class="flex-btn">
            <a href="login.php" class="option-btn">Login</a>
            <a href="register.php" class="option-btn">Register</a>
         </div>
         <?php
            } else {
                echo '<p class="empty">No profile data found.</p>';
            }
         ?>
      </div>

   </div>

</header>
