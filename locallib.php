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
 * This file contains the definition for the library class for androidmarker feedback plugin
 * @package   assignfeedback_androidmarker
 * @author Thando Peter (thandopeter8@gmail.com)
 */

defined('MOODLE_INTERNAL') || die();

// File area for androidmarker submission assignment.
define('ASSIGNFEEDBACK_ANDROIDMARKER_FILEAREA_SUBMISSION', 'submissions_androidmarker');

// File area for androidmarker Android project to be uploaded by the teacher.
define('ASSIGNFEEDBACK_ANDROIDMARKER_FILEAREA_ZIP', 'zip_androidmarker');

// File area for androidmarker requirements document to be uploaded by the teacher
define('ASSIGNFEEDBACK_ANDROIDMARKER_FILEAREA_DOC', 'doc_androidmarker');


require_once(dirname(__FILE__) . "/lib.php");

/**
 * Library class for androidmarker feedback plugin extending feedback plugin base class.
 *
 * @package   assignfeedback_androidmarker
 */
class assign_feedback_androidmarker extends assign_feedback_plugin {

    /**
     * Get the name of the androidmarker feedback plugin.
     *
     * @return string
     */
    public function get_name() {
        return get_string('androidmarker', COMPONENT_NAME);
    }

    /**
     * Any extra validation checks needed for the settings
     * form for this feedback plugin should be added to this method.
     */
    function form_validation($data) {
        global $CFG;

        $errors = array();
        if (!isset($data->androidmarkerzip)){
          $errors['androidmarkerzip'] = get_string('no_lecturer_zip', COMPONENT_NAME);
        }
        if (!isset($data->androidmarkerdoc)) {
          $errors['androidmarkerdoc'] = get_string('no_lecturer_doc', COMPONENT_NAME);
        }
        // Check if the required documents are in the zip file;
        return $errors;
    }

    /**
     * Get feedback AndroidMarker file information from the database.
     *
     * @param int $gradeid
     * @return mixed
     */
    public function get_androidmarker_feedback($userid) {
        global $DB;
        return $DB->get_record(TABLE_ASSIGNFEEDBACK_ANDROIDMARKER, array('user_id' => $userid, 'assignment_id' => $this->assignment->get_instance()->id));
    }

    /**
     * Get the default setting for androidmarker feedback plugin
     *
     * @param MoodleQuickForm $mform The form to add elements to
     * @return void
     */
    public function get_settings(MoodleQuickForm $mform) {
        // Zip file with the lecturers entire android project
        $nameRequiredDoc = get_string("setting_required_documents", COMPONENT_NAME);
        $fileoptionsRequiredDoc = $this->get_required_documents_options();
        $mform->addElement("filemanager", "androidmarkerdoc", $nameRequiredDoc, null, $fileoptionsRequiredDoc);
        $mform->addHelpButton("androidmarkerdoc",
            "setting_required_documents_help",
            "assignfeedback_androidmarker");

        // Text file that specifies the student file submissions
        $nameLecturerZip = get_string("setting_lecturer_project", COMPONENT_NAME);
        $fileoptionsLecturerZip = $this->get_lecturer_zip_options();
        $mform->addElement("filemanager", "androidmarkerzip", $nameLecturerZip, null, $fileoptionsLecturerZip);
        $mform->addHelpButton("androidmarkerzip",
            "setting_lecturer_project_help",
            "assignfeedback_androidmarker");

        $mform->hideif('androidmarkerzip', 'assignfeedback_androidmarker_enabled', 'notchecked');
        $mform->hideif('androidmarkerdoc', 'assignfeedback_androidmarker_enabled', 'notchecked');
    }

