<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Redirect to login page if user is not logged in
if (!isset($_SESSION["is_logged_in"]) || !$_SESSION["is_logged_in"]) {
    header("Location: signIn.php");
    exit;
}
require("cookies.php");
require("../phpFiles/connect.php");
//----------------------------------------------------------patient profile setting-------------------------------------------------------------
if (isset($_COOKIE["user_name"]) && !empty($_COOKIE["user_name"])){
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $name = trim($_POST['name']);
        $phone = trim($_POST['phone']);
        $age = trim($_POST['age']);
        $password = trim($_POST['password']);
        $id = $_SESSION["id"];
        // Set needed variables and array
        $error = [];
        $success = "";
        $correct_password = false;                                                           
        // Input validation
        if (!empty($name)) {
            if (!preg_match("/^[a-zA-Z-' ]+$/", $name)) { 
                $error[] = "Invalid name. Only letters, spaces, hyphens, and apostrophes are allowed.";
            }
        }
        if (empty($phone) && empty($name) && empty($age)) {
            $error[] = 'Please fill in at least one field.';
        }    
        if (!empty($phone)&&!preg_match('/^[0-9]{10}$/', $phone)) {
            $error[] = "Please enter a valid 10-digit phone number.";
        }
        if(!empty($age)){
            if (!is_numeric($age) || $age <= 0 || $age > 120 || $age<18) {
                $error[] = "Age must be 18 or older.";
            }
        }
        // Check if phone number already exists
        $sql = "SELECT DISTINCT phone
                FROM (
                    SELECT d.phone_number AS phone
                    FROM doctors d
                    UNION
                    SELECT p.phone_number AS phone
                    FROM patients p
                    UNION
                    SELECT a.phone_number AS phone
                    FROM admins a
                ) AS combined_phones
                WHERE phone =?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $phone);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $error[] = "Looks like this phone number is taken. Try another one?";
        }
        // Check if the entered password is correct
        $stmt = $conn->prepare("SELECT password FROM patients WHERE patient_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows === 1) {
            $user = $res->fetch_assoc();
            // Verify password
            if (password_verify($password, $user['password'])) {
                $correct_password = true;
            } else {
                $error[] = "To confirm the changes, please enter your correct password.";
            }
        }
        // Check if there are any errors and if the password is correct
        if (empty($error) && $correct_password) {
            // Check if at least one field is provided
            if (!empty($name) || !empty($phone) || !empty($age)) {
                // Initialize query parts
                $setClauses = [];
                $params = [];
                $types = "";

                // check all fields 
                if (!empty($name)) {
                    $setClauses[] = "name = ?";
                    $params[] = $name;
                    $types .= "s"; 
                }
                if (!empty($phone)) {
                    $setClauses[] = "phone_number = ?";
                    $params[] = $phone;
                    $types .= "s";
                }
                if (!empty($age)) {
                    $setClauses[] = "age = ?";
                    $params[] = $age;
                    $types .= "i";
                }

                // Construct the query dynamically
                $query = "UPDATE patients SET " . implode(", ", $setClauses) . " WHERE patient_id = ?";
                $params[] = $id; 
                $types .= "i"; 

                // Prepare the statement
                $stmt = $conn->prepare($query);

                // Bind the parameters
                $stmt->bind_param($types, ...$params);

                // Execute the statement
                if ($stmt->execute()) {
                    $success= "Information updated successfully! You may now proceed to your dashboard.";
                    include("cookies.php");
                } else {
                    $error[] = "Oops! Something went wrong. Please try again.";
                }
            } 
        }
        $stmt->close();
        $conn->close();
}
?> 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Settings</title>
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
<!-- Main Section - Setting Profile -->
<div class="max-w-[600px] mx-auto p-10 bg-center bg-contain rounded-lg shadow-md anim" style="background-image: url('../pics/background8.jpeg'); 
            height:auto; width: 500px;">
