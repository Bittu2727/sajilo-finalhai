<?php

@include 'config.php';

session_start();

$admin_id = isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : 0;

if($admin_id == 0){
   header('location:login.php');
   exit;
}

if(isset($_GET['delete'])) {
   $delete_id = $_GET['delete'];
   $delete_message = $conn->prepare("DELETE FROM `messages` WHERE message_id = ?");
   $delete_message->execute([$delete_id]);
   header('location:admin_contacts.php');
   exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Messages</title>

   <!-- Font Awesome CSS link -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- Custom CSS file link -->
   <link rel="stylesheet" href="css/admin_style.css">

</head>
<body>
   
<?php include 'admin_header.php'; ?>

<section class="messages">

   <h1 class="title">Messages</h1>

   <div class="box-container">

   <?php
      $select_message = $conn->prepare("SELECT * FROM `messages`");
      $select_message->execute();
      if($select_message->rowCount() > 0){
         while($fetch_message = $select_message->fetch(PDO::FETCH_ASSOC)){
   ?>
   <div class="box">
      <p> Sender ID: <span><?= htmlspecialchars($fetch_message['sender_id']); ?></span> </p>
      <p> Name: <span><?= htmlspecialchars($fetch_message['name']); ?></span> </p>
      <p> Number: <span><?= htmlspecialchars($fetch_message['number']); ?></span> </p>
      <p> Email: <span><?= htmlspecialchars($fetch_message['email']); ?></span> </p>
      <p> Message: <span><?= htmlspecialchars($fetch_message['message']); ?></span> </p>
      <a href="admin_contacts.php?delete=<?= $fetch_message['message_id']; ?>" onclick="return confirm('Are you sure you want to delete this message?');" class="delete-btn">Delete</a>
   </div>
   <?php
         }
      } else {
         echo '<p class="empty">You have no messages to retrieve.</p>';
      }
   ?>

   </div>

</section>

<script src="js/script.js"></script>

</body>
</html>
