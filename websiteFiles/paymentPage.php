<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require("cookies.php");
require("../phpFiles/connect.php");    
//handle the booking process
//check if the user is a patient or not
if (!empty($_COOKIE["user_name"]) && isset($_COOKIE['user_name'])) {
    if($_SERVER['REQUEST_METHOD']=='POST'){
        //validate the payment details
        $cardHolder_name=$_POST['cardholder-name'];
        $cardNumber=$_POST['card-number'];
        $card_expiry=$_POST['expiry-date'];
        $cardHolder_cvv=$_POST['cvv'];
        //initialize the error and success variables
        $error = [];
        $success = "";
        $patient_id = $_SESSION['id'];
        //validate input
        // Cardholder Name Validation
        if (empty($_POST['cardholder-name'])) {
            $error[] = "Cardholder name is required.";
        } else {
            $cardHolder_name = trim($_POST['cardholder-name']);
            if (!preg_match("/^[a-zA-Z\s\-]+$/", $cardHolder_name)) {
                $error[] = "Cardholder name can only contain letters, spaces, and hyphens.";
            }
        }
        // Card Number Validation 
        if (empty($_POST['card-number'])) {
            $error[] = "Card number is required.";
        } else {
            $cardNumber = $_POST['card-number'];
            if (!preg_match("/^\d{13,16}$/", $cardNumber)) {
                $error[] = "Card number must be between 13 to 16 digits.";
            }
        }
        // Expiry Date Validation (MM/YY or MM/YYYY)
        if (empty($_POST['expiry-date'])) {
            $error[] = "Expiry date is required.";
        } else {
            if (!preg_match("/^((0[1-9]|1[0-2])\/(\d{2}|\d{4}))$/", $card_expiry)) {
                $error[] = "Expiry date must be in MM/YY or MM/YYYY format.";
            }
        }

        // CVV Validation
        if (empty($_POST['cvv'])) {
            $error[] = "CVV is required.";
        } else {
            $cardHolder_cvv = $_POST['cvv'];
            if (!preg_match("/^\d{3,4}$/", $cardHolder_cvv)) {
                $error[] = "CVV must be a 3 or 4 digit number.";
            }
        }
        
        // define the required variables from session
        $available_time_id=$_SESSION['available_time_id'];
        $service_id=$_SESSION['service_id'];
        $service_name=$_SESSION['service_name'];
        $time=$_SESSION['time'];
        $date =$_SESSION['date'];
        $doctor_id =$_SESSION['doctor_id'];
        $service_time=$_SESSION['service_time'];
        $end_time=$_SESSION['end_time'];

        //check if the time and date are booked already 
        $check_appointments = $conn->prepare('
            SELECT * 
            FROM appointments 
            WHERE (
                (? >= appointment_time AND ? <= appointment_end_time)
                OR (appointment_time >= ? AND appointment_time < ?)
                OR (appointment_end_time > ? AND appointment_end_time <= ?)
            )
            AND appointment_date = ? 
            AND status <> "completed" 
            AND doctor_id = ?');
        $check_appointments->bind_param('sssssssi', $time, $end_time, $time, $end_time, $time, $end_time, $date, $doctor_id);
        $check_appointments->execute();
        $chk_res=$check_appointments->get_result()->fetch_all();
        if(!empty($chk_res)){
            $error[] = "The selected date and time are already booked. Please choose a different time.";
        }    
        // Insert into the database if no errors
        if (empty($error)) {
            $stmt = $conn->prepare('INSERT INTO appointments (patient_id, doctor_id, appointment_time,appointment_end_time, 
            appointment_date, status, reason,service_id)
            VALUES (?, ?, ?, ?,?, "pending", ?,?)');
            $stmt->bind_param('iissssi', $patient_id, $doctor_id, $time,$end_time, $date, $service_name,$service_id);
            if ($stmt->execute()) {

                $success = "You're all set! Your appointment is booked. Check your email for the details.";
                //set the selected time to booked 
                $set_booked=$conn->prepare('UPDATE available_times set booked="yes"
                where available_time_id=?');
                $set_booked->bind_param('i',$available_time_id);
                $set_booked->execute();
                
                //covert the date and time for user
                $formatted_time = date("g:i A", strtotime($time));
                $formatted_end_time = date("g:i A", strtotime($end_time));
                $formatted_date = date("l, F j", strtotime($date));

                //get the patient data to send appointment details as an email to the user
                $patient_info=$conn->prepare('select name,email from patients where patient_id=?');
                $patient_info->bind_param('i',$patient_id);
                if($patient_info->execute()){
                    $res=$patient_info->get_result();
                    $array=$res->fetch_all();
                    $name=$array[0][0];
                    $email=$array[0][1];                        
                    $mail =require __DIR__."/mailer.php";
                    $mail->setFrom("noreplay@vitalCare.com");
                    $mail->addAddress($email);
                    $mail->Subject="Appointment Details";
                    $mail->Body = <<<END
                    <p>Dear <strong>$name</strong>,</p>  
                    <p>We are pleased to confirm that your appointment has been successfully booked for <strong>$formatted_date</strong> at <strong>$formatted_time - $formatted_end_time</strong>.</p>  
                    <p>Your doctor has been notified of your appointment. Please wait for confirmation.</p>  
                    <p>You can view your appointment details by logging into your account at any time.</p>  
                    <p>If you have any questions or need to reschedule, feel free to contact our support team.</p>  
                    <p>Thank you for choosing <strong>VitalCare</strong>. We look forward to serving you!</p>  
                    <p>Best regards,<br>  
                    <strong>VitalCare Team</strong></p>
                    END;
                    try{
                        $mail->send();
                    }
                    catch(Exception $e ){
                        $error[] = "Oops! Something went wrong and we couldn't send the email to the patient.";
                    }
                }
                //get the doctor data to send appointment details as an email 
                $doctor_info=$conn->prepare('select name,email from doctors where doctor_id=?');
                $doctor_info->bind_param('i',$doctor_id);
                if($doctor_info->execute()){
                    $patient_name=$_COOKIE['user_name'];
                    $res=$doctor_info->get_result();
                    $array=$res->fetch_all();
                    $name=$array[0][0];
                    $email=$array[0][1];
                    //send email to the doctor
                    $mail =require __DIR__."/mailer.php";
                    $mail->setFrom("noreplay@vitalCare.com");
                    $mail->addAddress($email);
                    $mail->Subject="New Booked Appointment";
                    $mail->Body = <<<END
                    <p>Dear Dr. <strong>$name</strong>,</p>  
                    <p>We would like to inform you that a patient, <strong>$patient_name</strong>, has scheduled an appointment with you on <strong>$formatted_date</strong> at <strong>$formatted_time -$formatted_end_time</strong>.</p>  
                    <p>Please log in to your dashboard to confirm the appointment and review the details.</p>  
                    <p>If you have any questions or need further assistance, feel free to contact our support team.</p>  
                    <p>Thank you for being a valued part of <strong>VitalCare</strong>. We appreciate your dedication to patient care!</p>  
                    <p>Best regards,<br>  
                    <strong>VitalCare Team</strong></p>
                    END;
                    try{
                        $mail->send();
                    }
                    catch(Exception $e ){
                        $error[] = "Oops! Something went wrong and we couldn't send the email to the doctor.";
                    }
                }
            } else {
                $error[] = "Failed to book the appointment. Please try again.";
            }
            $stmt->close();
        }
    }
    $conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Page</title>
<!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Faculty+Glyphic&display=swap" rel="stylesheet">
<!-- CSS Files -->
    <link rel="stylesheet" href="../cssFiles/styles.css">
<!-- Tailwind CSS -->
    <link href="../cssFiles/output.css" rel="stylesheet">
</head>
<!-- -----------------------------------------------------------------------Payment page : patients----------------------------------------------------------------------- -->
<body>
<div class="flex justify-center items-center min-h-screen bg-gray-100 mt-4">
    <div class="bg-[aliceblue] rounded-lg shadow-lg p-10 w-96 flex flex-col items-center anim">
<!-- Display Error Messages -->
        <?php if (!empty($error)): ?>
            <div class="px-4 py-3 rounded relative mb-4 bg-red-100 text-red-700 w-full text-center">
                <ul class="mt-2 list-disc list-inside">
                    <?php foreach ($error as $err): ?>
                        <li class="error-msg font-bold anim"><?php echo htmlspecialchars($err); ?></li>
                        <?php if($err=="The selected date and time are already booked. Please choose a different time." 
                        || $err=="Failed to book the appointment. Please try again.") {
                            echo "<script>setTimeout(() => {window.location.href = 'searchPage.php';}, 4000);</script>";
                        } 
                    endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
<!-- Display Success Message -->
        <?php if (!empty($success)): ?>
            <div class="px-4 py-3 rounded relative mb-4 bg-green-100 text-green-700 w-full text-center">
                <p class="succ-msg font-bold anim "><?php echo htmlspecialchars($success); ?></p>
                <script>
                    setTimeout(() => {
                        window.location.href = "index.php";  
                    }, 5000); 
                </script> 
            </div>
        <?php endif;  
        ?>
<!-- Heading -->
        <h1 class="text-xl font-bold text-[#1A4568] mb-10 text-center">Enter Your Payment Details</h1>
<!-- Form -->
        <form action ="#" class="space-y-6 w-full anim1" method="post">
<!-- Cardholder Name -->
            <div class="space-y-2">
                <label for="cardholder-name" class="block text-sm text-[#1A4568]">Cardholder Name</label>
                <input type="text" id="cardholder-name" name="cardholder-name" placeholder="John Doe" 
                    required class="w-full p-3 text-base border border-gray-300 rounded-md focus:outline-none focus:ring-2 
                    focus:border-[#1A4568]"/>
            </div>
<!-- Card Number -->
            <div class="space-y-2">
                <label for="card-number" class="block text-sm text-[#1A4568]">Card Number</label>
                <input type="text" id="card-number" name="card-number" placeholder="1234 5678 9012 3456" 
                    maxlength="19" required class="w-full p-3 text-base border border-gray-300 rounded-md focus:outline-none focus:ring-2
                    focus:ring-[#1A4568]"/>
            </div>
<!-- Expiration Date and CVV -->
            <div class="flex gap-4">
                <div class="flex-1 space-y-2">
                    <label for="expiry-date" class="block text-sm text-[#1A4568]">Expiration Date</label>
                    <input type="text" id="expiry-date" name="expiry-date" placeholder="MM/YY" maxlength="5" required 
                        class="w-full p-3 text-base border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#1A4568]"/>
                </div>
<!-- automatically insert  slash as the user types -->
                <script>
                document.getElementById('expiry-date').addEventListener('input', function (e) {
                    let input = e.target.value.replace(/\D/g, ''); // Remove non-digits
                    if (input.length >= 3) {
                        input = input.substring(0, 2) + '/' + input.substring(2, 4);
                    }
                    e.target.value = input;
                });
                </script>
                <div class="flex-1 space-y-2">
                    <label for="cvv" class="block text-sm text-[#1A4568]">CVV</label>
                    <input type="password" id="cvv" name="cvv" placeholder="329" maxlength="3" \
                        required class="w-full p-3 text-base border border-gray-300 rounded-md focus:outline-none focus:ring-2 
                        focus:ring-[#1A4568]"/>
                </div>
            </div>
<!-- Pay Button -->
            <button type="submit" class="w-full py-3 bg-[#1A4568] text-white rounded text-sm transition-transform 
                transform hover:-translate-y-1 hover:bg-[#2E597C] focus:outline-none focus:ring-2 focus:ring-[#1A4568]">
                Pay Now
            </button>
        </form>
    </div>
</div>
</body>
</html>
<?php 
} // end of the if statement checking if the user is patient
else {
    header("Location: index.php");
    exit();
}
?>

