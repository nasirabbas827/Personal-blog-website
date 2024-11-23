<?php
include('config.php');

session_start();

// Check if user is logged in, if not, redirect to login page
if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: index.php");
    exit;
}

// Get the user ID from the session
$user_id = $_SESSION["id"];

// Fetch the username from the users table
$user_sql = "SELECT username FROM users WHERE id = $user_id";
$user_result = mysqli_query($conn, $user_sql);

if ($user_result && mysqli_num_rows($user_result) > 0) {
    $user = mysqli_fetch_assoc($user_result);
    $_SESSION["username"] = $user['username']; // Store in session
} else {
    // Handle error if user not found
    echo "Error: User not found.";
    exit;
}

$post_id = $_GET['id'];

$post_sql = "SELECT posts.id, posts.title, posts.content, posts.created_at, posts.featured_image, posts.author_id, posts.category_id, 
             categories.name AS category_name, users.username AS author_name, users.bio AS author_bio, users.profile_picture AS author_profile_pic
             FROM BlogPosts AS posts
             JOIN categories ON posts.category_id = categories.id
             JOIN users ON posts.author_id = users.id
             WHERE posts.id = $post_id AND posts.status = 'published'";
$post_result = mysqli_query($conn, $post_sql);
$post = mysqli_fetch_assoc($post_result);

$tags_sql = "SELECT name FROM categories WHERE id = " . $post['category_id'];
$tags_result = mysqli_query($conn, $tags_sql);
$tags = mysqli_fetch_assoc($tags_result);

$comments_sql = "SELECT comment, author_name, created_at FROM comments WHERE post_id = $post_id ORDER BY created_at DESC";
$comments_result = mysqli_query($conn, $comments_sql);

$related_posts_sql = "SELECT id, title FROM BlogPosts WHERE author_id = " . $post['author_id'] . " AND id != $post_id AND status = 'published' ORDER BY created_at DESC LIMIT 5";
$related_posts_result = mysqli_query($conn, $related_posts_sql);

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['contact_message'])) {
        $contact_message = mysqli_real_escape_string($conn, $_POST['contact_message']);
        $contact_name = mysqli_real_escape_string($conn, $_POST['contact_name']);
        $contact_email = mysqli_real_escape_string($conn, $_POST['contact_email']);

        $insert_message_sql = "INSERT INTO post_messages (post_id, sender_name, sender_email, message) 
                               VALUES ('$post_id', '$contact_name', '$contact_email', '$contact_message')";
        if (mysqli_query($conn, $insert_message_sql)) {
            $message = "<div class='alert alert-success'>Your message has been sent to the author.</div>";
        } else {
            $message = "<div class='alert alert-danger'>There was an error sending your message. Please try again.</div>";
        }
    }

    if (isset($_POST['comment']) && isset($_SESSION["id"])) {
        $comment = mysqli_real_escape_string($conn, $_POST['comment']);
        $author_name = $_SESSION["username"];

        $insert_comment_sql = "INSERT INTO comments (post_id, comment, author_name, created_at) VALUES ('$post_id', '$comment', '$author_name', NOW())";
        if (mysqli_query($conn, $insert_comment_sql)) {
            $message = "<div class='alert alert-success'>Your comment has been added.</div>";
        } else {
            $message = "<div class='alert alert-danger'>There was an error adding your comment. Please try again.</div>";
        }
    }
}

