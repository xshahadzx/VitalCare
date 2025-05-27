<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require("../phpFiles/connect.php");
$doctor_id = $_SESSION['id'] ?? null; 
// Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Check if fields exist and trim inputs
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $price = isset($_POST['price']) ? trim($_POST['price']) : '';
    $time = isset($_POST['time']) ? trim($_POST['time']) : '';
    $errors = [];
    $success = "";

    // Validate inputs
    if (!preg_match("/^[a-zA-Z-' ]+$/", $name)) {
        $errors[]="Invalid service name. Only letters, spaces, hyphens, and apostrophes are allowed.";
    }
    // Validate price
    if (!filter_var($price, FILTER_VALIDATE_INT, ["options" => ["min_range" => 100]])) {
        $errors[] = 'Please enter a valid price. at least 100 SAR.';
    }

    // Validate time
    if (!filter_var($time, FILTER_VALIDATE_INT, ["options" => ["min_range" => 15]])) {
        $errors[] = 'Please enter a valid time. at least 15 minutes.';
    }
    // Check if time is in the range of 15 to 300 minutes(5 hours)
    if($time<=300&&($time>60||$time<=60)){
        $hours = floor($time / 60); // Get full hours
        $minutes = $time % 60; // Get remaining minutes
        $seconds = 0; // Always zero in this case
        $formatted_time = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
        $time=$formatted_time;
    }
    // Validate service name
    if (empty($name)) {
        $errors[] = 'Please enter a service name. It cannot be empty.';
    } else {
        // Remove line breaks and extra spaces
        $cleaned_name = preg_replace("/\r\n|\r|\n/", ' ', $name);
    }
    //check the the service already exits 
    $check_services=$conn->prepare('select service_id from services where service_name=? and doctor_id=?');
    $check_services->bind_param('si', $cleaned_name, $doctor_id);
    $check_services->execute();
    $check_res=$check_services->get_result();
    if($check_res->num_rows>0){
        $errors[] = 'This service has already been added.';
    }
    // If no errors, proceed with database insertion
    if (empty($errors)) {
        $stmt = $conn->prepare('INSERT INTO services (service_name, price, time, doctor_id) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('sisi', $cleaned_name, $price, $time, $doctor_id);

        if ($stmt->execute()) {
            $success = 'Great! The service has been added and is now available for patients to book.';
        } else {
            $errors[] = 'oops! Something went wrong. Please try again.';
        }
    }
}
?>
<!-------------------------------------------------------------------------Add services page : doctors----------------------------------------------------------------------- -->
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
<?php require("nav.php"); 
?>
<!-- Display Error Messages -->
<?php if (!empty($errors)): ?>
    <div class="px-4 py-3 rounded relative mb-4">
        <ul class="mt-2 list-disc list-inside">
            <?php foreach ($errors as $err): ?>
                <li class="error-msg font-bold anim"><?php echo htmlspecialchars($err); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <div class="px-4 py-3 rounded relative mb-4">
        <p class="succ-msg font-bold anim "><?php echo htmlspecialchars($success); ?></p>  
    </div>
<?php endif; ?>
<div class="max-w-[600px] mx-auto p-10 bg-center bg-contain rounded-lg shadow-md" 
    style="background-image: url('../pics/background8.jpeg'); height: 600px; width: 500px;">
<!-- Back Button -->
    <div class="mb-6">
        <a href="profilePage.php" class="text-[#1A4568] font-bold uppercase hover:underline m-4 anim">
            <i class="fas fa-arrow-left"></i> Back</a>
    </div>
<!-- service Section -->
    <div class="flex flex-col justify-center items-center p-12 rounded-2xl m-2  w-full sm:w-96 anim">
    <h3 class="text-2xl font-bold text-center text-[#1A4568] m-4 anim1">Add Your Available Services</h3>
    <form action="#" method="post" class="flex flex-col w-full space-y-6 anim2">
<!-- Service Name -->
        <div class="flex flex-col space-y-2">
            <label for="name" class="block text-sm font-bold text-[#1A4568] m-2">Service Name:</label>
            <input type="text" id="name" name="name" required
                placeholder="e.g , Checkups, Vaccinations, Dental Cleaning..." class="w-full p-3 text-sm border border-[#1A4568] 
                rounded-lg bg-[aliceblue] outline-none focus:ring-2 focus:ring-[#1A4568] transition" required>
        </div>
<!-- Price -->
        <div class="flex flex-col space-y-2">
            <label for="price" class="block text-sm font-bold text-[#1A4568] m-2">Price (in SAR):</label>
            <input type="number" id="price" name="price" required min="0"
                placeholder="Enter price (at least 100 SAR)" class="w-full p-3 text-sm border border-[#1A4568] rounded-lg 
                bg-[aliceblue] outline-none focus:ring-2 focus:ring-[#1A4568] transition" required>
        </div>
<!-- Estimated Time -->
        <div class="flex flex-col space-y-2">
            <label for="time" class="block text-sm font-bold text-[#1A4568] m-2">Estimated Time (in minutes):</label>
            <input type="number" id="time" name="time" required min="1"
                placeholder="Enter duration (at least 15 minute)" class="w-full p-3 text-sm border border-[#1A4568] rounded-lg bg-[aliceblue] outline-none focus:ring-2 
                focus:ring-[#1A4568] transition" required>
        </div>
<!-- Submit Button -->
        <button type="submit" 
            class="mt-6 px-6 py-3 bg-[#1A4568] text-white rounded-full font-bold uppercase shadow-md 
            hover:bg-white hover:text-[#1A4568] hover:scale-105 transition-transform duration-300 w-full">
            Add Service
        </button>
    </form>
</div>
    <div class="mt-6 anim2">
        <a href="setAvailableTimes.php" class="text-[#1A4568] font-bold uppercase hover:underline flex items-center">
        <i class="fas fa-clock m-2"></i> Set Available Time</a>
    </div>
</div>
<?php require("footer.php"); ?>
</body>
</html>
