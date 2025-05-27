<!------------------------------------------------------------------------footer: all ----------------------------------------------------------------------- -->
<?php 
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["footer_name"]??'');
    $email = trim($_POST["footer_email"]??'');
    $message = trim($_POST["footer_message"]??'');
    $to = "admin@gmail.com";
    $subject = "Contact Us Form Submission";
    $footer_success = ''; 
    $footer_error = []; 
    // Validate the input fields
    // Validate the name
    if (!preg_match("/^[a-zA-Z-' ]*$/", $name)) {
        $footer_error[] = "please enter a valid name";
    }
    
    // Validate the email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)&& !empty($email)) {
        $footer_error[] = "please enter a valid email address";
    }
    // Validate the message
    if (strlen($message) > 500) {
        $footer_error[] = "please enter a message with less than 500 characters";
    }
    // if the input fields are valid, send the email and no error messages are found
    if (empty($footer_error)&&$email!=null&&$name!=null&&$message!=null) {
        // Send an email to the admin with the contact form details
        $mail = require __DIR__ . "/mailer.php";
        $mail->setFrom($email);
        $mail->addAddress($to); 
        $mail->Subject = $subject;

        $mail->Body = <<<END
            <p>Dear <strong>Admin</strong>,</p>

            <p>You have received a new inquiry via the contact form on <strong>VitalCare</strong>.</p>

            <p><strong>Contact Details:</strong></p>
            <ul>
                <li><strong>Name:</strong> {$name}</li>
                <li><strong>Email:</strong> {$email}</li>
            </ul>

            <p><strong>Message:</strong></p>
            <p style="margin-left: 15px;">{$message}</p>

            <p>Best regards,<br>
            VitalCare Website Team</p>
        END;

        try {
            $mail->send();
            $footer_success = "Email sent successfully.";
        } catch (Exception $e) {
            $footer_error[] = "Email could not be sent. please try again later.";
        }
    }
}
?>
<!-- main page footer -->
<style>
    .modal {
        display: none;
        position: fixed;
        z-index: 1;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
    }
    .modal-content {
        background-color: white;
        margin: 10% auto;
        padding: 20px;
        width: 40%;
        border-radius: 8px;
        text-align: center;
    }
    .close {
        float: right;
        font-size: 20px;
        cursor: pointer;
    }
</style>
<!-- Main Footer -->
<footer class="bg-[#1A4568] text-[#DBECF4] p-6 m-4 rounded-lg shadow-lg anim">
    <div class="container mx-auto flex flex-col md:flex-row justify-between items-center ">
