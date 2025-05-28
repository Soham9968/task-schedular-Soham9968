<?php
require_once 'functions.php';

$success = false;
$message = "";

if (isset($_GET['email']) && isset($_GET['code'])) {
	$email = trim($_GET['email']);
	$code = trim($_GET['code']);
	$success = verifySubscription($email, $code);
	$message = $success ? "Subscription verified successfully." : "Verification failed. Code may be invalid or expired.";
}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Verify Subscription</title>
</head>
<body>
	<h2 id="verification-heading">Subscription Verification</h2>
	<p><?= htmlspecialchars($message) ?></p>
</body>
</html>
