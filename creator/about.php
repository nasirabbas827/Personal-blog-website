<?php
include('config.php');
session_start();

// Check if user is logged in
if (!isset($_SESSION['id']) || empty($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

$author_id = $_SESSION['id']; // Get the logged-in user's ID

// Fetch current about information for the logged-in author
$sql = "SELECT * FROM about_info WHERE author_id = $author_id";
$result = mysqli_query($conn, $sql);
$about = mysqli_fetch_assoc($result);

// Handle form submission to update information
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bio = mysqli_real_escape_string($conn, $_POST['bio']);
    $facebook = mysqli_real_escape_string($conn, $_POST['facebook']);
    $twitter = mysqli_real_escape_string($conn, $_POST['twitter']);
    $instagram = mysqli_real_escape_string($conn, $_POST['instagram']);
    
    // Update the about information if it exists
    if ($about) {
        $update_sql = "UPDATE about_info 
                       SET bio = '$bio', facebook_link = '$facebook', twitter_link = '$twitter', instagram_link = '$instagram', updated_at = NOW() 
                       WHERE author_id = $author_id";
        mysqli_query($conn, $update_sql);
    } else {
        // Insert new about information if not already present
        $insert_sql = "INSERT INTO about_info (author_id, bio, facebook_link, twitter_link, instagram_link) 
                       VALUES ($author_id, '$bio', '$facebook', '$twitter', '$instagram')";
        mysqli_query($conn, $insert_sql);
    }

    // Redirect after saving
    header("Location: about.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Me - Update Information</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include('navbar.php'); ?>

    <div class="container mt-5 mb-5">
        <h2>About Me</h2>

        <!-- Display current about info -->
        <div class="card mb-4">
            <div class="card-body">
                <h4>Personal Bio</h4>
                <p><?php echo htmlspecialchars($about['bio'] ?? 'No bio available.'); ?></p>
                <h5>Social Media Links</h5>
                <ul>
                    <li><a href="<?php echo htmlspecialchars($about['facebook_link'] ?? '#'); ?>" target="_blank">Facebook</a></li>
                    <li><a href="<?php echo htmlspecialchars($about['twitter_link'] ?? '#'); ?>" target="_blank">Twitter</a></li>
                    <li><a href="<?php echo htmlspecialchars($about['instagram_link'] ?? '#'); ?>" target="_blank">Instagram</a></li>
                </ul>
            </div>
        </div>

        <!-- Form to edit about info -->
        <h3>Update Your Information</h3>
        <form action="about.php" method="POST">
            <div class="form-group">
                <label for="bio">Personal Bio:</label>
                <textarea id="bio" name="bio" class="form-control" rows="4"><?php echo htmlspecialchars($about['bio'] ?? ''); ?></textarea>
            </div>
            <div class="form-group">
                <label for="facebook">Facebook Link:</label>
                <input type="url" id="facebook" name="facebook" class="form-control" value="<?php echo htmlspecialchars($about['facebook_link'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="twitter">Twitter Link:</label>
                <input type="url" id="twitter" name="twitter" class="form-control" value="<?php echo htmlspecialchars($about['twitter_link'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="instagram">Instagram Link:</label>
                <input type="url" id="instagram" name="instagram" class="form-control" value="<?php echo htmlspecialchars($about['instagram_link'] ?? ''); ?>">
            </div>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
