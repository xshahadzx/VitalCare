<?php 
// <!-- -----------------------------------------------------------------------reset appointmnets---------------------------------------------------------------------- -->
// here it should automatically reset every week all old appointments should be either cancel or completed fix it later 
require("../phpFiles/connect.php");
date_default_timezone_set('Asia/Riyadh');
$currentDate = date('Y-m-d');
$stmt=$conn->prepare("UPDATE appointments 
                            SET STATUS = 'completed'
                            WHERE appointment_date < ?");
$stmt->bind_param('s',$currentDate);
if ($stmt->execute()) {
    echo "all old Appointments status has been updated successfully!";
} else {
    echo "Error updating appointments: " . $stmt->error;
}
$stmt->close();
$conn->close();