    /**
     * Save the settings for androidmarker feedback plugin
     *
     * @param stdClass $userid
     * @param stdClass $data
     * @return bool
     */
    public function save_settings( stdClass $data) {
        global $USER, $CFG, $DB;

        if (isset($data->androidmarkerzip) && isset($data->androidmarkerdoc)) { // This saves the lecturers zip

            file_save_draft_area_files($data->androidmarkerdoc, $this->assignment->get_context()->id,
                COMPONENT_NAME, ASSIGNFEEDBACK_ANDROIDMARKER_FILEAREA_DOC, 0);

            file_save_draft_area_files($data->androidmarkerzip, $this->assignment->get_context()->id,
                COMPONENT_NAME, ASSIGNFEEDBACK_ANDROIDMARKER_FILEAREA_ZIP, 0);

            $fs = get_file_storage();

            $fileDOC = $fs->get_area_files($this->assignment->get_context()->id,
                COMPONENT_NAME,
                ASSIGNFEEDBACK_ANDROIDMARKER_FILEAREA_DOC,
                0,
                'id',
                false);

            $fileZIP = $fs->get_area_files($this->assignment->get_context()->id,
                COMPONENT_NAME,
                ASSIGNFEEDBACK_ANDROIDMARKER_FILEAREA_ZIP,
                0,
                'id',
                false);

            if (empty($fileZIP) || empty($fileDOC)) {
                \core\notification::warning(get_string("no_files_warning", COMPONENT_NAME));
                return true;
            }
            $AssignmentGradeData = $DB->get_record('grade_items',array('iteminstance'=>$this->assignment->get_instance()->id));
            $updateData = $this->insert_assignment_submission( $USER->id, -1, $this->assignment->get_instance()->id, -1);// Delete records

            // Send the submission to the marker
            $fileDOC = reset($fileDOC);
            $fileZIP = reset($fileZIP);

            // Always base64_encode the files
            $fileDOC = base64_encode($fileDOC->get_content());
            $fileZIP = base64_encode($fileZIP->get_content());

            // languageid, source, input, output and timelimit
            $data = array("submissiontype" => "LecturerSubmission",
            "id" => $updateData->id,
            "RequiredDocuments" => $fileDOC,
            "LecturerZip" => $fileZIP,
            "user_id" => $USER->id,
            "grade_item_id" => -1,
            "assignment_id" => $this->assignment->get_instance()->id,
            "priority" => $updateData->priority,
            "url" => $CFG->wwwroot . "/mod/assign/feedback/androidmarker/process_result.php");

            send_submission($data);
        }
        return true;
    }

    /**
     * Allows the plugin to update the defaultvalues passed in to
     * the settings form (needed to set up draft areas for editor
     * and filemanager elements)
     * @param array $defaultvalues
     */
    public function data_preprocessing(&$defaultvalues){

        $draftitemidZip = file_get_submitted_draft_itemid('androidmarkerzip');
        $draftitemidDoc = file_get_submitted_draft_itemid('androidmarkerdoc');

        file_prepare_draft_area($draftitemidZip, $this->assignment->get_context()->id,
            COMPONENT_NAME, ASSIGNFEEDBACK_ANDROIDMARKER_FILEAREA_ZIP,
            0, array('subdirs' => 0));
        file_prepare_draft_area($draftitemidDoc, $this->assignment->get_context()->id,
            COMPONENT_NAME, ASSIGNFEEDBACK_ANDROIDMARKER_FILEAREA_DOC,
            0, array('subdirs' => 0));

        $defaultvalues['androidmarkerzip'] = $draftitemidZip;
        $defaultvalues['androidmarkerdoc'] = $draftitemidDoc;

        return;
    }

    /**
     * File format options.
     *
     * @return array
     */
     private function get_lecturer_zip_options() {
         $fileoptions = array('subdirs' => 1,
             "maxfiles" => 1,
             'accepted_types' => array(".zip"),
             'return_types' => FILE_INTERNAL);
         return $fileoptions;
     }

     private function get_required_documents_options() {
         $fileoptions = array("maxfiles" => 1,
             'accepted_types' => array(".txt"),
             'return_types' => FILE_INTERNAL);
         return $fileoptions;
     }

    /**
     * Get form elements for grading form.
     *
     * @param stdClass $grade stdClass|null
     * @param MoodleQuickForm $mform
     * @param stdClass $data
     * @param int $userid The userid we are currently grading
     * @return bool true if elements were added to the form
     */
    public function get_form_elements_for_user($grade, MoodleQuickForm $mform, stdClass $data, $userid) {
      // This function shows output in the grader form. where you can save and next and stuff for each individual student


      // The get_form_elements function is called when building the feedback
      // form. It functions identically to the get_settings function except
      //  that the submission object is available (if there is a submission) to
      //  associate the settings with a single submission.

        $fileoptions = $this->get_lecturer_zip_options();
        $gradeid = $grade ? $grade->id : 0;
        $elementname = 'files_' . $userid;

        $data = file_prepare_standard_filemanager($data,
            'tasks',
            $fileoptions,
            $this->assignment->get_context(),
            COMPONENT_NAME,
            ASSIGNFEEDBACK_ANDROIDMARKER_FILEAREA_SUBMISSION,
            $gradeid);

        $name = get_string("androidmarker_submission", COMPONENT_NAME);
        $mform->addElement('filemanager', 'tasks_filemanager', $name, null, $fileoptions);
        $mform->addHelpButton("tasks_filemanager",
            "androidmarker_submission",
            COMPONENT_NAME);

        return true;
    }

