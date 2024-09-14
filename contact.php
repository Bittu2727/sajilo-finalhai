<?php
@include 'config.php'; // Database connection

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id']; // Use session to get the user ID
$message = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['send'])) {
        // Sanitize and validate input
        $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $number = filter_var($_POST['number'], FILTER_SANITIZE_STRING);
        $msg = filter_var($_POST['msg'], FILTER_SANITIZE_STRING);

        // Ensure the sender_id is retrieved from session
        $sender_id = $user_id;

        // Check if the message already exists
        $select_message = $conn->prepare("SELECT * FROM `messages` WHERE name = ? AND email = ? AND number = ? AND message = ?");
        $select_message->execute([$name, $email, $number, $msg]);

        if ($select_message->rowCount() > 0) {
            $message[] = 'Message already sent!';
        } else {
            // Insert new message into the database
            $insert_message = $conn->prepare("INSERT INTO `messages` (sender_id, name, email, number, message) VALUES (?, ?, ?, ?, ?)");
            try {
                $insert_message->execute([$sender_id, $name, $email, $number, $msg]);
                $message[] = 'Message sent successfully!';
            } catch (PDOException $e) {
                // Handle database errors
                $message[] = 'Error: ' . $e->getMessage();
            }
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
    <title>Contact</title>

    <!-- Font Awesome CDN link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <!-- Custom CSS file link -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'header.php'; ?>

<section class="contact">
    <h1 class="title">Get in Touch</h1>

    <form action="" method="POST">
        <input type="text" name="name" class="box" required placeholder="Enter your name">
        <input type="email" name="email" class="box" required placeholder="Enter your email">
        <input type="number" name="number" min="0" class="box" required placeholder="Enter your number">
        <textarea name="msg" class="box" required placeholder="Enter your message" cols="30" rows="10"></textarea>
        <input type="submit" value="Send Message" class="btn" name="send">
    </form>

    <?php
    if (!empty($message)) {
        foreach ($message as $msg) {
            echo '<p class="message">' . htmlspecialchars($msg) . '</p>';
        }
    }
    ?>

</section>

<?php include 'footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>
