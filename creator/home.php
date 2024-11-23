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

// Fetch total posts by the user
$post_count_sql = "SELECT COUNT(*) AS total_posts FROM BlogPosts WHERE author_id = $user_id";
$post_count_result = mysqli_query($conn, $post_count_sql);
$post_count = mysqli_fetch_assoc($post_count_result)['total_posts'];

// Fetch total comments on user's posts
$comment_count_sql = "SELECT COUNT(*) AS total_comments FROM comments WHERE post_id IN (SELECT id FROM BlogPosts WHERE author_id = $user_id)";
$comment_count_result = mysqli_query($conn, $comment_count_sql);
$comment_count = mysqli_fetch_assoc($comment_count_result)['total_comments'];

// Fetch total media uploaded by the user
$media_count_sql = "SELECT COUNT(*) AS total_media FROM media WHERE user_id = $user_id";
$media_count_result = mysqli_query($conn, $media_count_sql);
$media_count = mysqli_fetch_assoc($media_count_result)['total_media'];

// Fetch recent activity (latest posts and media uploads)
$recent_posts_sql = "SELECT title, created_at FROM BlogPosts WHERE author_id = $user_id ORDER BY created_at DESC LIMIT 5";
$recent_posts_result = mysqli_query($conn, $recent_posts_sql);

$recent_media_sql = "SELECT type, url, created_at FROM media WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 5";
$recent_media_result = mysqli_query($conn, $recent_media_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Creator Dashboard</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .dashboard-section {
            margin-top: 20px;
        }

        .card {
            text-align: center;
        }

        .chart-container {
            position: relative;
            margin: auto;
            height: 300px;
            width: 100%;
        }
    </style>
</head>
<body>

<?php include('navbar.php'); ?>

<div class="container mt-5 mb-5">
    <h1 class="text-center mb-4">Creator Dashboard</h1>

    <!-- Overview Cards -->
    <div class="row text-center">
        <div class="col-md-4 dashboard-section">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5>Total Posts</h5>
                    <h3><?php echo $post_count; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4 dashboard-section">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5>Total Comments</h5>
                    <h3><?php echo $comment_count; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4 dashboard-section">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5>Total Media</h5>
                    <h3><?php echo $media_count; ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Graph Section -->
    <div class="mt-5">
        <h3 class="text-center">Activity Overview</h3>
        <div class="chart-container">
            <canvas id="activityChart"></canvas>
        </div>
    </div>

    <!-- Recent Posts and Media -->
    <div class="row mt-5">
        <div class="col-md-6">
            <h4>Recent Posts</h4>
            <ul class="list-group">
                <?php while ($post = mysqli_fetch_assoc($recent_posts_result)) { ?>
                    <li class="list-group-item">
                        <strong><?php echo htmlspecialchars($post['title']); ?></strong> 
                        <span class="text-muted">(<?php echo date('F j, Y', strtotime($post['created_at'])); ?>)</span>
                    </li>
                <?php } ?>
                <?php if (mysqli_num_rows($recent_posts_result) == 0) { ?>
                    <li class="list-group-item">No recent posts.</li>
                <?php } ?>
            </ul>
        </div>

        <div class="col-md-6">
            <h4>Recent Media Uploads</h4>
            <ul class="list-group">
                <?php while ($media = mysqli_fetch_assoc($recent_media_result)) { ?>
                    <li class="list-group-item">
                        <strong><?php echo ucfirst(htmlspecialchars($media['type'])); ?>:</strong> 
                        <a href="../media/<?php echo htmlspecialchars($media['url']); ?>" target="_blank">View</a> 
                        <span class="text-muted">(<?php echo date('F j, Y', strtotime($media['created_at'])); ?>)</span>
                    </li>
                <?php } ?>
                <?php if (mysqli_num_rows($recent_media_result) == 0) { ?>
                    <li class="list-group-item">No recent media uploads.</li>
                <?php } ?>
            </ul>
        </div>
    </div>
</div>

<script>
    // Chart.js Configuration
    const ctx = document.getElementById('activityChart').getContext('2d');
    const activityChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Posts', 'Comments', 'Media Uploads'],
            datasets: [{
                label: 'Total Counts',
                data: [<?php echo $post_count; ?>, <?php echo $comment_count; ?>, <?php echo $media_count; ?>],
                backgroundColor: ['#007bff', '#28a745', '#17a2b8'],
                borderColor: ['#0056b3', '#1e7e34', '#117a8b'],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
