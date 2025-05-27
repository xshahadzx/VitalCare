<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Redirect to login page if user is not logged in
if (!isset($_SESSION["is_logged_in"]) || !$_SESSION["is_logged_in"]) {
    header("Location: signIn.php");
    exit;
}
require("../phpFiles/connect.php");
?> 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointments</title>
<!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Faculty+Glyphic&display=swap" rel="stylesheet">
<!-- CSS Files -->
    <link rel="stylesheet" href="../cssFiles/styles.css">
<!-- Tailwind CSS -->
    <link href="../cssFiles/output.css" rel="stylesheet">
</head>
<body>
<!-- -----------------------------------------------------------------------patient dashboard page : appointments----------------------------------------------------------------------- -->
<!-- Navigation Bar -->
<?php require("nav.php"); ?>
<!-- Dashboard -->
<div class="w-full bg-center bg-cover rounded-lg m-2.5 p-2.5 shadow-md anim" style="background-image: url('../pics/background1.jpg');">
<!-- Header Section -->   
    <header class="text-center m-4 text-white anim1">
        <h1 class="text-4xl font-bold text-white m-2 p-2 custom-text-shadow anim">Appointment List</h1>
    </header>
<!-- Appointments Section -->
        <section class="mb-10">
            <h2 class="text-2xl font-semibold text-[#1a4568] m-4 anim1">Upcoming Appointments</h2>
<!-- Appointments Tables - booked appointemnts -->
<table class="appointments-table w-full border-separate border-spacing-2 border border-[#1a4568] shadow-md rounded-lg anim2">
    <thead class="bg-[#24598a] text-white">
        <tr>
            <th class="text-left">Doctor Name</th>
            <th class="text-left">Appointment ID</th>
            <th class="text-left">Date</th>
            <th class="text-left">Time</th>
            <th class="text-left">Hospital</th>
            <th class="text-left">Status</th>
        </tr>
    </thead>
    <tbody>
<?php 
        $patient_id = $_SESSION['id'];
        $query = "SELECT d.name as doctor_name, a.appointment_id, a.appointment_date, a.appointment_time,h.name as hospital_name, a.status
                    FROM appointments a
                    JOIN doctors d ON d.doctor_id = a.doctor_id
                    JOIN hospital_doctors hd ON d.doctor_id=hd.doctor_id
                    JOIN hospitals h ON h.hospital_id=hd.hospital_id
                    WHERE (a.patient_id = ?) AND (a.status='pending' or a.status='confirmed')
                    ORDER BY a.appointment_id dESC";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i',$patient_id);
        $stmt->execute();
        $res=$stmt->get_result();
        $appointments = $res->fetch_all(MYSQLI_ASSOC);   
        if(empty($appointments)){
                        echo '<td class="px-6 py-4 border border-[#1a4568] font-bold">No appointments for now—schedule one when you\'re ready!
                            <a href="searchPage.php" class="underline text-blue-600 hover:text-blue-800 ml-1">Book Now</a>
                            </td>';
        }
        foreach ($appointments as $row) {
            //covert the date and time for user
            $formatted_time = date("g:i A", strtotime($row['appointment_time']));
            $formatted_date = date("n/j/Y", strtotime($row['appointment_date']));
            echo ' <tr class="bg-[#ebf5fa]">';
            echo '<td class="px-6 py-4 border border-[#1a4568] font-bold">Dr. ' . ucfirst(htmlspecialchars($row['doctor_name'])) . '</td>';
            echo '<td class="px-6 py-4 border border-[#1a4568] font-bold">' . $row['appointment_id'] . '</td>';
            echo '<td class="px-6 py-4 border border-[#1a4568] font-bold">' . $formatted_date . '</td>';
            echo '<td class="px-6 py-4 border border-[#1a4568] font-bold">' . $formatted_time . '</td>';
            echo '<td class="px-6 py-4 border border-[#1a4568] font-bold">' . ucwords(htmlspecialchars($row['hospital_name'])) . '</td>';
            echo '<td class="px-6 py-4 border border-[#1a4568] font-bold">' . ucfirst(htmlspecialchars($row['status'])) . '</td>';
            echo '</tr>';
        }
?>
    </tbody>
