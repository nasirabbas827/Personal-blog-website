<?php
include('config.php');
session_start();

// Fetch only creators (filter by user type)
$sql = "SELECT u.id, u.username, u.profile_picture, a.bio, a.facebook_link, a.twitter_link, a.instagram_link
        FROM users u
        LEFT JOIN about_info a ON u.id = a.author_id
        WHERE u.usertype = 'creator'";  // Filter by user type 'creator'
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Authors</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .author-card {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 350px; /* Fixed height for equal cards */
        }

        .author-card img {
            border-radius: 50%;
            width: 100px;
            height: 100px;
        }

        .author-card h4 {
            margin-top: 10px;
            font-size: 20px;
        }

        .author-card p {
            flex-grow: 1;
            font-size: 14px;
            color: #666;
        }

        .social-icons a {
            margin: 5px;
            font-size: 20px;
            color: #333;
        }

        .social-icons a:hover {
            color: #007bff;
        }

        /* Ensure all cards in the row have the same height */
        .row {
            display: flex;
            flex-wrap: wrap;
        }

        .col-md-4 {
            display: flex;
            justify-content: space-between;
        }
    </style>
</head>
<body>
    <?php include('navbar.php'); ?>

    <div class="container mt-5">
        <h2 class="text-center mb-5">About the Authors</h2>
        
        <div class="row">
            <?php
            // Check if there are any authors
            if (mysqli_num_rows($result) > 0) {
                while ($author = mysqli_fetch_assoc($result)) {
                    ?>
                    <div class="col-md-4">
                        <div class="author-card">
                            <?php if ($author['profile_picture']) { ?>
                                <img src="uploads/<?php echo $author['profile_picture']; ?>" alt="Profile Picture">
                            <?php } else { ?>
                                <img src="https://via.placeholder.com/100" alt="Profile Picture">
                            <?php } ?>
                            <h4><?php echo htmlspecialchars($author['username']); ?></h4>
                            <p><?php echo nl2br(htmlspecialchars($author['bio'])); ?></p>
                            
                            <div class="social-icons">
                                <?php if ($author['facebook_link']) { ?>
                                    <a href="<?php echo htmlspecialchars($author['facebook_link']); ?>" target="_blank">
                                        <i class="fab fa-facebook-f"></i>
                                    </a>
                                <?php } ?>
                                <?php if ($author['twitter_link']) { ?>
                                    <a href="<?php echo htmlspecialchars($author['twitter_link']); ?>" target="_blank">
                                        <i class="fab fa-twitter"></i>
                                    </a>
                                <?php } ?>
                                <?php if ($author['instagram_link']) { ?>
                                    <a href="<?php echo htmlspecialchars($author['instagram_link']); ?>" target="_blank">
                                        <i class="fab fa-instagram"></i>
                                    </a>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo "<p>No creators found.</p>";
            }
            ?>
        </div>
    </div>
    <?php
include 'footer.php';
?>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
