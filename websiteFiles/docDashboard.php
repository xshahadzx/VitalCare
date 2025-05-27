<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION["is_logged_in"]) || !$_SESSION["is_logged_in"]) {
    // Redirect to login page if user is not logged in
    header("Location: signIn.php");
    exit;
}
require("../phpFiles/connect.php");
//confirm appointments  
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $appointment_id = $_POST["appointment_id"] ?? null;
    $action = $_POST["action"] ?? null;
    $name = $_POST["name"] ?? null;
    $email = $_POST["email"] ?? null;
    if ($appointment_id && $action) {
        $error= [];
        $success="";
        //check if the appointment has been already confirmed 
        $check = $conn->prepare("select appointment_id from appointments WHERE appointment_id = ? and status ='confirmed'");
        $check->bind_param('i', $appointment_id);
        $check->execute();
        $check_res=$check->get_result();
        if($check_res->num_rows>0){
            $error[]='The appointment is already confirmed.';
        }
        if(empty($error)){
            $stmt = $conn->prepare("UPDATE appointments SET status = ? WHERE appointment_id = ?");
            // Execute the query with prepared statements
            $stmt->bind_param('si', $action,$appointment_id);
            if ($stmt->execute()) {
                $success = "The appointment has been successfully confirmed, and the patient has been notified.";
                //set needed varibales
                $doc_name=$_COOKIE["doc_name"];
                //get appointment data to send to patient
                $appoint_info=$conn->prepare('select appointment_date,appointment_time,appointment_end_time 
                from appointments where appointment_id=?');
                $appoint_info->bind_param('i',$appointment_id);
                $appoint_info->execute();
                $app_arr=$appoint_info->get_result()->fetch_assoc();
                //covert the date and time for user
                $booking_date=$app_arr['appointment_date'];
                $booking_time=$app_arr['appointment_time'];
                $booking_end_time=$app_arr['appointment_end_time'];
                $formatted_time = date("g:i A", strtotime($booking_time));
                $formatted_end_time = date("g:i A", strtotime($booking_end_time));
                $formatted_date = date("l, F j", strtotime($booking_date));
                //get doctors information
                $doctor_email=$_SESSION['email'];
                $doctor_phone=$_COOKIE['doc_phone_number'];
                //send an email to the patient to notify them 
                $mail =require __DIR__."/mailer.php";
                $mail->setFrom("noreplay@vitalCare.com");
                $mail->addAddress($email);
                $mail->Subject = "Appointment Confirmation with Dr. $doc_name";
                $mail->Body = <<<END
                    <p>Dear <strong>$name</strong>,</p>
                    <p>We're pleased to confirm your appointment with <strong>Dr. $doc_name</strong>.</p>
                    <p>
                        <strong>Date:</strong> $formatted_date<br>
                        <strong>Time:</strong> $formatted_time - $formatted_end_time
                    </p>
                    <p>Dr. $doc_name has officially confirmed the appointment, and everything is set.</p>
                    <p>You can view your appointment details anytime by logging into your account.
                    For questions or changes, feel free to reach out to Dr. $doc_name at <a href="mailto:$doctor_email">$doctor_email</a> 
                    or call $doctor_phone.</p>
                    <p>If you need any further assistance, our support team is always here to help.</p>
                    <p>Thank you for choosing <strong>VitalCare</strong>. We look forward to supporting your healthcare needs.</p>
                    <p>Best regards,<br>
                    <strong>The VitalCare Team</strong></p>
                END;
                try{
                    $mail->send();
                }
                catch(Exception $e ){
                    $error[]= "couldnt send the email to the patient!";
                }
            } //end inner if
        } //end outer if
    } else {
        $error[]= "unable to confirm at the momment! please try again later.";
    }
}
//----------------------------------------------------------doctor dashboard page-------------------------------------------------------------
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
<!-- -----------------------------------------------------------------------Doctor dashboard page : appointmnets----------------------------------------------------------------------- -->
<!--nav bar-->
<?php require("nav.php"); ?> 
<!-- Dashboard -->
<div class="w-full bg-center bg-cover rounded-lg m-2.5 p-2.5 shadow-md anim" style="background-image: url('../pics/background1.jpg');">
<!-- Header Section --> 
        <header class="text-center m-4 text-white anim1">
            <h1 class="text-4xl font-bold text-white m-2 p-2 custom-text-shadow anim">Appointment List</h1>
        </header>
<!-- Display Error Messages -->
        <?php if (!empty($error)): ?>
            <div class="px-4 py-3 rounded relative mb-4">
                <ul class="mt-2 list-disc list-inside">
                    <?php foreach ($error as $err): ?>
                        <li class="error-msg font-bold anim"><?php echo htmlspecialchars($err); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
