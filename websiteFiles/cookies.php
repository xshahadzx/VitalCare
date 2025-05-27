<?php
// Start the session if it's not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require("../phpFiles/connect.php");
$cookie_error = [];
// Check if the user is signed in
if (isset($_SESSION["is_logged_in"]) && $_SESSION["is_logged_in"]) {
    $email = $_SESSION["email"]; 
// ----------------------------------- ADMIN CHECK FIRST ----------------------------------- 
    if (isset($_SESSION["is_admin"]) && $_SESSION["is_admin"]) {
        $admin_id = $_SESSION['id'];
        // Query to fetch admin details
        $stmt = $conn->prepare("SELECT name, phone_number FROM admins WHERE admin_id = ?");
        if (!$stmt) {
            $cookie_error[] = "Error preparing statement: " . $conn->error;
        } else {
            $stmt->bind_param("i", $admin_id);
            $stmt->execute();
            $res = $stmt->get_result();

            if ($res->num_rows === 1) {
                $user = $res->fetch_assoc();
                // the cookies will expire in 2 hours
                setcookie("admin_name", $user["name"] ?? '', time() + 7200, "/");
                setcookie("admin_number", $user["phone_number"] ?? '', time() + 7200, "/");

                // Clear doctor cookies
                foreach (["doc_name", "doc_phone_number", "doc_specialty", "doc_experience_years", "doc_location", "hospital_name"] as $cookie) {
                    setcookie($cookie, "", time() - 3600, "/");
                }
                // Clear patient cookies
                foreach (["user_name", "user_phone_number", "user_age"] as $cookie) {
                    setcookie($cookie, "", time() - 3600, "/");
                }
            }
        }
    } //end if admin check 
// ----------------------------------- PATIENT CHECK ----------------------------------- 
    elseif (!$_SESSION['is_admin']) {  
        $stmt = $conn->prepare("SELECT name, phone_number, age FROM patients WHERE email = ?");
        if (!$stmt) {
            $cookie_error[] = "Error preparing statement: " . $conn->error;
        } else {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $res = $stmt->get_result();

            if ($res->num_rows === 1) {
                $user = $res->fetch_assoc();
                // the cookies will expire in 1 hour
                setcookie("user_name", $user["name"] ?? '', time() + 3600, "/");
                setcookie("user_phone_number", $user["phone_number"] ?? '', time() + 3600, "/");
                setcookie("user_age", $user["age"] ?? '', time() + 3600, "/");

                // Clear doctor cookies
                foreach (["doc_name", "doc_phone_number", "doc_specialty", "doc_experience_years", "doc_location", "hospital_name"] as $cookie) {
                    setcookie($cookie, "", time() - 3600, "/");
                }
                // Clear admin cookies
                foreach (["admin_name", "admin_number"] as $cookie) {
                    setcookie($cookie, "", time() - 3600, "/");
                }
            } //end if patient check
// ----------------------------------- DOCTOR CHECK ----------------------------------- 
            else {
                    $stmt = $conn->prepare("SELECT name, phone_number, specialty, experience_years, location FROM doctors WHERE email = ?");
                    if (!$stmt) {
                        $cookie_error[] = "Error preparing statement: " . $conn->error;
                    } else {
                        $stmt->bind_param("s", $email);
                        $stmt->execute();
                        $res = $stmt->get_result();
                        // Query to fetch hospital name
                        $doctor_id = $_SESSION['id'];
                        $stmt_hos = $conn->prepare("SELECT h.name FROM hospital_doctors hd 
                                                    JOIN hospitals h ON hd.hospital_id = h.hospital_id WHERE hd.doctor_id = ?");
                        $stmt_hos->bind_param("i", $doctor_id);
                        $stmt_hos->execute();
                        $hospital = $stmt_hos->get_result()->fetch_row()[0] ?? null;

                        if ($res->num_rows === 1 && isset($hospital)) {
                            $user = $res->fetch_assoc();
                            // Set cookies for doctor details : the cookies will expire in 1 hour
                            setcookie("hospital_name", $hospital ?? '', time() + 3600, "/");
                            setcookie("doc_name", $user["name"] ?? '', time() + 3600, "/");
                            setcookie("doc_phone_number", $user["phone_number"] ?? '', time() + 3600, "/");
                            setcookie("doc_specialty", $user["specialty"] ?? '', time() + 3600, "/");
                            setcookie("doc_experience_years", $user["experience_years"] ?? '', time() + 3600, "/");
                            setcookie("doc_location", $user["location"] ?? '', time() + 3600, "/");

                            // Clear patient cookies
                            foreach (["user_name", "user_phone_number", "user_age"] as $cookie) {
                                setcookie($cookie, "", time() - 3600, "/");
                            }
                            // Clear admin cookies
                            foreach (["admin_name", "admin_number"] as $cookie) {
                                setcookie($cookie, "", time() - 3600, "/");
                            }
                        }
                    }
            } //end if doctor check
        }
    } 
} //end if user is logged in
else {
    // Redirect to login page if not logged in
    header("Location: signIn.php");
    exit();
}
// ----------------------------------- ERROR DISPLAY ----------------------------------- 
if (!empty($cookie_error)): ?>
    <div class="px-4 py-3 rounded relative mb-4">
        <ul class="mt-2 list-disc list-inside">
            <?php foreach ($cookie_error as $msg): ?>
                <li class="error-msg font-bold anim"><?php echo htmlspecialchars($msg); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>
