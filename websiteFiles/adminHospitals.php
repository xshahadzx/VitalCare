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
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $errors = [];  
    $success = "";
    //deactivate hospital
    if (isset($_POST['delete']) && $_POST['delete'] === "delete") {
        $hospital_id = $_POST["hospital_id"] ?? null;
        if ($hospital_id) {
            $delete = $conn->prepare("UPDATE hospitals set active='no' WHERE hospital_id = ?");
            $delete->bind_param('i',$hospital_id);
            $delete->execute();
            if ($delete->execute()) {
                $success = "Hospital ID " . htmlspecialchars($hospital_id) . " and its registered doctors have been deactivated.";
                $deactivate_doc=$conn->prepare("UPDATE doctors 
                                                        SET active_hospital = 'no' 
                                                        WHERE doctor_id IN (
                                                            SELECT hd.doctor_id 
                                                            FROM hospital_doctors hd 
                                                            JOIN hospitals h ON hd.hospital_id = h.hospital_id 
                                                            WHERE h.hospital_id = ?)
                                                        ");
                $deactivate_doc->bind_param('i',$hospital_id);
                $deactivate_doc->execute();
            } else {
                $errors[] = "opss! Error deactivating the hospital please try again later.";
            }
        } else {
            $errors[] = "Invalid Hospital ID.";
        }
    }
}
//activate hospital
if (isset($_POST['active']) && $_POST['active'] === "active") {
    $hospital_id = $_POST["hospital_id"] ?? null;
    if ($hospital_id) {
        $stmt = $conn->prepare("UPDATE hospitals SET active='yes' WHERE hospital_id = ?");
        if ($stmt) {
            $stmt->bind_param('i', $hospital_id);
            if ($stmt->execute()) {
                $success = "Hospital ID " . htmlspecialchars($hospital_id) . " and its registered doctors have been activated.";
                $activate_doc=$conn->prepare("UPDATE doctors 
                                                        SET active_hospital = 'yes' 
                                                        WHERE doctor_id IN (
                                                            SELECT hd.doctor_id 
                                                            FROM hospital_doctors hd 
                                                            JOIN hospitals h ON hd.hospital_id = h.hospital_id 
                                                            WHERE h.hospital_id = ?)");
                $activate_doc->bind_param('i',$hospital_id);
                $activate_doc->execute();
            } else {
                $errors[] = "opss! Error reactivating the hospital please try again later.";
            }
        } else {
            $errors[] = "Invalid Hospital ID.";
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
<!-- -----------------------------------------------------------------------Admin dashboard page : hospitals----------------------------------------------------------------------- -->
<!--nav bar-->
<?php require("nav.php"); ?>
<!-- Dashboard -->
<div class="w-full bg-center bg-cover rounded-lg p-6 shadow-lg anim" style="background-image: url('../pics/background8.jpeg');">
<!-----------------------------------------------------------------Section: View Hospitals-------------------------------------------------------------- -->
<!-- Header Section -->
        <header class="text-center mb-6 text-[#1a4568] anim1">
            <h1 class="text-3xl font-bold leading-tight mb-4">VitalCare Hospitals</h1>
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
        <section class="mb-10">
<!-- Section: View hospitals -->
    <h3 class="text-2xl font-semibold text-[#1a4568] my-4 anim1">VitalCare Hospitals</h3>                   
    <table class="appointments-table w-full border-separate border-spacing-2 border border-[#1a4568] shadow-md rounded-lg anim2">
            <thead class="bg-[#24598a] text-white">
                <tr>
                    <th class="font-bold text-left">Hospital ID</th>
                    <th class="font-bold text-left">Hospital Name</th>
                    <th class="font-bold text-left">VitalCare Doctors</th>
                    <th class="font-bold text-left">Action</th>
                </tr>
            </thead>
            <tbody>
            <?php 
                $query = "SELECT  h.hospital_id,h.name, COUNT(*) AS doc_num
                            FROM hospitals h JOIN hospital_doctors hd ON h.hospital_id= hd.hospital_id
                            where active='yes'
                            GROUP BY h.hospital_id,h.name";
                $stmt = $conn->query($query);
                $hospitals = $stmt->fetch_all(MYSQLI_ASSOC);
                if (empty($hospitals)) {
                    echo '<tr><td colspan="4" class="font-bold border border-[#1a4568] text-center">No hospitals found in the database.</td></tr>';
                }
                // Loop through the hospitals and display them in the table
                foreach ($hospitals as $row) {
                    echo '<tr class="bg-[#ebf5fa] hover:bg-[#d3e8f1]">';
                    echo '<td class="font-bold border border-[#1a4568]">' . $row['hospital_id'] . '</td>';
                    echo '<td class="font-bold border border-[#1a4568]">' . ucfirst(htmlspecialchars($row['name'])) . '</td>';
                    echo '<td class="font-bold border border-[#1a4568]">' . $row['doc_num'] . '</td>';
                    echo '<td class="font-bold border border-[#1a4568]">';?>
                    <form action="#" method="post" onsubmit="return checker();">
                        <button type="submit" name="delete" value="delete" class="cancel-btn py-2 px-4 rounded-lg bg-red-500 text-white
                            hover:bg-red-600 uppercase">
                            Deactivate
                        </button>
                        <input type="hidden" name="hospital_id" value="<?php echo htmlspecialchars($row['hospital_id']); ?>">
                        <script>
                            function checker(){
                                return confirm("Deactivate this hospital? All associated doctor accounts will also be deactivated You can reverse this action later.");
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
<!-- deavtivated hospitals -->
<h3 class="text-2xl font-semibold text-[#1a4568] my-4 anim1">Deactivated Hospitals</h3>                   
<table class="appointments-table w-full border-separate border-spacing-2 border border-[#1a4568] shadow-md rounded-lg anim2">
            <thead class="bg-[#24598a] text-white">
                <tr>
                    <th class="font-bold text-left">Hospital ID</th>
                    <th class="font-bold text-left">Hospital Name</th>
                    <th class="font-bold text-left">VitalCare Doctors</th>
                    <th class="font-bold text-left">Action</th>
                </tr>
            </thead>
            <tbody>
            <?php 
                $query = "SELECT  h.hospital_id,h.name, COUNT(*) AS doc_num
                            FROM hospitals h JOIN hospital_doctors hd ON h.hospital_id= hd.hospital_id
                            where active='no'
                            GROUP BY h.hospital_id,h.name";
                $stmt = $conn->query($query);
                $hospitals = $stmt->fetch_all(MYSQLI_ASSOC);
                if (empty($hospitals)) {
                    echo '<tr><td colspan="4" class="font-bold border border-[#1a4568] text-center">No Deactivated Hospitals.</td></tr>';
                }
                // Loop through the hospitals and display them in the table
                foreach ($hospitals as $row) {
                    echo '<tr class="bg-[#ebf5fa] hover:bg-[#d3e8f1]">';
                    echo '<td class="font-bold border border-[#1a4568]">' . $row['hospital_id'] . '</td>';
                    echo '<td class="font-bold border border-[#1a4568]">' . ucfirst(htmlspecialchars($row['name'])) . '</td>';
                    echo '<td class="font-bold border border-[#1a4568]">' . $row['doc_num'] . '</td>';
                    echo '<td class="font-bold border border-[#1a4568]">';
                    ?>
                    <form action="#" method="post" onsubmit="return checker();">
                        <button type="submit" name="active" value="active" class="accept-btn py-2 px-4 mr-5 rounded-lg bg-green-500 text-white
                            hover:bg-green-600 uppercase ">
                            Activate
                        </button>
                        <input type="hidden" name="hospital_id" value="<?php echo htmlspecialchars($row['hospital_id'])?>">
                        <script>
                            function checker(){
                                return confirm("Activate this hospital? All associated doctor accounts will also be activated.");
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
<!-- Section: View hospitals and doctors -->
        <h3 class="text-2xl font-semibold text-[#1a4568] my-4 anim1">Our Hospitals & Our Doctors</h3>                   
        <table class="appointments-table w-full border-separate border-spacing-2 border border-[#1a4568] shadow-md rounded-lg anim2">
            <thead class="bg-[#24598a] text-white">
                <tr>
                    <th class="font-bold text-left">Hospital ID</th>
                    <th class="font-bold text-left">Hospital Name</th>
                    <th class="font-bold text-left">VitalCare Doctors</th>
                </tr>
            </thead>
            <tbody>
        <?php   
                $query = "SELECT  DISTINCT h.hospital_id,h.name AS hospital_name
                        FROM hospitals h JOIN hospital_doctors hd ON h.hospital_id= hd.hospital_id
                        JOIN doctors d ON hd.doctor_id=d.doctor_id
                        where h.active='yes'
                        ORDER BY h.hospital_id";
                $stmt = $conn->query($query);
                $hospitals = $stmt->fetch_all(MYSQLI_ASSOC);
                if (empty($hospitals)) {
                    echo '<tr><td colspan="4" class="font-bold border border-[#1a4568] text-center">No hospitals in the database</td></tr>';
                }
                // Loop through the hospitals and display them in the table
                foreach ($hospitals as $row) {
                    echo '<tr class="bg-[#ebf5fa] hover:bg-[#d3e8f1]">';
                    echo '<td class="font-bold border border-[#1a4568]">' . $row['hospital_id'] . '</td>';
                    echo '<td class="font-bold border border-[#1a4568]">' . ucfirst(htmlspecialchars($row['hospital_name'])).'</td>';
                    //get each doctors name for each hospital 
                    $sql=$conn->prepare("SELECT  DISTINCT d.name
                    FROM hospitals h JOIN hospital_doctors hd ON h.hospital_id= hd.hospital_id
                    JOIN doctors d ON hd.doctor_id=d.doctor_id
                    WHERE h.hospital_id=?
                    order by d.name");
                    $sql->bind_param('i',$row['hospital_id']);
                    $sql->execute();
                    $hospital_names=$sql->get_result();
                    if(empty($hospital_names)){
                        echo '<tr><td colspan="4" class="font-bold border border-[#1a4568] text-center">No doctors in the database</td></tr>';
                    }
                    else{
                        echo '<td class="border border-[#1a4568]">';
                        while($row=$hospital_names->fetch_assoc() ){
                            echo 'Dr. '.ucwords($row['name'])." , ";
                        }
                        echo ' . ';
                        echo '</td>';
                    }
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
