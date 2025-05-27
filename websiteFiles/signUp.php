<?php   
// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Check if the user is already logged in
if (isset($_SESSION["is_logged_in"]) && $_SESSION["is_logged_in"]) {
    header("Location: index.php");
    exit; 
}
require("../phpFiles/connect.php");
// handle the patient sign up data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Trim and sanitize input
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $confirm_password = trim($_POST["confirm_password"]);
    $phone = trim($_POST["phone_number"]);
    $age = trim($_POST["age"]);

    $success = ""; // Initialize success message variable
    $errors = [];  // Initialize error array

    // Input Validation
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $errors[] = "All fields are required.";
    }
    if (!preg_match("/^[a-zA-Z-' ]+$/", $name)) {
        $errors[]="Invalid name. Only letters, spaces, hyphens, and apostrophes are allowed.";
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
    if (!is_numeric($age) || $age <= 0 || $age > 100 || $age<18) {
        $errors[] = "Age must be 18 or older.";
    }
    if (!preg_match('/^[0-9]{10}$/', $phone)) {
        $errors[] = "Please enter a valid 10-digit phone number.";
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
    $stmt_email->close();
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
    $stmt->close();
    // If no validation errors, proceed
    if (empty($errors)) {
        // Hash the password using Argon2ID
        $hashed_password = password_hash($password, PASSWORD_ARGON2ID);
        $sql = "INSERT INTO patients (name, email, password, phone_number, age,active) VALUES (?, ?, ?, ?, ?,'yes')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $name, $email, $hashed_password, $phone, $age);
        if ($stmt->execute()) {
            $success = "Thank you for joining us! We're excited to have you.";
        } else {
            $errors[] = "Oops — something went wrong. Please try again.";
        }
        $stmt->close();
    }
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
<!-- -----------------------------------------------------------------------Sign Up as a patient page : patients----------------------------------------------------------------------- -->
<body>
<?php require("nav.php"); ?>
<!-- main signing page -->
<div class="flex items-center justify-center">
<!-- Sign-Up-->
    <div id="createAccountPage" class="flex m-5 p-10 rounded-2xl bg-cover shadow-md" style="background-image: url('../pics/background8.jpeg')">
<!-- Welcome Section -->
        <div class="flex flex-col justify-center items-center bg-[#1a4568] text-white p-10 rounded-l-xl w-full sm:w-96 anim">
            <h3 class="mb-10 text-3xl font-bold anim1">Welcome!</h3>
            <p class="mb-5 anim1">Already have an account?</p>
            <button class="px-4 py-3 w-52 bg-white text-[#1a4568] font-bold rounded-full hover:bg-[#90afc4] hover:text-white 
            transition duration-300 anim2" onclick="location.href='signIn.php';">Sign In</button>
            <p class="mt-3 anim2">OR</p>
            <a href="signAsDoc.php" class="mt-2 text-white hover:underline hover:text-[#90afc4] transition duration-300 anim2">
                    Sign Up As a Doctor</a>
        </div>
<!-- Sign-Up Section -->
        <div class="flex flex-col justify-center items-center bg-opacity-10 backdrop-blur-md border border-white/30 shadow-md p-10 
                rounded-r-xl w-full sm:w-96">
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
                    }, 3000); 
                    </script>   
                </div>
            <?php endif; ?>
<!-- sign up form -->
            <h3 class="mb-5 text-2xl anim1 text-[#1a4568]">Sign Up</h3>
            <form action="signUp.php" class="flex flex-col w-full anim2" method="post">
                <label for="name" class="my-4 font-bold text-[#1a4568]">Full Name:</label>
                <input type="text" placeholder="John Doe" id="name" name="name" pattern="^[a-zA-Z-' ]+$" required
                    class="font-bold p-2 text-lg outline-none border-b-2 border-[#1a4568] bg-transparent">
            
                <label for="email" class="my-4 font-bold text-[#1a4568]">Email:</label>
                <input type="text" placeholder="Patient@example.com" id="email" name="email" required
                    class="font-bold p-2 text-lg outline-none border-b-2 border-[#1a4568] bg-transparent">
            
                <label for="password" class="my-4 font-bold text-[#1a4568]">Password:</label>
                <input type="password" placeholder="SecurePass_2025" id="password" name="password" required
                    class="font-bold p-2 text-lg outline-none border-b-2 border-[#1a4568] bg-transparent">
            
                <label for="ConfirmPassword" class="my-4 font-bold text-[#1a4568]">Confirm Password:</label>
                <input type="password" placeholder="SecurePass_2025" id="ConfirmPassword" name="confirm_password" required
                    class="font-bold p-2 text-lg outline-none border-b-2 border-[#1a4568] bg-transparent">
            
                <label for="PhoneNum" class="my-4 font-bold text-[#1a4568]">Phone Number:</label>
                <input type="text" placeholder="05X XXX XXXX" id="PhoneNum" name="phone_number" required
                    class="font-bold p-2 text-lg outline-none border-b-2 border-[#1a4568] bg-transparent">
            
                <label for="age" class="my-4 font-bold text-[#1a4568]">Age:</label>
                <input type="number" placeholder="25" id="age" name="age" required
                    class="font-bold p-2 text-lg outline-none border-b-2 border-[#1a4568] bg-transparent">
                <button type="submit" class="mt-5 px-4 py-3 bg-[#1a4568] text-white rounded-full uppercase shadow-md 
                    hover:bg-white hover:text-[#1a4568] hover:scale-105 transition-transform duration-300">Sign Up</button>
            </form>
        </div>
    </div>
</div>
<?php require("footer.php"); ?>
</body>
</html>