</table>
<!-- canceled appointments table -->              
<h2 class="text-2xl font-semibold text-[#1a4568] m-4 anim1">Canceled Appointments</h2>
<!-- Appointments Table -->
<table class="appointments-table w-full border-separate border-spacing-2 border border-[#1a4568] shadow-md rounded-lg anim2">
    <thead class="bg-[#24598a] text-white">
        <tr>
            <th class="text-left">Doctor Name</th>
            <th class="text-left">Appointment ID</th>
            <th class="text-left">Date</th>
            <th class="text-left">Time</th>
            <th class="text-left">Status</th>
        </tr>
    </thead>
    <tbody>
<?php 
            $query = "SELECT d.name, a.appointment_id, a.appointment_date, a.appointment_time, a.status
                        FROM appointments a
                        JOIN doctors d ON d.doctor_id = a.doctor_id
                        WHERE a.patient_id = ? and a.status='Canceled'
                        ORDER BY a.appointment_date DESC";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('i',$patient_id);
            $stmt->execute();
            $res=$stmt->get_result();
            $appointments = $res->fetch_all(MYSQLI_ASSOC);
            if(empty($appointments)){
                echo '<td class="px-6 py-4 border border-[#1a4568] font-bold">' ."No cancellations to show right now.". '</td>';
            }
            foreach ($appointments as $row) {
                $formatted_time = date("g:i A", strtotime($row['appointment_time']));
                $formatted_date = date("n/j/Y", strtotime($row['appointment_date']));
                echo '<tr class="bg-[#ebf5fa] hover:bg-[#d3e8f1]">';
                echo '<td class="px-6 py-4 border border-[#1a4568] font-bold">Dr. ' . ucfirst(htmlspecialchars($row['name'])) . '</td>';
                echo '<td class="px-6 py-4 border border-[#1a4568] font-bold">' . $row['appointment_id'] . '</td>';
                echo '<td class="px-6 py-4 border border-[#1a4568] font-bold">' . $formatted_date . '</td>';
                echo '<td class="px-6 py-4 border border-[#1a4568] font-bold">' . $formatted_time . '</td>';
                echo '<td class="px-6 py-4 border border-[#1a4568] font-bold">' . ucfirst(htmlspecialchars($row['status'])) . '</td>';
                echo '</tr>';
            }
?>
    </tbody>
</table>
<!-- previous appointments table -->       
<h2 class="text-2xl font-semibold text-[#1a4568] m-4 anim1">Appointment History</h2>
<!-- Appointments Table -->
<table class="appointments-table w-full border-separate border-spacing-2 border border-[#1a4568] shadow-md rounded-lg anim2">
    <thead class="bg-[#24598a] text-white">
        <tr>
            <th class="text-left">Doctor Name</th>
            <th class="text-left">Date</th>
            <th class="text-left">Time</th>
            <th class="text-left">Status</th>
        </tr>
    </thead>
    <tbody>
<?php 
            $query = "SELECT d.name, a.appointment_date, a.appointment_time, a.status
                        FROM appointments a
                        JOIN doctors d ON d.doctor_id = a.doctor_id
                        WHERE a.patient_id = ? and a.status='completed'
                        ORDER BY a.appointment_date DESC";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('i',$patient_id );
            $stmt->execute();
            $res=$stmt->get_result();
            $appointments = $res->fetch_all(MYSQLI_ASSOC);
            if(empty($appointments)){
                echo '<td class="px-6 py-4 border border-[#1a4568] font-bold">' ."You haven't attended any appointments yet.". '</td>';
            }
            foreach ($appointments as $row) {
                $formatted_time = date("g:i A", strtotime($row['appointment_time']));
                $formatted_date = date("n/j/Y", strtotime($row['appointment_date']));
                echo '<tr class="bg-[#ebf5fa] hover:bg-[#d3e8f1]">';
                echo '<td class="px-6 py-4 border border-[#1a4568] font-bold">Dr. ' . ucfirst(htmlspecialchars($row['name'])) . '</td>';
                echo '<td class="px-6 py-4 border border-[#1a4568] font-bold">' . $formatted_date . '</td>';
                echo '<td class="px-6 py-4 border border-[#1a4568] font-bold">' . $formatted_time . '</td>';
                echo '<td class="px-6 py-4 border border-[#1a4568] font-bold">' . ucfirst(htmlspecialchars($row['status'])) . '</td>';
                echo '</tr>';
            }
?>
    </tbody>
</table>
<?php 
$stmt->close();
$conn->close();
?>
</section>
</div>
<!-- Footer -->
<?php require("footer.php"); ?>
</body>
</html>
