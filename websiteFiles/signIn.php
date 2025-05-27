<?php
// Start the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Check if the user is signed in
if (isset($_SESSION["is_logged_in"]) && $_SESSION["is_logged_in"]) {
    header("Location: index.php");
    exit; 
}
//handle the sign in process
require("../phpFiles/connect.php");
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $error = [];
    $success = "";
    // Input validation
    if (empty($email) || empty($password)) {
        $error[] = "Please fill in all fields.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error[] = "Invalid email address.";
    }
    if (empty($error)) {
        //flag vari for admin 
        $_SESSION["is_admin"]=false;
        $stmt = $conn->prepare("
            SELECT patient_id as id,email, password FROM patients WHERE email = ? and active='yes'
            UNION 
            SELECT doctor_id as id,email, password FROM doctors WHERE email = ? and active='yes' 
            and admin_approval='yes'and active_hospital= 'yes'
        ");
        $stmt->bind_param("ss", $email, $email);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows === 1) {
            $user = $res->fetch_assoc();
            // Verify password
            if (password_verify($password, $user['password'])) {
                $_SESSION["email"] = $user["email"];
                $_SESSION["id"] = $user["id"];
                $_SESSION["is_logged_in"] = true; 
                $success = "Welcome to VitalCare!";
            }
        } else {
            //check if the user is an admin
            $admins=$conn->prepare("select admin_id,email,password from admins where email=? and active='yes'");
            $admins->bind_param("s", $email);
            $admins->execute();
            $res= $admins->get_result();
            if ($res->num_rows === 1) {
                $user = $res->fetch_assoc();
                if (password_verify($password, $user["password"])) {
                    $_SESSION['is_super']=false;
                    $_SESSION["is_logged_in"] = true; 
                    $_SESSION["is_admin"]=true;
                    $_SESSION["email"] = $user["email"];
                    $_SESSION["id"] = $user["admin_id"];
                    $success = "Welcome back, Admin!";
                    //chek if the admin is a super admin
                    if($user["admin_id"]==33){
                        $_SESSION['is_super']=true;
                    }
                    include('cookies.php');
                }
            }
        }
        if(empty($success)){
            $error[] = "Incorrect username or password.";
        }
        $stmt->close();
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">    
    <title>Sign in</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Faculty+Glyphic&display=swap" rel="stylesheet">
<!-- CSS Files -->
    <link rel="stylesheet" href="../cssFiles/styles.css">
<!-- Tailwind CSS -->
    <link href="../cssFiles/output.css" rel="stylesheet">
</head>
<!-- -----------------------------------------------------------------------Sign In page : all----------------------------------------------------------------------- -->
<body>
<?php require("nav.php"); ?>
<!-- main signing page -->
<div class="flex items-center justify-center anim">
    <div class="flex m-5 p-10 rounded-2xl bg-cover shadow-md" style="background-image: url('../pics/background8.jpeg')">
        <!-- Sign-In Section -->
        <div class="flex flex-col justify-center items-center bg-opacity-10 backdrop-blur-md border border-white/30 shadow-md p-10 
                    rounded-l-xl w-full sm:w-96">
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
                            window.location.href = "index.php";  
                        }, 2000); 
                    </script>   
                </div>
            <?php endif; ?>
            <h3 class="mb-5 text-2xl anim1 text-[#1a4568] font-bold">Sign In</h3>
            <form action="signIn.php" class="flex flex-col anim2" method="post">
                <label for="email" class="my-4 font-bold text-[#1a4568]">Email:</label>
                    <input type="email" placeholder="Email@example.com" id="email" name="email" required
                class="font-bold p-2 text-lg outline-none border-b-2 border-[#1a4568] bg-transparent">
                <label for="password" class="my-4 font-bold text-[#1a4568]">Password:</label>
                    <input type="password" placeholder="SecurePass_2024" id="password" name="password" required
                class="font-bold p-2 text-lg outline-none border-b-2 border-[#1a4568] bg-transparent">
                <button type="submit" class="mt-5 px-4 py-3 bg-[#1a4568] text-white rounded-full 
                    uppercase shadow-md hover:bg-white hover:text-[#1a4568] hover:scale-105 transition-transform duration-300">Sign In</button>
                <a href="passwordRecovery.php" class="text-[#1a4568] m-4 hover:underline transition duration-300">Forget password?</a>
            </form>
        </div>
<!-- Welcome Back Section -->
        <div class="flex flex-col justify-center items-center bg-[#1a4568] text-white p-10 rounded-r-xl w-full sm:w-96 text-center">
            <h3 class="mb-10 text-3xl font-bold anim1 ">Welcome Back!</h3>
            <p class="mb-5 anim1">Not a member yet? Join us now!</p>
            <button class="px-4 py-3 w-52 bg-white text-[#1a4568] font-bold rounded-full hover:bg-[#90afc4] hover:text-white 
                    transition duration-300 anim2" onclick="location.href='signUp.php';">Create Account</button>
        </div>
    </div>  
</div> 
</div>
<?php require("footer.php"); ?>
</body>
</html>