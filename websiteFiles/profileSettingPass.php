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
        $email = trim($_POST['email']);
        $current_password = trim($_POST['current-password']);
        $new_password = trim($_POST['new-password']);
        $confirm_password = trim($_POST['confirm-password']);
        $id = $_SESSION["id"];
        // Set needed variables and array
        $error = [];
        $success = "";
        $correct_password = false;
        // Input validation
        if (!empty($email)&&!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error[] = "Please enter a valid email address.";
        }
        if (!empty($new_password)&&strlen($new_password) < 8) {
            $error[] = "Password must be at least 8 characters.";
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
            $error[] = "Looks like this email is taken. Try another one?";
        }
        // Check if the entered password is correct
        $stmt = $conn->prepare("SELECT password FROM patients WHERE patient_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows === 1) {
            $user = $res->fetch_assoc();
            // Verify password
            if (password_verify($current_password, $user['password'])) {
                $correct_password = true;
            // Check if the new password matches the current password
            if (password_verify($new_password, $user['password'])) {
                $error[] = "Your new password must be different from your current or previous passwords. Please choose a unique password.";
            } else {
                // Hash the new password and proceed with the update
                $hash_password = password_hash($new_password, PASSWORD_ARGON2ID);
            }
            } else {
                $error[] = 'To confirm the changes, please enter your correct password.';
            }
        }
        if ($new_password !== $confirm_password) {
            $error[] = "Passwords do not match. make sure to enter the same password in both fields.";
        }
        // Check if there are any errors and if the password is correct
        if (empty($error) && $correct_password) {
            // Check if at least one field is provided
            if (!empty($email) || !empty($new_password )) {
                // Initialize query parts
                $setClauses = [];
                $params = [];
                $types = "";
                // check all fields 
                if (!empty($email)) {
                    $setClauses[] = "email = ?";
                    $params[] = $email;
                    $types .= "s"; 
                }
                if (!empty($new_password)) {
                    $setClauses[] = "password = ?";
                    $params[] = $hash_password;
                    $types .= "s";
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
                    $success = "Your information has been updated! You'll be logged out shortly. See you soon!";
                } else {
                $error[]= "Oops! Something went wrong. Please try again.";
                }
                // Close the statement
                $stmt->close();
            } else {
                $error[] = "No fields were provided to update.";
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
    <link href="../cssFiles/output.css" rel="stylesheet">
</head>
<body>
<!--nav bar-->
<?php require("nav.php"); ?>
<!-- Main Section - Setting Profile -->
<div class="max-w-[600px] mx-auto p-10 bg-center bg-contain rounded-lg shadow-md" style="background-image: url('../pics/background8.jpeg'); 
    height: auto; width: 500px;">
<!-- Back Button -->
    <div class="mb-6">
        <a href="profilePage.php" class="text-[#1A4568] font-bold uppercase hover:underline ml-2">
            <i class="fas fa-arrow-left"></i> Back</a>
    </div>
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
                    window.location.href = "signOut.php";  
                }, 3000); 
            </script>   
        </div>
    <?php endif; ?>
<!-- Page Title -->
    <h1 class="text-2xl font-bold text-center text-[#1A4568] mb-10 anim">Profile Settings</h1>
<!-- Profile Form -->
    <form action="profileSettingPass.php" class="flex flex-col space-y-6 anim1" method="post">
<!-- Change Password Section -->
        <div>
            <h2 class="text-lg font-bold text-center text-[#1A4568] mb-4">Change Password or Email</h2>
            <div class="space-y-4">
