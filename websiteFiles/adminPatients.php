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
// delete patients php 
require_once("../phpFiles/connect.php");
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $errors= [];
    $success="";
    if (isset($_POST['delete']) && $_POST['delete'] === "delete") {
        $patient_id = $_POST["patient_id"] ?? null;
        if ($patient_id) {
            $stmt = $conn->prepare("UPDATE patients SET active='no' WHERE patient_id = ?");
            if ($stmt) {
                $stmt->bind_param('i', $patient_id);
                $stmt->execute();
                $stmt->close();
                // Send email to the patient
                $success="patient with id ".$patient_id." account has been deactiveted";
                $stmt = $conn->prepare('SELECT name, email FROM patients WHERE patient_id = ?');
                $stmt->bind_param('i', $patient_id);
                $stmt->execute();
                $stmt->bind_result($name, $email);
                $stmt->fetch();
                $stmt->close();                
                $mail =require __DIR__."/mailer.php";
                $mail->setFrom("noreplay@vitalCare.com");
                $mail->addAddress($email);
                $mail->Subject = "Your VitalCare Account Has Been Deactivated";
                $mail->Body = <<<END
                <p>Hello <strong>$name</strong>,</p>
                <p>We would like to inform you that your <strong>VitalCare</strong> account has been <strong>deactivated</strong>.</p>
                <p>As a result, you will no longer be able to log in or access our services.</p>
                <p>If you believe this was a mistake, or if you have any questions or need further assistance, please don't 
                hesitate to contact our support team — we're here to help.</p>
                <p>Thank you for being with VitalCare.</p>
                <p>Warm regards,<br>
                <strong>The VitalCare Team</strong></p>
                END;
                try{
                    $mail->send();
                }
                catch(Exception $e ){
                    $error[]= "couldnt send the email to the patient with id ".$patient_id;
                }
            } else {
                $errors[] = "opss something went wrong, please try again later";
            }
        } else {
            $errors[] = "Invalid Patient ID.";
        }
    }
    if (isset($_POST['active']) && $_POST['active'] === "active") {
        $patient_id = $_POST["patient_id"] ?? null;
        if ($patient_id) {
            $stmt = $conn->prepare("UPDATE patients SET active='yes' WHERE patient_id = ?");
            if ($stmt) {
                $stmt->bind_param('i', $patient_id);
                $stmt->execute();
                $stmt->close();
                $success="patient with id ".$patient_id." account has been activeted";
                // Send email to the patient
                $success="patient with id ".$patient_id." account has been deactiveted";
                $stmt = $conn->prepare('SELECT name, email FROM patients WHERE patient_id = ?');
                $stmt->bind_param('i', $patient_id);
                $stmt->execute();
                $stmt->bind_result($name, $email);
                $stmt->fetch();
                $stmt->close();                
                $mail =require __DIR__."/mailer.php";
                $mail->setFrom("noreplay@vitalCare.com");
                $mail->addAddress($email);
                $mail->Subject = "Your VitalCare Account Has Been Reactivated";
                $mail->Body = <<<END
                <p>Hello <strong>$name</strong>,</p>
                <p>We're happy to inform you that your <strong>VitalCare</strong> account has been <strong>successfully reactivated</strong>.</p>
                <p>You can now log back in and access all of our services again.</p>
                <p>If you need any assistance or have any questions, please don't hesitate to contact our support team — we're here to help!</p>
                <p>Thank you for being with VitalCare. We're excited to have you back!</p>
                <p>Warm regards,<br>
                <strong>The VitalCare Team</strong></p>
                END;
                try{
                    $mail->send();
                }
                catch(Exception $e ){
                    $error[]= "couldnt send the email to the patient with id ".$patient_id;
                }
            } else {
                $errors[] = "opss something went wrong, please try again later";
            }
        } else {
            $errors[] = "Invalid Patient ID.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashborad</title>
<!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Faculty+Glyphic&display=swap" rel="stylesheet">
<!-- CSS Files -->
    <link rel="stylesheet" href="../cssFiles/styles.css">
    <link href="../cssFiles/output.css" rel="stylesheet">
</head>
<body>
<!-- -----------------------------------------------------------------------Admin dashboard page : patients----------------------------------------------------------------------- -->
<!--nav bar-->
<?php require("nav.php"); ?>
<!-- Dashboard -->
    <div class="w-full bg-center bg-cover rounded-lg p-6 shadow-lg anim" style="background-image: url('../pics/background8.jpeg');">
<!-- Header Section -->
        <header class="text-center mb-6 text-[#1a4568] anim1">
            <h1 class="text-3xl leading-tight mb-4 font-bold">VitalCare Patients</h1>
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
<!-----------------------------------------------------------------Section: View patients-------------------------------------------------------------- -->
<h3 class="text-2xl text-[#1a4568] my-4 anim1 font-bold">All Patients</h3>
<!-- mangage patients Tables  -->                         
<table class="appointments-table w-full border-separate border-spacing-2 border border-[#1a4568] shadow-md rounded-lg anim2">
    <thead class="bg-[#24598a] text-white">
        <tr>
            <th class="font-bold text-left">Patient Name</th>
            <th class="font-bold text-left">Patient ID</th>
            <th class="font-bold text-left">Number Of Appointments</th>
            <th class="font-bold text-left">Email</th>
            <th class="font-bold text-left">Action</th>
        </tr>
    </thead>
    <tbody>
<?php 
            $query = "SELECT name,patient_id,email FROM patients where active='yes' order by patient_id";
            $stmt = $conn->query($query);
            $patients = $stmt->fetch_all(MYSQLI_ASSOC);
            if(empty($patients)){
                echo '<td class="font-bold border border-[#1a4568]">No Patient Records Found</td>';
            }
            foreach ($patients as $row) {
                echo '<tr class="bg-[#ebf5fa] hover:bg-[#d3e8f1]">';
                echo '<td class="font-bold border border-[#1a4568]">' . ucfirst(htmlspecialchars($row['name'])) . '</td>';
                echo '<td class="font-bold border border-[#1a4568]">' . $row['patient_id'] . '</td>';
                $appoint_count = $conn->prepare("SELECT COUNT(*) AS appointment_count FROM appointments WHERE patient_id = ?");
                $appoint_count->bind_param("i", $row['patient_id']);
                $appoint_count->execute();
                $appoint_result = $appoint_count->get_result();
                $count = $appoint_result->fetch_assoc();
                // Ensure count exists
                $appointment_count = $count ? htmlspecialchars($count['appointment_count']) : 0;
                echo '<td class="font-bold border border-[#1a4568]">' . $appointment_count . '</td>'; 
                echo '<td class="font-bold border border-[#1a4568]">' . htmlspecialchars($row['email']) . '</td>';
                echo '<td class="font-bold border border-[#1a4568]">';
?>
                <!-- DEACTIVATE button -->
                <form action="#" method="post" onsubmit="return checker();">
                        <!-- Cancel Button -->
                        <button type="submit" name="delete" value="delete" class="cancel-btn py-2 px-4 rounded-lg bg-red-500 text-white
                        hover:bg-red-600 uppercase">
                        Deactivate  
                        </button>
                        <!-- Hidden Input for doctor ID -->
                        <input type="hidden" name="patient_id" value="<?php echo htmlspecialchars($row['patient_id']); ?>">
                        <script>
                        function checker() {
                            return confirm("Are you sure you want to deactivate this patient account? The patient will be notified.");
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
<h3 class="text-2xl font-semibold text-[#1a4568] my-4 anim1">Deactivated Patients Account</h3>
<!-- mangage patients Tables  -->                         
<table class="appointments-table w-full border-separate border-spacing-2 border border-[#1a4568] shadow-md rounded-lg anim2">
    <thead class="bg-[#24598a] text-white">
        <tr>
            <th class="font-bold text-left">Patient Name</th>
            <th class="font-bold text-left">Patient ID</th>
            <th class="font-bold text-left">Email</th>
            <th class="font-bold text-left">Action</th>
        </tr>
    </thead>
    <tbody>
        <?php 
            $query = "SELECT name,patient_id,email FROM patients where active='no' order by patient_id";
            $stmt = $conn->query($query);
            $patients = $stmt->fetch_all(MYSQLI_ASSOC);
            if(empty($patients)){
                echo '<td class="font-bold border border-[#1a4568]">No Deactivated Patient Accounts Found</td>';
            }
            foreach ($patients as $row) {
                echo '<tr class="bg-[#ebf5fa] hover:bg-[#d3e8f1]">';
                echo '<td class="font-bold border border-[#1a4568]">' . ucfirst(htmlspecialchars($row['name'])) . '</td>';
                echo '<td class="font-bold border border-[#1a4568]">' . $row['patient_id'] . '</td>';
                echo '<td class="font-bold border border-[#1a4568]">' . htmlspecialchars($row['email']) . '</td>';
                echo '<td class="font-bold border border-[#1a4568]">';
        ?>
                <!-- ACTIVATE button -->
                <form action="#" method="post" onsubmit="return checker();">
                        <button type="submit" name="active" value="active" class="accept-btn py-2 px-4 mr-5 rounded-lg bg-green-500
                        text-white hover:bg-green-600 uppercase ">
                        Activate
                        </button>
                        <!-- Hidden Input for patient ID -->
                        <input type="hidden" name="patient_id" value="<?php echo htmlspecialchars($row['patient_id']); ?>">
                        <script>
                        function checker() {
                            return confirm("Are you sure you want to reactivate this patient account? The patient will be notified.");
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