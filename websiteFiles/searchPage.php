<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Redirect to login page if user is not logged in
if (!isset($_SESSION["is_logged_in"]) || !$_SESSION["is_logged_in"]) {
    header("Location: signIn.php");
    exit;
}
// Redirect to home page if user is logged in as a admin
if (isset($_SESSION["is_admin"]) && !empty($_SESSION['is_admin'])){
    header("Location: index.php");
    exit;
}
require("../phpFiles/connect.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VitalCare</title>
<!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Faculty+Glyphic&display=swap" rel="stylesheet">
<!-- CSS Files -->
    <link href="../cssFiles/output.css" rel="stylesheet">
</head>
<!------------------------------------------------------------------------- Main search page : patients and doctors----------------------------------------------------------------------- -->
<!-- nav bar -->
<?php require("nav.php"); ?>
<!-- Main Section in Search Page -->
<div class=" anim flex flex-col gap-6 bg-cover bg-center p-10 lg:p-20 shadow-lg mx-4 my-6 rounded-lg relative mb-8 h-auto" 
    style="background-image: url('../pics/background1.jpg');">
<!-- Search Header -->
    <div class="flex flex-col items-center mt-20 mb-16 px-4 text-center">
    <h2 class="anim1 text-white text-5xl sm:text-3xl md:text-4xl lg:text-5xl font-[Faculty_Glyphic] 
            leading-snug tracking-tight custom-text-shadow">
        Care That Fits Your Coverage.
    </h2>
    <h3 class="anim2 mt-4 text-white text-xl sm:text-3xl md:text-4xl lg:text-5xl font-[Faculty_Glyphic] 
            leading-snug tracking-tight custom-text-shadow">
        You choose. We find.
    </h3>
</div>
<!-- Search Form -->
<div class="mt-10">
    <form action="searchPageDoc.php" method="post" class="flex flex-col gap-6 mx-auto w-full max-w-6xl px-4">
<!-- Input Fields -->
        <div class="p-6 bg-[#DBECF4] flex flex-wrap gap-4 sm:gap-5 justify-center rounded-[50px]">
<!-- Specialty Field -->
            <div class="flex items-center gap-3 w-full sm:w-[48%] md:w-[30%]">
                <i class="fa-solid fa-user-doctor text-[#1A4568] text-2xl shrink-0"></i>
                <select id="specialty" name="specialty" required
                    class="anim2 bg-[#DBECF4] border-none outline-none w-full px-4 py-3 text-[#1A4568] font-bold text-lg rounded-[15px]">
                    <?php
                    if(isset($_SESSION['specialty']) && $_SESSION['specialty']){
                        echo '<option value="' . htmlspecialchars($_SESSION['specialty']) . '">' 
                        . ucwords(htmlspecialchars($_SESSION['specialty'])) . '</option>';
                    } else {
                        echo '<option value="" disabled selected class="text-gray-500">Specialty</option>';
                    }
                    $stmt = $conn->query("SELECT DISTINCT specialty FROM doctors WHERE admin_approval='yes' AND active='yes'");
                    while ($row = $stmt->fetch_assoc()) {
                        echo '<option value="' . htmlspecialchars($row['specialty']) . '">' 
                        . ucwords(htmlspecialchars($row['specialty'])) . '</option>';
                    }
                    ?>
                </select>
            </div>
<!-- Location Field -->
            <div class="flex items-center gap-3 w-full sm:w-[48%] md:w-[30%]">
                <i class="fa-solid fa-location-dot text-[#1A4568] text-2xl shrink-0"></i>
                <select id="city" name="city" required
                    class="anim2 bg-[#DBECF4] border-none outline-none w-full px-4 py-3 text-[#1A4568] font-bold text-lg rounded-[15px]">
                    <?php
                    if(isset($_SESSION['location']) && $_SESSION['location']){
                        echo '<option value="' . htmlspecialchars($_SESSION['location']) . '">' 
                        . ucwords(htmlspecialchars($_SESSION['location'])) . '</option>';
                    } else {
                        echo '<option value="" disabled selected class="text-gray-500">Location</option>';
                    }
                    $stmt = $conn->query("SELECT DISTINCT location FROM doctors WHERE admin_approval='yes' AND active='yes' AND active_hospital='yes'");
                    while ($row = $stmt->fetch_assoc()) {
                        echo '<option value="' . htmlspecialchars($row['location']) . '">' 
                        . ucwords(htmlspecialchars($row['location'])) . '</option>';
                    }
                    ?>
                </select>
            </div>
<!-- Insurance Field -->
            <div class="flex items-center gap-3 w-full sm:w-[48%] md:w-[30%]">
                <i class="fa-solid fa-shield text-[#1A4568] text-2xl shrink-0"></i>
                <select id="insurance" name="insurance" required
                    class="anim2 bg-[#DBECF4] border-none outline-none w-full px-4 py-3 text-[#1A4568] font-bold text-lg rounded-[15px]">
                    <?php
                    if(isset($_SESSION['insurance']) && $_SESSION['insurance']){
                        echo '<option value="' . htmlspecialchars($_SESSION['insurance']) . '">' 
                        . ucwords(htmlspecialchars($_SESSION['insurance'])) . '</option>';
                    } else {
                        echo '<option value="" disabled selected class="text-gray-500">Insurance Plan</option>';
                    }
                    $stmt = $conn->query("SELECT provider_name FROM insurances WHERE active='yes'");
                    while ($row = $stmt->fetch_assoc()) {
                        echo '<option value="' . htmlspecialchars($row['provider_name']) . '">' 
                        . ucwords(htmlspecialchars($row['provider_name'])) . '</option>';
                    }
                    $conn->close();
                    $stmt->close();
                    ?>
                </select>
            </div>
<!-- Date Field -->
            <div class="flex items-center gap-3 w-full sm:w-[48%] md:w-[30%] anim2">
                <?php
                date_default_timezone_set('Asia/Riyadh');
                $currentDate = date('Y-m-d'); 
                $lastDayOfYear = date('Y-m-d', strtotime('last day of December this year'));
                ?>
                <input type="date" id="date" name="date" required
                    class="bg-[#DBECF4] w-full px-4 py-3 text-[#1A4568] font-bold text-lg rounded-[50px] border-none outline-none"
                    min="<?php echo $currentDate; ?>"
                    max="<?php echo $lastDayOfYear; ?>"
                    value="<?php echo isset($_SESSION['date']) ? $_SESSION['date'] : ''; ?>">
            </div>
        </div>
<!-- Search Button -->
        <div class="flex justify-center mt-6 anim2">
            <button type="submit"
                class="bg-[#1A4568] text-white px-6 py-3 rounded-full font-bold uppercase transition-all duration-300 
                hover:bg-white hover:text-[#1A4568] hover:scale-105 hover:shadow-lg transform hover:-translate-y-1 
                active:scale-95 active:shadow-sm">
                Search
            </button>
        </div>
    </form>
</div>
</div>
<!-- main page footer -->
<?php require("footer.php"); ?>
</body>
</html>