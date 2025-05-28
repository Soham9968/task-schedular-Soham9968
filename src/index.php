<?php
require_once 'functions.php';

// Handle Add Task form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	if (isset($_POST['task-name'])) {
		$task_name = trim($_POST['task-name']);
		addTask($task_name);
	}
	if (isset($_POST['email'])) {
		$email = trim($_POST['email']);
		subscribeEmail($email);
	}
	if (isset($_POST['mark-task']) && isset($_POST['task-id'])) {
		markTaskAsCompleted($_POST['task-id'], $_POST['mark-task'] === '1');
	}
	if (isset($_POST['delete-task']) && isset($_POST['task-id'])) {
		deleteTask($_POST['task-id']);
	}
}

$tasks = getAllTasks();
?>
<!DOCTYPE html>
<html>

<head>
	<title>Task Planner</title>
</head>

<body>

	<h2>Add New Task</h2>
	<form method="POST" action="">
		<input type="text" name="task-name" id="task-name" placeholder="Enter new task" required>
		<button type="submit" id="add-task">Add Task</button>
	</form>

	<h2>Task List</h2>
	<ul id="tasks-list">
		<?php foreach ($tasks as $task): ?>
			<li class="task-item">
				<form method="POST" action="" style="display:inline;">
					<input type="hidden" name="task-id" value="<?= $task['id'] ?>">
					<input type="checkbox" class="task-status" name="mark-task" value="1"
						<?= $task['completed'] ? 'checked' : '' ?> onchange="this.form.submit();">
				</form>
				<span><?= htmlspecialchars($task['name']) ?></span>
				<form method="POST" action="" style="display:inline;">
					<input type="hidden" name="task-id" value="<?= $task['id'] ?>">
					<button type="submit" name="delete-task" class="delete-task" value="1">Delete</button>
				</form>
			</li>
		<?php endforeach; ?>
	</ul>

	<h2>Subscribe for Reminders</h2>
	<form method="POST" action="">
		<input type="email" name="email" id="email" placeholder="Enter email" required>
		<button type="submit" id="submit-email">Subscribe</button>
	</form>

</body>
</html>