<!-- Display Success Message -->
        <?php if (!empty($success)): ?>
            <div class="px-4 py-3 rounded relative mb-4">
                <p class="succ-msg font-bold anim "><?php echo htmlspecialchars($success); ?></p>
                <script>
                    setTimeout(() => {
                        window.location.href = "#";  
                    }, 1000); 
                </script>   
            </div>
        <?php endif; ?>
<!-- Appointments Section -->
<section class="mb-10 anim">
            <h2 class="text-2xl font-semibold text-[#1a4568] m-4 anim1">Pending Appointments</h2>
<!-- Appointments Tables - pending appointemnts -->
<table class="appointments-table w-full border-separate border-spacing-2 border border-[#1a4568] shadow-md rounded-lg anim1">
    <thead class="bg-[#24598a] text-white">
        <tr>
            <th class="text-left">Patient Name</th>
            <th class="text-left">Appointment ID</th>
            <th class="text-left">Date</th>
            <th class="text-left">Time</th>
            <th class="text-left">Reason</th>
            <th class="text-left">Action</th>
        </tr>
    </thead>
    <tbody>
    <?php 
        //show all future and current appointments only
        date_default_timezone_set('Asia/Riyadh');
        $currentTime = time();
        $currentDate = date("Y-m-d", $currentTime); 
        $doctor_id = $_SESSION['id'];
        $query = "SELECT p.name,p.email, a.appointment_id, a.appointment_date, a.appointment_time, a.reason
                    FROM appointments a
                    JOIN patients p ON p.patient_id = a.patient_id
                    WHERE a.doctor_id = ? and a.status='pending'
                    AND ( a.appointment_date >= ?)
                    ORDER BY a.appointment_date, a.appointment_time";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('is',$doctor_id,$currentDate);
        $stmt->execute();
        $res=$stmt->get_result();
        $appointments = $res->fetch_all(MYSQLI_ASSOC);
        if(empty($appointments)){
            echo '<td class="px-6 py-4 border border-[#1a4568] font-bold">' ."You have no pending appointments at the moment.". '</td>';
        }
        else{
            foreach ($appointments as $row) {
                //covert the date and time for user
                $formatted_time = date("g:i A", strtotime($row['appointment_time']));
                $formatted_date = date("n/j/Y", strtotime($row['appointment_date']));
                echo '<tr class="bg-[#ebf5fa] hover:bg-[#d3e8f1]">';
                echo '<td class="px-6 py-4 border border-[#1a4568] font-bold"> ' 
                    . ucfirst(htmlspecialchars($row['name'])) . '</td>';
                echo '<td class="px-6 py-4 border border-[#1a4568] font-bold">' . $row['appointment_id'] . '</td>';
                echo '<td class="px-6 py-4 border border-[#1a4568] font-bold">' . $formatted_date . '</td>';
                echo '<td class="px-6 py-4 border border-[#1a4568] font-bold">' . $formatted_time . '</td>';
                echo '<td class="px-6 py-4 border border-[#1a4568] font-bold"> ' 
                    . ucfirst(htmlspecialchars($row['reason'])) . '</td>';
                echo '<td class="px-6 py-4 border border-[#1a4568] font-bold">';
        ?>
<!-- confirm Button Form -->
                <form action="#" method="post" onsubmit="window.location.reload();">
<!-- confirm Button -->
                    <button type="submit" name="action" value="confirmed" 
                        class="accept-btn py-2 px-4 mr-5 rounded-lg bg-green-500 text-white hover:bg-green-600 uppercase ">
                        confirm
                    </button>
<!-- Hidden Input for Appointment ID and email and name  -->
                    <input type="hidden" name="appointment_id" value="<?php echo htmlspecialchars($row['appointment_id']); ?>">
                    <input type="hidden" name="email" value="<?php echo htmlspecialchars($row['email']); ?>">
                    <input type="hidden" name="name" value="<?php echo htmlspecialchars($row['name']); ?>">
                </form>
                    <?php
                    echo '</td>';
                    echo '</tr>';
            } //end foreach
        } //end else 
            ?>
    </tbody>
