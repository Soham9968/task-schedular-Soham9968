#!/bin/bash

# Path to your PHP binary
PHP_BIN="/usr/bin/php"

# Path to your cron.php file (update this to your project path)
CRON_FILE="/path/to/your/project/cron.php"

# Add to crontab (runs every day at 9 AM)
(crontab -l 2>/dev/null; echo "0 9 * * * $PHP_BIN $CRON_FILE") | crontab -

echo "Cron job installed to send task reminders daily at 9 AM."
