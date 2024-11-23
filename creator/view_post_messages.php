<?php
include('config.php');
session_start();

// Check if user is logged in, if not, redirect to login page
if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: ../index.php");
    exit;
}

// Get the user ID from the session
$user_id = $_SESSION["id"];

// Handle message deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $message_id = $_GET['delete'];

    // Delete message only if it belongs to the creator's posts
    $delete_sql = "
        DELETE FROM post_messages 
        WHERE id = $message_id 
        AND post_id IN (SELECT id FROM BlogPosts WHERE author_id = $user_id)";
    mysqli_query($conn, $delete_sql);

    // Redirect back to avoid resubmission
    header("Location: view_post_messages.php");
    exit;
}

// Fetch messages for posts authored by the logged-in creator
$messages_sql = "
    SELECT pm.id, pm.sender_name, pm.sender_email, pm.message, pm.created_at, bp.title AS post_title
    FROM post_messages pm
    JOIN BlogPosts bp ON pm.post_id = bp.id
    WHERE bp.author_id = $user_id
    ORDER BY pm.created_at DESC";
$messages_result = mysqli_query($conn, $messages_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>View Post Messages</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">

</head>
<body>

<?php include('navbar.php'); ?>

<div class="container mt-5">
    <h1 class="text-center mb-4">Post Messages</h1>

    <!-- Check if there are messages -->
    <?php if (mysqli_num_rows($messages_result) > 0) { ?>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>Post Title</th>
                        <th>Sender Name</th>
                        <th>Sender Email</th>
                        <th>Message</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $counter = 1; ?>
                    <?php while ($message = mysqli_fetch_assoc($messages_result)) { ?>
                        <tr>
                            <td><?php echo $counter++; ?></td>
                            <td><?php echo htmlspecialchars($message['post_title']); ?></td>
                            <td><?php echo htmlspecialchars($message['sender_name']); ?></td>
                            <td><?php echo htmlspecialchars($message['sender_email']); ?></td>
                            <td><?php echo htmlspecialchars($message['message']); ?></td>
                            <td><?php echo date('F j, Y, g:i a', strtotime($message['created_at'])); ?></td>
                            <td>
                                <a href="?delete=<?php echo $message['id']; ?>" 
                                   class="btn btn-danger btn-sm" 
                                   onclick="return confirm('Are you sure you want to delete this message?');">Delete</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    <?php } else { ?>
        <div class="alert alert-info text-center">No messages found for your posts.</div>
    <?php } ?>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
