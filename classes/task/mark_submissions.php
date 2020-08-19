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
 * A scheduled task.
 *
 * @package assignfeedback_androidmarker
 * @author Thando Peter (thandopeter8@gmail.com)
 */
namespace assignfeedback_androidmarker\task;

use core\task\scheduled_task;
//use assignfeedback_editpdf\document_services;
//use assignfeedback_editpdf\combined_document;
use context_module;
use assign;

/**
 * Task to find submission that have to be marked and send them to a server.
 */
class mark_submissions extends scheduled_task {
    // Database table names.
    const TABLE_ASSIGNFEEDBACK_ANDROIDMARKER = "assignfeedback_androidmarker";
    const TABLE_ANDROIDMARKER_TESTRESULT = "androidmarker_testresult";
    const TABLE_ANDROIDMARKER_COMPILATIONERROR = "androidmarker_errors";

    const COMPONENT_NAME = "assignfeedback_androidmarker";

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('marksubmissions', 'assignfeedback_androidmarker');
    }

    /**
     * Do the job.
     * Throw exceptions on errors (the job will be retried).
     */
    public function execute() {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/mod/assign/locallib.php');

        /*
        * @Check if there are server to mark projects without
        *
        */

        /*
        * @Look for Lecturer submissions first
        * @Loop through all created assignfeedback_androidmarker assignments
        *
        * @ When an assignment is created an entry is made into the TABLE_ASSIGNFEEDBACK_ANDROIDMARKER
        * table. This is the lecturer's submission with a priority of -1. Each assignment only has one
        * of these.
        *
        * @ If one of these assignments are not marked then we can not mark the rest of the student submissions
        *
        */

        $LecturerSubmissions = $DB->get_records(self::TABLE_ASSIGNFEEDBACK_ANDROIDMARKER, array("priority" => -1));
        foreach ($LecturerSubmissions as $LecturerSubmission) {
          if($LecturerSubmission->status == get_string('pending', self::COMPONENT_NAME)){

          }
        }

        /* THis is how you get student assignment submissions with just the assignment-id and userid
        $assignment = self::get_assignment_from_param($assignment);

        // Capability checks.
        if (!$assignment->can_view_submission($userid)) {
            print_error('nopermission');
        }

        $grade = $assignment->get_user_grade($userid, true, $attemptnumber);
        if ($assignment->get_instance()->teamsubmission) {
            $submission = $assignment->get_group_submission($userid, 0, false, $attemptnumber);
        } else {
            $submission = $assignment->get_user_submission($userid, false, $attemptnumber);
        }

        $contextid = $assignment->get_context()->id;
        $component = 'assignfeedback_editpdf';
        $filearea = self::COMBINED_PDF_FILEAREA;
        $partialfilearea = self::PARTIAL_PDF_FILEAREA;
        $itemid = $grade->id;
        $filepath = '/';
        $filename = self::COMBINED_PDF_FILENAME;
        $fs = get_file_storage();

        $partialpdf = $fs->get_file($contextid, $component, $partialfilearea, $itemid, $filepath, $filename);
        if (!empty($partialpdf)) {
            $combinedpdf = $partialpdf; // We do not need this
        } else {
            $combinedpdf = $fs->get_file($contextid, $component, $filearea, $itemid, $filepath, $filename);
        }
        */

        $wsbaseaddress = get_config(self::COMPONENT_NAME, "wsbase");
        $s = curl_init();

        //curl_setopt($s,CURLOPT_URL, $wsbaseaddress.DIRECTORY_SEPARATOR.$submissionPath.DIRECTORY_SEPARATOR."Mark.php");
        curl_setopt($s,CURLOPT_URL, $wsbaseaddress.DIRECTORY_SEPARATOR."Mark.php");
        // Enable the post response.
        curl_setopt($s, CURLOPT_POST, true);

        $dbhost = get_config(self::COMPONENT_NAME, "database_host");
        $dbuser = get_config(self::COMPONENT_NAME, "database_user");
        $dbpass = get_config(self::COMPONENT_NAME, "database_password");
        $db = get_config(self::COMPONENT_NAME, "database_database");
        $sdk = get_config(self::COMPONENT_NAME, "sdk_path");

        // Setup request to send json via POST
      /*  $data = array('UserID' => $userid,
          'AssignmentID' => $this->assignment->get_instance()->id,
          'assignfeedback_androidmarker' => self::TABLE_ASSIGNFEEDBACK_ANDROIDMARKER,
          'androidmarker_testresult' => self::TABLE_ANDROIDMARKER_TESTRESULT,
          'androidmarker_errors' => self::TABLE_ANDROIDMARKER_COMPILATIONERROR,
          'dbhost' => $dbhost,
          'dbuser' => $dbuser,
          'dbpass' => $dbpass,
          'db' => $db,
          'sdk' => $sdk);*/

        // Attach encoded JSON string to the POST fields
        //curl_setopt($s, CURLOPT_POSTFIELDS, json_encode($data));

        // Set the content type to application/json
        //curl_setopt($s, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_exec($s);
        curl_close($s);

        /*
        * @Search for all the student submissions
        * @If they have not been added to TABLE_ASSIGNFEEDBACK_ANDROIDMARKER then
        * add them.
        */








        // editpdf stuff
/*
        $records = $DB->get_records('assignfeedback_editpdf_queue');

        $assignmentcache = array();

        $conversionattemptlimit = !empty($CFG->conversionattemptlimit) ? $CFG->conversionattemptlimit : 3;
        foreach ($records as $record) {
            $submissionid = $record->submissionid;
            $submission = $DB->get_record('assign_submission', array('id' => $submissionid), '*', IGNORE_MISSING);
            if (!$submission || $record->attemptedconversions >= $conversionattemptlimit) {
                // Submission no longer exists; or we've exceeded the conversion attempt limit.
                $DB->delete_records('assignfeedback_editpdf_queue', array('id' => $record->id));
                continue;
            }

            // Record that we're attempting the conversion ahead of time.
            // We can't do this afterwards as its possible for the conversion process to crash the script entirely.
            $DB->set_field('assignfeedback_editpdf_queue', 'attemptedconversions',
                    $record->attemptedconversions + 1, ['id' => $record->id]);

            $assignmentid = $submission->assignment;
            $attemptnumber = $record->submissionattempt;

            if (empty($assignmentcache[$assignmentid])) {
                $cm = get_coursemodule_from_instance('assign', $assignmentid, 0, false, MUST_EXIST);
                $context = context_module::instance($cm->id);

                $assignment = new assign($context, null, null);
                $assignmentcache[$assignmentid] = $assignment;
            } else {
                $assignment = $assignmentcache[$assignmentid];
            }

            $users = array();
            if ($submission->userid) {
                array_push($users, $submission->userid);
            } else {
                $members = $assignment->get_submission_group_members($submission->groupid, true);

                foreach ($members as $member) {
                    array_push($users, $member->id);
                }
            }

            mtrace('Convert ' . count($users) . ' submission attempt(s) for assignment ' . $assignmentid);
            $conversionrequirespolling = false;

            foreach ($users as $userid) {
                try {
                    $combineddocument = document_services::get_combined_pdf_for_attempt($assignment, $userid, $attemptnumber);
                    switch ($combineddocument->get_status()) {
                        case combined_document::STATUS_READY:
                        case combined_document::STATUS_READY_PARTIAL:
                        case combined_document::STATUS_PENDING_INPUT:
                            // The document has not been converted yet or is somehow still ready.
                            $conversionrequirespolling = true;
                            continue 2;
                    }
                    document_services::get_page_images_for_attempt(
                            $assignment,
                            $userid,
                            $attemptnumber,
                            false
                        );
                    document_services::get_page_images_for_attempt(
                            $assignment,
                            $userid,
                            $attemptnumber,
                            true
                        );
                } catch (\moodle_exception $e) {
                    mtrace('Conversion failed with error:' . $e->errorcode);
                }
            }

            // Remove from queue.
            if (!$conversionrequirespolling) {
                $DB->delete_records('assignfeedback_editpdf_queue', array('id' => $record->id));
            }

        }*/
    }

}
