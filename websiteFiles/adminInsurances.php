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
//decativate and activate the insurances according to the button clicked 
require_once("../phpFiles/connect.php");
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $errors = [];  
    $success = "";
    if (isset($_POST['delete']) && $_POST['delete'] === "delete") {
        $insurance_id = $_POST["insurance_id"] ?? null;
        if ($insurance_id) {
            $delete = $conn->prepare("UPDATE insurances set active='no' WHERE insurance_id = ?");
            $delete->bind_param('i',$insurance_id);
            $delete->execute();
            if ($delete->execute()) {
                $success = "Insurance with ID " . htmlspecialchars($insurance_id) . " has been successfully deactivated.";
            } else {
                $errors[] = "opss! Error deactivating the insurance. please try again later.";
            }
        } else {
            $errors[] = "Invalid Insurance ID.";
        }
    }
}
// Check if the form is submitted for activation
if (isset($_POST['active']) && $_POST['active'] === "active") {
    $insurance_id = $_POST["insurance_id"] ?? null;
    if ($insurance_id) {
        $stmt = $conn->prepare("UPDATE insurances SET active='yes' WHERE insurance_id = ?");
        if ($stmt) {
            $stmt->bind_param('i', $insurance_id);
            if ($stmt->execute()) {
                $success = "Insurance with ID " . htmlspecialchars($insurance_id) . " successfully has been activated.";
            } else {
                $errors[] = "opss! Error deactivating the insurance. please try again later.";
            }
        } else {
            $errors[] = "Invalid Insurance ID.";
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
<!-- -----------------------------------------------------------------------Admin dashboard page : insurances----------------------------------------------------------------------- -->
<!--nav bar-->
<?php require("nav.php"); ?>
<!-- Dashboard -->
<div class="w-full bg-center bg-cover rounded-lg p-6 shadow-lg anim" style="background-image: url('../pics/background8.jpeg');">
<!-----------------------------------------------------------------Section: View insurances-------------------------------------------------------------- -->
<!-- Header Section -->
    <header class="text-center mb-6 text-[#1a4568] anim1">
        <h1 class="text-3xl font-bold leading-tight mb-4">Insurances</h1>
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
<!-- Add Insurance Button -->
<section class="mb-10 anim1">
    <h3 class="text-2xl font-bold text-[#1a4568] my-4">Add a New Insurance:</h3>
    <div class="flex items-center justify-start ">
        <a href="addInsurance.php" class="text-xl font-bold mt-5 px-4 py-3 bg-[#1a4568] text-white rounded-full
        uppercase shadow-md hover:bg-white hover:text-[#1a4568] hover:scale-105 transition-transform duration-300">
            Add Insurance
        </a>
    </div>
<!-- Section: View Insurances -->
        <h3 class="text-2xl font-semibold text-[#1a4568] my-4 anim1">All Insurances</h3>                   
        <table class="appointments-table w-full border-separate border-spacing-2 border border-[#1a4568] shadow-md rounded-lg anim2">
            <thead class="bg-[#24598a] text-white">
                <tr>
                    <th class="font-bold text-left">Insurance ID</th>
                    <th class="font-bold text-left">Insurance Provider</th>
                    <th class="font-bold text-left">VitalCare hospitals</th>
                    <th class="font-bold text-left">Action</th>
                </tr>
            </thead>
            <tbody>
<?php 
                $query = "SELECT i.insurance_id, i.provider_name, COUNT(*) AS hospital_num
                        FROM insurances i 
                        JOIN hospital_insurances hi ON i.insurance_id = hi.insurance_id
                        where i.active='yes'
                        GROUP BY i.insurance_id, i.provider_name
                        ORDER BY i.insurance_id";
                $stmt = $conn->query($query);
                $insurances = $stmt->fetch_all(MYSQLI_ASSOC);
                if (empty($insurances)) {
                    echo '<tr>
                    <td colspan="4" class="font-bold border border-[#1a4568] text-center">
                        No Insurance Records Found. Please add the hospitals insurances 
                        <a href="setAvailableTimes.php" class="underline text-blue-600 hover:text-blue-800 ml-1">here</a>.</td>
                    </tr>';
                }
                // Loop through the insurances and display them in the table
                foreach ($insurances as $row) {
                    echo '<tr class="bg-[#ebf5fa] hover:bg-[#d3e8f1]">';
                    echo '<td class="font-bold border border-[#1a4568]">' . $row['insurance_id'] . '</td>';
                    echo '<td class="font-bold border border-[#1a4568]">' . ucwords(htmlspecialchars($row['provider_name'])) 
                        . '</td>';
                    echo '<td class="font-bold border border-[#1a4568]">' . $row['hospital_num'] . '</td>';
                    echo '<td class="font-bold border border-[#1a4568]">';
                    ?>
                    <form action="#" method="post" onsubmit="return checker();">
                        <button type="submit" name="delete" value="delete" class="cancel-btn py-2 px-4 rounded-lg bg-red-500 text-white
                            hover:bg-red-600 uppercase">
                            Deactivate
                        </button>
                        <input type="hidden" name="insurance_id" value="<?php echo htmlspecialchars($row['insurance_id'])?> ">
                        <script>
                        function checker() {
                            return confirm("Are you sure you want to deactivate this insurance record?");
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
<h3 class="text-2xl font-semibold text-[#1a4568] my-4 anim1">Deactivated Insurances</h3>                   
        <table class="appointments-table w-full border-separate border-spacing-2 border border-[#1a4568] shadow-md rounded-lg anim2">
            <thead class="bg-[#24598a] text-white">
                <tr>
                    <th class="font-bold text-left">Insurance ID</th>
                    <th class="font-bold text-left">Insurance Provider</th>
                    <th class="font-bold text-left">Action</th>
                </tr>
            </thead>
            <tbody>
<?php 
                $query = "SELECT i.insurance_id, i.provider_name
                        FROM insurances i 
                        where i.active='no'
                        GROUP BY i.insurance_id, i.provider_name
                        ORDER BY i.insurance_id";
                $stmt = $conn->query($query);
                $insurances = $stmt->fetch_all(MYSQLI_ASSOC);
                if (empty($insurances)) {
                    echo '<tr><td colspan="4" class="font-bold border border-[#1a4568] text-center">No Deactivated Insurances in the Database</td></tr>';
                }
                // Display each insurance in the table
                foreach ($insurances as $row) {
                    echo '<tr class="bg-[#ebf5fa] hover:bg-[#d3e8f1]">';
                    echo '<td class="font-bold border border-[#1a4568]">' . $row['insurance_id'] . '</td>';
                    echo '<td class="font-bold border border-[#1a4568]">' . ucwords(htmlspecialchars($row['provider_name'])).'</td>';
                    echo '<td class="font-bold border border-[#1a4568]">';
?>
                    <form action="#" method="post" onsubmit="return checker();">
                        <button type="submit" name="active" value="active" class="accept-btn py-2 px-4 mr-5 rounded-lg bg-green-500 text-white
                        hover:bg-green-600 uppercase ">
                        Activate
                        </button>
                        <input type="hidden" name="insurance_id" value="<?php echo htmlspecialchars($row['insurance_id'])?> ">
                        <script>
                        function checker() {
                            return confirm("Are you sure you want to reactivate this insurance record?");
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
        <!-- Section: View Insurances and hospitals -->
        <h3 class="text-2xl font-semibold text-[#1a4568] my-4 anim1">All Hospitals and Insurances </h3>                          
        <table class="appointments-table w-full border-separate border-spacing-2 border border-[#1a4568] shadow-md rounded-lg anim2">
            <thead class="bg-[#24598a] text-white">
                <tr>
                    <th class="font-bold text-left">Hospital ID</th>
                    <th class="font-bold text-left">Hospital Name</th>
                    <th class="font-bold text-left">Insurance Provider</th>
                </tr>
            </thead>
            <tbody>
<?php 
                $query = "SELECT DISTINCT h.hospital_id, h.name
                        FROM hospital_insurances hi 
                        JOIN hospitals h ON h.hospital_id=hi.hospital_id
                        JOIN insurances i ON i.insurance_id = hi.insurance_id
                        where i.active='yes'
                        ORDER BY h.hospital_id";
                $stmt = $conn->query($query);
                $insurances = $stmt->fetch_all(MYSQLI_ASSOC);
                if (empty($insurances)) {
                    echo '<tr><td colspan="4" class="font-bold border border-[#1a4568] text-center">No insurances in the database</td></tr>';
                }
                // Display each insurance in the table
                foreach ($insurances as $row) {
                    echo '<tr class="bg-[#ebf5fa] hover:bg-[#d3e8f1]">';
                    echo '<td class="font-bold border border-[#1a4568]">' . $row['hospital_id'] . '</td>';
                    echo '<td class="font-bold border border-[#1a4568]">' . ucwords(htmlspecialchars($row['name'])) . '</td>';
                    //get each hospitals insurances list
                    $sql=$conn->prepare("SELECT DISTINCT i.provider_name
                        FROM hospital_insurances hi 
                        JOIN hospitals h ON h.hospital_id=hi.hospital_id
                        JOIN insurances i ON i.insurance_id = hi.insurance_id
                        where hi.hospital_id=?
                        order by i.provider_name");
                    $sql->bind_param('i',$row['hospital_id']);
                    $sql->execute();
                    $insurances_names=$sql->get_result();
                    if(empty($insurances_names)){
                        echo '<tr><td colspan="4" class="font-bold border border-[#1a4568] text-center">No insurances in the database</td></tr>';
                    }
                    else{
                        echo '<td class="border border-[#1a4568]">';
                        // Loop through the insurances and display them in the table
                        while($row=$insurances_names->fetch_assoc() ){
                            echo ucwords($row['provider_name'])." , ";
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
