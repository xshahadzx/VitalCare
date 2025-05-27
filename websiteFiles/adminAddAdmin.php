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
// Redirect to home page if user is not logged in as a super admin
if(!$_SESSION['is_super']){
    header("Location: index.php");
    exit;
}
require("../phpFiles/connect.php");
// add another admin 
if($_SERVER['REQUEST_METHOD']=="POST"){
        $name = trim($_POST["name"]);
        $email = trim($_POST["email"]);
        $password = trim($_POST["password"]);
        $confirm_password = trim($_POST["confirm_password"]);
        $phone = trim($_POST["phone_number"]);

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
        // if no errors, insert the new admin into the database
        if(empty($errors)){
            // Hash the password using Argon2ID
            $hash_password=password_hash($password,PASSWORD_ARGON2ID);
            $stmt = $conn->prepare("insert into admins(email,password,name,phone_number,active) values(?,?,?,?,'yes')");
            // Execute the query with prepared statements
            $stmt->bind_param('ssss', $email,$hash_password,$name,$phone);
            if ($stmt->execute()) {
                $success="Admin added successfully!";
            } else {
                $error[]= "Error adding admin please try again!";
            }
        } else {
            $error[]= "unable to add an admin at the momment!";
        }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
<!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Faculty+Glyphic&display=swap" rel="stylesheet">
    <!-- CSS Files -->
    <link rel="stylesheet" href="../cssFiles/styles.css">
    <link href="../cssFiles/output.css" rel="stylesheet">
</head>
<body>
<!-------------------------------------------------------------------------Admin dashboard page : appointments----------------------------------------------------------------------- -->
<!--nav bar-->
<?php require("nav.php"); ?>
<!-- Dashboard -->
<div class="flex items-center justify-center anim">
<div class="flex m-5 p-10 rounded-2xl bg-cover shadow-md" style="background-image: url('../pics/background8.jpeg')">
    <div class="flex flex-col justify-center items-center bg-opacity-10 backdrop-blur-md border border-white/30 shadow-md p-10 rounded-l-xl 
        w-full sm:w-96">
<!-- Header Section -->
    <header class="text-center mb-6 text-[#1a4568] anim1">
        <h1 class="text-3xl font-bold leading-tight mb-4">Admin Registeration</h1>
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
                window.location.href = "admins.php"; // Redirect to the admins page after 3 seconds
            }, 1000); 
        </script>   
    </div>
    <?php endif; ?>
        <form action="#" class="flex flex-col w-full anim2" method="post">
            <label for="name" class="my-4 font-bold text-[#1a4568]">Full Name:</label>
            <input type="text" placeholder="Admin Name" id="name" name="name" pattern="^[a-zA-Z-' ]+$" required
                class="font-bold p-2 text-lg outline-none border-b-2 border-[#1a4568] bg-transparent">
        
            <label for="email" class="my-4 font-bold text-[#1a4568]">Email:</label>
            <input type="text" placeholder="admin@example.com" id="email" name="email" required
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
        <button type="submit" class="mt-5 px-4 py-3 bg-[#1a4568] text-white rounded-full uppercase shadow-md hover:bg-white 
            hover:text-[#1a4568] hover:scale-105 transition-transform duration-300">Add Admin</button>
        </form>
    </div>
    </div>
</div>
</div>
<!-- Footer -->
<?php require("footer.php"); ?>
</body>
</html>