    /**
     * Get androidmarker submission information from the database
     *
     * @param int $submissionid
     * @return mixed
     */
    private function get_androidmarker_submission($userid) {
        global $DB;
        return $DB->get_record(TABLE_ASSIGNFEEDBACK_ANDROIDMARKER, array('user_id' => $userid, 'assignment_id' => $this->assignment->get_instance()->id));
    }

    /**
    * Save a submission into the database
    * @param int $userid
    * @param int $preference A number from -1 upwards. -1 represents a lecturer's submission.
    * @param int $assignmentid
    * @return stdClass
    */
    public function insert_assignment_submission( $userid, $priority, $assignmentid, $cmid){
      global $DB;

      $androidmarkersubmission = $this->get_androidmarker_submission($userid);

      if ($androidmarkersubmission) {
          // If there are old results, delete them.
          $this->delete_test_data($assignmentid, $userid);
      } else {

          //  $userid = required_param('userid', PARAM_INT);
        //'userid' => $submission->userid
      //   $submission = $this->assignment->get_user_submission($grade->userid, false);

          $androidmarkersubmission = new stdClass();
          $androidmarkersubmission->user_id = $userid;
          $androidmarkersubmission->assignment_id = $assignmentid;
          $androidmarkersubmission->grade_item_id = $cmid;
          $androidmarkersubmission->priority = $priority;
          $androidmarkersubmission->status = get_string('pending', COMPONENT_NAME);
          $androidmarkersubmission->id = $DB->insert_record(TABLE_ASSIGNFEEDBACK_ANDROIDMARKER, $androidmarkersubmission);
      }

      return $androidmarkersubmission;
    }

    /**
     * Display the list of feedback androidmarker files in the feedback status table.
     *
     * @param stdClass $grade
     * @param $showviewlink
     * @return string - return a string representation of the submission status.
     * @throws coding_exception
     */
    public function view_summary(stdClass $grade, & $showviewlink) {
      /*
        The view_summary function is called to display a summary of the
        submission to both markers and students. It counts the number of
        files submitted and if it is more that a set number, it only displays
        a count of how many files are in the submission - otherwise it uses a
        helper function to write the entire list of files. This is because we
        want to keep the summaries really short so they can be displayed in a
        table. There will be a link to view the full submission on the
        submission status page.
      */

      // This is where the lecturers submission results should be shown
      global $PAGE, $DB;
      //debugging("My OUtput: " . $PAGE->url->get_param("action"));

    /*  if ($PAGE->url->get_param("action") == "grading") {
          return $this->view_grading_summary($grade->userid, $showviewlink);
      } else {
          return $this->view_submission_summary($grade->userid);
      }*/

      $result = $DB->get_record(TABLE_ASSIGNFEEDBACK_ANDROIDMARKER,array("assignment_id" => $this->assignment->get_instance()->id, "user_id" => $grade->userid));

      $output = "";
      // Allowing view link to be rendered.
      $showviewlink = true;
      if($result){
        $statusstyle = 'notifysuccess';
        // 'notifyproblem'; When there is a problem
        $statustext = html_writer::tag('span', get_string('status' . $result->status, COMPONENT_NAME), array('class' => $statusstyle));
        $output .= $statustext;
      }
      return $output; // Always return since parent do so too
    }

