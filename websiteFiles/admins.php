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
require_once("../phpFiles/connect.php");
// deactivate admins accounts
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $errors= [];
    $success="";
    if (isset($_POST['delete']) && $_POST['delete'] === "delete") {
        $admin_id = $_POST["admin_id"] ?? null;
        // Check if the admin_id 33 and if the admin_id is not the only one in the database
        if($admin_id==33){
            $errors[] = "You can't Deactivate a super admin.";
        }
        $check_admin_num=$conn->query('SELECT COUNT(*) AS admin_num FROM admins where active="yes"');
        $res=$check_admin_num->fetch_all();
        if($res[0][0]<=1){
            $errors[] = "You can't Deactivate all admins.";
        }
        if ($admin_id && empty($errors)) {
            $stmt = $conn->prepare("UPDATE admins SET active='no' WHERE admin_id = ?");
            if ($stmt) {
                $stmt->bind_param('i', $admin_id);
                if ($stmt->execute()) {
                    $stmt->close();
                    // Send email to the admin about the deactivation
                    $success = "Admin with ID " . $admin_id . " has been deactivated, and the admin has been notified.";
                    $stmt = $conn->prepare('SELECT name, email FROM admins WHERE admin_id = ?');
                    $stmt->bind_param('i', $admin_id);
                    $stmt->execute();
                    $stmt->bind_result($name, $email);
                    $stmt->fetch();
                    $stmt->close();                    
                    $mail =require __DIR__."/mailer.php";
                    $mail->setFrom("noreplay@vitalCare.com");
                    $mail->addAddress($email);
                    $mail->Subject = "Account Deactivation Notice";
                    $mail->Body = <<<END
                    <p>Dear $name,</p>
                    <p>We would like to inform you that your admin account has been <strong>deactivated</strong>.</p>
                    <p>As a result, you will no longer be able to log in or perform administrative actions on the platform.</p>
                    <p>If you believe this was a mistake or need further assistance, please contact the system administrator.</p>
                    <p>Thank you for your understanding.</p>
                    <p>Best regards,<br>
                    <strong>VitalCare Team</strong></p>
                    END;
                    try{
                        $mail->send();
                    }
                    catch(Exception $e ){
                        $error[]= "couldnt send email please try again later";
                    }
                }
            } else {
                $errors[] = "opps! something went wrong while deactivating the admin account.";
            }
        } 
    }
    // reactivate admins accounts
    // Check if the form is submitted to reactivate an admin account
    if (isset($_POST['active']) && $_POST['active'] === "active") {
        $admin_id = $_POST["admin_id"] ?? null;
        if ($admin_id) {
            $stmt = $conn->prepare("UPDATE admins SET active='yes' WHERE admin_id = ?");
            if ($stmt) {
                $stmt->bind_param('i', $admin_id);
                if ($stmt->execute()) {
                    $success = "Admin with ID " . $admin_id . " has been reactivated, and the admin has been notified.";
                    // Send email to the admin about the activation
                    $stmt = $conn->prepare('SELECT name, email FROM admins WHERE admin_id = ?');
                    $stmt->bind_param('i', $admin_id);
                    $stmt->execute();
                    $stmt->bind_result($name, $email);
                    $stmt->fetch();
                    $stmt->close();                    
                    $mail =require __DIR__."/mailer.php";
                    $mail->setFrom("noreplay@vitalCare.com");
                    $mail->addAddress($email);
                    $mail->Subject = "Account Reactivation Notice";
                    $mail->Body = <<<END
                    <p>Dear $name,</p>
                    <p>We are pleased to inform you that your admin account has been <strong>reactivated</strong>.</p>
                    <p>You can now log in and resume performing administrative actions on the platform.</p>
                    <p>If you have any questions or need further assistance, please feel free to contact the system administrator.</p>
                    <p>Welcome back!</p>
                    <p>Best regards,<br>
                    <strong>VitalCare Team</strong></p>
                    END;
                    try{
                        $mail->send();
                    }
                    catch(Exception $e ){
                        $error[]= "couldnt send email please try again later";
                    }
                } 
            } else {
                $errors[] = "opps! something went wrong while reactivating the admin account.";
            }
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
<!-- -----------------------------------------------------------------------Admin dashboard page : admins----------------------------------------------------------------------- -->
<!--nav bar-->
<?php require("nav.php"); ?>
<!-- Dashboard -->
    <div class="w-full bg-center bg-cover rounded-lg p-6 shadow-lg anim" style="background-image: url('../pics/background8.jpeg');">
<!-- Header Section -->
        <header class="text-center mb-6 text-[#1a4568] anim1">
            <h1 class="text-3xl font-bold leading-tight mb-4">Admins</h1>
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
<!-----------------------------------------------------------------Section: View admins-------------------------------------------------------------- -->
<h3 class="text-2xl font-bold text-[#1a4568] my-4 anim1">Our Admins</h3>
<!-- mangage admins Tables  -->                         
<table class="appointments-table w-full border-separate border-spacing-2 border border-[#1a4568] shadow-md rounded-lg anim2">
    <thead class="bg-[#24598a] text-white">
        <tr>
            <th class="font-bold text-left">Admin Name</th>
            <th class="font-bold text-left">Admin Id</th>
            <th class="font-bold text-left">Email</th>
            <th class="font-bold text-left">Phone Number</th>
            <th class="font-bold text-left">Action</th>
        </tr>
    </thead>
    <tbody>
<?php 
            $query = "SELECT name,admin_id,email,phone_number FROM admins where active='yes' order by admin_id";
            $stmt = $conn->query($query);
            $admins = $stmt->fetch_all(MYSQLI_ASSOC);
            if(empty($admins)){
                echo '<td class="font-bold border border-[#1a4568] text-center">No admins found in the database</td>';
            }
            foreach ($admins as $row) {
                echo '<tr class="bg-[#ebf5fa] hover:bg-[#d3e8f1]">';
                echo '<td class="font-bold border border-[#1a4568]">' . ucfirst(htmlspecialchars($row['name'])) . '</td>';
                echo '<td class="font-bold border border-[#1a4568]">' . $row['admin_id'] . '</td>';
                echo '<td class="font-bold border border-[#1a4568]">' . ucfirst(htmlspecialchars($row['email'])) . '</td>';
                echo '<td class="font-bold border border-[#1a4568]">' . htmlspecialchars($row['phone_number']) . '</td>';
                echo '<td class="font-bold border border-[#1a4568]">';
?>
                <!-- Approve and Cancel Button Form -->
                <form action="#" method="post" onsubmit="return checker();">
                        <!-- Cancel Button -->
                        <button type="submit" name="delete" value="delete" class="cancel-btn py-2 px-4 rounded-lg bg-red-500 text-white
                        hover:bg-red-600 uppercase">
                        Deactivate  
                        </button>
                        <!-- Hidden Input for doctor ID -->
                        <input type="hidden" name="admin_id" value="<?php echo htmlspecialchars($row['admin_id']); ?>">
                        <script>
                            function checker(){
                                return confirm("Are you sure you want to deactivate this admin?");
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
<h3 class="text-2xl font-bold text-[#1a4568] my-4 anim1">Deactivated admins Account</h3>
<!-- mangage admins Tables  -->                         
<table class="appointments-table w-full border-separate border-spacing-2 border border-[#1a4568] shadow-md rounded-lg anim2">
    <thead class="bg-[#24598a] text-white">
        <tr>
        <th class="font-bold text-left">Admin Name</th>
            <th class="font-bold text-left">Admin Id</th>
            <th class="font-bold text-left">Email</th>
            <th class="font-bold text-left">Phone Number</th>
            <th class="font-bold text-left">Action</th>
        </tr>
    </thead>
    <tbody>
        <?php 
            $query = "SELECT name,admin_id,email,phone_number FROM admins where active='no' order by admin_id";
            $stmt = $conn->query($query);
            $admins = $stmt->fetch_all(MYSQLI_ASSOC);
            if(empty($admins)){
                echo '<td class="font-bold border border-[#1a4568] text-center">No deactivated accounts</td>';
            }
            foreach ($admins as $row) {
                echo '<tr class="bg-[#ebf5fa] hover:bg-[#d3e8f1]">';
                echo '<td class="font-bold border border-[#1a4568]">' . ucfirst(htmlspecialchars($row['name'])) . '</td>';
                echo '<td class="font-bold border border-[#1a4568]">' . $row['admin_id'] . '</td>';
                echo '<td class="font-bold border border-[#1a4568]">' . ucfirst(htmlspecialchars($row['email'])) . '</td>';
                echo '<td class="font-bold border border-[#1a4568]">' . htmlspecialchars($row['phone_number']) . '</td>';
                echo '<td class="font-bold border border-[#1a4568]">';
        ?>
<!-- Approve and Cancel Button Form -->
                <form action="#" method="post" onsubmit="return checker();">
                        <button type="submit" name="active" value="active" class="accept-btn py-2 px-4 mr-5 rounded-lg bg-green-500
                        text-white hover:bg-green-600 uppercase ">
                        Activate
                        </button>
<!-- Hidden Input for Admin Id -->
                        <input type="hidden" name="admin_id" value="<?php echo htmlspecialchars($row['admin_id']); ?>">
                        <script>
                            function checker(){
                                return confirm("Are you sure you want to activate this admin?");
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
<?php 
// Check if the user is a super admin and display the "Add Admin" button
if($_SESSION['is_super']&&isset($_SESSION['is_super'])){
?>
    <h3 class="text-2xl font-bold text-[#1a4568] my-4 anim1">Add a New Admin</h3>
    <div class="flex items-center justify-start anim2 ">
        <a href="adminAddAdmin.php" class="text-xl font-bold mt-5 px-4 py-3 bg-[#1a4568] text-white rounded-full uppercase 
        shadow-md hover:bg-white hover:text-[#1a4568] hover:scale-105 transition-transform duration-300">
            Add Admin
        </a>
    </div>
<?php 
}
?>
</section>
</div>
<!-- Footer -->
<?php require("footer.php"); ?>
</body>
</html>