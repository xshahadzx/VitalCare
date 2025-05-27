<?php
// get the token and user type from the URL
// the token is passed as a query parameter in the URL
$token= $_GET['token'];
$user_type = isset($_GET['user_type']) ? $_GET['user_type'] : "";
$token_hash = hash('sha256',$token);
require('../phpFiles/connect.php');
$errors=[];
$success="";
$allowed_tables = ['patients', 'doctors', 'admins'];
$sql="";
// Check if the user type is valid
if (in_array($user_type, $allowed_tables)) {
    $sql = "SELECT * FROM $user_type WHERE reset_token_hash = ?";
}
$stmt=$conn->prepare($sql);
$stmt->bind_param('s',$token_hash);
$stmt->execute();
$result=$stmt->get_result();
$users=$result->fetch_assoc();
// Check if the user exists and the token is valid
if($users==null){
    $errors[]="This password reset link is no longer valid.";
}
// Check if the token has expired
elseif(strtotime($users["reset_token_expires_at"])<=time()){
    $errors[]="Your password reset link is no longer valid. Please request another.";
}
if($_SERVER["REQUEST_METHOD"]=="POST" && empty($errors)){
    $password=$_POST['password'];
    $confirm_password=$_POST['confirm_paswword'];
    //validate password 
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters.";
    }
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }
    if(empty($errors)){ 
        $hashed_password = password_hash($password, PASSWORD_ARGON2ID);
        $stm="";
        if($user_type=="patients"){
            // Check if the new password matches the current password
            if (password_verify($password, $users['password'])) {
                $errors[] = "New password must differ from the previous one.";
            } else {
            // Hash the new password and proceed with the update
            $sql = "update patients set password=?,reset_token_hash=?,reset_token_expires_at=? where patient_id=? and active='yes'";
            $stm = $conn->prepare($sql);
            // Prepare your variables
            $reset_token_hash = null;
            $reset_token_expires_at = null;
            $stm->bind_param('sssi',$hashed_password,$reset_token_hash, $reset_token_expires_at,$users['patient_id']); 
            if($stm->execute()){
                $success="Password changed. Redirecting to sign-in…";
            } 
            }
        }
        elseif($user_type=="doctors"){
            // Check if the new password matches the current password
            if (password_verify($password, $users['password'])) {
                $errors[] = "New password must differ from the previous one.";
            } else {
                $sql = "update doctors set password=?,reset_token_hash=?,reset_token_expires_at=? where doctor_id=? and active='yes'";
                $stm = $conn->prepare($sql);
                // Prepare your variables
                $reset_token_hash = null;
                $reset_token_expires_at = null;
                $stm->bind_param('sssi',$hashed_password,$reset_token_hash, $reset_token_expires_at,$users['doctor_id']);
                if($stm->execute()){
                    $success="Password changed. Redirecting to sign-in…";
                }   
            }
        }
        elseif($user_type=="admins"){
            // Check if the new password matches the current password
            if (password_verify($password, $users['password'])) {
                $errors[] = "New password must differ from the previous one.";
            } else {
            $sql = "update admins set password=?,reset_token_hash=?,reset_token_expires_at=? where admin_id=? and active='yes'";
            $stm = $conn->prepare($sql);
            // Prepare your variables
            $reset_token_hash = null;
            $reset_token_expires_at = null;
            $stm->bind_param('sssi',$hashed_password,$reset_token_hash, $reset_token_expires_at,$users['admin_id']);      
            if($stm->execute()){
                $success="Password changed. Redirecting to sign-in…";
            }  
            }
        }
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">    
    <title>Reset Paasword</title>
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
<!-- -----------------------------------------------------------------------password recovery page : all ----------------------------------------------------------------------- -->
<body>
<div class="flex items-center justify-center">
    <div class="flex m-5 p-10 rounded-2xl bg-cover shadow-md anim" style="background-image: url('../pics/background8.jpeg')">
<!-- reset password form Section -->
        <div class=" anim1 flex flex-col justify-center items-center bg-opacity-10 backdrop-blur-md border border-white/30 shadow-md p-10 rounded-xl w-full sm:w-96">
<!-- Display Error Messages -->
    <?php if (!empty($errors)): ?>
        <div class="px-4 py-3 rounded relative mb-4">
            <ul class="mt-2 list-disc list-inside">
                <?php foreach ($errors as $err): ?>
                    <li class="error-msg font-bold anim width:20px"><?php echo htmlspecialchars($err); ?></li>
                <?php 
                if($err=="Your password reset link is no longer valid. Please request another." || $err=="This password reset link is no longer valid."){ 
                ?>
                <script>
                    setTimeout(() => {
                    window.location.href = "passwordRecovery.php"; 
                    }, 2000); 
                </script> 
            <?php
                }
            endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
<!-- Display Success Message -->
    <?php if (!empty($success)): ?>
        <div class="px-4 py-3 rounded relative mb-4">
        <p class="succ-msg font-bold anim"><?php echo htmlspecialchars($success); ?></p>
        <script>
                setTimeout(() => {
                    window.location.href = "signIn.php"; 
                }, 3000); 
        </script> 
        </div>
    <?php endif; ?>
    <h3 class="mb-5 text-2xl font-bold anim2 text-[#1a4568]">Recover Password:</h3>
    <form action="#" class="flex flex-col anim2" method="post">
                    <label for="password" class="my-2 font-bold text-[#1a4568]">Password:</label>
                    <input type="password" placeholder="Enter your password" id="password" name="password" required
                        class="font-bold p-2 text-lg outline-none border-b-2 border-[#1a4568] bg-transparent">
                    <label for="confirm_paswword" class="my-4 font-bold text-[#1a4568]">Confirm paswword:</label>
                    <input type="password" placeholder="Repeat your password" id="confirm_paswword" name="confirm_paswword" required
                        class="font-bold p-2 text-lg outline-none border-b-2 border-[#1a4568] bg-transparent">
                    <button type="submit" class="m-5 px-4 py-3 bg-[#1a4568] text-white rounded-full 
                        uppercase shadow-md hover:bg-white hover:text-[#1a4568] hover:scale-105 transition-transform duration-300">Recover Password</button>
                </form>
            </div>
            </div>
        </div>
</body>
</html>