</table>
<!-- confirmed and completed appointments table -->
<h2 class="text-2xl font-semibold text-[#1a4568] m-4 anim1">Confirmed Appointments</h2>
<!-- Appointments Table -->
<table class="appointments-table w-full border-separate border-spacing-2 border border-[#1a4568] shadow-md rounded-lg anim">
    <thead class="bg-[#24598a] text-white">
        <tr>
            <th class="text-left">Patient Name</th>
            <th class="text-left">Appointment ID</th>
            <th class="text-left">Date</th>
            <th class="text-left">Time</th>
            <th class="text-left">Reason</th>
        </tr>
    </thead>
    <tbody>
    <?php 
        $query = "SELECT p.name, a.appointment_id, a.appointment_date, a.appointment_time,a.reason
                    FROM appointments a
                    JOIN patients p ON p.patient_id = a.patient_id
                    WHERE a.doctor_id = ? and a.status='confirmed'
                    ORDER BY a.appointment_date, a.appointment_time";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i',$doctor_id);
        $stmt->execute();
        $res=$stmt->get_result();
        $appointments = $res->fetch_all(MYSQLI_ASSOC);
        if(empty($appointments)){
            echo '<td class="px-6 py-4 border border-[#1a4568] font-bold">' ."Looks like you don’t have any confirmed appointments yet.". '</td>';
        }
        else{        
            foreach ($appointments as $row) {
            $formatted_time = date("g:i A", strtotime($row['appointment_time']));
            $formatted_date = date("n/j/Y", strtotime($row['appointment_date']));
            echo '<tr class="bg-[#ebf5fa] hover:bg-[#d3e8f1]">';
            echo '<td class="px-6 py-4 border border-[#1a4568] font-bold">' 
                . ucfirst(htmlspecialchars($row['name'])) . '</td>';
            echo '<td class="px-6 py-4 border border-[#1a4568] font-bold">' . $row['appointment_id'] . '</td>';
            echo '<td class="px-6 py-4 border border-[#1a4568] font-bold">' . $formatted_date . '</td>';
            echo '<td class="px-6 py-4 border border-[#1a4568] font-bold">' . $formatted_time . '</td>';
            echo '<td class="px-6 py-4 border border-[#1a4568] font-bold"> ' 
                . ucfirst(htmlspecialchars($row['reason'])) . '</td>';
            echo '</tr>';
            } //end foreach
        } //end else

    ?>
    </tbody>
</table>
<!-- canceled appointments table -->
<h2 class="text-2xl font-semibold text-[#1a4568] m-4 anim1">Canceled Appointments</h2>
<!-- Appointments Table -->
<table class="appointments-table w-full border-separate border-spacing-2 border border-[#1a4568] shadow-md rounded-lg anim">
    <thead class="bg-[#24598a] text-white">
        <tr>
            <th class="text-left">Patient Name</th>
            <th class="text-left">Appointment ID</th>
            <th class="text-left">Date</th>
            <th class="text-left">Time</th>
        </tr>
    </thead>
    <tbody>
        <?php 
            $query = "SELECT p.name, a.appointment_id, a.appointment_date, a.appointment_time
                        FROM appointments a
                        JOIN patients p ON p.patient_id = a.patient_id
                        WHERE a.doctor_id = ? and a.status='Canceled'
                        ORDER BY a.appointment_date, a.appointment_time";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('i',$doctor_id);
            $stmt->execute();
            $res=$stmt->get_result();
            $appointments = $res->fetch_all(MYSQLI_ASSOC);
            if(empty($appointments)){
                echo '<td class="px-6 py-4 border border-[#1a4568] font-bold">' ."No Canceled Appointmnets.". '</td>';
            }
            else{
                foreach ($appointments as $row) {
                    $formatted_time = date("g:i A", strtotime($row['appointment_time']));
                    $formatted_date = date("n/j/Y", strtotime($row['appointment_date']));
                    echo '<tr class="bg-[#ebf5fa] hover:bg-[#d3e8f1]">';
                    echo '<td class="px-6 py-4 border border-[#1a4568] font-bold">' 
                        . ucfirst(htmlspecialchars($row['name'])) . '</td>';                
                    echo '<td class="px-6 py-4 border border-[#1a4568] font-bold">' . $row['appointment_id'] . '</td>';
                    echo '<td class="px-6 py-4 border border-[#1a4568] font-bold">' . $formatted_date . '</td>';
                    echo '<td class="px-6 py-4 border border-[#1a4568] font-bold">' . $formatted_time . '</td>';
                    echo '</tr>';
                }
            }
        ?>
    </tbody>
</table>
<!-- Header Section -->
<header class="text-center m-4 text-white anim1">
    <h1 class="text-4xl font-bold text-white m-2 p-2 custom-text-shadow anim">Services and Availability</h1>
