<?php 
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    require("cookies.php");
    require("../phpFiles/connect.php");
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $error = [];
        $success = "";
        $patient_id = $_SESSION['id'];

        // Validate required POST data
        if (empty($_POST['doctor_id']) || empty($_POST['date'])|| empty($_POST['service_id'])) {
            if(empty($_POST['service_name']) || empty($_POST['time'])||empty($_POST['available_time_id'])){
                $error[] = 'Sorry, Date is missing.';
            }
        } else {
            $_SESSION['doctor_id'] = $_POST['doctor_id'];
            $_SESSION['time']=$_POST['time'];
            $_SESSION['service_name']=$_POST['service_name'];
            $_SESSION['service_id']=$_POST['service_id']; //needed for the next page -payment page
            $_SESSION['available_time_id']=$_POST['available_time_id'];//needed for the next page -payment page
        }
    }
    //get the name of the patient
    $patient_name=$_COOKIE['user_name'];
    //get the service data 
    $services=$conn->prepare('select price,time from services where service_id=?');
    $services->bind_param('i',$_SESSION['service_id']);
    $services->execute();
    $services_arr=$services->get_result()->fetch_assoc();
    $service_time=$services_arr['time'];
    $price=$services_arr['price'];
    if ($services_arr) {
            //set session vari
            $_SESSION['service_time']=$service_time; //for the next page 
            //create the end time of the appointment 
            $time_stamp = strtotime($_SESSION['time']);
            $service_time_stamp = strtotime($service_time);
            $end_time_stamp = $time_stamp + $service_time_stamp; 
            $end_time = date('H:i:s', $end_time_stamp);
            $_SESSION['end_time']=$end_time;
    }
    //covert the date and time for user
    $formatted_time = date("g:i A", strtotime($_SESSION['time']));
    $formatted_end_time = date("g:i A", strtotime($end_time));
    $formatted_date = date("l, F j", strtotime($_SESSION['date']));
    //get docs information
    $doc_info=$conn->prepare('SELECT d.name as doc_name ,h.name AS hos_name 
        FROM doctors d JOIN hospital_doctors hd ON d.doctor_id =hd.doctor_id
        JOIN hospitals h ON hd.hospital_id= h.hospital_id 
        WHERE d.doctor_id=?');
    $doc_info->bind_param('i',$_SESSION['doctor_id']);
    $doc_info->execute();
    $doc_info_arr=$doc_info->get_result()->fetch_assoc();
    //define doc information variables
    $doc_name=$doc_info_arr['doc_name'];
    $hos_name=$doc_info_arr['hos_name'];
    $formatted_price = number_format($price, 2); // Format the price to 2 decimal places
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Details</title>
<!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<!-- CSS Files -->
    <link href="../cssFiles/styles.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
<div class="container bg-cover bg-center max-w-4xl mx-auto my-10 p-8 bg-[#DBECF4] bg-opacity-90 rounded-2xl shadow-lg"
    style="background-image: url('../pics/background8.jpeg');">
<!-- Header Section -->
    <div class="rounded-full p-4 text-center bg-[#1A4568] text-white shadow-md">
        <h1 class="text-3xl font-semibold">Appointment Details</h1>
    </div>
<!-- Patient & Appointment Info -->
    <div class="profile-section mt-8 p-6">
        <div class="profile-info text-[#1A4568] space-y-3">
            <p class="text-lg font-semibold">Name: <span class="font-normal">
                <?php echo ucwords(htmlspecialchars($patient_name)); ?></span></p>
            <p class="text-lg font-semibold">Doctor: <span class="font-normal">
                <?php echo ucwords(htmlspecialchars($doc_name ?? 'Unknown')); ?></span></p>
            <p class="text-lg font-semibold">Hospital: <span class="font-normal">
                <?php echo ucwords(htmlspecialchars($hos_name ?? 'Unknown')); ?></span></p>
            <p class="text-lg font-semibold"><i class="fas fa-calendar-day"></i> Date: <span class="font-normal">
                <?php echo htmlspecialchars($formatted_date); ?></span></p>
            <p class="text-lg font-semibold"><i class="fas fa-clock"></i> Time: <span class="font-normal">
                <?php echo htmlspecialchars($formatted_time ); ?></span> - <span class="font-normal">
                <?php echo htmlspecialchars($formatted_end_time ?? 'N/A'); ?></span></p>
            <p class="text-lg font-semibold"><i class="fas fa-money-bill-wave"></i> Price: <span class="font-normal text-green-600">
                <?php echo htmlspecialchars($formatted_price ?? 'N/A'); ?></span> SAR</p>
        </div>
    </div>
<!-- Back & Confirm Buttons -->
    <div class="flex justify-between mt-8">
    <a href="searchPage.php" 
        class="bg-gray-600 text-white px-6 py-2 rounded-lg shadow-md hover:bg-gray-700 transition"
        onclick="return confirm('Are you sure you want to cancel the booking? Any unsaved changes will be lost.');">
        Back
    </a>
    <a href="paymentPage.php" class="bg-[#1A4568] text-white px-6 py-2 rounded-lg shadow-md
        hover:bg-[#123456] transition">Proceed to Payment</a>
    </div>
</div>
</body>
</html>
