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
 * An event observer.
 *
 * @package    assignfeedback_androidmarker
 * @author     Thando Peter (thandopeter8@gmail.com)
 */
namespace assignfeedback_androidmarker\event;

require_once($CFG->dirroot . "/mod/assign/feedback/androidmarker/lib.php");

class observer {
    // Database table names.
    const TABLE_ASSIGNFEEDBACK_ANDROIDMARKER = "assignfeedback_androidmarker";
    const TABLE_ANDROIDMARKER_TESTRESULT = "androidmarker_testresult";
    const TABLE_ANDROIDMARKER_COMPILATIONERROR = "androidmarker_errors";

    const COMPONENT_NAME = "assignfeedback_androidmarker";

    /**
     * Listen to events and queue the submission for processing.
     * @param \mod_assign\event\submission_created $event
     */
    public static function submission_created(\mod_assign\event\submission_created $event) {
        self::enter_student_record($event);
    }

    /**
     * Listen to events and queue the submission for processing.
     * @param \mod_assign\event\submission_updated $event
     */
    public static function submission_updated(\mod_assign\event\submission_updated $event) {
        self::enter_student_record($event);
    }

    /**
     * Enter student submission into TABLE_ASSIGNFEEDBACK_ANDROIDMARKER
     * @param \mod_assign\event\base $event The submission created/updated event.
     */
     // The lecturer submitting a helping file might end up here
     protected static function enter_student_record($event) {
         global $DB, $CFG;
         $assignmentid = $event->get_record_snapshot($event->objecttable, $event->objectid)->assignment;
         $submissionid = $event->get_record_snapshot($event->objecttable, $event->objectid)->submission;
         $userid = $event->userid;
         $DBassignGrades = $DB->get_record('assign_grades',array('userid' => $userid, 'assignment' => $assignmentid));
         if($DBassignGrades){
           $DBassignGrades->grade = NULL;
           $DB->update_record('assign_grades',$DBassignGrades);
         // Even though the gui of moodle works with this line. It does not keep the attempt number
         }

         $updateData = $DB->get_record(self::TABLE_ASSIGNFEEDBACK_ANDROIDMARKER, array('user_id' => $userid, 'assignment_id' => $assignmentid));

         if ($updateData) {
             // Update the assignfeedback_androidmarker table to Pending
             $updateData->status = get_string('pending', self::COMPONENT_NAME);
             $DB->update_record(self::TABLE_ASSIGNFEEDBACK_ANDROIDMARKER,
                                 $updateData,
                                 $bulk=false);

             // Delete compilation errors.
             //$DB->delete_records(self::TABLE_ANDROIDMARKER_COMPILATIONERROR, array("androidmarker_id" => $androidmarkerid));

             // Delete test results.
             $DB->delete_records(self::TABLE_ANDROIDMARKER_TESTRESULT, array("assignment_id" => $assignmentid, "user_id" => $userid));
         } else {
             $AssignmentGradeData = $DB->get_record('grade_items',array('iteminstance'=>$assignmentid));
             $updateData = new \stdClass();
             $updateData->user_id = $userid;
             $updateData->assignment_id = $assignmentid;
             $updateData->coursemodule_id = $AssignmentGradeData->id;
             $updateData->priority = 1;
             $updateData->status = get_string('pending', self::COMPONENT_NAME);
             $updateData->id = $DB->insert_record(self::TABLE_ASSIGNFEEDBACK_ANDROIDMARKER, $updateData);
         }

         $fs = get_file_storage();
         $studentZIP = $fs->get_area_files($event->contextid,
             "assignsubmission_file",
             "submission_files",
             $submissionid,
             'id',
             false);

         if (empty($studentZIP)) {
             \core\notification::warning("Could not get Student Zip");
             return;
         }

         $studentZIP = reset($studentZIP);

         // Always base64_encode the files
         $studentZIP = base64_encode($studentZIP->get_content());
         $AssignmentGradeData = $DB->get_record('grade_items',array('iteminstance'=>$assignmentid));

         // languageid, source, input, output and timelimit
         $data = array("submissiontype" => "StudentSubmission",
         "StudentZip" => $studentZIP,
         "id" => $updateData->id,
         "coursemodule_id" => $AssignmentGradeData->id,
         "user_id" => $userid,
         "assignment_id" => $assignmentid,
         "priority" => $updateData->priority,
         "url" => $CFG->wwwroot . "/mod/assign/feedback/androidmarker/process_result.php");

         send_submission($data);
     }
}
