#!/bin/bash

# This file is not needed

#mysql -u thando -p << EOF
#use moodle
#DROP TABLE mdl_androidmarker_testresult;
#DROP TABLE mdl_assignfeedback_androidmarker;
#DROP TABLE mdl_androidmarker_testfailure; # You might still have this table. If so delete it
#DROP TABLE mdl_androidmarker_errors;
#exit
#EOF
# well moodle deletes the tables for you now so you don't need this

#echo "Tables Cleared!"

sudo rm -rf /var/www/html/moodle/mod/assign/feedback/androidmarker
sudo cp -R /home/thando/Desktop/Android_Marker_Plugin/androidmarker /var/www/html/moodle/mod/assign/feedback

# sudo php /var/www/html/moodle/admin/tool/task/cli/schedule_task.php --execute='\core\task\context_cleanup_task' --showdebugging

# In your favorite editor, open lib/classes/task/context_cleanup_task.php and edit the line that reads
#\context_helper::build_all_paths(false); to true

# sudo gedit /var/www/html/moodle/lib/classes/task/context_cleanup_task.php

# Run the \core\task\context_cleanup_task scheduled task again, as you did in Step A. Your errors should now be gone.

# sudo php /var/www/html/moodle/admin/tool/task/cli/schedule_task.php --execute='\core\task\context_cleanup_task' --showdebugging

# sudo gedit /var/www/html/moodle/lib/classes/task/context_cleanup_task.php
