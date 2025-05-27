<!--php code-->
<?php
require("../phpFiles/connect.php");
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email=$_POST['email'];
    //check if the user is in the database 
    $exists=false;
    $is_patient=false;
    $is_doctor=false;
    $is_admin=false;
    $error=[];
    $success="";
    //check if there is an active token
    $stmt=$conn->prepare('SELECT reset_token_expires_at FROM patients WHERE email = ? and reset_token_expires_at > NOW()
    UNION
    SELECT reset_token_expires_at FROM doctors WHERE email = ? and reset_token_expires_at > NOW()
    UNION
    SELECT reset_token_expires_at FROM admins WHERE email = ? and reset_token_expires_at > NOW()');
    $stmt->bind_param('sss',$email,$email,$email);
    $stmt->execute();
    $res=$stmt->get_result();
    if($row=$res->fetch_assoc()){
        $error[]="If a reset request exists, you'll receive an email with instructions shortly.";
    }
    $stmt->close();
    if (empty($error)) {
        $stmt=$conn->prepare('SELECT email, "patient" AS user_type FROM patients WHERE email = ? and active="yes"
                                    UNION
                                    SELECT email, "doctor" AS user_type FROM doctors WHERE email = ? and active="yes"
                                    UNION
                                    SELECT email, "admin" AS user_type FROM admins WHERE email = ? and active="yes"');
        $stmt->bind_param('sss',$email,$email,$email);
        $stmt->execute();
        $res=$stmt->get_result();
        if($row=$res->fetch_assoc()){
            $exists=true;
            if($row['user_type']=='patient'){
                $is_patient=true;
            }
            elseif($row['user_type']=='doctor'){
                $is_doctor=true;
            }
            elseif($row['user_type']=='admin'){
                $is_admin=true;
            }
        }
        $stmt->close();
        //check if the user is registerd in the system 
        if($exists){
            // generate a random token and hash it
            //using random_bytes to generate a secure token
            $token= bin2hex(random_bytes(16));
            $token_hash= hash("sha256",$token);
            date_default_timezone_set('Asia/Riyadh');
            //set the expiry time to 30 minutes from now
            $expiry=date("Y-m-d H:i:s",time()+60*30);
            $sql_stmt="";
            if($is_patient){
                $sql_stmt='UPDATE patients 
                SET reset_token_hash=? , reset_token_expires_at=?
                WHERE email=?';
                $user_type="patients";
            }
            elseif($is_doctor){
                $sql_stmt='UPDATE doctors 
                SET reset_token_hash=? , reset_token_expires_at=?
                WHERE email=?';
                $user_type="doctors";
            }
            elseif($is_admin){
                $sql_stmt='UPDATE admins 
                SET reset_token_hash=? , reset_token_expires_at=?
                WHERE email=?';
                $user_type="admins";
            }
            $sql=$conn->prepare($sql_stmt);
            $sql->bind_param('sss',$token_hash, $expiry,$email);
            $sql->execute();
            if($sql->affected_rows){
                    $mail =require __DIR__."/mailer.php";
                    $mail->setFrom("noreplay@vitalCare.com");
                    $mail->addAddress($email);
                    $mail->Subject = "Password Reset Request - VitalCare";
                    $resetLink = "http://localhost/VitalCare/websiteFiles/resetPassword.php?token=$token&user_type=$user_type";
                    $mail->Body = <<<END
                    <p>Hello,</p>
                    <p>We received a request to reset your password for your VitalCare account. If you made this request, please click the link below to reset your password:</p>
                    <p><a href="$resetLink" style="background-color: #1a4568; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Reset Your Password</a></p>
                    <p>If you did not request this change, you can safely ignore this email. Your password will remain unchanged.</p>
                    <p>If you have any questions, feel free to contact our support team.</p>
                    <p>Best regards,</p>
                    <p>The VitalCare Team</p>
                    <p><small>If you're having trouble, copy and paste this link into your browser: $resetLink</small></p>
                    END;            
                    try{
                        $mail->send();
                    }
                    catch(Exception $e ){
                        $error[]= "opps! something went wrong, please try again later";
                    }
            }
        }
    }
    //show message regardless if the message sent or not for security
    if(empty($error)){
        $success="Message sent, please check your inbox"; 
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">    
    <title>VitalCare</title>
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
<?php require("nav.php"); ?>
<!-- main signing page -->
<div class="flex items-center justify-center">
    <div id="signInPage" class="flex m-5 p-10 rounded-2xl bg-cover shadow-md anim" style="background-image: url('../pics/background8.jpeg')">
<!-- password-recovery Section -->
        <div class="flex flex-col justify-center items-center bg-opacity-10 backdrop-blur-md border border-white/30 shadow-md p-10 rounded-l-xl 
                    w-full sm:w-96">
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
                </div>
            <?php endif; ?>
            <h3 class="mb-5 text-2xl text-[#1a4568] font-bold anim1 text-center">Forgot Your Password?</h3>
            <form action="passwordRecovery.php" class="flex flex-col m-4 anim2" method="post">
                <label for="email" class="m-2 font-bold text-[#1a4568]">Email:</label>
                    <input type="email" placeholder="you@example.com" id="email" name="email" required
                class="font-bold p-2 text-lg outline-none border-b-2 border-[#1a4568] bg-transparent">
                <button type="submit" class="m-5 px-4 py-3 bg-[#1a4568] text-white rounded-full 
                    uppercase shadow-md hover:bg-white hover:text-[#1a4568] hover:scale-105 transition-transform duration-300">
                    Send Reset Link
                </button>
            </form>
        </div>
<!-- Welcome Back Section -->
        <div class="flex flex-col justify-center items-center bg-[#1a4568] text-white p-10 rounded-r-xl w-full sm:w-96 anim2">
            <h3 class="mb-10 text-2xl font-bold anim text-center">Looking to sign in?</h3>
            <p class="mb-6 text-lg text-center">Need to sign in? </p>
            <p class="mb-6 text-lg text-center">Click below to go back.</p>
            <button class="px-4 py-3 w-52 bg-white text-[#1a4568] font-bold rounded-full hover:bg-[#90afc4] hover:text-white transition 
                duration-300 m-4" onclick="location.href='signIn.php';">Go Back
            </button>
        </div>
    </div> 
</div> 
</div>
<?php require("footer.php"); ?>
</body>
</html>