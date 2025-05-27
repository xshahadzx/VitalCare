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
//delete the comments (hide it) from the database
require_once("../phpFiles/connect.php");
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $errors = [];  
    $success = "";
    if (isset($_POST['delete']) && $_POST['delete'] === "delete") {
        $comment_id = $_POST["comment_id"] ?? null;
        if ($comment_id) {
            $delete = $conn->prepare("UPDATE comments set hide='yes' WHERE comment_id = ?");
            $delete->bind_param('i',$comment_id);
            $delete->execute();
            if ($delete->execute()) {
                $success = "Comment with ID " . htmlspecialchars($comment_id) . " has been successfully deleted.";
            } else {
                $errors[] = "ops! Something went wrong. Please try again later.";
            }
        } else {
            $errors[] = "Invalid comment ID.";
        }
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
<!-- -----------------------------------------------------------------------Admin dashboard page : comments----------------------------------------------------------------------- -->
<!--nav bar-->
<?php require("nav.php"); ?>
<!-- Dashboard -->
<div class="w-full bg-center bg-cover rounded-lg p-6 shadow-lg anim" style="background-image: url('../pics/background8.jpeg');">
<!-----------------------------------------------------------------Section: View appointments-------------------------------------------------------------- -->
<!-- Header Section -->
    <header class="text-center mb-6 text-[#1a4568]">
        <h1 class="text-3xl font-bold leading-tight mb-4 anim1">Comments</h1>
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
                    window.location.href = "#"; 
                }, 1000); 
            </script>   
        </div>
    <?php endif; ?>
        <section class="mb-10">
<!-- Section: View comments -->
        <h3 class="text-2xl font-bold text-[#1a4568] my-4 anim1">All Comments</h3>                   
        <table class="appointments-table w-full border-separate border-spacing-2 border border-[#1a4568] shadow-md rounded-lg anim2">
            <thead class="bg-[#24598a] text-white">
                <tr>
                    <th class="font-bold text-left">Comment ID</th>
                    <th class="font-bold text-left">Comment</th>
                    <th class="font-bold text-left">Rating Value</th>
                    <th class="font-bold text-left">Rating Date</th>
                    <th class="font-bold text-left">Patient Name</th>
                    <th class="font-bold text-left">Patient ID</th>
                    <th class="font-bold text-left">Doctor Name</th>
                    <th class="font-bold text-left">Doctor ID</th>
                    <th class="font-bold text-left">Action</th>
                </tr>
            </thead>
            <tbody>
        <?php 
                $query = "SELECT c.comment_id,p.name as patient_name,c.patient_id,d.name as doctor_name, c.doctor_id,c.comment,c.rating_value,c.rating_date FROM comments c 
                JOIN doctors d ON d.doctor_id =c.doctor_id JOIN patients p ON p.patient_id=c.patient_id 
                WHERE hide='no' order by c.rating_date desc";
                $stmt = $conn->query($query);
                $comments = $stmt->fetch_all(MYSQLI_ASSOC);
                if (empty($comments)) {
                    echo '<tr><td colspan="4" class="font-bold border border-[#1a4568] text-center">No Comment Records Found</td></tr>';
                }
                // Loop through the comments and display them in the table
                foreach ($comments as $row) {
                    $formatted_date = date("n/j/Y", strtotime($row['rating_date']));
                    echo '<tr class="bg-[#ebf5fa] hover:bg-[#d3e8f1]">';
                    echo '<td class="font-bold border border-[#1a4568]">' . $row['comment_id'] . '</td>';
                    echo '<td class="font-bold border border-[#1a4568]">' . ucfirst(htmlspecialchars($row['comment'])) . '</td>';
                    echo '<td class="font-bold border border-[#1a4568]">' . $row['rating_value'] . '</td>';
                    echo '<td class="font-bold border border-[#1a4568]">' . $formatted_date . '</td>';
                    echo '<td class="font-bold border border-[#1a4568]">' . ucfirst(htmlspecialchars($row['patient_name'])) . '</td>';
                    echo '<td class="font-bold border border-[#1a4568]">' . $row['patient_id'] . '</td>';
                    echo '<td class="font-bold border border-[#1a4568]">'.'Dr. ' . ucfirst(htmlspecialchars($row['doctor_name'])) . '</td>';
                    echo '<td class="font-bold border border-[#1a4568]">' . $row['doctor_id'] . '</td>';
                    echo '<td class="font-bold border border-[#1a4568]">';
        ?>
                    <form action="#" method="post" onsubmit="return checker();">
                    <button type="submit" name="delete" value="delete" class="cancel-btn py-2 px-4 rounded-lg bg-red-500 text-white hover:bg-red-600 uppercase">
                                    delete Comment
                    </button>
                    <input type="hidden" name="comment_id" value="<?php echo htmlspecialchars($row['comment_id']); ?>">
                    <script>
                    function checker() {
                        return confirm("Are you sure you want to permanently delete this comment?");
                    }
                    </script>
                    </form>
        <?php
                    echo '</td>';
                    echo '</tr>';
                }
        ?>
            </tbody>
        </table>
<!-- Section: View comments -->
<h3 class="text-2xl font-bold text-[#1a4568] my-4 anim1">Deleted Comments</h3>                   
        <table class="appointments-table w-full border-separate border-spacing-2 border border-[#1a4568] shadow-md rounded-lg anim2">
            <thead class="bg-[#24598a] text-white">
                <tr>
                    <th class="font-bold text-left">Comment ID</th>
                    <th class="font-bold text-left">Comment</th>
                    <th class="font-bold text-left">Patient Name</th>
                    <th class="font-bold text-left">Patient ID</th>
                    <th class="font-bold text-left">Doctor Name</th>
                    <th class="font-bold text-left">Doctor ID</th>
                </tr>
            </thead>
            <tbody>
        <?php 
                $query = "SELECT c.comment_id,p.name as patient_name,c.patient_id,d.name as doctor_name,
                c.doctor_id,c.comment FROM comments c 
                JOIN doctors d ON d.doctor_id =c.doctor_id JOIN patients p ON p.patient_id=c.patient_id 
                WHERE hide='yes' order by c.rating_date desc";
                $stmt = $conn->query($query);
                $comments = $stmt->fetch_all(MYSQLI_ASSOC);

                if (empty($comments)) {
                    echo '<tr><td colspan="4" class="font-bold border border-[#1a4568] text-center">No comments in the database</td></tr>';
                }
                // Loop through the comments and display them in the table
                foreach ($comments as $row) {
                    echo '<tr class="bg-[#ebf5fa] hover:bg-[#d3e8f1]">';
                    echo '<td class="font-bold border border-[#1a4568]">' . $row['comment_id'] . '</td>';
                    echo '<td class="font-bold border border-[#1a4568]">' . ucfirst(htmlspecialchars($row['comment'])) . '</td>';
                    echo '<td class="font-bold border border-[#1a4568]">' . ucfirst(htmlspecialchars($row['patient_name'])) . '</td>';
                    echo '<td class="font-bold border border-[#1a4568]">' . $row['patient_id'] . '</td>';
                    echo '<td class="font-bold border border-[#1a4568]">'."Dr. " . ucfirst(htmlspecialchars($row['doctor_name'])) . '</td>';
                    echo '<td class="font-bold border border-[#1a4568]">' . $row['doctor_id'] . '</td>';
                    echo '</tr>';
                }
        ?>
            </tbody>
        </table>
        </section>
    </div>
<!-- Footer -->
<?php require("footer.php"); ?>
</body>
</html>
