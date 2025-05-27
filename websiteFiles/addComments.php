<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require("../phpFiles/connect.php");
// Check if the user is logged in and has a confirmed appointment
$patient_id = $_SESSION['id'] ?? null; 
$check=$conn->prepare('SELECT appointment_id FROM appointments 
                            WHERE patient_id=? AND STATUS="confirmed"');
$check->bind_param('i',$patient_id);
$check->execute();
$result=$check->get_result();
if($result &&$result->num_rows ==0){
    header("Location: profilePage.php");
    exit;
}
$error = [];
$success = "";
// Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $doctor_id = $_POST['doctor_info'] ?? null;
    $comment = trim($_POST['comment'] ?? '');
    $rating = $_POST['rating'] ?? null;
    $appointment_id = $_POST['appointment_id'] ?? null;
    $rating_date = date('Y-m-d');

    // Validation
    if (empty($doctor_id)) $error[] = "Please select a doctor.";
    if (empty($comment)) $error[] = "Please enter your comment.";
    if (empty($rating)) $error[] = "Please provide a rating.";
    // if no error, proceed to insert the comment
    if (empty($error)) {
        $cleaned_comment=preg_replace("/\r\n|\r|\n/", ' ', trim($comment));
        $stmt = $conn->prepare("INSERT INTO comments (patient_id, doctor_id, comment, rating_value, rating_date,hide) 
                                VALUES (?, ?, ?, ?, ?,'no')");
        $stmt->bind_param("iisis", $patient_id, $doctor_id, $cleaned_comment, $rating, $rating_date);
        if ($stmt->execute()) {
            $success = "Your comment was posted successfully. Thank you for your feedback!";
            $updateStmt = $conn->prepare("UPDATE appointments SET status = 'completed' 
                        WHERE patient_id = ? AND doctor_id = ? AND appointment_id = ?");
            $updateStmt->bind_param("iii", $patient_id, $doctor_id, $appointment_id);
            $updateStmt->execute();
            $updateStmt->close();
        } else {
            $error[] = "An error occurred while submitting your comment. Please try again later.";
        }
    }
}
?>
<!-------------------------------------------------------------------------Add comments page : patients----------------------------------------------------------------------- -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VitalCare</title>
<!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Faculty+Glyphic&display=swap" rel="stylesheet">
<!-- CSS Files -->
    <link href="../cssFiles/output.css" rel="stylesheet">
    <link rel="stylesheet" href="../cssFiles/styles.css">
</head>
<body>
<?php require("nav.php");?>
<!-- Main Comment Section -->
<div class="max-w-[600px] mx-auto p-10 bg-center bg-contain rounded-lg shadow-md" 
    style="background-image: url('../pics/background8.jpeg'); height: 700px; width: 500px;">
<!-- Back Button -->
    <div class="mb-6">
        <a href="profilePage.php" class="text-[#1A4568] font-bold uppercase hover:underline ml-2">
            <i class="fas fa-arrow-left"></i> Back</a>
    </div>
<!-- Display Error Messages -->
<?php if (!empty($error)): ?>
    <div class="px-4 py-3 rounded relative mb-4">
        <ul class="mt-2 list-disc list-inside">
<?php foreach ($error as $err): ?>
<li class="error-msg font-bold anim"><?php echo htmlspecialchars($err); ?></li>
<?php endforeach; ?>
        </ul>
    </div>
<?php endif;?>
<!-- Display Success Message -->
<?php if (!empty($success)): ?>
    <div class="px-4 py-3 rounded relative mb-4">
<p class="succ-msg font-bold anim "><?php echo htmlspecialchars($success); ?></p>  
    </div>
<?php endif; ?>
<!-- Comment Section -->
    <div class="flex flex-col justify-center items-center bg-opacity-10 p-10 rounded-xl w-full sm:w-96 anim">
    <h3 class="text-2xl font-bold text-center text-[#1A4568] mb-8 anim1">Share Your Thoughts</h3>
        <form action="#" class="flex flex-col w-full anim2" method="post">
<!-- Doctor Selection -->
<?php
        $stmt = $conn->prepare("
            SELECT DISTINCT d.doctor_id, d.name, a.appointment_id 
            FROM doctors d 
            JOIN appointments a ON d.doctor_id = a.doctor_id 
            WHERE patient_id = ? AND a.status = 'confirmed'");
        $stmt->bind_param("i", $patient_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        if (empty($rows)) {
            echo '<p class="px-4 py-2 text-[#1a4568] font-bold text-center">You\'ve shared feedback on all your appointments. Thank you!</p>';
        }
        else{
        // Store the appointment_id in a hidden input field
        echo '<input type="hidden" name="appointment_id" value="' . htmlspecialchars($rows[0]['appointment_id']) . '">';
?>
            <select id="specialty" name="doctor_info" 
                class="bg-[#DBECF4] border-none outline-none w-full px-4 py-3 
                text-[#1A4568] font-bold text-lg rounded-[15px] hover:text-[#1A4568]" required>
                <option value="" disabled selected class="text-gray-500">Review a doctor</option>
<?php
                foreach ($rows as $row) {
                    echo '<option value="' . htmlspecialchars($row['doctor_id']) . '">' . ucwords(htmlspecialchars($row['name']))
                        . '</option>';
                }
                $stmt->close();
                $conn->close()
?>
            </select>
<!-- Rating Input -->
            <label for="rating" class="text-[#1A4568] text-lg font-bold m-2">Rating:</label>
            <input type="number" id="rating" name="rating" min="1" max="5" step="1" 
                class="bg-[#DBECF4] text-[#1A4568] font-bold text-lg px-4 py-3 w-full 
                rounded-[15px] outline-none hover:text-[#1A4568]" required>
<!-- Comment Input -->
            <label for="comment" class="block text-[#1A4568] mt-4 mb-2 text-lg font-bold">Add Your Comment:</label>
            <textarea maxlength="300" id="comment" name="comment" rows="4" 
                placeholder="Share your feedback here..." 
                class="text-[#1A4568] p-4 text-lg outline-none border-2 border-[#1A4568] 
                bg-transparent rounded-lg w-full" required></textarea>
<!-- Submit Button -->
            <button type="submit" 
                class="mt-5 px-6 py-3 bg-[#1A4568] text-white rounded-full font-bold uppercase shadow-md 
                hover:bg-white hover:text-[#1A4568] hover:scale-105 transition-transform duration-300 w-full">
                Submit Comment
            </button>
<?php
            } // End of else statement for checking if there are any appointments
?>
        </form>
    </div>
    <div class="mt-4">
        <a href="allComments.php" class="text-[#1A4568] font-bold uppercase hover:underline flex items-center anim1">
            <i class="fas fa-comment mr-2"></i>View All Comments You've Posted</a>
    </div>
</div>
<?php require("footer.php"); ?>
</body>
</html>