<!-- Back Button -->
    <div class="mb-6">
        <a href="profilePage.php" class="text-[#1A4568] font-bold uppercase hover:underline ml-2">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
<!-- Page Title -->
    <h1 class="text-2xl font-bold text-center text-[#1A4568] mb-10 anim1">Profile Settings</h1>
<!-- Profile Form -->
    <form action="profileSetting.php" class="flex flex-col space-y-6 anim1" method="post">
<!-- Personal Information Section -->
        <div>
            <h2 class="text-lg font-bold text-center text-[#1A4568] mb-2">Personal Information</h2>
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
<!-- Display Success Message -->
    <?php if (!empty($success)): ?>
        <div class="px-4 py-3 rounded relative mb-4">
            <p class="succ-msg font-bold anim "><?php echo htmlspecialchars($success); ?></p>
            <script>
                setTimeout(() => {
                    window.location.href = "profilePage.php";  
                }, 4000); 
            </script> 
        </div>
    <?php endif; 
    // get the users data from the database to show it to the user
    $name=$_COOKIE['user_name'];
    $phone=$_COOKIE['user_phone_number'];
    $age=$_COOKIE['user_age'];
?>
            <div class="space-y-4 anim2">
<!-- name -->
                <div class="m-4">
                    <label for="name" class="block text-sm font-bold text-[#1A4568] mb-2">Name</label>
                    <input pattern="^[a-zA-Z-' ]+$" type="text" id="name" name="name" placeholder="<?php echo ucwords( $name); ?>"
                        class="w-full p-3 text-sm border border-[#1A4568] rounded bg-[aliceblue] outline-none focus:ring-2 
                        focus:ring-[#1A4568]"/>
                </div>
<!-- Phone -->
                <div class="m-4">
                    <label for="phone" class="block text-sm font-bold text-[#1A4568] mb-2">Phone</label>
                    <input type="tel" id="phone" name="phone" placeholder="<?php echo $phone; ?>" 
                        class="w-full p-3 text-sm border border-[#1A4568] rounded bg-[aliceblue] outline-none focus:ring-2 
                        focus:ring-[#1A4568]" />
                </div>
<!-- age -->
                <div class="m-4">
                    <label for="age" class="block text-sm font-bold text-[#1A4568] mb-2">Age</label>
                <input type="tel" id="age" name="age" placeholder="<?php echo $age; ?>" 
                    class="w-full p-3 text-sm border border-[#1A4568] rounded bg-[aliceblue] outline-none focus:ring-2 
                    focus:ring-[#1A4568]" />
                </div>
                <div class="m-4">
                    <label for="current-password" class="block text-sm font-bold text-[#1A4568] mb-2">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter password to verify its you" 
                        class="w-full p-3 text-sm border border-[#1A4568] rounded bg-[aliceblue] outline-none focus:ring-2 
                        focus:ring-[#1A4568]" required/>
                </div>
            </div>
        </div>
<!-- Save Changes Button -->
        <div class="flex justify-center">
        <button type="submit" class="px-6 py-2 text-sm font-bold text-white bg-[#1A4568] rounded hover:bg-[#183141] transition-all">
            Save Changes
        </button>
        </div>
    </form>
<!-- Change Passowrd or Email Button -->
    <div class="mt-6 anim2">
        <a href="profileSettingPass.php" class="text-[#1A4568] font-bold uppercase hover:underline flex items-center">
            <i class="fas fa-lock mr-2"></i> Change Passowrd or Email</a>
    </div>
</div>
<!-- footer -->
<?php require("footer.php"); ?>
</body>
</html>
<?php 
}
//----------------------------------------------------------doctor profile setting-------------------------------------------------------------
if (isset($_COOKIE["doc_name"]) && !empty($_COOKIE['doc_name'])){
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $name = trim($_POST['name']);
        $phone = trim($_POST['phone']);
        $specialty = trim($_POST['specialty']);
        $exp_years= trim($_POST['exp_years']);
        $location= trim($_POST['location']);
        $password = trim($_POST['password']);
        $id = $_SESSION["id"];
        // Set needed variables and array
        $error = [];
        $success = "";
        $correct_password = false;  
        // Input validation
        if (!empty($name)) { 
            if (!preg_match("/^[a-zA-Z-' ]+$/", $name)) { 
                $error[] = "Invalid name. Only letters, spaces, hyphens, and apostrophes are allowed.";
            }
        }
        if (!empty($location)) {
            if (!preg_match("/^[a-zA-Z-' ]+$/", $location)) {
                $error[] = "Invalid location. Only letters, spaces, hyphens, and apostrophes are allowed.";
                }  
        } 
        if (!empty($specialty)) {
            if (!preg_match("/^[a-zA-Z-' ]+$/", $specialty)) {
                $error[] = "Invalid specialty. Only letters, spaces, hyphens, and apostrophes are allowed.";
            }  
        } 
        if (empty($phone) && empty($name) && empty($age)&& empty($specialty)&& empty($exp_years)&& empty($location)) {
            $error[] = 'At least one field has to be filled.';
        }
        if (!empty($phone)&&!preg_match('/^[0-9]{10}$/', $phone)) {
            $error[] = "Please enter a valid 10-digit phone number.";
        }
        if (!empty($exp_years)) {
            if (!is_numeric($exp_years) || $exp_years < 0 || $exp_years> 50) {
                $error[] = "Experience years must be a valid number between 0 and 50.";
            }
        }
        // Check if phone number already exists
        $sql = "SELECT DISTINCT phone
        FROM (
            SELECT d.phone_number AS phone
            FROM doctors d
            UNION
            SELECT p.phone_number AS phone
            FROM patients p
            UNION
            SELECT a.phone_number AS phone
            FROM admins a
        ) AS combined_phones
        WHERE phone =?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $phone);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $error[] = "Looks like this phone number is taken. Try another one?";
        }
        // Check if the entered password is correct
        $stmt = $conn->prepare("SELECT password FROM doctors WHERE doctor_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows === 1) {
            $user = $res->fetch_assoc();
            // Verify password
            if (password_verify($password, $user['password'])) {
                $correct_password = true;
            } else {
                $error[] = "To confirm the changes, please enter your correct password.";
            }
        }
        // Check if there are any errors and if the password is correct
        if (empty($error) && $correct_password) {
            // Check if at least one field is provided
                if (!empty($name) || !empty($phone) || !empty($specialty)|| !empty($exp_years)|| !empty($location)) {
                    // Initialize query parts
                    $setClauses = [];
                    $params = [];
                    $types = "";
                    // check all fields 
                    if (!empty($name)) {
                        $setClauses[] = "name = ?";
                        $params[] = $name;
                        $types .= "s"; 
                    }
                    if (!empty($phone)) {
                        $setClauses[] = "phone_number = ?";
                        $params[] = $phone;
                        $types .= "s"; 
                    }
                    if (!empty($specialty)) {
                        $setClauses[] = "specialty = ?";
                        $params[] = $specialty;
                        $types .= "s";
                    }
                    if (!empty($exp_years)) {
                        $setClauses[] = "experience_years = ?";
                        $params[] = $exp_years;
                        $types .= "i";
                    }
                    if (!empty($location)) {
                        $setClauses[] = "location = ?";
                        $params[] = $location;
                        $types .= "s";
                    }
                    // Construct the query dynamically
                    $query = "UPDATE doctors SET " . implode(", ", $setClauses) . " WHERE doctor_id = ?";
                    $params[] = $id; 
                    $types .= "i"; 
                    // Prepare the statement
                    $stmt = $conn->prepare($query);
                    // Bind the parameters
                    $stmt->bind_param($types, ...$params);
                    // Execute the statement
                    if ($stmt->execute()) {
                        $success= "Information updated successfully! You may now proceed to your dashboard.";
                        include("cookies.php");
                    } else {
                        $error[]= "Oops! Something went wrong. Please try again.";
                    }
                    // Close the statement
                    $stmt->close();
            } 
        }
        $conn->close();
}
?> 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Settings</title>
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
<!-- Main Section - Setting Profile -->
<div class="max-w-[600px] mx-auto p-10 bg-center bg-contain rounded-lg shadow-md anim" style="background-image: url('../pics/background8.jpeg'); height:auto; width: 500px;">
<!-- Back Button -->
    <div class="mb-6">
        <a href="profilePage.php" class="text-[#1A4568] font-bold uppercase hover:underline ml-2">
            <i class="fas fa-arrow-left"></i> Back</a>
    </div>
<!-- Page Title -->
    <h1 class="text-2xl font-bold text-center text-[#1A4568] mb-10 anim1">Profile Settings</h1>
<!-- Profile Form -->
    <form action="profileSetting.php" class="flex flex-col space-y-6 anim1" method="post">
<!-- Personal Information Section -->
        <div>
        <h2 class="text-lg font-bold text-center text-[#1A4568] mb-2">Personal Information</h2>
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
<!-- Display Success Message -->
    <?php if (!empty($success)): ?>
        <div class="px-4 py-3 rounded relative mb-4">
            <p class="succ-msg font-bold anim "><?php echo htmlspecialchars($success); ?></p>
            <script>
                setTimeout(() => {
                    window.location.href = "profilePage.php";  
                }, 4000); 
            </script>
        </div>
    <?php endif; ?>
            <div class="space-y-4 anim2">
<?php 
                $name=$_COOKIE['doc_name'];
                $phone=$_COOKIE['doc_phone_number'];
                $specialty=$_COOKIE['doc_specialty'];
                $exp_years=$_COOKIE['doc_experience_years'];
                $doc_location=$_COOKIE['doc_location'];
?>
<!-- name -->
                <div class="m-4">
                    <label for="name" class="block text-sm font-bold text-[#1A4568] mb-2">Name</label>
                    <input pattern="^[a-zA-Z-' ]+$" type="text" id="name" name="name" placeholder="<?php echo ucwords($name); ?>"
                        class="w-full p-3 text-sm border border-[#1A4568] rounded bg-[aliceblue] outline-none focus:ring-2 
                        focus:ring-[#1A4568]"/>
                </div>
<!-- Phone -->
                <div class="m-4">
                    <label for="phone" class="block text-sm font-bold text-[#1A4568] mb-2">Phone</label>
                    <input type="tel" id="phone" name="phone" placeholder="<?php echo $phone; ?>"
                        class="w-full p-3 text-sm border border-[#1A4568] rounded bg-[aliceblue] outline-none focus:ring-2 
                        focus:ring-[#1A4568]" />
                </div>
<!-- specilty and years of experinece -->
                <div class="m-4">
                    <label for="specialty" class="block text-sm font-bold text-[#1A4568] mb-2">Specialty</label>
                    <input type="tel" id="specialty" name="specialty" placeholder="<?php echo ucfirst($specialty); ?>" 
                        class="w-full p-3 text-sm border border-[#1A4568] rounded bg-[aliceblue] outline-none focus:ring-2 
                        focus:ring-[#1A4568]" />
                </div>
                <div class="m-4">
                    <label for="exp_years" class="block text-sm font-bold text-[#1A4568] mb-2">Experience Years</label>
                    <input type="tel" id="exp_years" name="exp_years" placeholder="<?php echo $exp_years; ?>" 
                        class="w-full p-3 text-sm border border-[#1A4568] rounded bg-[aliceblue] outline-none focus:ring-2 
                        focus:ring-[#1A4568]" />
                </div>
                <!-- doctor location-->
                <div class="m-4">
                    <label for="location" class="block text-sm font-bold text-[#1A4568] mb-2">Location</label>
                    <input type="tel" id="location" name="location" placeholder="<?php echo ucfirst($doc_location); ?>"  
                        class="w-full p-3 text-sm border border-[#1A4568] rounded bg-[aliceblue] outline-none focus:ring-2 
                        focus:ring-[#1A4568]" />
                </div>
                <!-- verify user-->
                <div class="m-4">
                    <label for="current-password" class="block text-sm font-bold text-[#1A4568] mb-2">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter password to verify its you" 
                        class="w-full p-3 text-sm border border-[#1A4568] rounded bg-[aliceblue] outline-none focus:ring-2 
                        focus:ring-[#1A4568]" required/>
                </div>
            </div>
        </div>
        <!-- Save Changes Button -->
        <div class="flex justify-center">
        <button type="submit" class="px-6 py-2 text-sm font-bold text-white bg-[#1A4568] rounded hover:bg-[#183141] transition-all">
            Save Changes
        </button>
        </div>
    </form>
    <!-- Change Passowrd or Email Button -->
    <div class="mt-6 anim2">
        <a href="profileSettingPass.php" class="text-[#1A4568] font-bold uppercase hover:underline flex items-center">
            <i class="fas fa-lock mr-2"></i> Change Passowrd or Email</a>
    </div>
</div>
<!-- footer -->
<?php require("footer.php"); ?>
</body>
</html>
<?php 
} // end if 
//----------------------------------------------------------admin profile setting-------------------------------------------------------------
if (isset($_SESSION["is_admin"]) && !empty($_SESSION["is_admin"])){
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $name = trim($_POST['name']);
        $phone = trim($_POST['phone']);
        $password = trim($_POST['password']);
        $id = $_SESSION["id"];
        // Set needed variables and array
        $error = [];
        $success = "";
        $correct_password = false;  
        // Input validation
        if (!empty($name)) {
            if (!preg_match("/^[a-zA-Z-' ]+$/", $name)) { 
                $error[] = "Invalid name. Only letters, spaces, hyphens, and apostrophes are allowed.";
            }
        }
        if (empty($phone) && empty($name) && empty($age)) {
            $error[] = 'At least one field has to be filled.';
        }
        if (!empty($phone)&&!preg_match('/^[0-9]{10}$/', $phone)) {
            $error[] = "Please enter a valid 10-digit phone number.";
        }
        // Check if phone number already exists
        $sql = "SELECT DISTINCT phone
        FROM (
            SELECT d.phone_number AS phone
            FROM doctors d
            UNION
            SELECT p.phone_number AS phone
            FROM patients p
            UNION
            SELECT a.phone_number AS phone
            FROM admins a
        ) AS combined_phones
        WHERE phone =?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $phone);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $error[] = "Looks like this phone number is taken. Try another one?";
        }
        // Check if the entered password is correct
        $stmt = $conn->prepare("SELECT password FROM admins WHERE admin_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows === 1) {
            $user = $res->fetch_assoc();
            // Verify password
            if (password_verify($password, $user['password'])) {
                $correct_password = true;
            } else {
                $error[] = "To confirm the changes, please enter your correct password.";
            }
        }
        // Check if there are any errors and if the password is correct
        if (empty($error) && $correct_password) {
            // Check if at least one field is provided
            if (!empty($name) || !empty($phone)) {
                // Initialize query parts
                $setClauses = [];
                $params = [];
                $types = "";
                // check all fields 
                if (!empty($name)) {
                    $setClauses[] = "name = ?";
                    $params[] = $name;
                    $types .= "s"; 
                }
                if (!empty($phone)) {
                    $setClauses[] = "phone_number = ?";
                    $params[] = $phone;
                    $types .= "s";
                }
                // Construct the query dynamically
                $query = "UPDATE admins SET " . implode(", ", $setClauses) . " WHERE admin_id = ?";
                $params[] = $id; 
                $types .= "i"; 
                // Prepare the statement
                $stmt = $conn->prepare($query);
                // Bind the parameters
                $stmt->bind_param($types, ...$params);
                // Execute the statement
                if ($stmt->execute()) {
                    $success= "Information updated successfully! You may now proceed to your dashboard.";
                    include("cookies.php");
                } else {
                    $error[]= "Oops! Something went wrong. Please try again.";
                }
            } 
        }
        $stmt->close();
        $conn->close();
}
?> 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Settings</title>
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
<!-- Main Section - Setting Profile -->
<div class="max-w-[600px] mx-auto p-10 bg-center bg-cover rounded-lg shadow-md anim" style="background-image: url('../pics/background8.jpeg'); height:auto; width: 500px; 
    background-repeat: no-repeat;">
<!-- Back Button -->
    <div class="mb-6">
        <a href="profilePage.php" class="text-[#1A4568] font-bold uppercase hover:underline ml-2">
            <i class="fas fa-arrow-left"></i> Back</a>
    </div>
<!-- Page Title -->
    <h1 class="text-2xl font-bold text-center text-[#1A4568] mb-10 anim1">Profile Settings</h1>
<!-- Profile Form -->
    <form action="profileSetting.php" class="flex flex-col space-y-6 anim1" method="post">
<!-- Personal Information Section -->
        <div>
        <h2 class="text-lg font-bold text-center text-[#1A4568] mb-2">Personal Information</h2>
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
<!-- Display Success Message -->
    <?php if (!empty($success)): ?>
        <div class="px-4 py-3 rounded relative mb-4">
            <p class="succ-msg font-bold anim "><?php echo htmlspecialchars($success); ?></p>
            <script>
                setTimeout(() => {
                    window.location.href = "profilePage.php";  
                }, 4000); 
            </script>
        </div>
    <?php endif;
    // get the users data from the cookies to show it to the user
    $name=$_COOKIE['admin_name'];
    $phone=$_COOKIE['admin_number'];
?>
            <div class="space-y-4 anim2">
<!-- name -->
                <div class="m-4">
                    <label for="name" class="block text-sm font-bold text-[#1A4568] mb-2">Name</label>
                    <input pattern="^[a-zA-Z-' ]+$" type="text" id="name" name="name" placeholder="<?php echo $name; ?>"
                        class="w-full p-3 text-sm border border-[#1A4568] rounded bg-[aliceblue] outline-none focus:ring-2 
                        focus:ring-[#1A4568]"/>
                </div>
<!-- Phone -->
                <div class="m-4">
                    <label for="phone" class="block text-sm font-bold text-[#1A4568] mb-2">Phone</label>
                    <input type="tel" id="phone" name="phone" placeholder="<?php echo $phone; ?>" 
                        class="w-full p-3 text-sm border border-[#1A4568] rounded bg-[aliceblue] outline-none focus:ring-2 
                        focus:ring-[#1A4568]" />
                </div>
                <div class="m-4">
                    <label for="current-password" class="block text-sm font-bold text-[#1A4568] mb-2">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter password to verify its you" 
                        class="w-full p-3 text-sm border border-[#1A4568] rounded bg-[aliceblue] outline-none focus:ring-2 
                        focus:ring-[#1A4568]" required/>
                </div>
            </div>
        </div>
    <!-- Save Changes Button -->
        <div class="flex justify-center">
        <button type="submit" class="px-6 py-2 text-sm font-bold text-white bg-[#1A4568] rounded hover:bg-[#183141] transition-all">
        Save Changes
        </button>
        </div>
    </form>
    <!-- Change Passowrd or Email Button -->
    <div class="mt-6 anim2">
        <a href="profileSettingPass.php" class="text-[#1A4568] font-bold uppercase hover:underline flex items-center">
            <i class="fas fa-lock mr-2"></i> Change Passowrd or Email</a>
    </div>
</div>
<!-- footer -->
<?php require("footer.php"); ?>
</body>
</html>
<?php 
}
?>