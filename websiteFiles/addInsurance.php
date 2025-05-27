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
$errors = [];
$success = "";
require("../phpFiles/connect.php");
// Check if the user is signed in
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $insurance = trim($_POST['insurance'])?? null;
    $hospital_id = trim($_POST['hospital_id'])?? null;
    // Validation
    if (empty($insurance)||empty($hospital_id)) {
        $errors[] = "Please enter the insurance name and select a hospital.";
    }
    if (!preg_match("/^[a-zA-Z-' ]+$/", $insurance)) {
        $errors[]="Invalid insurance name. Only letters, spaces, hyphens, and apostrophes are allowed.";
    }
    // check if the insurance already exists and is linked with the hospital
    $stmt_select = $conn->prepare("SELECT insurance_id FROM insurances WHERE provider_name = ?");
    $stmt_select->bind_param("s", $insurance);
    $stmt_select->execute();
    $insurance_id = $stmt_select->get_result()->fetch_assoc()['insurance_id']??'';
    $check=$conn->prepare('select hospital_insurances_id from hospital_insurances where insurance_id=? and hospital_id=?');
    $check->bind_param('ii',$insurance_id,$hospital_id);
    $check->execute();
    $check_res=$check->get_result();
    if($check_res->num_rows>0){
        $errors[] = "This hospital already has this insurance registered.";
    }
    if (empty($errors)) {
        // Insert or update insurance
        $stmt_insurance = $conn->prepare("
            INSERT INTO insurances (provider_name,active) 
            VALUES (?,'yes') 
            ON DUPLICATE KEY UPDATE provider_name = VALUES(provider_name)");
        $stmt_insurance->bind_param("s", $insurance);
        if ($stmt_insurance->execute()) {
            $stmt_insurance->close();
            // Link doctor and insurance (insurnace_id and hospital_id)
            $hospital_insurances = $conn->prepare("
                INSERT INTO hospital_insurances (hospital_id, insurance_id) 
                VALUES (?, ?)
            ");
            $hospital_insurances->bind_param("ii", $hospital_id, $insurance_id);
            if ($hospital_insurances->execute()) {
                $success = "The insurance list was successfully updated.";
            } else {
                $errors[] = "There was an issue updating the insurance list.";
            }
            $hospital_insurances->close();
        } 
        else {
            $errors[] = "Failed to update the insurance record.";
        }
    }
}
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
<!-- Javascript -->
        <link href="../cssFiles/output.css" rel="stylesheet">
<!-- CSS Files -->
        <link rel="stylesheet" href="../cssFiles/styles.css">
    </head>
<!-- -----------------------------------------------------------------------Add insurance page : doctors----------------------------------------------------------------------- -->
<body>
<?php require("nav.php"); ?>
<!-- Display Error Messages -->
    <?php if (!empty($errors)): ?>
        <div class="px-4 py-3 rounded relative mb-4">
            <ul class="mt-2 list-disc list-inside">
                <?php foreach ($errors as $err): ?>
                    <li class="error-msg font-bold anim"><?php echo htmlspecialchars($err); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
<!-- Display Success Message -->
    <?php if (!empty($success)): ?>
        <div class="px-4 py-3 rounded relative mb-4">
            <p class="succ-msg font-bold anim "><?php echo htmlspecialchars($success); ?></p> 
        </div>
    <?php endif; ?>
<!-- main insurances page -->
<div class="max-w-[600px] mx-auto p-10 bg-center bg-cover rounded-lg shadow-md flex justify-center items-center" 
    style="background-image: url('../pics/background8.jpeg'); height: 600px; width: 500px;">
    <!-- Content Box -->
    <div class="w-full max-w-md p-8 rounded-xl flex flex-col items-center gap-6 anim">
        <!-- Back Button -->
        <div class="m-4 w-full flex justify-between items-center">
            <a href="adminInsurances.php" class="text-[#1A4568] font-bold uppercase hover:underline flex items-center gap-2">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
        <h3 class="text-2xl font-bold text-center text-[#1A4568] m-4 anim1">
            Add Insurances<br>
            <span class="text-lg font-normal">Select a hospital and enter the supported insurance plan.</span>
        </h3>
        <form action="addInsurance.php" class="flex flex-col w-full anim2" method="post">
            <?php
            $stmt = $conn->query("SELECT name,hospital_id FROM hospitals WHERE active='yes'");
            $rows = $stmt->fetch_all(MYSQLI_ASSOC);
            ?>
            <!-- Hospital Selection -->
            <select id="specialty" name="hospital_id" 
                class="bg-[#DBECF4] border-none outline-none w-full px-4 py-3 text-[#1A4568] font-bold text-lg rounded-lg 
                hover:text-[#1A4568] focus:ring-2 focus:ring-[#1A4568] transition duration-300 my-4" required>
                <option value="" disabled selected class="text-gray-500">Select The Hospital</option>
                <?php
                foreach ($rows as $row) {
                    echo '<option value="' . htmlspecialchars($row['hospital_id']) . '">' . htmlspecialchars($row['name']) 
                    . '</option>';
                }
                $stmt->close();
                $conn->close();
                ?>
            </select>
            <!-- Insurance Input -->
            <input type="text" placeholder="HealthPlus, Bupa, etc." id="insurance" name="insurance" required
                class="font-bold px-4 py-3 my-4 text-lg outline-none border-2 border-[#1A4568] bg-transparent rounded-lg
                focus:ring-2 focus:ring-[#1A4568] transition duration-300">
            <!-- Submit Button -->
            <button type="submit" class="my-4 px-6 py-3 bg-[#1A4568] text-white rounded-full font-bold uppercase shadow-md 
            hover:bg-white hover:text-[#1A4568] hover:scale-105 focus:ring-2 focus:ring-[#1A4568] transition-transform duration-300">
                Add Insurance
            </button>
        </form>
    </div>
</div>
<?php require("footer.php"); ?>
</body>
</html>