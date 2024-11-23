<?php
include('config.php');
session_start();

// Check if user is logged in
if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: index.php");
    exit;
}

$user_id = $_SESSION["id"];  // Get the logged-in user's ID
$message = "";

// Handle media upload
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_FILES["media_file"]) && $_FILES["media_file"]["error"] == 0) {
        $media_type = $_POST['media_type'];
        $media_description = mysqli_real_escape_string($conn, $_POST['description']);

        // Get file info
        $file_name = $_FILES["media_file"]["name"];
        $file_tmp = $_FILES["media_file"]["tmp_name"];
        $file_size = $_FILES["media_file"]["size"];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // Define allowed file extensions for different media types
        $allowed_image_ext = ['jpg', 'jpeg', 'png', 'gif'];
        $allowed_video_ext = ['mp4', 'avi', 'mov', 'wmv'];
        $allowed_audio_ext = ['mp3', 'wav', 'ogg'];
        $allowed_other_ext = ['pdf', 'txt', 'docx'];

        $media_types = [
            'image' => $allowed_image_ext,
            'video' => $allowed_video_ext,
            'audio' => $allowed_audio_ext,
            'other' => $allowed_other_ext
        ];

        // Validate file extension
        if (!in_array($file_ext, $media_types[$media_type])) {
            $message = "Invalid file type. Please upload an image, video, or audio file.";
        } else {
            // Generate a unique file name
            $new_file_name = uniqid() . '.' . $file_ext;
            $upload_dir = '../media/';
            $upload_path = $upload_dir . $new_file_name;

            // Move the uploaded file to the media directory
            if (move_uploaded_file($file_tmp, $upload_path)) {
                // Insert media into the database
                $insert_sql = "INSERT INTO media (user_id, type, url, description) VALUES ('$user_id', '$media_type', '$new_file_name', '$media_description')";
                if (mysqli_query($conn, $insert_sql)) {
                    $message = "Media uploaded successfully!";
                } else {
                    $message = "Error: " . mysqli_error($conn);
                }
            } else {
                $message = "Failed to upload the file.";
            }
        }
    } else {
        $message = "No file selected or an error occurred.";
    }
}

// Handle media deletion
if (isset($_GET['delete_id'])) {
    $media_id = $_GET['delete_id'];

    // Get the file name to delete from the media folder
    $sql = "SELECT url FROM media WHERE id = '$media_id' AND user_id = '$user_id'";
    $result = mysqli_query($conn, $sql);
    $media = mysqli_fetch_assoc($result);

    if ($media) {
        // Delete the file from the server
        $file_path = '../media/' . $media['url'];
        if (unlink($file_path)) {
            // Delete the record from the database
            $delete_sql = "DELETE FROM media WHERE id = '$media_id' AND user_id = '$user_id'";
            if (mysqli_query($conn, $delete_sql)) {
                $message = "Media deleted successfully!";
            } else {
                $message = "Error: " . mysqli_error($conn);
            }
        } else {
            $message = "Failed to delete the media file.";
        }
    }
}

// Fetch uploaded media for the logged-in user
$sql = "SELECT * FROM media WHERE user_id = '$user_id' ORDER BY created_at DESC";
$media_result = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uploaded Media</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include('navbar.php'); ?>

<div class="container mt-5">
    <h2>Your Uploaded Media</h2>

    <?php if ($message != ""): ?>
        <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <!-- Media Upload Form -->
    <form action="upload_media.php" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="media_type">Select Media Type:</label>
            <select class="form-control" name="media_type" required>
                <option value="image">Image</option>
                <option value="video">Video</option>
                <option value="audio">Audio</option>
                <option value="other">Other</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="media_file">Choose Media File:</label>
            <input type="file" class="form-control" name="media_file" accept="image/*,video/*,audio/*,.pdf,.txt,.docx" required>
        </div>

        <div class="form-group">
            <label for="description">Description (Optional):</label>
            <textarea class="form-control" name="description" rows="4"></textarea>
        </div>
        
        <button type="submit" class="btn btn-primary">Upload Media</button>
    </form>

    <h3 class="mt-5">Your Media List</h3>
    <div class="row">
        <?php
        if (mysqli_num_rows($media_result) > 0) {
            while ($media = mysqli_fetch_assoc($media_result)) {
                ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <img src="../media/<?php echo $media['url']; ?>" class="card-img-top" alt="Media">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo ucfirst($media['type']); ?></h5>
                            <p class="card-text"><?php echo nl2br(htmlspecialchars($media['description'])); ?></p>
                            <a href="edit_media.php?id=<?php echo $media['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="upload_media.php?delete_id=<?php echo $media['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this media?');">Delete</a>
                        </div>
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

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