<!-- Navigation Links -->
        <ul class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-6 text-center md:text-left md:ml-4">
            <li>
                <a href="#" onclick="openModal('contactModal')" class=" text-sm font-semibold uppercase 
                hover:text-[#90AFC4] transition-all">Contact Us</a>
            </li>
            <li>
                <a href="#" onclick="openModal('privacyModal')" class=" text-sm font-semibold uppercase 
                hover:text-[#90AFC4] transition-all">Privacy Policy</a>
            </li>
            <li>
                <a href="#" onclick="openModal('termsModal')" class=" text-sm font-semibold uppercase 
                hover:text-[#90AFC4] transition-all">Terms of Service</a>
            </li>
        </ul>
        
<!-- Social Icons -->
        <div class="flex justify-center md:justify-end items-center space-x-4 md:mr-4">
            <a href="https://www.tiktok.com/en/" class="flex items-center space-x-2 hover:opacity-80 transition-all ">
                <img src="../pics/tiktok.png" alt="TikTok icon" class="w-6 h-6 md:w-8 md:h-8">
                <span class="hidden sm:inline text-sm font-semibold uppercase"></span>
            </a>
            <a href="https://x.com/X." class="flex items-center space-x-2 hover:opacity-80 transition-all ">
                <img src="../pics/x.png" alt="Twitter icon" class="w-6 h-6 md:w-8 md:h-8">
                <span class="hidden sm:inline text-sm font-semibold uppercase"></span>
            </a>
            <a href="https://www.instagram.com/" class="flex items-center space-x-2 hover:opacity-80 transition-all ">
                <img src="../pics/insta.png" alt="Instagram icon" class="w-6 h-6 md:w-8 md:h-8">
                <span class="hidden sm:inline text-sm font-semibold uppercase"></span>
            </a>
            <a href="https://wa.me/966562254544" class="flex items-center space-x-2 hover:opacity-80 transition-all ">
                <img src="../pics/whatsappicon.png" alt="whatsapp icon" class="w-6 h-6 md:w-8 md:h-8">
                <span class="hidden sm:inline text-sm font-semibold uppercase"></span>
            </a>
        </div>
    </div>
</footer>

<!-- Modals -->
<div id="contactModal" class="modal anim fixed inset-0 flex items-center justify-center 
                            bg-[#7B9DB4] bg-opacity-60 z-50 rounded-lg m-4 custom-text-shadow">
    <div class="modal-content anim1 max-w-sm w-full text-center p-6 sm:p-8 lg:p-10 m-4" style="background-image: url('../pics/background8.jpeg');">
        <span class="close absolute top-6 right-6 text-2xl text-[#1A4568] cursor-pointer" onclick="closeModal('contactModal')">&times;</span>
        <section class="max-w-3xl mx-auto p-6">
            <h2 class="text-2xl font-bold text-[#1A4568] mb-6 anim1">Contact Us</h2>
<!-- Display Error Messages -->
            <?php if (!empty($footer_error)): ?>
                <div class="px-4 py-3 rounded relative mb-4">
                    <ul class="mt-2 list-disc list-inside">
                    <?php foreach ($footer_error as $err): ?>
                        <li class="error-msg font-bold anim"><?php echo htmlspecialchars($err); ?></li>
                    <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

<!-- Display Success Message -->
            <?php if (!empty($footer_success)): ?>
                <div class="px-4 py-3 rounded relative mb-4">
                    <p class="succ-msg font-bold anim "><?php echo htmlspecialchars($footer_success); ?></p>
                </div>
            <?php endif; ?>
            <p class="text-[#1A4568] text-base leading-relaxed anim2"> 
                <br><a href="https://wa.me/966562254544" class="text-blue-600 hover:underline">Via WhatsApp</a> <br>
                <a href="mailto:support@vitalcare.com" class="text-blue-600 hover:underline"> Or support@vitalcare.com</a>.
            </p>
            <p class="text-[#1A4568] text-base leading-relaxed anim2"> 
                <br>Or fill out the form below and we will get back to you as soon as possible.
            </p>
<!-- form is sent to index.php to avoid conflicts -->
            <form class="anim2 font-bold text-base my-4 space-y-6 anim2" action="index.php" method="post">
                <div>
                    <label for="name" class="block text-sm font-medium text-[#1A4568] mb-2">Name:</label>
                    <input type="text" id="name" name="footer_name"  class="w-full p-3 text-sm  border-[#1A4568] rounded
                    bg-[aliceblue] outline-none focus:ring-2 focus:ring-[#1A4568]" required/>
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-[#1A4568] mb-2">Email:</label>
                    <input type="email" id="email" name="footer_email"  class="w-full p-3 text-sm  border-[#1A4568] rounded
                    bg-[aliceblue] outline-none focus:ring-2 focus:ring-[#1A4568]" required />
                </div>
                <div>
                    <label for="message" class="block text-sm font-medium text-[#1A4568] mb-2">Message:</label>
                    <textarea id="message" rows="4" name="footer_message" class="w-full p-3 text-sm  border-[#1A4568] rounded 
                    bg-[aliceblue] outline-none focus:ring-2 focus:ring-[#1A4568]" required></textarea>
                </div>
                <button type="submit" 
                class="px-6 py-2 text-sm font-bold text-white bg-[#1A4568] rounded hover:bg-[#183141] transition-all">
                    Send
                </button>
            </form>

        </section>
    </div>
</div>
<!-- Privacy Policy Modal -->
<div id="privacyModal" class="modal anim fixed inset-0 flex items-center justify-center 
                            bg-[#7B9DB4] bg-opacity-60 z-50 rounded-lg m-4 custom-text-shadow">
    <div class="modal-content anim1 max-w-sm w-full p-6 sm:p-8 lg:p-10 m-4 bg-white shadow-lg rounded-lg" 
        style="background-image: url('../pics/background8.jpeg');">
        <span class="close absolute top-6 right-6 text-2xl text-[#1A4568] cursor-pointer" onclick="closeModal('privacyModal')">&times;</span>
        <section class="max-w-3xl mx-auto p-6 text-left">
            <h2 class="text-2xl font-bold text-[#1A4568] mb-6 anim1 text-center">Privacy Policy</h2>
            <h3 class="text-base font-semibold text-[#1A4568] mt-4 anim2">1. Information We Collect</h3>
            <ul class="list-disc pl-5 text-[#1A4568] text-base leading-relaxed anim2">
                <li><strong>Personal Information:</strong> Name, email, phone number, insurance provider, and age.</li>
                <li><strong>Appointment Details:</strong> Selected doctor, date, and time of the appointment.</li>
                <li><strong>Technical Data:</strong> IP address, device information, and browsing behavior.</li>
            </ul>
        
            <h3 class="text-base font-semibold text-[#1A4568] mt-4 anim2">2. How We Use Your Information</h3>
            <p class="text-[#1A4568] text-base leading-relaxed anim2">
                We use your information to schedule appointments, improve our services, and ensure security.
            </p>
            
            <h3 class="text-base font-semibold text-[#1A4568] mt-4 anim2">3. Data Protection & Security</h3>
            <p class="text-[#1A4568] text-base leading-relaxed anim2">
                We implement industry-standard security measures to protect your data, but no online service is 100% secure.
            </p>
            
            <h3 class="text-base font-semibold text-[#1A4568] mt-4 anim2">4. Sharing of Information</h3>
            <p class="text-[#1A4568] text-base leading-relaxed anim2">
                We do not sell your information. It is shared only with healthcare providers to 
                facilitate appointments or when required by law.
            </p>
            
            <h3 class="text-base font-semibold text-[#1A4568] mt-4 anim2">5. Your Rights & Choices</h3>
            <p class="text-[#1A4568] text-base leading-relaxed anim2">
                You can access, update, or delete your data at any time. 
            </p>
            
            <h3 class="text-base font-semibold text-[#1A4568] mt-4 anim2">6. Changes to This Policy</h3>
            <p class="text-[#1A4568] text-base leading-relaxed anim2">
                We may update this policy. Any changes will be posted on this page with an updated effective date.
            </p>
            
            <h3 class="text-base font-semibold text-[#1A4568] mt-4 anim2">7. Contact Us</h3>
            <p class="text-[#1A4568] text-base leading-relaxed anim2">
                For privacy-related concerns, contact us at 
                <a href="mailto:support@vitalcare.com" class="text-blue-600 hover:underline">support@vitalcare.com</a>.
            </p>
        </section>
    </div>
</div>

<!-- Terms of Service Modal -->
<div id="termsModal" class="modal anim fixed inset-0 flex items-center justify-center 
                        bg-[#7B9DB4] bg-opacity-60 z-50 rounded-lg m-4 custom-text-shadow">
    <div class="modal-content anim1 max-w-sm w-full p-6 sm:p-8 lg:p-10 m-4 bg-white shadow-lg rounded-lg" 
        style="background-image: url('../pics/background8.jpeg');">
        <span class="close absolute top-6 right-6 text-2xl text-[#1A4568] cursor-pointer" onclick="closeModal('termsModal')">&times;</span>
        
        <section class="max-w-3xl mx-auto p-6 text-left">
            <h2 class="text-2xl font-bold text-[#1A4568] mb-6 anim1 text-center">Terms of Service</h2>
            
            <p class="text-[#1A4568] text-base leading-relaxed anim2">
                By using our doctor appointment booking service, you agree to the following terms.
            </p>
            
            <h3 class="text-base font-semibold text-[#1A4568] mt-4 anim2">1. Use of Service</h3>
            <p class="text-[#1A4568] text-base leading-relaxed anim2">
                This platform allows patients to book medical appointments. You agree to use it responsibly and 
                provide accurate information.
            </p>
            
            <h3 class="text-base font-semibold text-[#1A4568] mt-4 anim2">2. Appointment Booking & Cancellations</h3>
            <p class="text-[#1A4568] text-base leading-relaxed anim2">
                You must provide valid details when booking. Cancellations should be made in advance.
                Failure to do so may result in a cancellation fee.
            </p>
            
            <h3 class="text-base font-semibold text-[#1A4568] mt-4 anim2">3. User Responsibilities</h3>
            <ul class="list-disc pl-5 text-[#1A4568] text-base leading-relaxed anim2">
                <li>You are responsible for keeping your account secure.</li>
                <li>Misuse of the platform may result in suspension.</li>
                <li>Providing false information may lead to account termination.</li>
            </ul>
            
            <h3 class="text-base font-semibold text-[#1A4568] mt-4 anim2">4. Privacy & Data Usage</h3>
            <p class="text-[#1A4568] text-base leading-relaxed anim2">
                Your personal data is handled as per our Privacy Policy.
            </p>
            
            <h3 class="text-base font-semibold text-[#1A4568] mt-4 anim2">5. Changes to Terms</h3>
            <p class="text-[#1A4568] text-base leading-relaxed anim2">
                We may update these terms periodically. Continued use of our service implies acceptance of any changes.
            </p>
            
            <h3 class="text-base font-semibold text-[#1A4568] mt-4 anim2">6. Contact Us</h3>
            <p class="text-[#1A4568] text-base leading-relaxed anim2">
                If you have any questions about these terms, contact us at 
                <a href="mailto:support@vitalcare.com" class="text-blue-600 hover:underline">support@vitalcare.com</a>.
            </p>
        </section>
    </div>
</div>
<script>
    function openModal(id) {
        document.getElementById(id).style.display = "block";
    }
    function closeModal(id) {
        document.getElementById(id).style.display = "none";
    }

    // Check if PHP has set a success or error message
    <?php if (!empty($footer_success) || !empty($footer_error)): ?>
        window.addEventListener('DOMContentLoaded', function () {
            openModal('contactModal');
        });
    <?php endif; ?>
</script>



