<?php

include '../connection.php';
include 'security.php';

$clar_id = $_POST['clar_id'];

$qry = "SELECT Problem, Ques, _user, Replied FROM Uesr_Clarifi_Comment WHERE Cmnt_Id = {$clar_id};";
$res = mysqli_query($con, $qry);

while ($row = mysqli_fetch_assoc($res)) {
	$Problem = $row['Problem'];
	$Ques = $row['Ques'];
	$_user = $row['_user'];
	$Replied = $row['Replied'];
}
?>

<span class="h5">
	<span class="text-danger"><?php echo $Problem.'. '; ?></span>
	<?php echo nl2br($Ques); ?>
</span>
<span>
	@<a href="../profile.php?user=<?php echo $_user; ?>"><?php echo $_user; ?></a>
</span>

<?php
if ($Replied == 'yes') {
	$qry = "SELECT Ans FROM Uesr_Clarifi_Reply WHERE Against = {$clar_id};";
	$res = mysqli_query($con, $qry);
	echo '<div class="mt-3">&nbsp&nbsp&nbsp&nbsp' . nl2br(mysqli_fetch_assoc($res)['Ans']) . '</div>';
}
?>