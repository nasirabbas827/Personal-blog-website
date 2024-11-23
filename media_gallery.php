<?php
include('config.php');
session_start();

// Fetch media for the logged-in user (or all users, depending on your needs)
$sql = "SELECT m.id, m.type, m.url, m.description, u.username 
        FROM media m
        JOIN users u ON m.user_id = u.id
        ORDER BY m.created_at DESC";  
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Media Gallery</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .media-card {
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

        .media-card img, .media-card video, .media-card audio {
            max-width: 100%;
            max-height: 200px;
            margin-bottom: 10px;
        }

        .media-card h4 {
            margin-top: 10px;
            font-size: 18px;
        }

        .media-card p {
            flex-grow: 1;
            font-size: 14px;
            color: #666;
        }

        .media-card .btn {
            margin-top: 10px;
        }

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
    <h2 class="text-center mb-5">Media Gallery</h2>
    
    <div class="row">
        <?php
        // Check if there are any media items
        if (mysqli_num_rows($result) > 0) {
            while ($media = mysqli_fetch_assoc($result)) {
                ?>
                <div class="col-md-4">
                    <div class="media-card">
                        <?php
                        // Check the media type and display accordingly
                        if ($media['type'] == 'image') {
                            echo "<img src='media/" . htmlspecialchars($media['url']) . "' alt='Image'>";
                        } elseif ($media['type'] == 'video') {
                            echo "<video controls><source src='media/" . htmlspecialchars($media['url']) . "' type='video/mp4'>Your browser does not support the video tag.</video>";
                        } elseif ($media['type'] == 'audio') {
                            echo "<audio controls><source src='media/" . htmlspecialchars($media['url']) . "' type='audio/mp3'>Your browser does not support the audio element.</audio>";
                        } else {
                            echo "<p>File type not supported for display.</p>";
                        }
                        ?>

                        <h4><?php echo htmlspecialchars($media['username']); ?></h4>
                        <p><?php echo nl2br(htmlspecialchars($media['description'])); ?></p>
                        
                    </div>
                </div>
                <?php
            }
        } else {
            echo "<p>No media uploaded yet.</p>";
        }
        ?>
    </div>
</div>

<?php include 'footer.php'; ?>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
