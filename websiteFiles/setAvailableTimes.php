<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require("../phpFiles/connect.php");
$doctor_id = $_SESSION['id'] ?? null; 
// Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Check if fields exist and trim inputs
    $time = isset($_POST['time']) ? trim($_POST['time']) : '';
    $date = isset($_POST['date']) ? trim($_POST['date']) : '';
    $errors = [];
    $success = "";

    date_default_timezone_set('Asia/Riyadh');
    $currentDate = date("Y-m-d");
    
    // Validate date and time 
    if (empty($time) || empty($date)) {
        $errors[] = 'Please enter a date and time.';
    }
    //check if the time and date are already added
    $check_td=$conn->prepare('select * from available_times where doctor_id=? and available_time=? and available_date=?');
    $check_td->bind_param('iss',$doctor_id,$time,$date);
    $check_td->execute();
    $rows=$check_td->get_result();
    if($rows->num_rows!=0){
        $errors[] = 'The entered time is already marked as available in the system.';
    }
    // Check if there's already an available time within 1 hour for this doctor on the same date
    $start_time = date('H:i:s', strtotime('-1 hour', strtotime($time)));
    $end_time = date('H:i:s', strtotime('+1 hour', strtotime($time)));

    $check_td = $conn->prepare('SELECT * FROM available_times 
    WHERE doctor_id = ? AND available_date = ? AND available_time BETWEEN ? AND ?');
    $check_td->bind_param('isss', $doctor_id, $date, $start_time, $end_time);
    $check_td->execute();
    $rows = $check_td->get_result();

    if ($rows->num_rows != 0) {
        $errors[] = 'Oops! You can only set one available time per hour.';
    }

    
    // Ensure the entered date is not in the past
    if (strtotime($date) < strtotime($currentDate)) {
        $errors[] = "Oops! You can't choose a date that's already passed.";
    }
    // Validate time And check if it is in the future
    $currentTime = strtotime(date('H:i'));
    $selectedTime = strtotime($time);
    // Check if selected time is at least 2 hours (7200 seconds) ahead
    if (($selectedTime <= $currentTime || ($selectedTime - $currentTime) < 7200)&&$date==$currentDate) {
        $errors[] = 'Please choose a time that is at least two hours from now.';
    }

    // If no errors, proceed with database insertion
    if (empty($errors)) {
        $stmt = $conn->prepare('INSERT INTO available_times (available_time, available_date,booked, doctor_id) VALUES ( ?, ?,"no", ?)');
        $stmt->bind_param('ssi',$time,$date,  $doctor_id);
        if ($stmt->execute()) {
            $success = 'Time and date has been added successfully! Patients can now book it.';
        } else {
            $errors[] = 'oops! Something went wrong. Please try again.';
        }
    }
}
?>
<!-------------------------------------------------------------------------set avaliable page : doctors----------------------------------------------------------------------- -->
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
<!-- Display Success Message -->
<?php if (!empty($success)): ?>
    <div class="px-4 py-3 rounded relative mb-4">
    <p class="succ-msg font-bold anim "><?php echo htmlspecialchars($success); ?></p>
    </div>
<?php endif; ?>
<!-- Main  Section -->
<div class="max-w-[600px] mx-auto p-10 bg-center bg-contain rounded-lg shadow-md" style="background-image: url('../pics/background8.jpeg'); 
    height: 600px; width: 500px;">
<!-- Back Button -->
    <div class="mb-6">
        <a href="profilePage.php" class="text-[#1A4568] font-bold uppercase hover:underline m-4">
            <i class="fas fa-arrow-left"></i> Back</a>
    </div>
<!-- service Section -->
    <div class="flex flex-col justify-center items-center p-12 rounded-2xl m-2 w-full sm:w-96 anim ">
    <h3 class="text-2xl font-bold text-center text-[#1A4568] mb-8 anim1">Add Your Availability</h3>
    <form action="#" method="post" class="flex flex-col w-full space-y-6 anim2">
<?php
        $currentDate = date('Y-m-d'); 
        $lastDayOfYear = date('Y-m-d', strtotime('last day of December this year'));
        ?>
<!-- avaliable Time -->
        <div class="flex flex-col space-y-2">
            <label for="time" class="block text-sm font-bold text-[#1A4568] m-2">Time:</label>
            <input type="time" id="time" name="time" 
                placeholder="Enter Time (e.g., 5:30)" class="w-full p-3 text-sm border border-[#1A4568] rounded-lg
                bg-[aliceblue] outline-none focus:ring-2 focus:ring-[#1A4568] transition" required>
        </div>
<!-- avaliable date -->
        <div class="flex flex-col space-y-2">
            <label for="date" class="block text-sm font-bold text-[#1A4568] m-2">Date:</label>
            <input type="date" id="date" name="date" 
            placeholder="Enter Date" class="w-full p-3 text-sm border border-[#1A4568] rounded-lg bg-[aliceblue] outline-none 
            focus:ring-2 focus:ring-[#1A4568] transition" 
            min="<?php echo $currentDate; ?>"
            max="<?php echo $lastDayOfYear; ?>" required>
        </div>
<!-- Submit Button -->
        <button type="submit" 
            class="mt-6 px-6 py-3 bg-[#1A4568] text-white rounded-full font-bold uppercase shadow-md 
            hover:bg-white hover:text-[#1A4568] hover:scale-105 transition-transform duration-300 w-full">
            Add Availability
        </button>
    </form>
</div>
    <div class="m-4 anim2">
        <a href="addServices.php" class="text-[#1A4568] font-bold uppercase hover:underline flex items-center">
        <i class="fas fa-stethoscope mr-2"></i>Add Services</a>
    </div>
</div>
<?php require("footer.php"); ?>
</body>
</html>
