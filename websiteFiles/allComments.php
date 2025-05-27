<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION["is_logged_in"]) || !$_SESSION["is_logged_in"]) {
    // Redirect to login page if user is not logged in
    header("Location: signIn.php");
    exit;
}
require("cookies.php"); 
require("../phpFiles/connect.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reviews</title>
<!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Faculty+Glyphic&display=swap" rel="stylesheet">
<!-- Tailwind CSS -->
    <link href="../cssFiles/output.css" rel="stylesheet">
</head>
<body>
<!-- -----------------------------------------------------------------------view all comments page : patients & doctors ----------------------------------------------------------------------- -->

<!-- Navigation Bar -->
<?php require("nav.php"); ?>
<!-- Main Content -->
    <main class="w-full bg-center bg-cover rounded-lg p-6 shadow-md anim" style="background-image: url('../pics/background8.jpeg');">
<!-- Back Button -->
        <div class="mb-6 anim1">
            <a href="profilePage.php" class="text-[#1A4568] font-bold uppercase hover:underline ml-2">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
        <div class="container mx-auto">
            <h1 class="text-4xl font-bold text-center text-[#1A4568] mb-8 anim1">Reviews</h1>
            <div class="bg-[aliceblue] w-[90%] mx-auto p-6 rounded-lg shadow-lg">
            <div class="space-y-6">
<!-- Individual Review -->
<?php
    //if the doctor is logged in, show all their comments
    if (isset($_COOKIE["doc_name"]) && !empty($_COOKIE['doc_name'])){
        // Prepare and execute the query
        $stmt = $conn->prepare("SELECT c.comment, c.rating_value, c.rating_date, p.name AS patient_name 
        FROM comments c JOIN patients p ON c.patient_id = p.patient_id 
        WHERE c.doctor_id = ? and hide='no'");
        $stmt->bind_param("i", $_SESSION["id"]);
        $stmt->execute();
        $res = $stmt->get_result();

        // Check if there are results
        if ($res->num_rows > 0) {
            // Loop through the results and create a separate container for each comment
            while ($comment_array = $res->fetch_assoc()) {
                echo '<div class="bg-white p-6 rounded-lg shadow-md mb-6">'; 
                echo '<p class="text-lg text-[#1A4568] mb-3 anim2"><strong>Patient Name: </strong>' . 
                    htmlspecialchars($comment_array["patient_name"]) . '</p>'; 
                echo '<p class="text-lg text-[#1A4568] mb-3 anim2"><strong>Comment: </strong>' . 
                    htmlspecialchars($comment_array["comment"]) . '</p>'; 
                echo '<p class="text-lg text-[#1A4568] mb-3 anim2"><strong>Rating: </strong>' . 
                    htmlspecialchars($comment_array["rating_value"]) . '</p>'; 
                echo '<p class="text-lg text-[#1A4568] mb-3 anim2" style="border-bottom: 1px solid #1A4568; 
                    padding-bottom: 8px;"><strong>Date: </strong>' .htmlspecialchars($comment_array["rating_date"]) . '</p>'; 
                echo '</div>'; // Closing the container div
            }
        } else {
            // No comments found
            echo '<p class="text-sm text-[#1A4568] anim2 font-bold">Patients haven\'t left any reviews yet.</p>';
        }
        //if the patient is logged in, show all their comments
    } elseif(isset($_COOKIE["user_name"]) && !empty($_COOKIE["user_name"])){
        // Prepare and execute the query
        $stmt = $conn->prepare("SELECT c.comment, c.rating_value, c.rating_date, d.name AS doctor_name 
        FROM comments c JOIN doctors d ON c.doctor_id = d.doctor_id 
        WHERE c.patient_id = ? and hide='no'");
        $stmt->bind_param("i", $_SESSION["id"]);
        $stmt->execute();
        $res = $stmt->get_result();

        // Check if there are results
        if ($res->num_rows > 0) {
            // Loop through the results and create a separate container for each comment
            while ($comment_array = $res->fetch_assoc()) {
                echo '<div class="bg-white p-6 rounded-lg shadow-md mb-6">'; 
                echo '<p class="text-sm text-[#1A4568] mb-2 anim2"><strong>Doctor Name: </strong>' . 
                htmlspecialchars($comment_array["doctor_name"]) . '</p>';
                echo '<p class="text-sm text-[#1A4568] mb-2 anim2"><strong>Comment: </strong>' . 
                htmlspecialchars($comment_array["comment"]) . '</p>';
                echo '<p class="text-sm text-[#1A4568] mb-2 anim2"><strong>Rating: </strong>' . 
                htmlspecialchars($comment_array["rating_value"]) . '</p>';
                echo '<p class="text-sm text-[#1A4568] mb-2 anim2" style="border-bottom: 1px solid #1A4568; 
                    padding-bottom: 5px;"><strong>Date: </strong>' .htmlspecialchars($comment_array["rating_date"]) . '</p>';
                echo '</div>'; 
            }
        } else {
            // No comments found
            echo '<p class="text-sm text-[#1A4568] anim2 font-bold">No comments have been posted yet.</p>';
        }
    }
    // Clean up
    $stmt->close();
    $conn->close();
?>
                </div>
            </div>
        </div>
    </main>
<!-- Footer -->
<?php require("footer.php"); ?>
</body>
</html>