<!-- Email -->
                <div class="m-4">
                    <label for="email" class="block text-sm font-bold text-[#1A4568] mb-2">Email</label>
                    <input type="email" id="email" name="email" placeholder="<?php echo $_SESSION["email"]; ?>" 
                        class="w-full p-3 text-sm border border-[#1A4568] rounded bg-[aliceblue] outline-none focus:ring-2 
                        focus:ring-[#1A4568]"/>
                </div>
<!-- New Password -->
                <div class="m-4">
                        <label for="new-password" class="block text-sm font-bold text-[#1A4568] mb-2">New Password</label>
                        <input type="password" id="new-password" name="new-password" placeholder="New Password"
                            class="w-full p-3 text-sm border border-[#1A4568] rounded bg-[aliceblue] outline-none focus:ring-2 
                            focus:ring-[#1A4568]"/>
                </div>
<!-- confirm Password -->
                <div class="m-4">
                    <label for="confirm-password" class="block text-sm font-bold text-[#1A4568] mb-2">Confirm Password</label>
                    <input type="password" id="confirm-password" name="confirm-password" placeholder="Repeat New Password"
                        class="w-full p-3 text-sm border border-[#1A4568] rounded bg-[aliceblue] outline-none focus:ring-2 
                        focus:ring-[#1A4568]"/>
                </div>
<!-- current Password -->
                <div class="m-4">
                    <label for="current-password" class="block text-sm font-bold text-[#1A4568] mb-2">Current Password</label>
                    <input type="password" id="current-password" name="current-password" placeholder="Current Password" 
                        class="w-full p-3 text-sm border border-[#1A4568] rounded bg-[aliceblue] outline-none focus:ring-2 
                        focus:ring-[#1A4568]" required/>
                </div>
            </div>
        </div>
<!-- Save Changes Button -->
        <div class="flex justify-center">
            <button
                type="submit" class="px-6 py-2 text-sm font-bold text-white bg-[#1A4568] rounded hover:bg-[#183141] transition-all">
                Save Changes
            </button>
        </div>
    </form>
<!-- Personal Information Button -->
    <div class="mt-6 anim1">
        <a href="profileSetting.php" class="text-[#1A4568] font-bold uppercase hover:underline flex items-center">
            <i class="fas fa-user mr-2"></i> Personal Information</a>
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
        $email = trim($_POST['email']);
        $current_password = trim($_POST['current-password']);
        $new_password = trim($_POST['new-password']);
        $confirm_password = trim($_POST['confirm-password']);
        $id = $_SESSION["id"];
        // Set needed variables and array
        $error = [];
        $success = "";
        $correct_password = false;
        // Input validation
        if (!empty($email)&&!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error[] = "Please enter a valid email address.";
        }
        if (!empty($new_password)&&strlen($new_password) < 8) {
            $error[] = "Password must be at least 8 characters.";
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
            $error[] = "Looks like this email is taken. Try another one?";
        }
        // Check if the entered password is correct
        $stmt = $conn->prepare("SELECT password FROM doctors WHERE doctor_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows === 1) {
            $user = $res->fetch_assoc();
            // Verify password
            if (password_verify($current_password, $user['password'])) {
                $correct_password = true;
            // Check if the new password matches the current password
            if (password_verify($new_password, $user['password'])) {
                $error[] = "Your new password must be different from your current or previous passwords. Please choose a unique password.";
            } else {
                // Hash the new password and proceed with the update
                $hash_password = password_hash($new_password, PASSWORD_ARGON2ID);
            }
            } else {
                $error[] = 'To confirm the changes, please enter your correct password.';
            }
        }
        if ($new_password !== $confirm_password) {
            $error[] = "Passwords do not match. make sure to enter the same password in both fields.";
        }
        // Check if there are any errors and if the password is correct
        if (empty($error) && $correct_password) {
            // Check if at least one field is provided
            if (!empty($email) || !empty($new_password )) {
                // Initialize query parts
                $setClauses = [];
                $params = [];
                $types = "";
                // check all fields 
                if (!empty($email)) {
                    $setClauses[] = "email = ?";
                    $params[] = $email;
                    $types .= "s"; 
                }
                if (!empty($new_password)) {
                    $setClauses[] = "password = ?";
                    $params[] = $hash_password;
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
                    $success = "Your information has been updated! You'll be logged out shortly. See you soon!";
                } else {
                    $error[]= "Oops! Something went wrong. Please try again.";
                }
                // Close the statement
                $stmt->close();
            } else {
                $error[]= "No fields provided for update.";
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
    <link href="../cssFiles/output.css" rel="stylesheet">
</head>
<body>
<!--nav bar-->
<?php require("nav.php"); ?>
<!-- Main Section - Setting Profile -->
<div class="max-w-[600px] mx-auto p-10 bg-center bg-contain rounded-lg shadow-md" style="background-image: url('../pics/background8.jpeg');
        height: auto; width: 500px;">
<!-- Back Button -->
    <div class="mb-6">
        <a href="profilePage.php" class="text-[#1A4568] font-bold uppercase hover:underline ml-2">
            <i class="fas fa-arrow-left"></i> Back</a>
    </div>
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
                    window.location.href = "signOut.php";  
                }, 3000); 
            </script>   
        </div>
    <?php endif; ?>
<!-- Page Title -->
    <h1 class="text-2xl font-bold text-center text-[#1A4568] mb-10 anim1">Profile Settings</h1>
<!-- Profile Form -->
    <form action="profileSettingPass.php" class="flex flex-col space-y-6 anim1" method="post">
<!-- Change Password Section -->
        <div>
            <h2 class="text-lg font-bold text-center text-[#1A4568] mb-4">Change Password or Email</h2>
            <div class="space-y-4">
<!-- Email -->
                <div class="m-4">
                    <label for="email" class="block text-sm font-bold text-[#1A4568] mb-2">Email</label>
                    <input type="email" id="email" name="email" placeholder="<?php echo $_SESSION["email"]; ?>" 
                        class="w-full p-3 text-sm border border-[#1A4568] rounded bg-[aliceblue] outline-none focus:ring-2 
                        focus:ring-[#1A4568]"/>
                </div>
<!-- New Password -->
                <div class="m-4">
                    <label for="new-password" class="block text-sm font-bold text-[#1A4568] mb-2">New Password</label>
                    <input type="password" id="new-password" name="new-password" placeholder="New Password"
                        class="w-full p-3 text-sm border border-[#1A4568] rounded bg-[aliceblue] outline-none focus:ring-2 
                        focus:ring-[#1A4568]"/>
                </div>
<!-- confirm Password -->
                <div class="m-4">
                    <label for="confirm-password" class="block text-sm font-bold text-[#1A4568] mb-2">Confirm Password</label>
                    <input type="password" id="confirm-password" name="confirm-password" placeholder="Repeat New Password"
                        class="w-full p-3 text-sm border border-[#1A4568] rounded bg-[aliceblue] outline-none focus:ring-2 
                        focus:ring-[#1A4568]"/>
                </div>
<!-- current Password -->
                <div class="m-4">
                    <label for="current-password" class="block text-sm font-bold text-[#1A4568] mb-2">Current Password</label>
                    <input type="password" id="current-password" name="current-password" placeholder="Current Password" 
                        class="w-full p-3 text-sm border border-[#1A4568] rounded bg-[aliceblue] outline-none focus:ring-2 
                        focus:ring-[#1A4568]" required/>
                </div>
            </div>
        </div>
<!-- Save Changes Button -->
        <div class="flex justify-center">
            <button
                type="submit" class=" anim1 px-6 py-2 text-sm font-bold text-white bg-[#1A4568] rounded hover:bg-[#183141] transition-all">
                Save Changes
            </button>
        </div>
    </form>
<!-- Personal Information Button -->
    <div class="mt-6 anim1">
        <a href="profileSetting.php" class="text-[#1A4568] font-bold uppercase hover:underline flex items-center">
            <i class="fas fa-user mr-2"></i> Personal Information</a>
    </div>
</div>
<!-- footer -->
<?php require("footer.php"); ?>
</body>
</html>
<?php 
} 
//----------------------------------------------------------admin profile setting-------------------------------------------------------------
if (isset($_SESSION["is_admin"]) && !empty($_SESSION["is_admin"])){
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $email = trim($_POST['email']);
        $current_password = trim($_POST['current-password']);
        $new_password = trim($_POST['new-password']);
        $confirm_password = trim($_POST['confirm-password']);
        $id = $_SESSION["id"];
        // Set needed variables and array
        $error = [];
        $success = "";
        $correct_password = false;
        // Input validation
        if (!empty($email)&&!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error[] = "Please enter a valid email address.";
        }
        if (!empty($new_password)&&strlen($new_password) < 8) {
            $error[] = "Password must be at least 8 characters.";
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
            $error[] = "Looks like this email is taken. Try another one?";
        }
        // Check if the entered password is correct
        $stmt = $conn->prepare("SELECT password FROM admins WHERE admin_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows === 1) {
            $user = $res->fetch_assoc();
            // Verify password
            if (password_verify($current_password, $user['password'])) {
                $correct_password = true;
            // Check if the new password matches the current password
            if (password_verify($new_password, $user['password'])) {
                $error[] = "Your new password must be different from your current or previous passwords. Please choose a unique password.";
            } else {
                // Hash the new password and proceed with the update
                $hash_password = password_hash($new_password, PASSWORD_ARGON2ID);
            }
            } else {
                $error[] = 'To confirm the changes, please enter your correct password.';
            }
        }
        if ($new_password !== $confirm_password) {
            $error[] = "Passwords do not match. make sure to enter the same password in both fields.";
        }
        // Check if there are any errors and if the password is correct
        if (empty($error) && $correct_password) {
            // Check if at least one field is provided
            if (!empty($email) || !empty($new_password )) {
                // Initialize query parts
                $setClauses = [];
                $params = [];
                $types = "";
                // check all fields 
                if (!empty($email)) {
                    $setClauses[] = "email = ?";
                    $params[] = $email;
                    $types .= "s"; 
                }
                if (!empty($new_password)) {
                    $setClauses[] = "password = ?";
                    $params[] = $hash_password;
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
                    $success = "Your information has been updated! You'll be logged out shortly. See you soon!";
                } else {
                    $error[]= "Oops! Something went wrong. Please try again.";
                }
                // Close the statement
                $stmt->close();
            } else {
                $error[]= "No fields provided for update.";
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
    <link href="../cssFiles/output.css" rel="stylesheet">
</head>
<body>
<!--nav bar-->
<?php require("nav.php"); ?>
<!-- Main Section - Setting Profile -->
    <div class="max-w-[600px] mx-auto p-10 bg-center bg-contain rounded-lg shadow-md" style="background-image: url('../pics/background8.jpeg'); 
            height: auto; width: 500px;">
<!-- Back Button -->
        <div class="mb-6">
            <a href="profilePage.php" class="text-[#1A4568] font-bold uppercase hover:underline ml-2">
                <i class="fas fa-arrow-left"></i> Back</a>
        </div>
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
                    window.location.href = "signOut.php";  
                }, 3000); 
            </script>   
        </div>
    <?php endif; ?>
<!-- Page Title -->
        <h1 class="text-2xl font-bold text-center text-[#1A4568] mb-10 anim1">Profile Settings</h1>
<!-- Profile Form -->
        <form action="profileSettingPass.php" class="flex flex-col space-y-6 anim1" method="post">
<!-- Change Password Section -->
            <div>
                <h2 class="text-lg font-bold text-center text-[#1A4568] mb-4">Change Password or Email</h2>
                <div class="space-y-4">
<!-- Email -->
                <div class="m-4">
                    <label for="email" class="block text-sm font-bold text-[#1A4568] mb-2">Email</label>
                    <input type="email" id="email" name="email" placeholder="<?php echo $_SESSION["email"]; ?>" 
                        class="w-full p-3 text-sm border border-[#1A4568] rounded bg-[aliceblue] outline-none focus:ring-2 
                        focus:ring-[#1A4568]"/>
                </div>
<!-- New Password -->
                <div class="m-4">
                    <label for="new-password" class="block text-sm font-bold text-[#1A4568] mb-2">New Password</label>
                    <input type="password" id="new-password" name="new-password" placeholder="New Password"
                        class="w-full p-3 text-sm border border-[#1A4568] rounded bg-[aliceblue] outline-none focus:ring-2 
                        focus:ring-[#1A4568]"/>
                </div>
<!-- confirm Password -->
                <div class="m-4">
                    <label for="confirm-password" class="block text-sm font-bold text-[#1A4568] mb-2">Confirm Password</label>
                    <input type="password" id="confirm-password" name="confirm-password" placeholder="Repeat New Password"
                        class="w-full p-3 text-sm border border-[#1A4568] rounded bg-[aliceblue] outline-none focus:ring-2 
                        focus:ring-[#1A4568]"/>
                </div>
<!-- current Password -->
                <div class="m-4">
                    <label for="current-password" class="block text-sm font-bold text-[#1A4568] mb-2">Current Password</label>
                    <input type="password" id="current-password" name="current-password" placeholder="Current Password" 
                        class="w-full p-3 text-sm border border-[#1A4568] rounded bg-[aliceblue] outline-none focus:ring-2 
                        focus:ring-[#1A4568]" required/>
                </div>
                </div>
            </div>
<!-- Save Changes Button -->
            <div class="flex justify-center">
                <button
                    type="submit" class="px-6 py-2 text-sm font-bold text-white bg-[#1A4568] rounded hover:bg-[#183141] transition-all">
                    Save Changes
                </button>
            </div>
        </form>
<!-- Personal Information Button -->
        <div class="mt-6 anim1">
            <a href="profileSetting.php" class="text-[#1A4568] font-bold uppercase hover:underline flex items-center">
                <i class="fas fa-user mr-2"></i> Personal Information</a>
        </div>
    </div>
<!-- footer -->
<?php require("footer.php"); ?>
    </body>
    </html>
<?php 
} 
?>