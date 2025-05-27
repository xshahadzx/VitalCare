<?php
// Start the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
} 
// Redirect to home page if user is not  logged in as a admin
if (!isset($_SESSION["is_admin"]) && empty($_SESSION['is_admin'])){
    header("Location: signIn.php");
    exit;
}
//cancel appointmnets 
require_once("../phpFiles/connect.php");
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $appointment_id = $_POST["appointment_id"] ?? null;
    $patient_id= $_POST["patient_id"] ?? null;
    $doctor_id= $_POST["doctor_id"] ?? null;
    $action= $_POST["action"] ?? null;
    $errors = [];
    $success = "";
    // Check if the appointment ID and action are set
    if ($appointment_id && $action) {
        // Update appointment status
        $stmt1 = $conn->prepare("UPDATE appointments SET status = ? WHERE appointment_id = ?");
        $stmt1->bind_param('si', $action, $appointment_id);
        if ($stmt1->execute()) {
            $success = "Appointment canceled successfully! Both the doctor and patient will be notified.";
        } else {
            $errors[] = "Failed to update appointment status.";
        }
        $stmt1->close();
        // Get appointment details
        $appoint_info = $conn->prepare("SELECT appointment_date FROM appointments WHERE appointment_id = ?");
        $appoint_info->bind_param('i', $appointment_id);
        $appoint_info->execute();
        $result = $appoint_info->get_result();
        $app_arr = $result->fetch_assoc();
        $appoint_info->close();
        if ($app_arr) {
            $booking_date= $app_arr['appointment_date'];
            $formatted_date = date("l, F j", strtotime($booking_date));
        } else {
            $errors[] = "Appointment details not found.";
        }
        // Get doctor's info
        $stmt = $conn->prepare("SELECT name, email FROM doctors WHERE doctor_id = ?");
        $stmt->bind_param('i', $doctor_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $doc_arr = $res->fetch_assoc();
        $stmt->close();
        if ($doc_arr) {
            $doc_name  = $doc_arr['name'];
            $doc_email = $doc_arr['email'];
            // Notify doctor by email
            $mail = require __DIR__ . "/mailer.php";        
            $mail->setFrom("noreply@vitalCare.com");
            $mail->addAddress($doc_email);
            $mail->Subject = "Appointment Cancellation Notification";
            $mail->Body = <<<END
            <p>Dear Dr. $doc_name,</p>
            <p>We would like to inform you that your appointment with the following details has been canceled:</p>
            <ul>
            <li><strong>Patient ID:</strong> $patient_id</li>
            <li><strong>Appointment ID:</strong> $appointment_id</li>
            <li><strong>Scheduled Date:</strong> $formatted_date</li>
            </ul>
            <p>If you require any further information or assistance regarding this cancellation, 
            please feel free to reach out to our support team.</p>
            <p>Thank you for your understanding and continued cooperation.</p>
            <p>Kind regards,</p>
            <p><strong>The VitalCare Team</strong></p>
            END;
            try {
                $mail->send();
            } catch (Exception $e) {
                $errors[] = "Couldn't send email to the doctor. Please try again!";
            }
        } else {
            $errors[] = "Doctor details not found.";
        }
        // Get patient’s info
        $stmt = $conn->prepare("SELECT name, email FROM patients WHERE patient_id = ?");
        $stmt->bind_param('i', $patient_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $patient_arr = $res->fetch_assoc();
        $stmt->close();
        if ($patient_arr) {
            $patient_name  = $patient_arr['name'];
            $patient_email = $patient_arr['email'];
            // Notify patient by email
            $mail = require __DIR__ . "/mailer.php";        
            $mail->setFrom("noreply@vitalCare.com");
            $mail->addAddress($patient_email);
            $mail->Subject = "Appointment Cancellation Notice";
            $mail->Body = <<<END
            <p>Dear $patient_name,</p>
            <p>We would like to inform you that your appointment has been canceled. Below are the appointment details for your reference:</p>
            <ul>
            <li><strong>Doctor ID:</strong> $doctor_id</li>
            <li><strong>Appointment ID:</strong> $appointment_id</li>
            <li><strong>Scheduled Date:</strong> $formatted_date</li>
            </ul>
            <p>If you have any questions, or if you would like to reschedule, please don't hesitate to contact our support team. We're here to assist you.</p>
            <p>Thank you for choosing VitalCare.</p>
            <p>Kind regards,</p>
            <p><strong>The VitalCare Team</strong></p>
            END;
            try {
                $mail->send();
            } catch (Exception $e) {
                $errors[] = "Couldn't send email to the patient. Please try again!";
            }
        } else {
            $errors[] = "Patient details not found.";
        }
    } else {
        $errors[] = "Unable to cancel at the moment. Missing required data!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
<!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Faculty+Glyphic&display=swap" rel="stylesheet">
<!-- CSS Files -->
    <link rel="stylesheet" href="../cssFiles/styles.css">
    <link href="../cssFiles/output.css" rel="stylesheet">
</head>
<body>
<!-------------------------------------------------------------------------Admin dashboard page : appointments----------------------------------------------------------------------- -->
<!--nav bar-->
<?php require("nav.php"); ?>
<!-- Dashboard -->
    <div class="w-full bg-center bg-cover rounded-lg p-6 shadow-lg anim" style="background-image: url('../pics/background8.jpeg');">
<!-- Header Section -->
        <header class="text-center mb-6 text-[#1a4568] anim1">
            <h1 class="text-3xl font-bold leading-tight mb-4">Appointmnets</h1>
        </header>
<!-- display error message -->
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
<!-- Section: Manage Doctors -->
        <section class="mb-10">
<!-- Section: View Appointments -->
        <section class="mb-10">
            <h2 class="text-2xl font-semibold text-[#1a4568] mb-6 anim1">View All Appointments</h2>
            <table class="appointments-table w-full border-separate border-spacing-2 border border-[#1a4568] shadow-md rounded-lg anim2">
    <thead class="bg-[#24598a] text-white">
        <tr>
            <th class="text-left">Appointment Id</th>
            <th class="text-left">Doctor Name</th>
            <th class="text-left">Patient Name</th>
            <th class="text-left">Appointment Date</th>
            <th class="text-left">Appointment Time</th>
            <th class="text-left">Appointment Status</th>
            <th class="text-left">Action</th>
        </tr>
    </thead>
    <tbody>
        <?php 
            $query = "SELECT a.appointment_id, d.name AS doctor_name, d.doctor_id, p.name AS patient_name, p.patient_id, a.appointment_date, 
                    a.appointment_time, a.status
                    FROM appointments a JOIN doctors d ON a.doctor_id=d.doctor_id 
                    JOIN patients p ON p.patient_id=a.patient_id
                    where status <>'Canceled' and status <>'completed'
                    order by a.appointment_date desc, a.appointment_time desc";
            $stmt = $conn->query($query);
            $appointments = $stmt->fetch_all(MYSQLI_ASSOC);
            if(empty($appointments)){
                echo '<td class="font-bold border border-[#1a4568]">' ."No current appointmnets". '</td>';
            }
            foreach ($appointments as $row) {
                //covert the date and time for user
                $formatted_time = date("g:i A", strtotime($row['appointment_time']));
                $formatted_date = date("n/j/Y", strtotime($row['appointment_date']));
                echo '<tr class="bg-[#ebf5fa] hover:bg-[#d3e8f1]">';
                echo '<td class="font-bold border border-[#1a4568]">' . $row['appointment_id'] . '</td>';
                echo '<td class="font-bold border border-[#1a4568] "> Dr. ' . ucfirst(htmlspecialchars($row['doctor_name'])) . '</td>';
                echo '<td class="font-bold border border-[#1a4568] ">' . ucfirst(htmlspecialchars($row['patient_name'])) . '</td>';
                echo '<td class="font-bold border border-[#1a4568] ">' . htmlspecialchars($formatted_date) . '</td>';
                echo '<td class="font-bold border border-[#1a4568] ">' . htmlspecialchars($formatted_time) . '</td>';
                echo '<td class="font-bold border border-[#1a4568] ">' . ucfirst(htmlspecialchars($row['status'])) . '</td>';
                echo '<td class="font-bold border border-[#1a4568] ">';
                ?>
<!--  Cancel Button Form -->
            <form action="#" method="post" onsubmit="return checker();">
<!-- Cancel Button -->
            <button type="submit" name="action" value="canceled" class="cancel-btn py-2 px-4 rounded-lg bg-red-500 text-white
                    hover:bg-red-600 uppercase">
            Cancel
            </button>
<!-- Hidden Inputs for appointment ID, patient_id, and doctor_id -->
            <input type="hidden" name="doctor_id" value="<?php echo htmlspecialchars($row['doctor_id']); ?>">
            <input type="hidden" name="patient_id" value="<?php echo htmlspecialchars($row['patient_id']); ?>">
            <input type="hidden" name="appointment_id" value="<?php echo htmlspecialchars($row['appointment_id']); ?>">
            <script>
            function checker() {
                return confirm("Are you sure you want to cancel this appointment? This action cannot be undone.");
            }
            </script>
            </form>
        <?php
                echo '</td>';
                echo '</tr>';
            }
        ?>
    </tbody>
</table>
        </section>
<!-- Section: View Appointments -->
        <section class="mb-10">
            <h2 class="text-2xl font-semibold text-[#1a4568] mb-6 anim1">View All Canceled and Completed Appointments</h2>
            <table class="appointments-table w-full border-separate border-spacing-2 border border-[#1a4568] shadow-md rounded-lg anim2">
    <thead class="bg-[#24598a] text-white">
        <tr>
            <th class="text-left">Appointment Id</th>
            <th class="text-left">Doctor Name</th>
            <th class="text-left">Patient Name</th>
            <th class="text-left">Appointment Date</th>
            <th class="text-left">Appointment Time</th>
            <th class="text-left">Appointment Status</th>
        </tr>
    </thead>
    <tbody>
<!-- view completed and canceled appointments -->
        <?php 
            $query = "SELECT a.appointment_id, d.name AS doctor_name, d.doctor_id, p.name AS patient_name, p.patient_id, a.appointment_date,
                    a.appointment_time, a.status
                    FROM appointments a JOIN doctors d ON a.doctor_id=d.doctor_id 
                    JOIN patients p ON p.patient_id=a.patient_id
                    where status ='Canceled' or status ='completed'
                    order by a.appointment_date desc, a.appointment_time desc";
            $stmt = $conn->query($query);
            $appointments = $stmt->fetch_all(MYSQLI_ASSOC);
            if(empty($appointments)){
                echo '<td class="font-bold border border-[#1a4568]">' ."No current appointmnets". '</td>';
            }
            foreach ($appointments as $row) {
                $formatted_time = date("g:i A", strtotime($row['appointment_time']));
                $formatted_date = date("n/j/Y", strtotime($row['appointment_date']));
                echo '<tr class="bg-[#ebf5fa] hover:bg-[#d3e8f1]">';
                echo '<td class="font-bold border border-[#1a4568] ">' . $row['appointment_id'] . '</td>';
                echo '<td class="font-bold border border-[#1a4568] "> Dr. ' . ucfirst(htmlspecialchars($row['doctor_name'])) . '</td>';
                echo '<td class="font-bold border border-[#1a4568] ">' . ucfirst(htmlspecialchars($row['patient_name'])) . '</td>';
                echo '<td class="font-bold border border-[#1a4568] ">' . $formatted_date . '</td>';
                echo '<td class="font-bold border border-[#1a4568] ">' . $formatted_time . '</td>';
                echo '<td class="font-bold border border-[#1a4568] ">' . ucfirst(htmlspecialchars($row['status'])) . '</td>';
                echo '</tr>';
            }
        ?>
    </tbody>
</table>
        </section>
    </section>
    </div>
<!-- Footer -->
<?php require("footer.php"); ?>
</body>
</html>

