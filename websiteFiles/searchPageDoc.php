<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Redirect to login page if user is not logged in
if (!isset($_SESSION["is_logged_in"]) || !$_SESSION["is_logged_in"]) {
    header("Location: signIn.php");
    exit;
}
// Redirect to home page if the admin is trying to acesss the search page 
if (isset($_SESSION["is_admin"]) && $_SESSION["is_admin"]) {
    header("Location: index.php");
    exit;
}
// Set the default timezone to Asia/Riyadh
date_default_timezone_set('Asia/Riyadh');

require("cookies.php");
require("../phpFiles/connect.php");
//handle the search form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $specialty = $_POST['specialty'];
    $location= $_POST['city'];
    $insurance = $_POST['insurance'];
    $date= $_POST['date'];

    //save user search choices in session variables
    $_SESSION['specialty']=$specialty;
    $_SESSION['location']=$location;
    $_SESSION['insurance']=$insurance;
    $_SESSION['date']=$date;

    $error = [];
    // Check for missing inputs
    if (empty($specialty) || empty($location) || empty($insurance) || empty($date)) {
        $error[]="Error: Missing required input fields.";
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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
<!-- -----------------------------------------------------------------------Search result page: patients----------------------------------------------------------------------- -->
<!--nav bar-->
<?php require("nav.php"); ?>
<!-- Main Search section -->
<div class="bg-center bg-cover p-5 m-2 shadow-lg flex items-center justify-center rounded-lg" 
    style="background-image: url('../pics/background1.jpg');">
<!-- Search bar- form -->
    <form action="searchPageDoc.php" class=" anim m-4 flex flex-col sm:flex-row gap-5 p-5 
        bg-[#DBECF4] rounded-[50px] mx-auto max-w-[max-content] justify-center" method="post">
<!-- Specialty Field -->
        <div class="flex items-center gap-4 w-full sm:w-auto">
            <i class="fa-solid fa-user-doctor text-[#1A4568] text-2xl"></i>
            <select id="specialty" name="specialty" class=" anim1 bg-[#DBECF4] border-none outline-none w-full sm:w-60 px-4 py-3 
                    text-[#1A4568] font-bold text-lg hover:text-[#1A4568]" required>
                <?php
                    if(isset($_SESSION['specialty'])&&$_SESSION['specialty']){
                        echo '<option value="' . htmlspecialchars($_SESSION['specialty'])
                            . '">' . ucwords(htmlspecialchars($_SESSION['specialty'])) . '</option>';
                    }
                    else{
                        echo '<option value="" disabled selected class="text-gray-500">'."Specialty".'</option>';
                    }
                    $stmt_specialty = $conn->query("SELECT DISTINCT specialty FROM doctors where admin_approval='yes' and active='yes'");
                    if ($stmt_specialty->num_rows > 0) {
                        while ($row = $stmt_specialty->fetch_assoc()) {
                            echo '<option value="' . htmlspecialchars($row['specialty']). '">' 
                                . ucwords(htmlspecialchars($row['specialty'])) . '</option>';
                        }
                    }
                ?>
            </select>
        </div>

<!-- Location Field -->
        <div class="flex items-center gap-4 w-full sm:w-auto">
            <i class="fa-solid fa-location-dot text-[#1A4568] text-2xl"></i>
            <select id="city" name="city" class="anim1 bg-[#DBECF4] border-none outline-none w-full sm:w-60 px-4 py-3 
                    text-[#1A4568] font-bold text-lg hover:text-[#1A4568]" required>
                <?php
                if(isset($_SESSION['location'])&&$_SESSION['location']){
                    echo '<option value="' . htmlspecialchars($_SESSION['location']) . '">' 
                        .ucfirst( htmlspecialchars($_SESSION['location'])) . '</option>';
                }
                else{
                    echo '<option value="" disabled selected class="text-gray-500">'."Location".'</option>';
                }
                    $stmt_location = $conn->query("SELECT DISTINCT location FROM doctors where admin_approval='yes' and active='yes'");
                    if ($stmt_location->num_rows > 0) {
                        while ($row = $stmt_location->fetch_assoc()) {
                            echo '<option value="' .htmlspecialchars($row['location']) . '">' 
                                . ucfirst(htmlspecialchars($row['location'])) . '</option>';
                        }
                    }
                ?>
            </select>
        </div>

<!-- Insurance Field -->
        <div class="flex items-center gap-4 w-full sm:w-auto">
            <i class="fa-solid fa-shield text-[#1A4568] text-2xl"></i>
            <select id="insurance" name="insurance" class="anim1 bg-[#DBECF4] border-none outline-none w-full sm:w-60 px-4 py-3 
                    text-[#1A4568] font-bold text-lg hover:text-[#1A4568]" required>
                <?php
                    if(isset($_SESSION['insurance'])&&$_SESSION['insurance']){
                        echo '<option value="' .htmlspecialchars($_SESSION['insurance']) . '">' 
                            .ucwords( htmlspecialchars($_SESSION['insurance'])) . '</option>';
                    }
                    else{
                        echo '<option value="" disabled selected class="text-gray-500">'."Insurance Plan".'</option>';
                    }
                    $stmt_insurance = $conn->query("SELECT provider_name FROM insurances where active='yes' ");
                    if ($stmt_insurance->num_rows > 0) {
                        while ($row = $stmt_insurance->fetch_assoc()) {
                            echo '<option value="' . htmlspecialchars($row['provider_name']) . '">' 
                                . ucwords(htmlspecialchars($row['provider_name'])) . '</option>';
                        }
                    }
                    $stmt_insurance->close();
                    $stmt_location->close();
                    $stmt_specialty->close();
                ?>
            </select>
        </div>

<!-- Date Field -->
        <div class="flex items-center gap-4 w-full sm:w-auto anim2">
            <?php
                date_default_timezone_set('Asia/Riyadh');
                $currentDate = date('Y-m-d'); 
                $lastDayOfYear = date('Y-m-d', strtotime('last day of December this year'));
                ?>
            <input type="date" id="date" name="date" class="w-full sm:w-60 px-4 py-3 text-[#1A4568] font-bold text-lg rounded-[50px]
                bg-[#DBECF4] border-none outline-none" min="<?php echo $currentDate; ?>" max="<?php echo $lastDayOfYear; ?>" 
                value="<?php echo isset($_SESSION['date']) ? $_SESSION['date'] : ''; ?>" required>
        </div>

<!-- Search Button -->
        <div class="flex justify-center m-4 w-full sm:w-auto">
            <button type="submit" 
                class="bg-[#1A4568] text-white px-6 py-3 rounded-full font-bold uppercase transition-all duration-300 
                hover:bg-white hover:text-[#1A4568] hover:scale-105 hover:shadow-lg transform hover:-translate-y-1 
                active:scale-95 active:shadow-sm">
                Search
            </button>
        </div>
    </form>
</div>

<!-- Search Results- search header results -->
<div class=" bg-center bg-cover p-5 m-2 rounded-lg shadow-lg" style="background-image: url('../pics/background3.jpg');">
    <div class=" anim flex justify-between items-center border-b border-[#ddd] p-3 m-4">
    <?php
        date_default_timezone_set('Asia/Riyadh');
        $currentDate = date("d M, l");
        echo '<h2 class=" anim1 text-xl font-bold text-[#1A4568]"><strong> Available Providers </strong></h2>';
        echo '<h2 class=" anim1 text-xl font-bold text-[#1A4568]">' 
            . ucfirst( "Available Services") . "</h2>";
        echo '<h2 class=" anim1 text-xl font-bold text-[#1A4568]">' . '<strong> Today: </strong>'
            . htmlspecialchars($currentDate).'</h2>';
    ?>
    </div>
<!-- Search result processing - doc profile card -->
<?php 
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Prepare and execute query to fetch doctors based on user input
    $stmt = $conn->prepare("
        SELECT  
            d.name AS doctor_name,
            d.doctor_id, 
            d.specialty, 
            d.location,
            h.name AS hospital_name, 
            i.provider_name, 
            AVG(c.rating_value) AS avg_rating
        FROM 
            doctors d
        INNER JOIN 
            hospital_doctors hd ON d.doctor_id = hd.doctor_id
        INNER JOIN 
            hospital_insurances hi ON hd.hospital_id = hi.hospital_id
        INNER JOIN 
            insurances i ON hi.insurance_id = i.insurance_id
        INNER JOIN 
            hospitals h ON h.hospital_id = hi.hospital_id
        LEFT JOIN 
            comments c ON c.doctor_id = d.doctor_id
        INNER JOIN 
            available_times at ON at.doctor_id = d.doctor_id
        INNER JOIN 
        services s ON s.doctor_id = d.doctor_id
        WHERE 
            d.specialty = ? 
            AND d.location = ?
            AND i.provider_name = ? 
            AND at.available_date = ?
            AND d.admin_approval = 'yes'
            AND d.active = 'yes'
            AND d.active_hospital= 'yes'
            AND i.active = 'yes'
            AND at.booked = 'no'
            AND h.active='yes'
        GROUP BY 
            d.doctor_id, d.name, d.specialty, d.location, i.provider_name, h.name
        ORDER BY 
            avg_rating DESC
    ");
    // check if everything is ok with the statement
    if (!$stmt) {
        $error[] = "Oops! Something went wrong while processing your request. Please try again later.";
    }

    $stmt->bind_param("ssss", $specialty, $location, $insurance, $date);

    if (!$stmt->execute()) {
        $error[] = "Oops! We couldn't complete your request. Please try again.";
    }

    $res = $stmt->get_result();
    if ($res === false) {
        $error[] = "An error occurred while fetching the results. Please try again later.";
    }

    // Fetch results
    $results_array = $res->fetch_all(MYSQLI_ASSOC);
    // show results or show error message if no results are found
    if ($res->num_rows > 0 && empty($error)) {
        // Loop through to get each doctor's cards
        foreach ($results_array as $row) {
            echo '<div class="bg-[#DBECF4] bg-opacity-40 rounded-lg flex items-start justify-between mb-5 p-8 shadow-md">'; 
            // Get the average rating and format it to 2 decimal places : default value is 0.00          
            $rating_avg = isset($row['avg_rating']) ? number_format($row['avg_rating'], 2) : '0.00';
            // Show each doctor info
?>
            <div class="flex items-start justify-start">
                <div class="m-4 anim1"> 
                    <img src="../pics/docProfile.png" alt="Doctor profile picture" class="w-24 h-24 rounded-full m-1 shadow-md"/>
                </div>
                <div class=" anim2 doctor-info text-[#1A4568] flex flex-col justify-start items-start">
<?php
                    echo '<h2 class="search-h font-bold text-lg mb-2">'."Dr. " 
                        . ucwords(htmlspecialchars($row['doctor_name'])) . "</h2>";
                    echo '<h3 class="font-semibold text-sm mb-2">' . ucfirst(htmlspecialchars($row['specialty'])) . "</h3>";
                    echo '<h3 class="font-semibold text-sm mb-2">' . ucfirst(htmlspecialchars($row['hospital_name'])) . "</h3>";
                    echo '<h3 class="font-semibold text-sm mb-2"><i class="fa fa-check"></i> ' 
                        . htmlspecialchars($row['provider_name']) . "</h3>";
                    echo '<p class="flex items-center"><i class="far fa-star mr-2 mb-2"></i>' .$rating_avg. "</p>";
?>
                </div>
            </div>
<!-- showing available time and services of each doctor -->
<?php
                $doctor_id=$row['doctor_id'];
                //check avaliable time 
                //limit 5 : will cause error if they are less than 5
                $check_times=$conn->prepare('select available_time from available_times 
                where doctor_id=? and available_date=? and booked="no" limit 5');
                $check_times->bind_param('is',$doctor_id,$date);
                $check_times->execute();
                $available_times=$check_times->get_result();
                //get available services 
                $check_services=$conn->prepare('SELECT DISTINCT service_name FROM services WHERE doctor_id = ? limit 3');
                $check_services->bind_param('i',$doctor_id);
                $check_services->execute();
                $services_res=$check_services->get_result();

                if(($available_times->num_rows>0)||($services_res->num_rows>0)){
                    $available_times_arr=$available_times->fetch_all(MYSQLI_ASSOC);
                    $services_arr=$services_res->fetch_all(MYSQLI_ASSOC);
                    $merged_array=$services_arr+$available_times_arr;
                    echo '<div class="p-4 anim">';
                    echo '<div class="text-center">'; 
                    echo '<p class="text-lg font-bold text-[#1A4568] mb-5 anim2 ">Some Available Services and Times:</p>';
                    echo '</div>';
                    echo '<div class=" anim1 flex flex-wrap gap-4 justify-center">'; 
                    // Loop through the merged array
                    foreach ($merged_array as $row) {
                        echo '<div class="text-[#1A4568] w-40 p-4 bg-white border border-gray-300 rounded-lg shadow-md text-center">';
                        // Display service name if available
                        if (!empty($row['service_name'])) {
                            echo '<p class="font-semibold">' . htmlspecialchars($row['service_name']) . '</p>';
                        }
                        // Display available time if available
                        if (!empty($row['available_time'])) {
                            // Convert time to 12-hour format
                            $time_12hr = date('h:i A', strtotime($row['available_time']));
                            echo '<p class="font-semibold mt-2">' . htmlspecialchars($time_12hr) . '</p>';
                        }
                        echo '</div>'; 
                    }                    
                    echo '</div>'; 
                    echo '</div>'; 
                }
?>
<!-- Book button -->
            <div class=" anim2 flex justify-end items-end mt-6 h-[150px]">
                <form action="docCardResult.php" method="post">
<!-- Send data (doctor_id and date)to docCardResult.php to handle the appointment booking -->
                    <input type="hidden" name="doctor_id" value="<?php echo $doctor_id; ?>">
                    <input type="hidden" name="date" value="<?php echo $date; ?>">
                    <button type="submit" 
                        class="bg-[#1A4568] text-white px-6 py-3 rounded-full font-bold uppercase transition-all duration-300 
                        hover:bg-white hover:text-[#1A4568] hover:scale-105 hover:shadow-lg transform hover:-translate-y-1 
                        active:scale-95 active:shadow-sm">
                        Book Now
                    </button>
            </form>
        </div>
<?php
            echo '</div>'; // Close the doctor card container
        }// end for
    }//end if 
    else {
        $error[] = "We couldn't find any results at the moment. Please consider selecting a different date or adjusting your search criteria.";
    }
} // end if ($_SERVER["REQUEST_METHOD"] === "POST")
    $conn->close();
    $stmt->close();
?>
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
</div>
<!-- footer -->
<?php require("footer.php"); ?>
</body>
</html>