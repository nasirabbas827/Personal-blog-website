<?php
include('config.php');
session_start();

// Check if user is logged in
if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: index.php");
    exit;
}

$user_id = $_SESSION["id"];  // Get the logged-in user's ID

// Get the media ID to edit
if (isset($_GET['id'])) {
    $media_id = $_GET['id'];

    // Fetch media details from the database
    $sql = "SELECT * FROM media WHERE id = '$media_id' AND user_id = '$user_id'";
    $result = mysqli_query($conn, $sql);
    $media = mysqli_fetch_assoc($result);

    // Check if media exists
    if (!$media) {
        echo "Media not found or you don't have permission to edit it.";
        exit;
    }

    // Handle media update (file, type, and description)
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $media_description = mysqli_real_escape_string($conn, $_POST['description']);
        $media_type = $_POST['media_type']; // New media type selected

        // Process the new file upload if it exists
        if (isset($_FILES["media_file"]) && $_FILES["media_file"]["error"] == 0) {
            // Get file info
            $file_name = $_FILES["media_file"]["name"];
            $file_tmp = $_FILES["media_file"]["tmp_name"];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            // Define allowed file types based on the selected type
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

            // Validate file type
            if (!in_array($file_ext, $media_types[$media_type])) {
                echo "Invalid file type. Please upload a valid file.";
                exit;
            }

            // Generate a new file name
            $new_file_name = uniqid() . '.' . $file_ext;
            $upload_dir = '../media/';
            $upload_path = $upload_dir . $new_file_name;

            // Delete the old file from the server
            $old_file_path = '../media/' . $media['url'];
            if (file_exists($old_file_path)) {
                unlink($old_file_path);  // Remove the old file
            }

            // Move the new uploaded file to the media directory
            if (move_uploaded_file($file_tmp, $upload_path)) {
                // Update the media entry in the database with the new file URL
                $update_sql = "UPDATE media SET type = '$media_type', url = '$new_file_name', description = '$media_description' WHERE id = '$media_id' AND user_id = '$user_id'";
                if (mysqli_query($conn, $update_sql)) {
                    echo "<script>alert('Media updated successfully'); window.location.href='upload_media.php';</script>";
                } else {
                    echo "Error: " . mysqli_error($conn);
                }
            } else {
                echo "Failed to upload the new file.";
            }
        } else {
            // If no new file is uploaded, just update the description and type
            $update_sql = "UPDATE media SET type = '$media_type', description = '$media_description' WHERE id = '$media_id' AND user_id = '$user_id'";
            if (mysqli_query($conn, $update_sql)) {
                echo "<script>alert('Media updated successfully'); window.location.href='upload_media.php';</script>";
            } else {
                echo "Error: " . mysqli_error($conn);
            }
        }
    }
} else {
    echo "Invalid media ID.";
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Media</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">

</head>
<body>

<?php include('navbar.php'); ?>

<div class="container mt-5">
    <h2>Edit Media</h2>
    
    <form action="edit_media.php?id=<?php echo $media['id']; ?>" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="media_type">Select Media Type:</label>
            <select class="form-control" name="media_type" required>
                <option value="image" <?php echo $media['type'] == 'image' ? 'selected' : ''; ?>>Image</option>
                <option value="video" <?php echo $media['type'] == 'video' ? 'selected' : ''; ?>>Video</option>
                <option value="audio" <?php echo $media['type'] == 'audio' ? 'selected' : ''; ?>>Audio</option>
                <option value="other" <?php echo $media['type'] == 'other' ? 'selected' : ''; ?>>Other</option>
            </select>
        </div>

        <div class="form-group">
            <label for="media_file">Choose New Media File (Optional):</label>
            <input type="file" class="form-control" name="media_file" accept="image/*,video/*,audio/*,.pdf,.txt,.docx">
        </div>

        <div class="form-group">
            <label for="description">Description:</label>
            <textarea class="form-control" name="description" rows="4"><?php echo htmlspecialchars($media['description']); ?></textarea>
        </div>
        
        <button type="submit" class="btn btn-primary">Update Media</button>
    </form>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
