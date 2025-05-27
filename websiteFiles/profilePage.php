<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Redirect to login page if user is not logged in
if (!isset($_SESSION["is_logged_in"]) || !$_SESSION["is_logged_in"]) {
    header("Location: signIn.php");
    exit;
}
require("../phpFiles/connect.php");
require("cookies.php");

//----------------------------------------------------------doctor profile page-------------------------------------------------------------
if (isset($_COOKIE["doc_name"]) && !empty($_COOKIE['doc_name'])){
    $name=$_COOKIE["doc_name"];
    $doc_phone_number=$_COOKIE["doc_phone_number"];
    $specialty=$_COOKIE["doc_specialty"];
    $experience_years=$_COOKIE["doc_experience_years"];
    $doc_location=$_COOKIE["doc_location"];
    $hospital_name=$_COOKIE["hospital_name"];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
<!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Faculty+Glyphic&display=swap" rel="stylesheet">
<!-- CSS Files -->   
    <link rel="stylesheet" href="../cssFiles/styles.css">
<!-- Tailwind CSS -->
    <link href="../cssFiles/output.css" rel="stylesheet">
</head>
<body>
<!--nav bar-->
<?php require("nav.php"); ?>

<!-- User Profile Section -->
<div class="w-full bg-center bg-cover rounded-lg m-2.5 p-2.5 shadow-md" style="background-image: url('../pics/background1.jpg');">
<!-- main Section -->
    <div class=" text-center m-4 p-2  w-3/4 mx-auto anim1">
        <h1 class="text-4xl font-bold text-white m-2 p-2 custom-text-shadow anim">Welcome, <?php echo $name?> !</h1>
    </div>
<!-- Profile Card -->
    <div class=" anim1 flex flex-row items-center gap-6 px-4 sm:px-8">
<!-- Profile Picture -->
        <div class="w-24 h-24 sm:w-32 sm:h-32 rounded-full border-4 border-[#1A4568] overflow-hidden">
            <img src="../pics/docProfile.png" alt="Doctor profileicon" class="w-full h-full object-cover">
        </div>
<!-- doctor main info -->
        <div class="text-left text-lg">
            <?php 
            echo '<h2 class="text-lg sm:text-xl font-bold text-[#1A4568] mb-4">'."<strong>"
                . ucwords(htmlspecialchars($name)) ."</strong>". '</h2>';
            echo '<div class="text-md sm:text-lg text-[#1A4568] border-b-2 border-[#1A4568] w-max mb-2">'
                ."<strong>Doctor id: </strong>" . htmlspecialchars($_SESSION["id"]) . '</div>';
            echo '<div class="text-md sm:text-lg text-[#1A4568] border-b-2 border-[#1A4568] w-max mb-2">'
                ."<strong>Specialization: </strong>" . ucwords(htmlspecialchars($specialty)) . '</div>';
            ?>
        </div>
    </div>
<!-- Doctor Details Section -->
    <div class="bg-[aliceblue] w-[90%] p-4 rounded-lg my-10 mx-auto">
        <h2 class=" anim1 text-lg font-bold mb-2 text-[#1A4568]">Profile Details:</h2>
        <div class="anim2 bg-white p-4 rounded-lg shadow-md m-4">
            <?php 
            echo '<p class="text-sm text-[#1A4568] mb-5">'."<strong>Email: </strong>" . htmlspecialchars($email).'</p>';
            echo '<p class="text-sm text-[#1A4568] mb-5">'."<strong>Phone Number: </strong>".htmlspecialchars( $doc_phone_number). '</p>';
            echo '<p class="text-sm text-[#1A4568] mb-5">'."<strong>Hospital: </strong>" . htmlspecialchars($hospital_name).'</p>';
            echo '<p class="text-sm text-[#1A4568] mb-5">'."<strong>Location: </strong>" 
                . ucfirst(htmlspecialchars($doc_location)).'</p>';
            echo '<p class="text-sm text-[#1A4568] mb-5">'."<strong>Years of Experience: </strong>" 
                . htmlspecialchars($experience_years) . '</p>';
            ?>
<!-- services and Available times buttons -->
            <button class="m-4 rounded bg-[#1A4568] text-white px-4 py-2 hover:bg-[#183141] 
                hover:text-[white] transition-all" onclick="location.href='addServices.php';">
                    Add Services
            </button>
            <button class="m-4 rounded bg-[#1A4568] text-white px-4 py-2 hover:bg-[#183141]     
                hover:text-[white] transition-all" onclick="location.href='setAvailableTimes.php';">
                Set Available Times
            </button>
        </div>
    </div>
<!-- Patient Comments Section -->
    <div class="anim1 bg-[aliceblue] w-[90%] p-4 rounded-lg my-10 mx-auto shadow-lg">
        <h2 class="text-lg font-bold mb-4 text-[#1A4568]">Patient Reviews & Feedback:</h2>
        <div class="anim2 space-y-4">
            <div class="p-4">
                <?php
                    // Prepare and execute the query
                    $stmt = $conn->prepare("SELECT c.comment, c.rating_value, c.rating_date, p.name AS patient_name 
                    FROM comments c JOIN patients p ON c.patient_id = p.patient_id 
                    WHERE c.doctor_id = ? order by c.rating_date DESC LIMIT 2");
                    $stmt->bind_param("i", $_SESSION["id"]);
                    $stmt->execute();
                    $res = $stmt->get_result();
                    if ($res->num_rows > 0) {
                        while ($comment_array = $res->fetch_assoc()) {
                            echo '<div class="bg-white p-4 rounded-lg shadow-md m-4 anim">';
                            echo '<p class="text-sm text-[#1A4568] mb-2"><strong>Name: </strong>' . 
                                ucwords(htmlspecialchars($comment_array["patient_name"])) . '</p>';
                            echo '<p class="text-sm text-[#1A4568] mb-2"><strong>Comment: </strong>' . 
                                htmlspecialchars($comment_array["comment"]) . '</p>';
                            echo '<p class="text-sm text-[#1A4568] mb-2"><strong>Rating: </strong>' . 
                                htmlspecialchars($comment_array["rating_value"]) . '</p>';
                            echo '<p class="text-sm text-[#1A4568] mb-2" style="border-bottom: 1px solid #1A4568; padding-bottom: 5px;">
                                <strong>Date: </strong>' . htmlspecialchars($comment_array["rating_date"]) . '</p>';
                            echo '</div>';
                        }
                    } else {
                        echo '<p class="text-sm text-[#1A4568] anim">Patients haven\'t left any reviews yet.</p>';
                    }
                    $stmt->close();
                    ?>
            </div>
            <button class=" anim2 mt-4 rounded bg-[#1A4568] text-white px-4 py-2 hover:bg-[#183141] 
                hover:text-[white] transition-all" onclick="location.href='allComments.php';">
                View All Comments
            </button>
        </div>
    </div>
    <div class="anim1 flex items-center justify-end mr-18">
        <button class="m-5 rounded bg-[#1A4568] text-white px-4 py-2 hover:bg-[#183141] 
            hover:text-[white] transition-all" onclick="location.href='ProfileSetting.php';">
            Edit Profile Information
        </button>
    </div>
</div>
<!-- Footer -->
<?php require("footer.php"); ?>
</body>
</html>
<?php
} // end of the cookie if : doctor profile page
//----------------------------------------------------------patient profile page-------------------------------------------------------------
if (isset($_COOKIE["user_name"]) && !empty($_COOKIE["user_name"])) {
    $name=$_COOKIE["user_name"];
    $phone_num =$_COOKIE['user_phone_number'];
    $age=$_COOKIE["user_age"];
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Profile</title>
<!-- Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<!-- Google Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=Faculty+Glyphic&display=swap" rel="stylesheet">
<!-- CSS Files -->
        <link rel="stylesheet" href="../cssFiles/styles.css">
<!-- Tailwind CSS -->
        <link href="../cssFiles/output.css" rel="stylesheet">
    </head>
<body>
<!-- Navigation Bar -->
<?php require("nav.php"); ?>

<!-- Patient Profile Section -->
<div class="w-full bg-center bg-cover rounded-lg m-2.5 p-2.5 shadow-md anim" style="background-image: url('../pics/background1.jpg');">
<!-- main Section-->
    <div class=" text-center m-4 p-2  w-3/4 mx-auto anim1">
        <h1 class="text-4xl font-bold text-white m-2 p-2 custom-text-shadow anim">Welcome, <?php echo $name?> !</h1>
    </div>
<!-- Profile Card -->
    <div class="flex flex-row items-center gap-6 px-4 sm:px-8 anim1">
<!-- Profile Picture -->
        <div class="w-24 h-24 sm:w-32 sm:h-32 rounded-full border-4 border-[#1A4568] overflow-hidden">
            <img src="../pics/profileicon.png" alt="Profile Picture" class="w-full h-full object-cover">
        </div>
<!-- Profile Details -->
        <div class="text-left text-lg">
            <?php 
            echo '<h2 class="text-lg sm:text-xl font-bold text-[#1A4568] mb-3">'."<strong>"
                . ucwords(htmlspecialchars($name)) ."</strong>". '</h2>';
            echo '<div class="text-md sm:text-lg text-[#1A4568] border-b-2 border-[#1A4568] w-max mb-2">'."<strong>Patient id: </strong>" 
                . htmlspecialchars($_SESSION["id"]) . '</div>';
            ?>
        </div>
    </div>

<!-- Patient Details Section -->
    <div class="bg-[aliceblue] w-[90%] p-4 rounded-lg my-10 mx-auto anim1">
        <h2 class="text-lg font-bold mb-2 text-[#1A4568] anim1">Profile Details</h2>
        <div class="bg-white p-4 rounded-lg shadow-md m-4 anim2">
            <?php 
            echo '<p class="text-sm text-[#1A4568] mb-5">'."<strong>Email: </strong>" . htmlspecialchars($email) . '</p>';
            echo '<p class="text-sm text-[#1A4568] mb-5">'."<strong>Phone Number: </strong>" 
                . htmlspecialchars($phone_num) . '</p>';
            echo '<p class="text-sm text-[#1A4568] mb-5">'."<strong>"."Age: "."</strong>".htmlspecialchars($age) . '</p>';
            ?>
        </div>
    </div>

<!-- Patient Comments Section -->
    <div class="bg-[aliceblue] w-[90%] p-4 rounded-lg my-10 mx-auto shadow-lg anim1">
        <h2 class="text-lg font-bold mb-4 text-[#1A4568] anim1">My Comments</h2>
        <div class="space-y-4 anim2">
<?php
        // Prepare and execute the query
        $stmt = $conn->prepare("SELECT c.comment, c.rating_value, c.rating_date, d.name AS doctor_name 
        FROM comments c JOIN doctors d ON c.doctor_id = d.doctor_id 
        WHERE c.patient_id = ? order by c.rating_date DESC LIMIT 2;");
        $stmt->bind_param("i", $_SESSION["id"]);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows > 0) {
            while ($comment_array = $res->fetch_assoc()) {
                echo '<div class="bg-white p-4 rounded-lg shadow-md m-4 anim">';
                echo '<p class="text-sm text-[#1A4568] mb-2"><strong>Doctor Name: </strong>' . 
                    htmlspecialchars($comment_array["doctor_name"]) . '</p>';
                echo '<p class="text-sm text-[#1A4568] mb-2"><strong>Comment: </strong>' . 
                    htmlspecialchars($comment_array["comment"]) . '</p>';
                echo '<p class="text-sm text-[#1A4568] mb-2"><strong>Rating: </strong>' . 
                    htmlspecialchars($comment_array["rating_value"]) . '</p>';
                echo '<p class="text-sm text-[#1A4568] mb-2" style="border-bottom: 1px solid #1A4568; padding-bottom: 5px;">
                    <strong>Date: </strong>' . htmlspecialchars($comment_array["rating_date"]) . '</p>';
                echo '</div>';
            }
        } else {
            echo '<p class="text-sm text-[#1A4568] m-2">No comments have been posted yet.</p>';
        }
        $stmt->close();
?>
        </div>
        <button class="m-4 rounded bg-[#1A4568] text-white px-4 py-2 hover:bg-[#183141] 
            hover:text-[white] transition-all anim2" onclick="location.href='addComments.php';">
            Add New Comment
        </button>
        <button class="m-4 rounded bg-[#1A4568] text-white px-4 py-2 hover:bg-[#183141] 
            hover:text-[white] transition-all anim2" onclick="location.href='allComments.php';">
            View All Comments
        </button>
    </div>

    <!-- Profile Settings Button -->
    <div class="flex items-center justify-end mr-18 anim1">
        <button class="m-5 rounded bg-[#1A4568] text-white px-4 py-2 hover:bg-[#183141] 
            hover:text-[white] transition-all" onclick="location.href='ProfileSetting.php';">
            Edit Profile Information
        </button>
    </div>
</div>
<!-- Footer -->
<?php require("footer.php"); ?>
</body>
</html>
<?php  
} // End of patient prfile page block
//----------------------------------------------------------admin profile page-------------------------------------------------------------
// if the the user is a admin show the admin page
if (isset($_SESSION["is_admin"]) && !empty($_SESSION['is_admin'])){
    $name=$_COOKIE["admin_name"];
    $phone=$_COOKIE['admin_number'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
<!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Faculty+Glyphic&display=swap" rel="stylesheet">
<!-- CSS Files -->
    <link rel="stylesheet" href="../cssFiles/styles.css">
<!-- Tailwind CSS -->
    <link href="../cssFiles/output.css" rel="stylesheet">
</head>
<body>
<!-- Navigation Bar -->
<?php require("nav.php"); 
?>
<!-- Admin Profile Section -->
<div class="w-full bg-center bg-cover rounded-lg m-2.5 p-2.5 shadow-md anim" style="background-image: url('../pics/background1.jpg');">
<!-- main Section-->
    <div class=" text-center m-4 p-2  w-3/4 mx-auto anim1">
        <h1 class="text-4xl font-bold text-white m-2 p-2 custom-text-shadow anim">Welcome, <?php echo $name?> !</h1>
    </div>
<!-- Profile Card -->
    <div class="flex flex-row items-center gap-6 px-4 sm:px-8 anim1">
<!-- Profile Picture -->
        <div class="w-24 h-24 sm:w-32 sm:h-32 rounded-full border-4 border-[#1A4568] overflow-hidden">
            <img src="../pics/profileicon.png" alt="Profile Picture" class="w-full h-full object-cover">
        </div>
<!-- Profile Details -->
        <div class="text-left text-lg">
            <?php 
            echo '<h2 class="text-lg sm:text-xl font-bold text-[#1A4568] mb-3">'."<strong>"
                . ucwords(htmlspecialchars($name)) ."</strong>". '</h2>';
            echo '<div class="text-md sm:text-lg text-[#1A4568] border-b-2 border-[#1A4568] w-max mb-2">'
                ."<strong>Admin id: </strong>" . htmlspecialchars($_SESSION["id"]) . '</div>';
            ?>
        </div>
    </div>
    
    
<!-- Admin Details Section -->
    <div class="bg-[aliceblue] w-[90%] p-4 rounded-lg my-10 mx-auto anim1">
        <h2 class="text-lg font-bold mb-2 text-[#1A4568] anim1">Admin's Details:</h2>
        <div class="bg-white p-4 rounded-lg shadow-md mb-4 anim2">
        <?php 
        echo '<p class="text-sm text-[#1A4568] mb-5">'."<strong>Email: </strong>" . htmlspecialchars($email) . '</p>';
        echo '<p class="text-sm text-[#1A4568] mb-5">'."<strong>Phone Number: </strong>" . htmlspecialchars($phone) . '</p>';
        $conn->close();
        ?>
        </div>
    </div>

    <!-- Profile Settings Button -->
    <div class="anim1 flex items-center justify-end mr-18">
        <button class="m-5 rounded bg-[#1A4568] text-white px-4 py-2 hover:bg-[#183141] 
            hover:text-[white] transition-all" onclick="location.href='ProfileSetting.php';">
            Edit Profile Information
        </button>
    </div>
</div>
<?php require("footer.php"); ?>
</body>
</html>
<?php  
} // End admin profile page block
?>