    /**
     * Shows Test case information in the lecturer view
     * Shows the 'test cases management' and 'rejudge all' buttons.
     * @return string
     * @throws coding_exception
     * @throws moodle_exception
     */
    public function view_header() {
      global $USER;
      $course_context = context_module::instance($this->assignment->get_course_module()->id);
      if(has_capability('mod/assign:grade', $course_context)){ // Only if you are a lecturer

        $cmid = $this->assignment->get_course_module()->id;

        $output = $this->view_submission_summary($USER->id);
        $output .= '<div class="p-y-2">';

        //if (has_capability('mod/assign:grade', $course_context)) {
            // if (empty(get_testcases($this->assignment->get_instance()->id))) {
            //     $message = get_string('testcasesrequired', 'assignfeedback_onlinejudge');
            //     $output .= '<div class="alert alert-warning">';
            //     $output .= $message;
            //     $output .= '</div>';
            // }

            $urlparams = array('id' => $cmid, 'a' => $this->assignment->get_instance()->id);
            // Should change to stop marking when marking
            $url = new moodle_url('/mod/assign/feedback/onlinejudge/rejudge.php', array('id' => $cmid, 'a' => $this->assignment->get_instance()->id));
            $output .= "<a href='$url' class='btn btn-info' type='button'>" . get_string('runmarker', 'assignfeedback_androidmarker') . "</a>";

            $url = new moodle_url('/mod/assign/feedback/onlinejudge/rejudge.php', array('id' => $cmid, 'a' => $this->assignment->get_instance()->id));
            $output .= "<a href='$url' class='btn btn-info' type='button'>" . get_string('remarkallprojects', 'assignfeedback_androidmarker') . "</a>";
        //}
        $output .= '</div>';
        return $output;
      }
    }

    /**
     * Returns the view that should be displayed in the grading table.
     *
     * @param stdClass $user
     * @param bool $showviewlink
     * @return string
     */
    private function view_grading_summary(stdClass $user, & $showviewlink) {
        global $DB;
        $showviewlink = true;

        $testresults = $DB->get_records(TABLE_ANDROIDMARKER_TESTRESULT, array("user_id" => $userid, "assignment_id" => $this->assignment->get_instance()->id));
        $comperrorcount = $DB->count_records(TABLE_ANDROIDMARKER_COMPILATIONERROR, array("user_id" => $userid, "assignment_id" => $this->assignment->get_instance()->id));
        $result = $this->get_testresult_percentage($testresults, $comperrorcount);
        $result = html_writer::div($result, "feedbackandroidmarkergrading");

        return $result;
    }

    private function get_testresult_percentage($testresults, $comperrorcount) {
        $testcount = 0;
        $succcount = 0;
        foreach ($testresults as $tr) {
            $testcount += 1;
            if(trim($tr->result) == "passed"){
                $succcount += 1;
            }

        }

        $result = "";
        /*if ($comperrorcount > 0) {
            $result .= "Comp. Err.: " . $comperrorcount;
            $result .= "<br>";
        }*/
        if ($testcount > 0) {
            $percentage = round($succcount / $testcount, 1) * 100;
            $result .=  $percentage.'%';
        }

        return $result;
    }

