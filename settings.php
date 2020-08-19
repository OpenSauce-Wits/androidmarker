<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This file defines the admin settings for this plugin
 * @package   assignfeedback_androidmarker
 * @author Thando Peter (thandopeter8@gmail.com)
 * @author ... please add your names
 */

 // This is the setting for if the plugin should be enabled by default when creating a new assignment
 // The answer is no (0).
 $settings->add(new admin_setting_configcheckbox("assignfeedback_androidmarker/default",
     new lang_string("default", "assignfeedback_androidmarker"),
     new lang_string("default_help", "assignfeedback_androidmarker"), 1));

 // This is the second setting asking for the website where the scripts will be marked
 // We will not be using this code but we can adapt it.
 $settings->add(new admin_setting_configtext("assignfeedback_androidmarker/wsbase",
     new lang_string("wsbase", "assignfeedback_androidmarker"),
     new lang_string("wsbase_help", "assignfeedback_androidmarker"), "0.0.0.0:9999"));

 // Database Host Server
 $settings->add(new admin_setting_configtext("assignfeedback_androidmarker/database_host",
     new lang_string("database_host", "assignfeedback_androidmarker"),
     new lang_string("database_host_help", "assignfeedback_androidmarker"), "0.0.0.0"));

 // Database moodle database
 $settings->add(new admin_setting_configtext("assignfeedback_androidmarker/database_database",
     new lang_string("database_database", "assignfeedback_androidmarker"),
     new lang_string("database_database_help", "assignfeedback_androidmarker"), "moodle"));

 // Database User
 $settings->add(new admin_setting_configtext("assignfeedback_androidmarker/database_user",
     new lang_string("database_user", "assignfeedback_androidmarker"),
     new lang_string("database_user_help", "assignfeedback_androidmarker"), ""));

 // Database Password
 $settings->add(new admin_setting_configtext("assignfeedback_androidmarker/database_password",
     new lang_string("database_password", "assignfeedback_androidmarker"),
     new lang_string("database_password_help", "assignfeedback_androidmarker"), ""));

 // SDK PATH
 $settings->add(new admin_setting_configtext("assignfeedback_androidmarker/sdk_path",
     new lang_string("sdk_path", "assignfeedback_androidmarker"),
     new lang_string("sdk_path_help", "assignfeedback_androidmarker"), ""));

 // Moodledata PATH
 $settings->add(new admin_setting_configtext("assignfeedback_androidmarker/moodledata",
     new lang_string("moodledata", "assignfeedback_androidmarker"),
     new lang_string("moodledata_help", "assignfeedback_androidmarker"), ""));
