<?php 
//---------------------------------------------------------------navigation bar---------------------------------------------------//
// Start the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<header class="header">
    <nav class="bg-[#DBECF4] p-4 sm:p-6 lg:p-8 rounded-lg m-2 shadow-md anim">
        <div class="flex items-center justify-between">
<!-- vitalcare logo -->
        <div class="logo ">
            <a href="index.php">
                <img src="../pics/VitalCareLogo.png" alt="VitalCare Logo" class="w-24 h-12">
            </a>
        </div>
<?php 
// Check if the user is an admin and display the admin navigation bar
    if (isset($_SESSION["is_admin"])&&$_SESSION["is_admin"]){ 
        ?>
        <ul class="flex items-center space-x-6 anim1">
            <li class="relative ">
                <a href="adminAppointments.php" class="text-black text-sm sm:text-base font-bold uppercase transition-all duration-300 
                hover:bg-[#1A4568] hover:text-white hover:ml-2 hover:scale-110 hover:shadow-lg px-2.5 py-2 rounded-lg">Appointments</a>
            </li>
            <li class="relative ">
                <a href="adminDoctors.php" class="text-black text-sm sm:text-base font-bold uppercase transition-all duration-300 
                hover:bg-[#1A4568] hover:text-white hover:ml-2 hover:scale-110 hover:shadow-lg px-2.5 py-2 rounded-lg">Doctors</a>
            </li>
            <li class="relative ">
                <a href="adminPatients.php" class="text-black text-sm sm:text-base font-bold uppercase transition-all duration-300 
                hover:bg-[#1A4568] hover:text-white hover:ml-2 hover:scale-110 hover:shadow-lg px-2.5 py-2 rounded-lg">Patients</a>
            </li>
            <li class="relative ">
                <a href="adminComments.php" class="text-black text-sm sm:text-base font-bold uppercase transition-all duration-300 
                hover:bg-[#1A4568] hover:text-white hover:ml-2 hover:scale-110 hover:shadow-lg px-2.5 py-2 rounded-lg">Comments</a>
            </li>
            <li class="relative ">
                <a href="adminInsurances.php" class="text-black text-sm sm:text-base font-bold uppercase transition-all duration-300 
                hover:bg-[#1A4568] hover:text-white hover:ml-2 hover:scale-110 hover:shadow-lg px-2.5 py-2 rounded-lg">insurances</a>
            </li>
            <li class="relative ">
                <a href="adminHospitals.php" class="text-black text-sm sm:text-base font-bold uppercase transition-all duration-300 
                hover:bg-[#1A4568] hover:text-white hover:ml-2 hover:scale-110 hover:shadow-lg px-2.5 py-2 rounded-lg">Hospitals</a>
            </li>
            <li class="relative ">
                <a href="admins.php" class="text-black text-sm sm:text-base font-bold uppercase transition-all duration-300 
                hover:bg-[#1A4568] hover:text-white hover:ml-2 hover:scale-110 hover:shadow-lg px-2.5 py-2 rounded-lg">admins</a>
            </li>
            <li class="relative ">
                <a href="profilePage.php" class="text-black text-sm sm:text-base font-bold uppercase transition-all duration-300 
                hover:bg-[#1A4568] hover:text-white hover:ml-2 hover:scale-110 hover:shadow-lg px-2.5 py-2 rounded-lg">profile</a>
            </li>
            <li>
            <?php         
            // Check if the user is signed in
            if (isset($_SESSION["is_logged_in"]) && $_SESSION["is_logged_in"]) {
                ?>
                <button class=" bg-[#1A4568] text-white px-2.5 py-3 w-30 rounded-full rounded-tr-none 
                    font-bold uppercase shadow-md transition-all duration-300 hover:bg-white 
                    hover:text-[#1A4568] hover:scale-105 hover:shadow-lg transform hover:-translate-y-1 active:scale-95 active:shadow-sm" 
                    onclick="location.href='signOut.php';">
                SIGN OUT
                </button>
            <?php 
            }//end if
            ?>
            </li>
        </ul>
<?php 
// -----------------------------------------------------patient and doctor navigation bar---------------------------------------------------//
// if the user is a patitent or a doctor show them them the below nav bar
    } else{
?>
    <ul class="flex items-center space-x-6">
        <li class="relative ">
        <a href="index.php" class="text-black text-sm sm:text-base font-bold uppercase transition-all duration-300 
        hover:bg-[#1A4568] hover:text-white hover:ml-2 hover:scale-110 hover:shadow-lg px-2.5 py-2 rounded-lg">Home</a>
        </li>
        <li class="relative ">
            <a href="searchPage.php" class="text-black text-sm sm:text-base font-bold uppercase transition-all duration-300 
            hover:bg-[#1A4568] hover:text-white hover:ml-2 hover:scale-110 hover:shadow-lg px-2.5 py-2 rounded-lg">Search</a>
        </li>
        <li class="relative ">
            <a href="profilePage.php" class="text-black text-sm sm:text-base font-bold uppercase transition-all duration-300 
            hover:bg-[#1A4568] hover:text-white hover:ml-2 hover:scale-110 hover:shadow-lg px-2.5 py-2 rounded-lg">Profile</a>
        </li>                
        <li class="relative ">
        <?php 
            // Check if the user is signed in
            if (isset($_SESSION["is_logged_in"]) && $_SESSION["is_logged_in"]) {
                include("cookies.php");
                if(isset($_COOKIE['doc_name'])&&$_COOKIE['doc_name']){?>
                    <a href="docDashboard.php" class="  text-black text-sm sm:text-base font-bold uppercase transition-all duration-300 
                    hover:bg-[#1A4568] hover:text-white hover:ml-2 hover:scale-110 hover:shadow-lg px-2.5 py-2 rounded-lg">Dashboard</a>
                    <?php 
                }
                else{
                ?>
                    <a href="patDashboard.php" class=" text-black text-sm sm:text-base font-bold uppercase transition-all duration-300
                    hover:bg-[#1A4568] hover:text-white hover:ml-2 hover:scale-110 hover:shadow-lg px-2.5 py-2 rounded-lg">Appointments</a>
                <?php 
                }
            }?>
        </li>
        <li>
            <?php            
            // Check if the user is signed in
            if (isset($_SESSION["is_logged_in"]) && $_SESSION["is_logged_in"]) {
                ?>
                <button class=" bg-[#1A4568] text-white px-2.5 py-3 w-30 rounded-full rounded-tr-none 
                    font-bold uppercase shadow-md transition-all duration-300 hover:bg-white 
                    hover:text-[#1A4568] hover:scale-105 hover:shadow-lg transform hover:-translate-y-1 active:scale-95 active:shadow-sm" 
                    onclick="location.href='signOut.php';">
                SIGN OUT
                </button>
            <?php 
            } else {?>
                <button class=" bg-[#1A4568] text-white px-2.5 py-3 w-24 rounded-full rounded-tr-none 
                    font-bold uppercase shadow-md transition-all duration-300 hover:bg-white 
                    hover:text-[#1A4568] hover:scale-105 hover:shadow-lg transform hover:-translate-y-1 active:scale-95 active:shadow-sm" 
                    onclick="location.href='signIn.php';">
                SIGN in
                </button>
                <?php
            }?>
        </li>
    </ul>
<?php
    } //end else
?>
        </div>
    </nav>
</header>