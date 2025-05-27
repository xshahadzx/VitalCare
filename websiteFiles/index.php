<?php                
// Start the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
} 
require_once("../phpFiles/connect.php");
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
<!-- CSS Files -->
    <link href="../cssFiles/output.css" rel="stylesheet">
    <link href="../cssFiles/styles.css" rel="stylesheet">
<!-- JavaScript -->
    <script src="../javascriptFiles/javascript.js" defer></script>
    </head>
<!-------------------------------------------------------------------------Home page : all----------------------------------------------------------------------- -->
<!-- if the user is a doctor and tries to book using her/his account , error message is shown -->
    <?php 
        if (isset($_COOKIE['doc_name']) && !empty($_COOKIE['doc_name'])) {
            if (isset($_SERVER['HTTP_REFERER'])) {
                $previousPage = $_SERVER['HTTP_REFERER'];
                if (strpos($previousPage, 'searchPageDoc.php') == true) {
                    ?>
                    <div class="p-0 rounded relative mb-4 anim">
                        <div class="mt-2 list-disc list-inside">
                        <h1 class="error-msg font-bold text-center">You must sign in as a patient to book an appointment.</h1>
                        </div>
                    </div>
                    <?php
                }
            }
        }
        //nav bar 
        require("nav.php"); 
        // if the user is  logged in and is a patient , show him the pop up to comment on thier appointment
        if (isset($_SESSION["is_logged_in"]) && $_SESSION["is_logged_in"]&&!$_SESSION['is_admin']) {
            require("cookies.php");
            if (isset($_COOKIE["user_name"]) && !empty($_COOKIE["user_name"])){
                //check if patients has confirmed appointments to show him the pop up
                $check_app=$conn->prepare("SELECT appointment_id FROM appointments 
                WHERE patient_id=? AND STATUS='confirmed'");
                $check_app->bind_param("i", $_SESSION["id"]);
                $check_app->execute();
                $result=$check_app->get_result();
                if ($result &&$result->num_rows >0) {
    ?>
                    <div id="popup" class="fixed inset-0 flex items-center justify-center bg-[#7B9DB4] bg-opacity-60 z-50 rounded-lg m-2 
                        custom-text-shadow hidden anim ">
                        <div class="anim max-w-sm w-full text-center p-4 sm:p-6 lg:p-8 m-2">
                        <h2 class="anim1 text-white font-bold uppercase text-[24px]">Your Feedback Matters!</h2>
                        <p class="anim2 text-white text-[16px] my-4">
                        Help us improve by sharing your thoughts on your last appointment. Your feedback makes a difference!
                        </p>
                        <button class="bg-[#1A4568] text-white px-2.5 py-3 w-30 rounded-full rounded-tr-none 
                            font-bold uppercase shadow-md transition-all duration-300 hover:bg-white hover:text-[#1A4568] 
                            hover:scale-105 hover:shadow-lg transform hover:-translate-y-1 active:scale-95 active:shadow-sm anim2" 
                            onclick="location.href='addComments.php';">
                            Share Feedback
                            </button>
                        </div>
                    </div>
    <?php 
                } 
            }
            //if the user is a doctor show him the pop up to complete her/his profile 
            elseif (isset($_COOKIE["doc_name"]) && !empty($_COOKIE['doc_name'])){
                        ?>
                        <div id="popup" class="fixed inset-0 flex items-center justify-center bg-[#7B9DB4] bg-opacity-60 z-50 rounded-lg m-2 
                            custom-text-shadow hidden anim ">
                            <div class="anim max-w-sm w-full text-center p-4 sm:p-6 lg:p-8 m-2">
                            <h2 class="anim1 text-white font-bold uppercase text-[24px]">Complete Your Profile</h2>
                            <p class="anim2 text-white text-[16px] my-4">
                                To start receiving bookings, add your accepted insurance providers, set your availability, and list your services.
                            </p>
                            <button class="bg-[#1A4568] text-white px-2.5 py-3 w-30 rounded-full rounded-tr-none 
                                font-bold uppercase shadow-md transition-all duration-300 hover:bg-white hover:text-[#1A4568] 
                                hover:scale-105 hover:shadow-lg transform hover:-translate-y-1 active:scale-95 active:shadow-sm anim1" 
                                onclick="location.href='profilePage.php';">
                                Update profile
                            </button>
                            </div>
                        </div>
        <?php 
                } 
            } 
        ?>
            <script>
                        // Function to show the popup
                        function showPopup() {
                        var popup = document.getElementById('popup');
                        popup.classList.remove('hidden');

                        // Add click event listener to close popup if clicked outside, after popup is shown
                        popup.addEventListener('click', closePopup);

                        // Hide the popup
                        setTimeout(function() {
                            popup.classList.add('hidden');
                        }, 20000);  
                        }

                        // Function to hide the popup
                        function closePopup(event) {
                            if (event.target === document.getElementById('popup')) {
                                document.getElementById('popup').classList.add('hidden');
                            }
                        }
                        // Show the popup 
                        setTimeout(showPopup, 3000);
            </script>
<!-- Main section in Home page -->
    <section class=" anim relative bg-[#DBECF4] rounded-lg m-2 p-[30px] max-w-full h-[500px] flex justify-between items-center text-left gap-[15px] bg-cover bg-center shadow-lg" style="background-image: url('../pics/background1.jpg');">
        <div class=" anim1 flex flex-col justify-center items-start lg:items-center w-full lg:w-1/2 space-y-6 text-center">
            <h1 class="text-white text-[55px] lg:text-[70px] leading-tight font-[Faculty_Glyphic] custom-text-shadow">
            Find Top Doctors for a Healthier You
            </h1>
            <p class="text-[#1A4568] font-bold text-[18px] lg:text-[25px] leading-relaxed custom-text-shadow">
            Search, connect, and book your appointment today!
            </p>
            <a href="searchPage.php" class="bg-[#1A4568] text-white px-6 py-3 rounded-full rounded-tr-none 
            font-bold uppercase shadow-md transition-all duration-300 hover:bg-white hover:text-[#1A4568] hover:scale-105 hover:shadow-lg transform hover:-translate-y-1 active:scale-95 active:shadow-sm" >
            Book Now
            </a>
        </div>
        <div class="hidden lg:flex docImg">
            <img src="../pics/mainpagepic.png" alt="Doctors illustration" class="m-[60px] flex-1 animpic">
        </div>
        </section>

<!-- Information section in Home page -->
        <section class="bg-[#DBECF4] rounded-lg m-2 p-4">
            <div class="flex flex-col gap-6 p-4 max-w-full rounded-lg">
<!-- Comments Section -->
                <div class="bg-[#7B9DB4] text-[#1A4568] rounded-lg p-6 text-center hover:bg-[#6A8CA3] transition-all duration-300 shadow-md anim">
                    <p class="text-xl font-semibold anim1">Find the Right Doctor with Genuine Reviews from Real Patients</p>
                    <div class="bg-[#DBECF4] flex items-center text-left mt-4 border border-[#1A4568] rounded-lg italic shadow-sm anim2">
                    <img src="../pics/commenticon.png" alt="commenticon" class="m-2 mr-0 p-2 w-9 h-12">
                    <?php
                    $comments = $conn->query("SELECT comment FROM comments where hide='no' limit 10");
                    if ($comments->num_rows > 0) {
                        echo '<div class="comment-slider slider">'; 
                        while ($comment = $comments->fetch_assoc()) {
                            echo '<div class="slide">'.'"' . htmlspecialchars($comment['comment']).'"' . '</div>'; 
                        }
                        echo '</div>';
                    } else {
                        echo '<p>No reviews available yet. Be the first to share your experience!</p>';
                    }
                    ?>
                    </div>
                </div>
<!-- Doctors Profiles Section -->
                <div class=" anim bg-[#7B9DB4] text-[#1A4568] rounded-lg p-6 text-center hover:bg-[#6A8CA3] transition-all duration-300 shadow-md">
                    <p class="text-xl font-semibold anim1">Meet Our Top Doctors and Book Your Appointment Today</p>
                    <div class="anim2 bg-[#DBECF4] flex items-center text-left mt-4 border border-[#1A4568] rounded-lg italic shadow-sm">
                    <img src="../pics/profileicon.png" alt="profileicon" class="m-2 p-2 w-12 h-12">
                    <?php
                    $profiles = $conn->query("SELECT name FROM doctors where admin_approval='yes' and active='yes' limit 10");
                    if ($profiles->num_rows > 0) {
                        echo '<div class="profile-slider slider">'; 
                        while ($name = $profiles->fetch_assoc()) {
                            echo '<div class="slide">' ."Dr.".ucwords(htmlspecialchars($name['name']))  . '</div>'; // Using .slide for each profile
                        }
                        echo '</div>';
                    } else {
                        echo '<p>No doctor profiles available yet. Check back soon!</p>';
                    }
                    ?>
                    </div>
                </div>
<!-- Insurances Section -->
                <div class="anim bg-[#7B9DB4] text-[#1A4568] rounded-lg p-6 text-center hover:bg-[#6A8CA3] transition-all duration-300 shadow-md">
                    <p class=" anim1 text-xl font-semibold">Find Doctors Covered by Your Insurance Network</p>
                    <div class="anim2 bg-[#DBECF4] flex items-center text-left mt-4 border border-[#1A4568] rounded-lg italic shadow-sm">
                    <img src="../pics/insuranceicon.png" alt="insuranceicon" class="m-2 p-2 w-12 h-12">
                    <?php
                    $insurances = $conn->query("SELECT provider_name FROM insurances where active='yes' limit 10");
                    if ($insurances->num_rows > 0) {
                        echo '<div class="insurance-slider slider">'; 
                        while ($insurance = $insurances->fetch_assoc()) {
                            echo '<div class="slide">' . ucwords(htmlspecialchars($insurance['provider_name'])) . '</div>'; 
                        }
                        echo '</div>';
                    } else {
                        echo '<p>No insurance providers available at the moment. Check back soon!</p>';
                    }
                    $conn->close();
                    ?> 
                    </div>
                </div>
            </div>
        </section>
<!-- footer  -->
    <?php require("footer.php"); ?>
    </body>
</html>