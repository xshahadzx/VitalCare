<?php   
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION["is_logged_in"]) && $_SESSION["is_logged_in"]) {
    header("Location: index.php");
    exit; 
}
require("../phpFiles/connect.php");
// handle the input data from doctor 
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Trim and sanitize input
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $confirm_password = trim($_POST["confirm_password"]);
    $phone = trim($_POST["phone_number"]);
    $hospital = trim($_POST["hospital"]);
    $location = trim($_POST["location"]);
    $speciality = trim($_POST["speciality"]);
    $experience = trim($_POST["experience"]);

    $success = ""; // Initialize success message variable
    $errors = [];  // Initialize error array
    // Input Validation
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password) || empty($speciality)) {
        $errors[] = "All fields are required.";
    }
    if (!preg_match("/^[a-zA-Z-' ]+$/", $name)) {
        $errors[]="Invalid name. Only letters, spaces, hyphens, and apostrophes are allowed.";
    }
    if (!preg_match("/^[a-zA-Z-' ]+$/", $location)) {
        $errors[] = "Invalid location name. Only letters, spaces, hyphens, and apostrophes are allowed.";
    }   
    if (!preg_match("/^[a-zA-Z-' ]+$/", $hospital)) {
        $errors[] = "Invalid hospital name. Only letters, spaces, hyphens, and apostrophes are allowed.";
    }
    if (!preg_match("/^[a-zA-Z-' ]+$/", $speciality)) {
        $errors[] = "Invalid speciality name. Only letters, spaces, hyphens, and apostrophes are allowed.";
    }    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters.";
    }
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }
    if (!preg_match('/^[0-9]{10}$/', $phone)) {
        $errors[] = "Please enter a valid 10-digit phone number.";
    }
    if (!is_numeric($experience) || $experience < 0 || $experience > 50) {
        $errors[] = "Experience years must be a valid number at least 0 and at most 50.";
    }
    // Check if email already exists
    $stmt_email=$conn->prepare("SELECT DISTINCT email
                                    FROM (
                                        SELECT d.email AS email
                                        FROM doctors d
                                        UNION
                                        SELECT p.email AS email
                                        FROM patients p
                                        UNION
                                        SELECT a.email AS email
                                        FROM admins a
                                    ) AS combined_emails
                            WHERE email =?");
    $stmt_email->bind_param("s", $email);
    $stmt_email->execute();
    $result = $stmt_email->get_result();
    if ($result->num_rows > 0) {
        $errors[] = "This email is already registered.";
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
        $errors[] = "This phone number is already registered.";
    }
    // Only proceed if there are no validation errors
    if (empty($errors)) {
        // Hash the password using Argon2ID
        $hashed_password = password_hash($password, PASSWORD_ARGON2ID);
        $sql = "INSERT INTO doctors (name, email, password, phone_number, specialty, experience_years,location)
        VALUES (?, ?, ?, ?, ?, ?,?)";
        $hos_sql=$conn->prepare('INSERT INTO hospitals (name) VALUES (?) ON DUPLICATE KEY UPDATE name = name');
        $stmt = $conn->prepare($sql);
        $hos_sql->bind_param('s',$hospital);
        $stmt->bind_param("sssssis", $name, $email, $hashed_password, $phone, $speciality, $experience,$location);
        if ($stmt->execute()&&$hos_sql->execute()) {
            $success = "Registration successful! Await admin approval. You'll receive an email once approved.";
            //get the hospital id to insert into the doctor_hosptail database table
            $stmt_hos=$conn->prepare('SELECT hospital_id FROM hospitals WHERE name =?');
            $stmt_hos->bind_param('s',$hospital);
            $stmt_hos->execute();
            $res= $stmt_hos->get_result();
            $hospital_id=$res->fetch_column();
            //get doctor_id 
            $stmt_hos=$conn->prepare('SELECT doctor_id FROM doctors WHERE email =?');
            $stmt_hos->bind_param('s',$email);
            $stmt_hos->execute();
            $res= $stmt_hos->get_result();
            $doctor_id=$res->fetch_column();
            //link the doctor to the hospital 
            $doc_hos=$conn->prepare('INSERT INTO hospital_doctors (doctor_id,hospital_id) VALUES (?,?)');
            $doc_hos->bind_param('ii',$doctor_id,$hospital_id);
            $doc_hos->execute();
            // Admin email address : send the email to the admin
            $mail = require __DIR__ . "/mailer.php";
            $admin_email = "admin@vitalcare.com";
            $mail->setFrom("noreply@vitalCare.com");
            $mail->addAddress($admin_email);
            $mail->Subject = "New Doctor Registration - Approval Needed: Dr. $name";
            $mail->isHTML(true); 
            $mail->Body = <<<END
                <p>Dear Admin,</p>
                <p>A new doctor has registered on <strong>VitalCare</strong> and is awaiting your approval:</p>
                <p>
                    <strong>Name:</strong> Dr. $name<br>
                    <strong>Email:</strong> <a href="mailto:$email">$email</a><br>
                    <strong>Phone:</strong> $phone
                </p>
                <p>Please log in to the admin dashboard to review and approve this registration.</p>
                <p>Thank you,<br>
                <strong>The VitalCare System</strong></p>
            END;
            try {
                $mail->send();
            } catch (Exception $e) {
                $error[] = "Couldn't send the registration approval email to the admin!";
            }
        } else {
            $errors[] = "Oops — something went wrong. Please try again.";
        }
    } // Close the statement
    $stmt->close();
    // Close database connection
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Faculty+Glyphic&display=swap" rel="stylesheet">
<!-- CSS Files -->
    <link rel="stylesheet" href="../cssFiles/styles.css">
    <link href="../cssFiles/output.css" rel="stylesheet">
</head>
<!-- -----------------------------------------------------------------------Doctor Sign Up page : DOCTORS----------------------------------------------------------------------- -->
<body>
<!--nav bar-->
<?php require("nav.php"); ?>
<!-- Doctor Sign-Up Page -->
<div class="flex items-center justify-center">
    <div id="createDoctorAccountPage" class="m-5 p-10 rounded-2xl bg-cover shadow-md flex w-full sm:w-auto" 
        style="background-image: url('../pics/background8.jpeg')">
<!-- Welcome Section -->
        <div class=" anim flex flex-col justify-center items-center bg-[#1a4568] text-white p-10 rounded-l-xl w-full sm:w-80 text-center">
            <h3 class="mb-10 text-3xl font-bold anim1">Welcome, Doctor!</h3>
            <p class="mb-5 anim1">Already have an account?</p>
            <button class=" anim2 px-4 py-3 w-52 bg-white text-[#1a4568] font-bold rounded-full hover:bg-[#90afc4] hover:text-white 
                transition duration-300" onclick="location='signIn.php';">Sign In</button>
        </div>  
        <!-- Doctor Sign-Up Section -->
        <div class=" anim flex flex-col justify-center items-center bg-opacity-10 backdrop-blur-md border border-white/30 shadow-md p-10 
                rounded-r-xl w-full sm:w-96">
        <h3 class="mb-5 text-2xl font-bold anim1 text-[#1a4568]">Doctor Registration</h3>
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
                    window.location.href = "signIn.php"; 
                }, 5000); 
                </script>   
            </div>
        <?php endif; ?>
