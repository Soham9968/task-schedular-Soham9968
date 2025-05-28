<?php
require_once 'functions.php';

$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
	$email = trim($_POST['email']);
	$success = unsubscribeEmail($email);
	$message = $success ? "You have been unsubscribed." : "Unsubscription failed. Email not found.";
}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Unsubscribe</title>
</head>
<body>
	<h2 id="unsubscription-heading">Unsubscribe from Task Updates</h2>
	<form method="POST" action="">
		<input type="email" name="email" placeholder="Enter your email" required>
		<button type="submit">Unsubscribe</button>
	</form>
	<?php if (!empty($message)): ?>
		<p><?= htmlspecialchars($message) ?></p>
	<?php endif; ?>
</body>
</html>
