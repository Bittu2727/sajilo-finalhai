<?php

@include 'config.php';

session_start();

$user_id = $_SESSION['user_id'];

if(!isset($user_id)){
   header('location:login.php');
   exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">  
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>About</title>

   <!-- font awesome cdn link -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link -->
   <link rel="stylesheet" href="css/style.css">
</head>
<body>
   
<?php include 'header.php'; ?>

<section class="about">
   <div class="row">
      <div class="box">
         <img src="images/about-img-1.png" alt="">
         <h3>Why choose us?</h3>
         <p>Choose Sajilo Grocers for a streamlined, user-friendly solution that offers real-time inventory tracking, comprehensive reporting, and seamless integration with existing tools. Our customizable platform ensures your unique business needs are met, while our secure, reliable system and exceptional customer support provide peace of mind. Enjoy competitive pricing and a hassle-free experience as you optimize your grocery store operations with us.</p>
         <a href="contact.php" class="btn">Contact us</a>
      </div>

      <div class="box">
         <img src="images/about-img-2.png" alt="">
         <h3>What we provide?</h3>
         <p>Sajilo Grocers strengthens the relationship between buyers and sellers by ensuring real-time inventory tracking, so customers always find what they need. We offer insights into customer preferences, helping you tailor offerings to build loyalty. With an easy-to-use interface, customizable features, and secure, reliable support, we help you create a personalized shopping experience that fosters lasting connections and trust.</p>
         <a href="shop.php" class="btn">Our shop</a>
      </div>
   </div>
</section>

<?php include 'footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>