<!-- registeration form -->
        <form action="signAsDoc.php" class=" anim flex flex-col w-full max-w-sm" method="post">
            <!-- Name -->
            <label for="name" class="my-4 font-bold text-[#1a4568]">Full Name:</label>
            <input type="text" placeholder="Dr. John Doe" id="name" name="name" required pattern="^[a-zA-Z-' ]+$"
                class="font-bold p-2 text-lg outline-none border-b-2 border-[#1a4568] bg-transparent">

            <!-- Email -->
            <label for="email" class="my-4 font-bold text-[#1a4568]">Email:</label>
            <input type="email" placeholder="doctor@example.com" id="email" name="email" required
                class="font-bold p-2 text-lg outline-none border-b-2 border-[#1a4568] bg-transparent">

            <!-- Password -->
            <label for="password" class="my-4 font-bold text-[#1a4568]">Password:</label>
            <input type="password" placeholder="SecurePass_2025" id="password" name="password" required
                class="font-bold p-2 text-lg outline-none border-b-2 border-[#1a4568] bg-transparent">

            <!-- Confirm Password -->
            <label for="ConfirmPassword" class="my-4 font-bold text-[#1a4568]">Confirm Password:</label>
            <input type="password" placeholder="SecurePass_2025" id="ConfirmPassword" name="confirm_password" required
                class="font-bold p-2 text-lg outline-none border-b-2 border-[#1a4568] bg-transparent">

            <!-- Phone Number -->
            <label for="PhoneNum" class="my-4 font-bold text-[#1a4568]">Phone Number:</label>
            <input type="text" placeholder="05X XXX XXXX" id="PhoneNum" name="phone_number" required
                class="font-bold p-2 text-lg outline-none border-b-2 border-[#1a4568] bg-transparent">

            <!-- hospital -->
            <label for="hospital" class="my-4 font-bold text-[#1a4568]">Hospital:</label>
            <input type="text" placeholder="Medical Center, Saudi Hospital, etc." id="hospital" name="hospital" required
            class="font-bold p-2 text-lg outline-none border-b-2 border-[#1a4568] bg-transparent">

            <!-- location -->
            <label for="location" class="my-4 font-bold text-[#1a4568]">Location:</label>
            <input type="text" placeholder="Makkah, Jeddah, etc." id="location" name="location" required
            class="font-bold p-2 text-lg outline-none border-b-2 border-[#1a4568] bg-transparent">
        
            <!-- Speciality -->
            <label for="speciality" class="my-4 font-bold text-[#1a4568]">Speciality:</label>
            <input type="text" placeholder="Cardiologist, Dentist, etc." id="speciality" name="speciality" required
                class="font-bold p-2 text-lg outline-none border-b-2 border-[#1a4568] bg-transparent">

            <!-- Years of Experience -->
            <label for="experience" class="my-4 font-bold text-[#1a4568]">Years of Experience:</label>
            <input type="number" placeholder="e.g, 10" id="experience" name="experience" required
                class="font-bold p-2 text-lg outline-none border-b-2 border-[#1a4568] bg-transparent">
            <button class="mt-5 px-4 py-3 bg-[#1a4568] text-white rounded-full uppercase shadow-md hover:bg-white 
                hover:text-[#1a4568] hover:scale-105 transition-transform duration-300">Sign Up</button>
        </form>
        </div>
    </div>
</div>
<!-- footer -->
<?php require("footer.php"); ?>
</body>
</html>