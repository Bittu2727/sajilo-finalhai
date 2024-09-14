<?php

if(isset($message)){
   foreach($message as $message){
      echo '
      <div class="message">
         <span>'.$message.'</span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>
      ';
   }
}

?>

<header class="header">

   <div class="flex">

      <a href="vendor_page.php" class="logo">Vendor<span>Panel</span></a>

      <nav class="navbar">
         <a href="vendor_page.php">Home</a>
         <a href="vendor_product.php">Products</a>
         <a href="vendor_order.php">Orders</a>
         
      </nav>

      <div class="icons">
         <div id="menu-btn" class="fas fa-bars"></div>
         <div id="user-btn" class="fas fa-user"></div>
      </div>

      <div class="profile">
         <?php
            $select_profile = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
            $select_profile->execute([$vendor_id]);
            $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
         ?>
         <img src="uploaded_img/<?= htmlspecialchars($fetch_profile['image']); ?>" alt="">
         <p><?= htmlspecialchars($fetch_profile['username']); ?></p>
         <a href="vendor_update_profile.php" class="btn">Update Your Profile</a>
         <a href="logout.php" class="delete-btn">Logout</a>
         <div class="flex-btn">
            <a href="login.php" class="option-btn">Login</a>
            <a href="register.php" class="option-btn">Register</a>
         </div>
      </div>

   </div>
</header>


