<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require("cookies.php");
// if the user is a doctor, redirect to the index page
if (isset($_COOKIE['doc_name']) && !empty($_COOKIE['doc_name'])) {
    header("Location: Index.php");
    exit;
}
// Check if the user is a patient
if (!empty($_COOKIE["user_name"]) && isset($_COOKIE['user_name'])) {
    require("../phpFiles/connect.php");
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $doctor_id = $_POST["doctor_id"];
            $date= $_POST["date"];
            $stmt = $conn->prepare("
            SELECT 
                d.name AS doctor_name, d.specialty, d.experience_years, d.location,h.name AS hospital_name, AVG(c.rating_value) AS avg_rating
            FROM 
                doctors d 
            LEFT OUTER JOIN 
                comments c ON d.doctor_id = c.doctor_id
            INNER JOIN 
                hospital_doctors hd ON d.doctor_id = hd.doctor_id
            INNER JOIN 
                hospital_insurances hi ON hd.hospital_id = hi.hospital_id
            INNER JOIN 
                insurances i ON hi.insurance_id = i.insurance_id
            INNER JOIN 
                hospitals h ON h.hospital_id = hi.hospital_id
            WHERE 
                d.doctor_id = ?
            GROUP BY 
                doctor_name,hospital_name, d.specialty, d.experience_years, d.location;
            ");
            $stmt->bind_param("i", $doctor_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $doctor_array = $result->fetch_assoc();
            // set default value for the rating if the doctor has not received any ratings
            $rating_avg = isset($doctor_array['avg_rating']) ? number_format($doctor_array['avg_rating'], 2) : '0.00';
?>
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
    <link rel="stylesheet" href="../cssFiles/styles.css">
<!-- Tailwind CSS -->
    <link href="../cssFiles/output.css" rel="stylesheet">
</head>
<body>
<?php require("nav.php"); ?>
<!-- -----------------------------------------------------------------------Search results: Doctor Profile page : patients----------------------------------------------------------------------- -->
    <!-- Main Section -->
    <div class="container bg-cover bg-center max-w-4xl mx-auto my-10 p-8 bg-[#DBECF4] bg-opacity-90 rounded-2xl shadow-lg anim" 
        style="background-image: url(../pics/background8.jpeg);">
        <!-- Header Section -->
        <div class="rounded-full p-3 text-center py-5 bg-[#1A4568] text-white anim1">
            <h1 class="text-3xl font-semibold">Doctor Profile</h1>
        </div>
<?php 
        //print the doc's info
        if ($result->num_rows === 1) {
?>
            <div class="profile-section flex items-center my-10">
                <img src="../pics/docProfile.png" alt="Doctor Profile icon" class="w-24 h-24 rounded-full m-4 shadow-md anim2" />
                <div class="profile-info text-[#1A4568] ml-9">
                    <h2 class="search-h2 text-2xl font-bold mb-3 anim1">
                        Dr. <?php echo ucwords(htmlspecialchars($doctor_array['doctor_name'])); ?></h2>
                    <p class="text-lg mb-2 anim2"><span class="font-semibold">Specialty: </span>
                        <?php echo ucfirst(htmlspecialchars($doctor_array['specialty'])); ?></p>
                    <p class="text-lg mb-2 anim2"><span class="font-semibold">Hospital: </span>
                        <?php echo ucfirst(htmlspecialchars($doctor_array['hospital_name'])); ?></p>
                    <p class="text-lg mb-2 anim2"><span class="font-semibold">Experience Years: </span>
                        <?php echo $doctor_array['experience_years']; ?></p>
                    <p class="text-lg mb-2 anim2"><span class="font-semibold">Location: </span>
                        <?php echo htmlspecialchars($doctor_array['location']); ?></p>
                    <p class="text-lg anim2"><span class="font-semibold"><i class="far fa-star mr-2"></i><?php echo $rating_avg; ?></span></p>
                </div>
            </div>
<?php 
        } 
            //get all comments of the choosen doc
            if (isset($doctor_id) && is_numeric($doctor_id)) {
            $sql = $conn->prepare("SELECT p.name, c.comment FROM comments c left outer JOIN patients p 
            ON c.patient_id = p.patient_id WHERE c.doctor_id = ? and hide='no' limit 5;");
            $sql->bind_param('i',$doctor_id);
            $sql->execute();
            $res=$sql->get_result();
            $comments_array = $res->fetch_all();
?>
<!-- Comment Section -->
        <div class="patient-comments-sec my-10">
            <h2 class="search-h2 text-2xl font-semibold anim1"><i class="fas fa-comment"></i> Reviews </i></h2>
            <div class="text-lg text-[#1A4568] mt-5 space-y-4">
<?php 
                if (!empty($comments_array)) {
                    foreach ($comments_array as $comment ) {
                            echo '<div class="comment-block mb-4 p-4 bg-gray-100 rounded-lg shadow-md anim2">';
                            echo '<p class="text-lg text-gray-600 font-bold m-1">'
                                . ucwords(htmlspecialchars($comment[0])) ." wrote: ". '</p>';
                            echo '<p class="text-lg text-gray-600 mt-2">'."\"" . htmlspecialchars($comment[1]) ."\"". '</p>';
                            echo '</div>';
                    }
                } else {
                    echo '<p class="font-bold anim2">There are no comments to display right now. Be the first to leave one!</p>';
                }
            }
?>
            </div>
        </div>
<?php 
        } // end of if statement for checking if the user is a patient
?>
<!-- Appointment Section -->
        <div class="my-10">
            <h2 class="search-h2 text-2xl font-semibold anim1">Kindly select both a time and a service</h2>
            <form action="paymentDetails.php" method="post" class="ml-4 space-y-6 p-6 rounded-lg anim2">
                <!-- send needed information to next page -->
                <input type="hidden" name="doctor_id" value="<?php echo $doctor_id; ?>">
                <input type="hidden" name="date" value="<?php echo $date ;?>">
                <div>
<!-- Time for Appointment -->
<?php
                //only show available times in the future
                $current_time = date('H:i:s');
                $currentDate = date('Y-m-d');
                //check avaliable time 
                $check_times=$conn->prepare('SELECT DISTINCT available_time_id, available_time 
                                                    FROM available_times 
                                                    WHERE doctor_id=? 
                                                    AND booked="no" 
                                                    AND (available_date = ? 
                                                    OR (available_date=? AND available_time > ?))
                                                    ORDER BY available_time;
                                                    ');
                $check_times->bind_param('isss',$doctor_id,$date,$currentDate,$current_time);
                $check_times->execute();
                $available_times=$check_times->get_result();
                if($available_times&&$available_times->num_rows==0){
?>
<!--if no times are Available -->
                        <label for="time" class="block text-[#1A4568] font-semibold text-lg mb-2">Select a Time:</label>
                        <select id="time" name="time" class="text-[#1A4568] font-bold text-lg hover:text-[#1A4568] w-full p-3 border
                            border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1A4568] focus:outline-none outline-none" required>
                        <option value="" disabled selected class="text-gray-500">Oops! No times are available. Try picking another date.</option>
                        </select>
<?php
                } else {
?>
<!-- if there are Available times -->
                    <label for="time" class="block text-[#1A4568] font-semibold text-lg mb-2">Select a Time:</label>
                    <select id="time" name="time" class="text-[#1A4568] font-bold text-lg hover:text-[#1A4568] w-full p-3 border
                        border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1A4568] focus:outline-none outline-none" required>
                            <option value="" disabled selected class="text-gray-500">Please choose a preferred time</option>
<?php
                                $rows=$available_times->fetch_all(MYSQLI_ASSOC);
                                foreach ($rows as $row) {
                                        //get the time in 12 hour format
                                        $time_12hr = date('h:i A', strtotime($row['available_time']));
                                        echo '<option value="' . htmlspecialchars($row['available_time']) . '">' 
                                            . htmlspecialchars($time_12hr) . '</option>';
                                }
                                //send available_time id to book the appointment 
                                echo '<input type="hidden" name="available_time_id" value="'.htmlspecialchars($row['available_time_id']).'">';
?>
                    </select>
<?php 
                    } // end of if statement for checking available times
                    $check_times->close();
?>
                </div>
                <!-- Reason for Appointment -->
<?php
                $check_services=$conn->prepare('SELECT DISTINCT service_name,service_id 
                                FROM services WHERE doctor_id = ? order by service_name ');
                $check_services->bind_param('i',$doctor_id);
                $check_services->execute();
                $services_res=$check_services->get_result();
                if($services_res&&$services_res->num_rows==0){?>
                <!-- if no avaliable services  -->
                        <label for="service_name" class="block text-[#1A4568] font-semibold text-lg mb-2">Select a Service:</label>
                        <select id="service_name" name="service_name" class="text-[#1A4568] font-bold text-lg 
                            hover:text-[#1A4568] w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1A4568] 
                            focus:outline-none outline-none" required>
                        <option value="" disabled selected class="text-gray-500">Oops! No services are currently available</option>
                        </select>
<?php
                } else {
?>
                    <!-- show all available services -->
                    <label for="service_name" class="block text-[#1A4568] font-semibold text-lg mb-2">Select a Service:</label>
                    <select id="service_name" name="service_name" class="text-[#1A4568] font-bold text-lg hover:text-[#1A4568] w-full p-3 border border-gray-300 
                    rounded-lg focus:ring-2 focus:ring-[#1A4568] focus:outline-none outline-none" required>
                        <option value="" disabled selected class="text-gray-500">Please choose a service</option>
<?php
                                $rows=$services_res->fetch_all(MYSQLI_ASSOC);
                                foreach ($rows as $row) {
                                    echo '<option value="' . htmlspecialchars($row['service_name']) . '">' 
                                        . ucwords(htmlspecialchars($row['service_name'])). '</option>';
                                }
                                //send service id to book the appointment 
                                echo '<input type="hidden" name="service_id" value="'. htmlspecialchars($row['service_id']) .'">';
?>
                    </select>
<?php
                    }
                    $stmt->close();
                    $check_services->close();
                    $conn->close();
?>
            <button type="submit" class="book-btn w-full p-4 bg-[#1A4568] text-white rounded-lg
                    hover:bg-[#183141] transition-all focus:outline-none outline-none">Book Now</button>
            </form>
            <a href="searchPage.php" class="mt-5 block text-[#1A4568] text-lg font-semibold hover:underline anim1">Looking for someone else? See other doctors</a>
        </div>
    </div>
<!-- Footer -->
<?php require("footer.php"); ?>
</body>
</html>
<?php 
} //end of if statement for checking if the user is a patient
?>