function clean_html($content) {
    return strip_tags($content);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($post['title']); ?></title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
    <style>
        .author-profile {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        .author-profile img {
            border-radius: 50%;
            width: 80px;
            height: 80px;
            margin-right: 15px;
        }
        .img-fluid {
            height: 200px;
        }
        .comment-card {
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 15px;
        }

    </style>
</head>
<body>

<?php include('navbar.php'); ?>

<div class="container mt-5 mb-5">
    <?php echo $message; ?>

    <div class="author-profile card mb-4" style="background-color: #f8f9fa; border-radius: 10px; padding: 15px;">
    <div class="row no-gutters">
        <div class="col-md-3">
            <?php if ($post['author_profile_pic']) { ?>
                <img src="uploads/<?php echo $post['author_profile_pic']; ?>" alt="Author Profile Picture" class="img-fluid rounded-circle">
            <?php } else { ?>
                <img src="https://via.placeholder.com/80" alt="Author Profile Picture" class="img-fluid rounded-circle">
            <?php } ?>
        </div>
        <div class="col-md-9">
            <div class="card-body">
                <h5 class="card-title"><?php echo htmlspecialchars($post['author_name']); ?></h5>
                <p class="card-text"><?php echo nl2br(htmlspecialchars($post['author_bio'])); ?></p>
            </div>
        </div>
    </div>
</div>


    <h1><?php echo htmlspecialchars($post['title']); ?></h1>
    <p class="text-muted">By <strong><?php echo htmlspecialchars($post['author_name']); ?></strong> on <?php echo date('F j, Y', strtotime($post['created_at'])); ?></p>

    <?php if ($post['featured_image']) { ?>
        <img src="images/<?php echo $post['featured_image']; ?>" class="img-fluid mb-4" alt="Featured Image">
    <?php } else { ?>
        <img src="https://via.placeholder.com/800x400" class="img-fluid mb-4" alt="Featured Image">
    <?php } ?>

    <div class="card">
        <div class="card-body">
            <?php echo htmlspecialchars_decode($post['content']); ?>
        </div>
    </div>

    <div class="related-categories mt-4">
        <h5>Category:</h5>
        <p><a href="home.php?search=<?php echo urlencode($tags['name']); ?>"><?php echo htmlspecialchars($tags['name']); ?></a></p>
    </div>

    <div class="comments-section mt-4">
    <h4>Comments</h4>
    <div class="card">
        <div class="card-body">
            <div class="row">
                <?php 
                $count = 0;
                if (mysqli_num_rows($comments_result) > 0) {
                    while ($comment = mysqli_fetch_assoc($comments_result)) { 
                        if ($count > 0 && $count % 3 == 0) echo '</div><div class="row">'; 
                        ?>
                        <div class="col-md-4">
                            <div class="card mb-3" style="border: 2px solid #ddd; border-radius: 10px; background-color:#d5e6da;">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($comment['author_name']); ?></h5>
                                    <p class="card-subtitle text-muted"><?php echo date('F j, Y', strtotime($comment['created_at'])); ?></p>
                                    <p class="card-text"><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php 
                    $count++;
                    }
                } else {
                    echo "<p class='text-center'>No comments yet. Be the first to comment!</p>";
                }
                ?>
            </div>
        </div>
    </div>
</div>


    <div class="leave-comment mt-4">
        <h5>Leave a Comment</h5>
        <?php if (isset($_SESSION["id"])) { ?>
            <form action="view_post.php?id=<?php echo $post['id']; ?>" method="POST">
                <div class="form-group">
                    <textarea name="comment" class="form-control" rows="4" placeholder="Your comment" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Submit Comment</button>
            </form>
        <?php } else { ?>
            <p>You need to <a href="login.php">log in</a> to leave a comment.</p>
        <?php } ?>
    </div>

    <div class="contact-form mt-5">
        <h5>Contact the Author</h5>
        <form method="POST">
            <div class="form-group">
                <label for="contact_name">Your Name</label>
                <input type="text" class="form-control" name="contact_name" required>
            </div>
            <div class="form-group">
                <label for="contact_email">Your Email</label>
                <input type="email" class="form-control" name="contact_email" required>
            </div>
            <div class="form-group">
                <label for="contact_message">Your Message</label>
                <textarea class="form-control" name="contact_message" rows="5" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Send Message</button>
        </form>
    </div>
</div>

<?php
include 'footer.php';
?>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.4.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
