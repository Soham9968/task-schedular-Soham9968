<?php
require_once 'functions.php';

/**
 * CRON Trigger File
 *
 * This file is meant to be scheduled to run at regular intervals.
 * It will send pending task reminder emails to all subscribed users.
 */

// Call the reminder sender function
sendTaskReminders();