    /**
     * Returns the view that should be displayed to the student.
     *
     * @param int $userid
     * @return string
     */
    private function view_submission_summary($userid) {
      //The view function is called to display the entire submission to both
      //markers and students. In this case it uses the helper function in the
      //assignment class to write the list of files.

        global $DB;
        $html = "";

        $androidmarkersubmission = $DB->get_record(TABLE_ASSIGNFEEDBACK_ANDROIDMARKER, array("user_id" => $userid, "assignment_id" => $this->assignment->get_instance()->id));

        if($androidmarkersubmission == NULL || $androidmarkersubmission->status == NULL){
          return $html;
        }
        //$html .= $this->heading(get_string('submissionfeedbackheading', COMPONENT_NAME), 3);

        $table = new html_table();
        $table->id = 'assignment_androidmarker_information';
        $table->attributes['class'] = 'generaltable';
        $table->size = array('40%', '80%');

        // This displays the status of the submission
        $item_name = get_string('submissionstatus', 'assignfeedback_androidmarker') . ':';
        $item = $androidmarkersubmission->status;
        $table->data[] = array($item_name, $item);

        // If the status is not equal to marking we can display the results.
        if($androidmarkersubmission->status !== get_string('marking', COMPONENT_NAME) &&
           $androidmarkersubmission->status !== get_string('pending', COMPONENT_NAME)){
          $testresults = $DB->get_records(TABLE_ANDROIDMARKER_TESTRESULT, array("user_id" => $userid, "assignment_id" => $this->assignment->get_instance()->id));
          //$compilationerrors = $DB->get_records(TABLE_ANDROIDMARKER_COMPILATIONERROR, array("androidmarker_id" => $androidmarkersubmission->id));


          $item_name = "Overall results:";
          $item = "";
          $table->data[] = array($item_name, $item);

          $item_name = "Percentage:";
          $item = $this->get_testresult_percentage($testresults, 0);
          $table->data[] = array($item_name, $item);

          foreach ($testresults as $tr) {
              $testname = html_writer::tag("h5", $tr->testname);
              $testresult = html_writer::tag("h5", $tr->result);

              $item_name = $testname;
              $item = $testresult;
              $table->data[] = array($item_name, $item);
          }

          /*if ($compilationerrors) {
              $html .= html_writer::tag("h6", "Compilation errors");
              foreach ($compilationerrors as $ce) {
                  $tmpdiv = html_writer::div("Filename:", "failedtestsidebar");
                  $tmpdiv .= html_writer::div($ce->filename, "failedtestcontent");
                  $html .= html_writer::div($tmpdiv, "failedTestWrapper");

                  $tmpdiv = html_writer::div("Message:", "failedtestsidebar");
                  $tmpdiv .= html_writer::div($ce->message, "failedtestcontent");
                  $html .= html_writer::div($tmpdiv, "failedTestWrapper");

                  $tmpdiv = html_writer::div("Column-No.:", "failedtestsidebar");
                  $tmpdiv .= html_writer::div($ce->columnnumber, "failedtestcontent");
                  $html .= html_writer::div($tmpdiv, "failedTestWrapper");

                  $tmpdiv = html_writer::div("Line-No.:", "failedtestsidebar");
                  $tmpdiv .= html_writer::div($ce->linenumber, "failedtestcontent");
                  $html .= html_writer::div($tmpdiv, "failedTestWrapper");

                  $tmpdiv = html_writer::div("Position:", "failedtestsidebar");
                  $tmpdiv .= html_writer::div($ce->position, "failedtestcontent");
                  $html .= html_writer::div($tmpdiv, "failedTestWrapper");
              }
          }*/
        }
        $html .= html_writer::table($table);
        return $html;
    }

    /**
     * Count the number of files
     *
     * @param int $gradeid
     * @param string $area
     * @return int
     */
    private function count_files($gradeid, $area) {
        $fs = get_file_storage();
        $files = $fs->get_area_files($this->assignment->get_context()->id,
            COMPONENT_NAME,
            $area,
            $gradeid,
            'id',
            false);

        return count($files);
    }

    /**
    * Display judge info about the submission
    * @param stdClass grade data
    * @return string - return a string representation of the submission in full
    * @throws coding_exception
    * @throws moodle_exception
     */
    public function view(stdClass $grade) {
      //\core\notification::warning("This is where the feed back is at");
      //$output = $this->view_submission_summary($grade->userid);
      //return $output;

      global $OUTPUT, $DB;
      ///////////////////////////////////////////

      $table = new html_table();
      $table->id = 'assignment_onlinejudge_summary';
      $table->attributes['class'] = 'generaltable';
      $table->size = array('30%', '80%');
      $submission = $this->assignment->get_user_submission($grade->userid, false);
      $onlinejudge_result = $DB->get_record(TABLE_ASSIGNFEEDBACK_ANDROIDMARKER, array('user_id' => $grade->userid,'assignment_id' => $this->assignment->get_instance()->id), "id");
      // Status
      $item_name = get_string('status', COMPONENT_NAME) . $OUTPUT->help_icon('status', COMPONENT_NAME);
      $item = get_string('notavailable');
      if (isset($onlinejudge_result->status)) {
          $itemstyle = 'label label-success' ;
          // 'label label-warning'; FOR WRONG RESULTS
          $item = html_writer::tag('h5', html_writer::tag('span', get_string('status' . $onlinejudge_result->status, COMPONENT_NAME), array('class' => $itemstyle)));
      }
      $table->data[] = array($item_name, $item);
      ///////////////////////////////////////////

      // Judge time
    /*  $item_name = get_string('judgetime', 'assignfeedback_onlinejudge');
      $item = get_string('notavailable');
      if (!empty($onlinejudge_result->judgetime)) {
          $item = userdate($onlinejudge_result->judgetime) . '&nbsp(' . get_string('submittedearly', 'assign', format_time(time() - $onlinejudge_result->judgetime)) . ')';
      }
      $table->data[] = array($item_name, $item);*/
      ///////////////////////////////////////////

      $output = html_writer::table($table);
      return $output;
    }

