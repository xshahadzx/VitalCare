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
// delete doctors php
require_once("../phpFiles/connect.php");
//  aprroving & rejecting doctors php 
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $errors = [];  
    $success = "";
    //accept doctors or reject them
    if(!empty($_POST['manage_doctors'])){
        $doctor_id = $_POST["doctor_id"] ?? null;
        $action = $_POST["action"] ?? null;
        if ($doctor_id && $action) {
            $errors= [];
            $success="";
            $stmt = $conn->prepare("UPDATE doctors SET admin_approval = ? WHERE doctor_id = $doctor_id");
            if ($action === "accept") {
                $status = 'yes';
            } elseif ($action === "refuse") {
                $status = 'rejected';
            } 
            // Execute the query with prepared statements
            $stmt->bind_param('s', $status);
            if ($stmt->execute()&&$action === "accept") {
                $stmt->close();
                $success = "The doctor has been successfully registered and notified!";
                $stmt = $conn->prepare('SELECT name, email FROM doctors WHERE doctor_id = ?');
                $stmt->bind_param('i', $doctor_id);
                $stmt->execute();
                $stmt->bind_result($name, $email);
                $stmt->fetch();
                $stmt->close();                
                $mail =require __DIR__."/mailer.php";
                $mail->setFrom("noreplay@vitalCare.com");
                $mail->addAddress($email);
                $mail->Subject = "Welcome to VitalCare - Your Account is Now Active!";
                $mail->Body = <<<END
                <p>Hello Dr. $name,</p>
                <p>We're excited to let you know that your account has been <strong>successfully activated</strong>!</p>
                <p>You can now log in and start using all of our services with ease. Don't forget to add your available services 
                and time slots so patients can easily book appointments with you.</p>
                <p>If you have any questions or need help getting started, feel free to reach out to us — we're always here to assist you!</p>
                <p>Welcome aboard!</p>
                <p>Warm regards,</p>
                <p><strong>The VitalCare Team</strong></p>
                END;
                try{
                    $mail->send();
                }
                catch(Exception $e ){
                    $error[]= "couldnt send email, please try again later";
                }
            } elseif($stmt->execute()&&$action === "accept") {
                $stmt->close();
                $success = "The doctor's application has been rejected and they have been notified.";
                $stmt = $conn->prepare('SELECT name, email FROM doctors WHERE doctor_id = ?');
                $stmt->bind_param('i', $doctor_id);
                $stmt->execute();
                $stmt->bind_result($name, $email);
                $stmt->fetch();
                $stmt->close();
                $mail =require __DIR__."/mailer.php";
                $mail->setFrom("noreplay@vitalCare.com");
                $mail->addAddress($email);
                $mail->Subject = "Important Update on Your VitalCare Application";
                $mail->Body = <<<END
                <p>Dear Dr. <strong>$name</strong>,</p>
                <p>Thank you for your interest in joining <strong>VitalCare</strong>. After careful consideration of your application,
                we regret to inform you that we are unable to approve your registration at this time.</p>
                <p>This means that you will not be able to access our platform or its services. We understand this may be disappointing news,
                and we truly appreciate the time and effort you invested in applying.</p>
                <p>If you would like more information about this decision or have any questions, please don't hesitate to reach out to our 
                support team — we're here to help.</p>
                <p>Thank you again for your interest in VitalCare, and we wish you the very best in your future endeavors.</p>
                <p>Warm regards,<br>
                <strong>The VitalCare Team</strong></p>
                END;
                try{
                    $mail->send();
                }
                catch(Exception $e ){
                    $error[]= "couldnt send email, please try again later";
                }
            }
        }
        else {
            $errors[] = "Invalid doctor ID.";
        }
    }
}
// deactiving the doctor account from the database 
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    if (isset($_POST['delete']) && $_POST['delete'] === "delete") {
        $doctor_id = $_POST["doctor_id"] ?? null;
        if ($doctor_id) {
            $stmt = $conn->prepare("UPDATE doctors SET active='no' WHERE doctor_id = ?");
            $stmt->bind_param('i', $doctor_id);
            if ($stmt->execute()) {
                $stmt->close();
                $success="Doctor with account id ".$doctor_id." has been deactiveted";
                $stmt = $conn->prepare('SELECT name, email FROM doctors WHERE doctor_id = ?');
                $stmt->bind_param('i', $doctor_id);
                $stmt->execute();
                $stmt->bind_result($name, $email);
                $stmt->fetch();
                $stmt->close();
                // Send email to the doctor                
                $mail =require __DIR__."/mailer.php";
                $mail->setFrom("noreplay@vitalCare.com");
                $mail->addAddress($email);
                $mail->Subject = "Important: Your VitalCare Account Has Been Deactivated";
                $mail->Body = <<<END
                <p>Hello Dr. <strong>$name</strong>,</p>
                <p>We would like to inform you that your <strong>VitalCare</strong> account has been <strong>deactivated</strong>.</p>
                <p>As a result, you will no longer be able to log in or access our services.</p>
                <p>If you believe this was a mistake, or if you have any questions or need assistance, please don't hesitate to
                reach out to our support team — we're here to help.</p>
                <p>Thank you for being a part of VitalCare.</p>
                <p>Warm regards,<br>
                <strong>The VitalCare Team</strong></p>
                END;          
                try{
                    $mail->send();
                }
                catch(Exception $e ){
                    $error[]= "couldnt send email, please try again later";
                }
            } else {
                $errors[] = "opss! Error deactivating the doctor account. " ;
            }
        } else {
            $errors[] = "Invalid doctor ID.";
        }
    }
    //activating the doctor account from the database
    if (isset($_POST['active']) && $_POST['active'] === "active") {
        $doctor_id = $_POST["doctor_id"] ?? null;
        if ($doctor_id) {
            $stmt = $conn->prepare("UPDATE doctors SET active='yes' WHERE doctor_id = ?");
            if ($stmt) {
                $stmt->bind_param('i', $doctor_id);
                $stmt->execute();
                $stmt->close();
                // Send email to the doctor
                $stmt = $conn->prepare('SELECT name, email FROM doctors WHERE doctor_id = ?');
                $stmt->bind_param('i', $doctor_id);
                $stmt->execute();
                $stmt->bind_result($name, $email);
                $stmt->fetch();
                $stmt->close();
                $success="Doctor with account id ".$doctor_id." has been Activeted";
                $mail =require __DIR__."/mailer.php";
                $mail->setFrom("noreplay@vitalCare.com");
                $mail->addAddress($email);
                $mail->Subject = "Your VitalCare Account Has Been Reactivated";
                $mail->Body = <<<END
                <p>Hello Dr. <strong>$name</strong>,</p>
                <p>We're pleased to inform you that your <strong>VitalCare</strong> account has been <strong>reinstated and reactivated</strong>.</p>
                <p>You can now log back in, manage your services and availability, and continue connecting with patients through our platform.</p>
                <p>If you have any questions or need assistance as you return, please don't hesitate to reach out — our support team is always 
                here to help.</p>
                <p>Welcome back to VitalCare!</p>
                <p>Warm regards,<br>
                <strong>The VitalCare Team</strong></p>
                END;
                try{
                    $mail->send();
                }
                catch(Exception $e ){
                    $error[]= "couldnt send email, please try again later";
                }
            } else {
                $errors[] = "opss! Error deactivating the doctor account. " ;
            }
        } else {
            $errors[] = "Invalid doctor ID.";
        }
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
<!-------------------------------------------------------------------------Admin dashboard page : doctors----------------------------------------------------------------------- -->
<!--nav bar-->
<?php require("nav.php");?>
<!-- Dashboard -->
    <div class="w-full bg-center bg-cover rounded-lg p-6 shadow-lg anim" style="background-image: url('../pics/background8.jpeg');">
<!-- Header Section -->
        <header class="text-center mb-6 text-[#1a4568] anim1">
            <h1 class="text-3xl font-bold leading-tight mb-4">VitalCare Doctors</h1>
        </header>
<!-- Display Error Messages -->
    <?php if (!empty($errors)): ?>
        <div class="px-4 py-3 rounded relative mb-4">
            <ul class="mt-2 list-disc list-inside">
                <?php foreach ($errors as $error): ?>
                    <li class="error-msg font-bold anim"><?php echo htmlspecialchars($error); ?></li>
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
        <h3 class="text-2xl font-bold text-[#1a4568] my-4 anim1">Newly Registered Doctors (Awaiting Approval)</h3>
        <!-- mangage doctors Tables  -->                         
<table class="appointments-table w-full border-separate border-spacing-2 border border-[#1a4568] shadow-md rounded-lg anim2">
    <thead class="bg-[#24598a] text-white">
        <tr>
            <th class="font-bold text-left">Doctor Name</th>
            <th class="font-bold text-left">Doctor ID</th>
            <th class="font-bold text-left">Email</th>
            <th class="font-bold text-left">Phone Number</th>
            <th class="font-bold text-left">Hospital</th>
            <th class="font-bold text-left">Specialty</th>
            <th class="font-bold text-left">Experience Years</th>
            <th class="font-bold text-left">Action</th>
        </tr>
    </thead>
    <tbody>
<?php 
        $query = "SELECT 
                    d.doctor_id,
                    d.name AS doctor_name,
                    d.email,
                    d.phone_number,
                    d.specialty,
                    d.experience_years,
                    d.location,
                    h.name AS hospital_name
                FROM doctors d
                JOIN hospital_doctors hd ON hd.doctor_id = d.doctor_id
                JOIN hospitals h ON h.hospital_id = hd.hospital_id
                WHERE d.admin_approval = 'no'
                order by d.doctor_id desc;
                ";
        $stmt = $conn->query($query);
        $doctors = $stmt->fetch_all(MYSQLI_ASSOC);
        
        if(empty($doctors)){
            echo '<td class="font-bold border border-[#1a4568]">No New Doctor Registrations</td>';
        }
        // Loop through the results and display them in the table
        foreach ($doctors as $row) {
            echo '<tr class="bg-[#ebf5fa] hover:bg-[#d3e8f1]">';
            echo '<td class="font-bold border border-[#1a4568]"> Dr. ' . ucfirst(htmlspecialchars($row['doctor_name'])) . '</td>';
            echo '<td class="font-bold border border-[#1a4568]">' . $row['doctor_id'] . '</td>';
            echo '<td class="font-bold border border-[#1a4568]">' . htmlspecialchars($row['email']) . '</td>';
            echo '<td class="font-bold border border-[#1a4568]"> ' . $row['phone_number']. '</td>';
            echo '<td class="font-bold border border-[#1a4568]">' . ucwords(htmlspecialchars($row['hospital_name'])) . '</td>';
            echo '<td class="font-bold border border-[#1a4568]">' . ucfirst(htmlspecialchars($row['specialty'])) . '</td>';
            echo '<td class="font-bold border border-[#1a4568]">' . $row['experience_years'] . '</td>';
            echo '<td class="font-bold border border-[#1a4568]">';
?>
<!-- Approve and Cancel Button Form -->
                <form action="#" method="post">
<!-- Approve Button -->
                        <button type="submit" name="action" value="accept" class="accept-btn py-2 px-4 m-2 rounded-lg bg-green-500 text-white
                        hover:bg-green-600 uppercase ">
                            accept
                        </button>
<!-- Cancel Button -->
                        <button type="submit" name="action" value="refuse" class="cancel-btn py-2 px-4 m-2 rounded-lg bg-red-500 text-white
                        hover:bg-red-600 uppercase">
                            reject
                        </button>
<!-- Hidden Input for doctor ID -->
                        <input type="hidden" name="doctor_id" value="<?php echo htmlspecialchars($row['doctor_id']); ?>">
                        <input type="hidden" name="manage_doctors" value="accept_refuse">
                </form>
                <?php
                echo '</td>';
                echo '</tr>';
            }
        ?>
    </tbody>
</table>
<!-- Section: View  all activated doctors -->
            <h3 class="text-2xl font-bold text-[#1a4568] my-4 anim1">VitalCare Doctors</h3>
<!-- mangage doctors Tables  -->                         
<table class="appointments-table w-full border-separate border-spacing-2 border border-[#1a4568] shadow-md rounded-lg anim2">
    <thead class="bg-[#24598a] text-white">
        <tr>
            <th class="font-bold text-left">Doctor Name</th>
            <th class="font-bold text-left">Doctor ID</th>
            <th class="font-bold text-left">Number Of Appointments</th>
            <th class="font-bold text-left">Email</th>
            <th class="font-bold text-left">Action</th>
        </tr>
    </thead>
    <tbody>
<?php 
            $query = "SELECT name,doctor_id,email FROM doctors where admin_approval='yes' order by doctor_id";
            $stmt = $conn->query($query);
            $doctors = $stmt->fetch_all(MYSQLI_ASSOC);
            if(empty($doctors)){
                echo '<td class="font-bold border border-[#1a4568]">No Doctor Records Found</td>';
            }
            // Loop through the doctors and display their information
            foreach ($doctors as $row) {
                echo '<tr class="bg-[#ebf5fa] hover:bg-[#d3e8f1]">';
                echo '<td class="font-bold border border-[#1a4568]"> Dr. ' . ucfirst(htmlspecialchars($row['name'])) . '</td>';
                echo '<td class="font-bold border border-[#1a4568]">' . $row['doctor_id'] . '</td>';
                $appoint_count = $conn->prepare("SELECT COUNT(*) AS appointment_count FROM appointments WHERE doctor_id = ?");
                $appoint_count->bind_param("i", $row['doctor_id']);
                $appoint_count->execute();
                $appoint_result = $appoint_count->get_result();
                $count = $appoint_result->fetch_assoc();
                // Ensure count exists
                $appointment_count = $count ? htmlspecialchars($count['appointment_count']) : 0;
                echo '<td class="font-bold border border-[#1a4568]">' . $appointment_count . '</td>'; 
                echo '<td class="font-bold border border-[#1a4568]">' . htmlspecialchars($row['email']) . '</td>';
                echo '<td class="font-bold border border-[#1a4568]">';
?>
<!-- deactivate button -->
                <form action="#" method="post" onsubmit="return checker();">
<!-- Cancel Button -->
                        <button type="submit" name="delete" value="delete" class="cancel-btn py-2 px-4 rounded-lg bg-red-500 text-white
                        hover:bg-red-600 uppercase">
                        Deactivate  
                        </button>
<!-- Hidden Input for doctor ID -->
                        <input type="hidden" name="doctor_id" value="<?php echo htmlspecialchars($row['doctor_id']); ?>">
                        <script>
                        function checker() {
                            return confirm("Are you sure you want to deactivate this account? The doctor will be notified.");
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
<!-- Section: View deactiveted doctors -->
<h3 class="text-2xl font-bold text-[#1a4568] my-4 anim1">Deactivated Doctors Accounts</h3>
<!-- mangage doctors Tables  -->                         
<table class="appointments-table w-full border-separate border-spacing-2 border border-[#1a4568] shadow-md rounded-lg anim2">
    <thead class="bg-[#24598a] text-white">
        <tr>
            <th class="font-bold text-left">Doctor Name</th>
            <th class="font-bold text-left">Doctor ID</th>
            <th class="font-bold text-left">Email</th>
            <th class="font-bold text-left">Action</th>
        </tr>
    </thead>
    <tbody>
<?php 
            $query = "SELECT name,doctor_id,email FROM doctors where admin_approval='yes' and active='no' order by doctor_id";
            $stmt = $conn->query($query);
            $doctors = $stmt->fetch_all(MYSQLI_ASSOC);
            if(empty($doctors)){
                echo '<td class="font-bold border border-[#1a4568]">' ."no doctors in the database". '</td>';
            }
            foreach ($doctors as $row) {
                echo '<tr class="bg-[#ebf5fa] hover:bg-[#d3e8f1]">';
                echo '<td class="font-bold border border-[#1a4568]"> Dr. ' . ucfirst(htmlspecialchars($row['name'])) . '</td>';
                echo '<td class="font-bold border border-[#1a4568]">' . $row['doctor_id'] . '</td>';
                echo '<td class="font-bold border border-[#1a4568]">' . htmlspecialchars($row['email']) . '</td>';
                echo '<td class="font-bold border border-[#1a4568]">';
                ?>
<!-- activate account -->
                <form action="#" method="post" onsubmit="return checker();">
                    <button type="submit" name="active" value="active" class="accept-btn py-2 px-4 mr-5 rounded-lg bg-green-500 text-white hover:bg-green-600 uppercase ">
                    Activate
                    </button>
<!-- Hidden Input for doctor ID -->
                    <input type="hidden" name="doctor_id" value="<?php echo htmlspecialchars($row['doctor_id']); ?>">
                    <script>
                    function checker() {
                        return confirm("Are you sure you want to activate this account? The doctor will be notified.");
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
    </div>
<!-- Footer -->
<?php require("footer.php"); ?>
</body>
</html>