</header>
<div class="flex flex-wrap gap-4">
<!-- Services Table -->
    <div class="w-full md:w-2/3">
        <h3 class="text-2xl font-semibold text-[#1a4568] m-4 anim1">Your Services</h3>
        <table class="appointments-table w-full border border-[#1a4568] shadow-md rounded-lg anim2 text-sm">
            <thead class="bg-[#24598a] text-white">
                <tr>
                    <th class="px-4 py-2 text-left">Services</th>
                    <th class="px-4 py-2 text-left">Price</th>
                    <th class="px-4 py-2 text-left">Time Needed</th>
                </tr>
            </thead>
            <tbody>
            <?php 
                $query = "SELECT service_name,price,time FROM services WHERE doctor_id=? ORDER BY service_name,price";
                $stmt = $conn->prepare($query);
                $stmt->bind_param('i', $doctor_id);
                $stmt->execute();
                $services = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                if (empty($services)) {
                    echo '<tr>
                    <td colspan="3" class="px-4 py-2 border border-[#1a4568] font-bold text-center">
                        No services available. Please add your services to get started.
                        <a href="addServices.php" class="underline text-blue-600 hover:text-blue-800 ml-1">Add Services</a>
                    </td>
                    </tr>';
                }
                else{
                    foreach ($services as $row) {
                        $formatted_price = number_format($row['price'], 2, '.', '');
                        echo '<tr class="bg-[#ebf5fa] hover:bg-[#d3e8f1]">';
                        echo '<td class="px-4 py-2 border border-[#1a4568] font-bold">' 
                            . ucwords(htmlspecialchars($row['service_name'])) . '</td>';
                        echo '<td class="px-4 py-2 border border-[#1a4568] font-bold">' 
                            . htmlspecialchars($formatted_price) . ' SAR</td>';
                        // Convert time to minutes
                        $total_minutes = (int)date("H", strtotime($row['time'])) * 60 
                            + (int)date("i", strtotime($row['time']));
                        echo '<td class="px-4 py-2 border border-[#1a4568] font-bold">' 
                            . htmlspecialchars($total_minutes) . ' Minutes</td>';
                        echo '</tr>';
                    }//end foreach
                }//end else
            ?>
            </tbody>
        </table>
    </div>
<!-- Available Times Table  -->
    <div class="w-full md:w-1/3 m-4">
    <h3 class="text-2xl font-semibold text-[#1a4568] m-4 anim1">Your Availability</h3>
        <table class="appointments-table w-full max-w-xs border border-[#1a4568] shadow-md rounded-lg anim2 text-sm">
            <thead class="bg-[#24598a] text-white">
                <tr>
                    <th class="px-3 py-2 text-left">Available Date</th>
                    <th class="px-3 py-2 text-left">Time</th>
                </tr>
            </thead>
            <tbody>
            <?php 
                $currentDate = date("Y-m-d", $currentTime);
                $query = "SELECT available_time, available_date FROM available_times 
                    WHERE doctor_id=? AND available_date>=? AND booked='no' ORDER BY available_date";
                $stmt = $conn->prepare($query);
                $stmt->bind_param('is', $doctor_id, $currentDate);
                $stmt->execute();
                $times = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                if (empty($times)) {
                    echo '<tr>
                    <td colspan="2" class="px-3 py-2 border border-[#1a4568] text-center font-bold">
                        No available times found. Please add your availability to begin accepting appointments.
                        <a href="setAvailableTimes.php" class="underline text-blue-600 hover:text-blue-800 ml-1">Add Availability</a>
                    </td>
                    </tr>';
                } else {
                    // Group times by date
                    $groupedTimes = [];
                    foreach ($times as $row) {
                        $formatted_date = date("j F", strtotime($row['available_date']));
                        $formatted_time = date("g:i A", strtotime($row['available_time']));

                        // Add time to the group for the specific date
                        $groupedTimes[$formatted_date][] = $formatted_time;
                    }
                    // Display grouped times
                    foreach ($groupedTimes as $date => $timeList) {
                        // Display the date in the first column
                        echo '<tr class="bg-[#ebf5fa] hover:bg-[#d3e8f1]">';
                        echo '<td class="px-3 py-2 border border-[#1a4568] font-bold">' . htmlspecialchars($date) . '</td>';
                        // Display all the times for the current date
                        echo '<td class="px-3 py-2 border border-[#1a4568] font-bold">';
                        foreach ($timeList as $time) {
                            echo ucwords(htmlspecialchars($time))." , ";
                        }
                        echo '</td>';
                        echo '</tr>';
                    }
                }
                $stmt->close();
                $conn->close();
            ?>
            </tbody>
        </table>
    </div>
</div>
</section>
</div>
<!-- Footer -->
<?php require("footer.php"); ?>
</body>
</html>
