<?php

/**
 * Adds a new task to the task list
 * 
 * @param string $task_name The name of the task to add.
 * @return bool True on success, false on failure.
 */
function addTask(string $task_name): bool {
    $file = __DIR__ . '/tasks.txt';
    $tasks = getAllTasks();
    foreach ($tasks as $task) {
        if (strcasecmp(trim($task['name']), trim($task_name)) === 0) {
            return false; // Duplicate task
        }
    }
    $id = uniqid();
    $line = $id . '|' . trim($task_name) . '|0' . PHP_EOL;
    return file_put_contents($file, $line, FILE_APPEND | LOCK_EX) !== false;
}

/**
 * Retrieves all tasks from the tasks.txt file
 * 
 * @return array Array of tasks. -- Format [ id, name, completed ]
 */
function getAllTasks(): array {
    $file = __DIR__ . '/tasks.txt';
    if (!file_exists($file)) return [];
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $tasks = [];
    foreach ($lines as $line) {
        list($id, $name, $completed) = explode('|', $line);
        $tasks[] = ['id' => $id, 'name' => $name, 'completed' => $completed == '1'];
    }
    return $tasks;
}

/**
 * Marks a task as completed or uncompleted
 * 
 * @param string $task_id The ID of the task to mark.
 * @param bool $is_completed True to mark as completed, false to mark as uncompleted.
 * @return bool True on success, false on failure
 */
function markTaskAsCompleted(string $task_id, bool $is_completed): bool {
    $file = __DIR__ . '/tasks.txt';
    $tasks = getAllTasks();
    foreach ($tasks as &$task) {
        if ($task['id'] === $task_id) {
            $task['completed'] = $is_completed;
        }
    }
    $data = "";
    foreach ($tasks as $task) {
        $data .= $task['id'] . '|' . $task['name'] . '|' . ($task['completed'] ? '1' : '0') . PHP_EOL;
    }
    return file_put_contents($file, $data, LOCK_EX) !== false;
}

/**
 * Deletes a task from the task list
 * 
 * @param string $task_id The ID of the task to delete.
 * @return bool True on success, false on failure.
 */
function deleteTask(string $task_id): bool {
    $file = __DIR__ . '/tasks.txt';
    $tasks = getAllTasks();
    $tasks = array_filter($tasks, fn($task) => $task['id'] !== $task_id);
    $data = "";
    foreach ($tasks as $task) {
        $data .= $task['id'] . '|' . $task['name'] . '|' . ($task['completed'] ? '1' : '0') . PHP_EOL;
    }
    return file_put_contents($file, $data, LOCK_EX) !== false;
}

/**
 * Generates a 6-digit verification code
 * 
 * @return string The generated verification code.
 */
function generateVerificationCode(): string {
    return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}

/**
 * Subscribe an email address to task notifications.
 *
 * @param string $email The email address to subscribe.
 * @return bool True if verification email sent successfully, false otherwise.
 */
function subscribeEmail(string $email): bool {
    $file = __DIR__ . '/pending_subscriptions.txt';
    $code = generateVerificationCode();
    $entry = $email . '|' . $code . PHP_EOL;
    file_put_contents($file, $entry, FILE_APPEND | LOCK_EX);
    $link = 'http://' . $_SERVER['HTTP_HOST'] . '/verify.php?email=' . urlencode($email) . '&code=' . $code;
    $subject = 'Verify subscription to Task Planner';
    $body = '<p>Click the link below to verify your subscription to Task Planner:</p>' .
            '<p><a id="verification-link" href="' . $link . '">Verify Subscription</a></p>';
    $headers = "From: no-reply@example.com\r\nContent-Type: text/html";
    return mail($email, $subject, $body, $headers);
}

/**
 * Verifies an email subscription
 * 
 * @param string $email The email address to verify.
 * @param string $code The verification code.
 * @return bool True on success, false on failure.
 */
function verifySubscription(string $email, string $code): bool {
    $pending_file = __DIR__ . '/pending_subscriptions.txt';
    $subscribers_file = __DIR__ . '/subscribers.txt';
    $lines = file($pending_file, FILE_IGNORE_NEW_LINES);
    $verified = false;
    $new_lines = [];
    foreach ($lines as $line) {
        list($e, $c) = explode('|', $line);
        if ($e === $email && $c === $code) {
            $verified = true;
            file_put_contents($subscribers_file, $email . PHP_EOL, FILE_APPEND | LOCK_EX);
        } else {
            $new_lines[] = $line;
        }
    }
    file_put_contents($pending_file, implode(PHP_EOL, $new_lines) . PHP_EOL);
    return $verified;
}

/**
 * Unsubscribes an email from the subscribers list
 * 
 * @param string $email The email address to unsubscribe.
 * @return bool True on success, false on failure.
 */
function unsubscribeEmail(string $email): bool {
    $subscribers_file = __DIR__ . '/subscribers.txt';
    $lines = file($subscribers_file, FILE_IGNORE_NEW_LINES);
    $lines = array_filter($lines, fn($line) => trim($line) !== trim($email));
    return file_put_contents($subscribers_file, implode(PHP_EOL, $lines) . PHP_EOL) !== false;
}

/**
 * Sends task reminders to all subscribers
 */
function sendTaskReminders(): void {
    $subscribers_file = __DIR__ . '/subscribers.txt';
    if (!file_exists($subscribers_file)) return;
    $emails = file($subscribers_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $tasks = getAllTasks();
    $pending = array_filter($tasks, fn($task) => !$task['completed']);
    foreach ($emails as $email) {
        sendTaskEmail($email, $pending);
    }
}

/**
 * Sends a task reminder email to a subscriber with pending tasks.
 *
 * @param string $email The email address of the subscriber.
 * @param array $pending_tasks Array of pending tasks to include in the email.
 * @return bool True if email was sent successfully, false otherwise.
 */
function sendTaskEmail(string $email, array $pending_tasks): bool {
    $subject = 'Task Planner - Pending Tasks Reminder';
    $body = '<h2>Pending Tasks Reminder</h2>';
    $body .= '<p>Here are the current pending tasks:</p><ul>';
    foreach ($pending_tasks as $task) {
        $body .= '<li>' . htmlspecialchars($task['name']) . '</li>';
    }
    $body .= '</ul>';
    $unsubscribe_link = 'http://' . $_SERVER['HTTP_HOST'] . '/unsubscribe.php?email=' . urlencode($email);
    $body .= '<p><a id="unsubscribe-link" href="' . $unsubscribe_link . '">Unsubscribe from notifications</a></p>';
    $headers = "From: no-reply@example.com\r\nContent-Type: text/html";
    return mail($email, $subject, $body, $headers);
}