    /**
     * The judge works as a daemon so there is nothing to be saved through the normal interface.
     *
     * @param stdClass $grade The grade.
     * @param stdClass $data Form data from the feedback form.
     * @return boolean - False
     */
    public function is_feedback_modified(stdClass $grade, stdClass $data) {
        return false;
    }

    /**
     * The assignment has been deleted - cleanup
     *
     * @return bool
     */
     // This deletes the entire assignment
    public function delete_instance() {
        /*The delete_instance function is called when a plugin is deleted.
        Note only database records need to be cleaned up - files belonging
        to fileareas for this assignment will be automatically cleaned up.
        */

        global $DB;
        $assignmentid = $this->assignment->get_instance()->id;

        $androidmarker = $DB->get_record(TABLE_ASSIGNFEEDBACK_ANDROIDMARKER, array('assignment_id' => $assignmentid), "id");

        if ($androidmarker) {
            //$this->delete_test_data($assignmentid, $androidmarker->id);
        }

        // Delete androidmarker assignment.
        $DB->delete_records(TABLE_ASSIGNFEEDBACK_ANDROIDMARKER, array("assignment_id" => $assignmentid));


        $wsbaseaddress = get_config(COMPONENT_NAME, "wsbase");
        if (empty($wsbaseaddress)) {
            \core\notification::error(get_string("wsbase_not_set", COMPONENT_NAME));
            return true;
        }

        $url = $wsbaseaddress;
        $curl = new curl();
        $paramsDelete = array(
            "assignment" => $this->assignment->get_instance()->id
        );
        $curl->delete($url, $paramsDelete);

        return true;
    }

    private function delete_test_data($assignmentid, $userid) {
        global $DB;

        $updateData = $DB->get_record(TABLE_ASSIGNFEEDBACK_ANDROIDMARKER, array("assignment_id" => $assignmentid, "user_id" => $userid), "id", IGNORE_MISSING);
        if (!$updateData) {
            return true;
        }

        // Update the assignfeedback_androidmarker table to Pending
        $updateData->status = get_string('pending', COMPONENT_NAME);
        $DB->update_record(TABLE_ASSIGNFEEDBACK_ANDROIDMARKER,
                            $updateData,
                            $bulk=false);

        // Delete compilation errors.
        //$DB->delete_records(TABLE_ANDROIDMARKER_COMPILATIONERROR, array("androidmarker_id" => $androidmarkerid));

        // Delete test results.
        $DB->delete_records(TABLE_ANDROIDMARKER_TESTRESULT, array("assignment_id" => $assignmentid, "user_id" => $userid));

        return true;
    }

    /**
     * Return true if there is no submission
     *
     * @param stdClass $grade
     */
    public function is_empty(stdClass $grade) {
      global $DB;
      /*If a plugin has no submission data to show - it can return true from
      the is_empty function. This prevents a table row being added to the
      submission summary for this plugin. It is also used to check if a student
      has tried to save an assignment with no data.*/
      $result = $DB->get_record(TABLE_ASSIGNFEEDBACK_ANDROIDMARKER,array("assignment_id" => $this->assignment->get_instance()->id, "user_id" => $grade->userid));
      if($result){
        return true;
      }
      else{
        return false;
      }
    }

    /**
     * Get file areas returns a list of areas this plugin stores files in.
     *
     * @return array - An array of fileareas (keys) and descriptions (values)
     */
    public function get_file_areas() {
        return array(
          ASSIGNFEEDBACK_ANDROIDMARKER_FILEAREA_SUBMISSION => get_string("androidmarker_submissions_fa", COMPONENT_NAME),
          ASSIGNFEEDBACK_ANDROIDMARKER_FILEAREA_ZIP => get_string("androidmarker_zip_fa", COMPONENT_NAME),
          ASSIGNFEEDBACK_ANDROIDMARKER_FILEAREA_DOC => get_string("androidmarker_requirement_document_fa", COMPONENT_NAME)
        );
    }
}
