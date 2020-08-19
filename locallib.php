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

/**
 * Library class for androidmarker feedback plugin extending feedback plugin base class.
 *
 * @package   assignfeedback_androidmarker
 */
class assign_feedback_androidmarker extends assign_feedback_plugin {
    // Database table names.
    const TABLE_ASSIGNFEEDBACK_ANDROIDMARKER = "assignfeedback_androidmarker";
    const TABLE_ANDROIDMARKER_TESTRESULT = "androidmarker_testresult";
    const TABLE_ANDROIDMARKER_COMPILATIONERROR = "androidmarker_errors";

    const COMPONENT_NAME = "assignfeedback_androidmarker";

    /**
     * Get the name of the androidmarker feedback plugin.
     *
     * @return string
     */
    public function get_name() {
        return get_string('androidmarker', self::COMPONENT_NAME);
    }

    /**
     * If this plugin adds to the gradebook comments field, it must specify the format
     * of the comment.
     *
     * (From weblib.php)
     * define('FORMAT_MOODLE',   '0');   // Does all sorts of transformations and filtering
     * define('FORMAT_HTML',     '1');   // Plain HTML (with some tags stripped)
     * define('FORMAT_PLAIN',    '2');   // Plain text (even tags are printed in full)
     * define('FORMAT_WIKI',     '3');   // Wiki-formatted text
     * define('FORMAT_MARKDOWN', '4');   // Markdown-formatted
     *
     * Only one feedback plugin can push comments to the gradebook and that is chosen by the assignment
     * settings page.
     *
     * @param stdClass $grade The grade
     * @return int
     */
    /*public function format_for_gradebook(stdClass $grade) {
        return FORMAT_MOODLE;
    }*/

    /**
    * If this plugin adds to the gradebook comments field, it must format the text
    * of the comment.
    *
    * Only one feedback plugin can push comments to the gradebook and that is chosen by the assignment
    * settings page.
    *
    * @param stdClass $grade The grade
    * @return string
    */
   /*public function text_for_gradebook(stdClass $grade) {
       return '';
   }*/

   /**
     * Return any files this plugin wishes to save to the gradebook.
     *
     * The array being returned should contain the necessary information to
     * identify and copy the files.
     *
     * eg.
     *
     * [
     *      'contextid' => $modulecontext->id,
     *      'component' => ASSIGNFEEDBACK_XYZ_COMPONENT,
     *      'filearea' => ASSIGNFEEDBACK_XYZ_FILEAREA,
     *      'itemid' => $grade->id
     * ]
     *
     * @param stdClass $grade The assign_grades object from the db
     * @return array
     */
    /*public function files_for_gradebook(stdClass $grade) : array {
        return [];
    }*/

    /**
     * Override to indicate a plugin supports quickgrading.
     *
     * @return boolean - True if the plugin supports quickgrading
     */
  /*  public function supports_quickgrading() {
        return false;
    }*/

    /**
     * Any extra validation checks needed for the settings
     * form for this feedback plugin should be added to this method.
     */
    /*function form_validation($data) {
        global $CFG;

        $errors = array();
        if (substr($data->language, -13) == 'sphere_engine') {
            // sphere-engine does support multifiles
            // TODO: allow multi-files submissions when sphere engine is used.
            if ($data->assignsubmission_file_maxfiles > 1) {
                $errors['Files Allowed'] = get_string('onefileonlyse', 'local_onlinejudge');
            }

            if (empty($data->clientid)) {
                $errors['clientid'] = get_string('seclientidrequired', 'local_onlinejudge');
            }
            if (empty($data->accesstoken)) {
                $errors['accesstoken'] = get_string('seclientidrequired', 'local_onlinejudge');
            } else if (!empty($data->clientid)) { // test username and password
                // requiring the sphere engine api files.
                require_once($CFG->dirroot . "/local/onlinejudge/judge/sphere_engine/api/CompilersClientV4.php");
                require_once($CFG->dirroot . "/local/onlinejudge/judge/sphere_engine/api/SphereEngineConnectionException.php");
                require_once($CFG->dirroot . "/local/onlinejudge/judge/sphere_engine/api/SphereEngineResponseException.php");
                require_once($CFG->dirroot . "/local/onlinejudge/judge/sphere_engine/api/vendor/autoload.php");
                // define access parameters
                $access_token = $data->accesstoken;
                $end_point = $data->clientid;

                $client = new CompilersClientV4($access_token, $end_point);

                // API usage
                try {
                    $response = $client->test();
                } catch (\SphereEngine\Api\SphereEngineResponseException $e) {
                    if ($e->getCode() == 401 or $e->getCode() == 402) {
                        $errors['accesstoken'] = get_string('seautherror', 'local_onlinejudge');
                    }
                }
            }

        }
        return $errors;
    }*/

    /**
     * Get feedback AndroidMarker file information from the database.
     *
     * @param int $gradeid
     * @return mixed
     */
    public function get_androidmarker_feedback($userid) {
        global $DB;
        return $DB->get_record(self::TABLE_ASSIGNFEEDBACK_ANDROIDMARKER, array('user_id' => $userid, 'assignment_id' => $this->assignment->get_instance()->id));
    }

    /**
     * Get the default setting for androidmarker feedback plugin
     *
     * @param MoodleQuickForm $mform The form to add elements to
     * @return void
     */
    public function get_settings(MoodleQuickForm $mform) {
        // Zip file with the lecturers entire android project
        $nameRequiredDoc = get_string("setting_required_documents", self::COMPONENT_NAME);
        $fileoptionsRequiredDoc = $this->get_required_documents_options();
        $mform->addElement("filemanager", "androidmarkerdoc", $nameRequiredDoc, null, $fileoptionsRequiredDoc);
        $mform->addHelpButton("androidmarkerdoc",
            "setting_required_documents_help",
            "assignfeedback_androidmarker");

        // Text file that specifies the student file submissions
        $nameLecturerZip = get_string("setting_lecturer_project", self::COMPONENT_NAME);
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
        global $USER;
        if (isset($data->androidmarkerzip) && isset($data->androidmarkerdoc)) { // This saves the lecturers zip

            file_save_draft_area_files($data->androidmarkerdoc, $this->assignment->get_context()->id,
                self::COMPONENT_NAME, ASSIGNFEEDBACK_ANDROIDMARKER_FILEAREA_DOC, 0);

            file_save_draft_area_files($data->androidmarkerzip, $this->assignment->get_context()->id,
                self::COMPONENT_NAME, ASSIGNFEEDBACK_ANDROIDMARKER_FILEAREA_ZIP, 0);

            $fs = get_file_storage();

            $fileDOC = $fs->get_area_files($this->assignment->get_context()->id,
                self::COMPONENT_NAME,
                ASSIGNFEEDBACK_ANDROIDMARKER_FILEAREA_DOC,
                0,
                'id',
                false);

            $fileZIP = $fs->get_area_files($this->assignment->get_context()->id,
                self::COMPONENT_NAME,
                ASSIGNFEEDBACK_ANDROIDMARKER_FILEAREA_ZIP,
                0,
                'id',
                false);

            if (empty($fileZIP) || empty($fileDOC)) {
                \core\notification::warning(get_string("no_files_warning", self::COMPONENT_NAME));
                return true;
            }

            $nothing = $this->insert_assignment_submission( $USER->id, -1, $this->assignment->get_instance()->id);
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
            self::COMPONENT_NAME, ASSIGNFEEDBACK_ANDROIDMARKER_FILEAREA_ZIP,
            0, array('subdirs' => 0));
        file_prepare_draft_area($draftitemidDoc, $this->assignment->get_context()->id,
            self::COMPONENT_NAME, ASSIGNFEEDBACK_ANDROIDMARKER_FILEAREA_DOC,
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
    public function get_form_elements_for_user($grade, MoodleQuickForm $mform, stdClass $data, $userid) { // Not sure what this function does
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
            self::COMPONENT_NAME,
            ASSIGNFEEDBACK_ANDROIDMARKER_FILEAREA_SUBMISSION,
            $gradeid);

        $name = get_string("androidmarker_submission", self::COMPONENT_NAME);
        $mform->addElement('filemanager', 'tasks_filemanager', $name, null, $fileoptions);
        $mform->addHelpButton("tasks_filemanager",
            "androidmarker_submission",
            self::COMPONENT_NAME);

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
        return $DB->get_record(self::TABLE_ASSIGNFEEDBACK_ANDROIDMARKER, array('user_id' => $userid, 'assignment_id' => $this->assignment->get_instance()->id));
    }

    /**
    * Save a submission into the database
    * @param int $userid
    * @param int $preference A number from -1 upwards. -1 represents a lecturer's submission.
    * @param int $assignmentid
    * @return stdClass
    */
    public function insert_assignment_submission( $userid, $priority, $assignmentid){
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
          $androidmarkersubmission->priority = $priority;
          $androidmarkersubmission->status = get_string('pending', self::COMPONENT_NAME);
          $androidmarkersubmission->id = $DB->insert_record(self::TABLE_ASSIGNFEEDBACK_ANDROIDMARKER, $androidmarkersubmission);
      }

      return $androidmarkersubmission;
    }

    /**
     * Save the feedback androidmarker files.
     *
     * @param stdClass $grade Grade data.
     * @param stdClass $data
     * @return bool True on successful save, false on error.
     */
    public function save(stdClass $submission, stdClass $data) {
      // I think this function will be used to save the gradle log data.
      // But thats not how this works. We will either have to display it on a different page
      // or make an instance of the file assignment feedback plugin
    /*  $fileoptions = $this->get_file_options();

        // The element name may have been for a different user.
        foreach ($data as $key => $value) {
            if (strpos($key, 'files_') === 0 && strpos($key, '_filemanager')) {
                $elementname = substr($key, 0, strpos($key, '_filemanager'));
            }
        }

        $data = file_postupdate_standard_filemanager($data,
                                                     $elementname,
                                                     $fileoptions,
                                                     $this->assignment->get_context(),
                                                     'assignfeedback_file',
                                                     ASSIGNFEEDBACK_FILE_FILEAREA,
                                                     $grade->id);

        return $this->update_file_count($grade);*/
    }

    /**
     * Produce a list of files suitable for export that represent this feedback or submission
     *
     * @param stdClass $submission The submission
     * @param stdClass $user The user record - unused
     * @return array - return an array of files indexed by filename
     */
  /*  public function get_files(stdClass $submission, stdClass $user) {
      // This is for the student
        $result = array();
        $fs = get_file_storage();

        $files = $fs->get_area_files($this->assignment->get_context()->id,
            self::COMPONENT_NAME,
            ASSIGNFEEDBACK_ANDROIDMARKER_FILEAREA_SUBMISSION,
            $submission->id,
            'timemodified',
            false);

        foreach ($files as $file) {
            // Do we return the full folder path or just the file name?
            if (isset($submission->exportfullpath) && $submission->exportfullpath == false) {
                $result[$file->get_filename()] = $file;
            } else {
                $result[$file->get_filepath().$file->get_filename()] = $file;
            }
        }
        return $result;
    }*/

    /**
     * Display the list of feedback androidmarker files in the feedback status table.
     *
     * @param stdClass $user
     * @param bool $showviewlink - Set to true to show a link to see the full list of files
     * @return string
     */
    public function view_summary(stdClass $user, & $showviewlink) {
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
      global $PAGE;
      //debugging("My OUtput: " . $PAGE->url->get_param("action"));

      if ($PAGE->url->get_param("action") == "grading") {
          return $this->view_grading_summary($user, $showviewlink);
      } else {
          return $this->view_submission_summary($user->id);
      }

      // This should be put in the view_submission_summary function
      /*  global $USER;

        // Show a view all link if the number of files is over this limit.
        $count = $this->count_files($grade->id, \local_androidmarker\api\base::ASSIGNFEEDBACK_ANDROIDMARKER_FILEAREA);
        $showviewlink = $count > \local_androidmarker\api\base::ASSIGNFEEDBACK_ANDROIDMARKER_MAXSUMMARYFILES;

        try {
            $androidmarkerapi = \local_androidmarker\api\base::getinstance();
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        $o = '';

        if ($count <= \local_androidmarker\api\base::ASSIGNFEEDBACK_ANDROIDMARKER_MAXSUMMARYFILES) {
            if (($grade->grade !== null) && ($grade->grade >= 0)) {
                if ($androidmarkerapi->is_logged_in()) {
                    // Show a link to open the androidmarker page.
                    $submission = $this->assignment->get_user_submission($grade->userid, false);
                    $isteacher = $androidmarkerapi->is_teacher($this->assignment->get_course_module()->id, $USER->id);
                    $o .= $androidmarkerapi->render_action_button(get_string('viewfeedback', 'assignfeedback_androidmarker'),
                            $this->assignment->get_course_module()->id, true, $isteacher,
                            $grade->userid, $submission ? $submission->id : 0, $grade->id);
                } else {
                    $o .= $androidmarkerapi->render_signin_widget();
                    $o .= '<br/><br/><p>' . get_string('signinhelp2', 'assignfeedback_androidmarker') . '</p>';
                }

                // Show standard link to download zip package.
                $o .= '<p>Download:</p>';
                $filearea = \local_androidmarker\api\base::ASSIGNFEEDBACK_ANDROIDMARKER_FILEAREA;
                $o .= $this->assignment->render_area_files('assignfeedback_androidmarker', $filearea, $grade->id);
            }

            return $o;
        } else {
            return get_string('countfiles', 'assignfeedback_androidmarker', $count);
        }*/
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

            $url = new moodle_url('<php?
              \core\notification::warning(shell_exec("adb devices")); ?>', array('id' => $cmid, 'a' => $this->assignment->get_instance()->id));
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

        $testresults = $DB->get_records(self::TABLE_ANDROIDMARKER_TESTRESULT, array("user_id" => $userid, "assignment_id" => $this->assignment->get_instance()->id));
        $comperrorcount = $DB->count_records(self::TABLE_ANDROIDMARKER_COMPILATIONERROR, array("user_id" => $userid, "assignment_id" => $this->assignment->get_instance()->id));
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

        $html .= $this->assignment->render_area_files(self::COMPONENT_NAME,
            ASSIGNFEEDBACK_ANDROIDMARKER_FILEAREA_SUBMISSION,
            $userid);

        $androidmarkersubmission = $DB->get_record(self::TABLE_ASSIGNFEEDBACK_ANDROIDMARKER, array("user_id" => $userid));

        if($androidmarkersubmission == NULL || $androidmarkersubmission->status == NULL){
          return $html;
        }
        $table = new html_table();
        $table->id = 'assignment_androidmarker_information';
        $table->attributes['class'] = 'generaltable';
        $table->size = array('50%', '');

        // This displays the status of the submission
        $item_name = get_string('submissionstatus', 'assignfeedback_androidmarker') . ':';
        $item = $androidmarkersubmission->status;
        $table->data[] = array($item_name, $item);

        // If the status is not equal to marking we can display the results.
        if($androidmarkersubmission->status !== get_string('marking', self::COMPONENT_NAME) &&
           $androidmarkersubmission->status !== get_string('pending', self::COMPONENT_NAME)){
          $testresults = $DB->get_records(self::TABLE_ANDROIDMARKER_TESTRESULT, array("user_id" => $userid, "assignment_id" => $this->assignment->get_instance()->id));
          //$compilationerrors = $DB->get_records(self::TABLE_ANDROIDMARKER_COMPILATIONERROR, array("androidmarker_id" => $androidmarkersubmission->id));


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
              $html = html_writer::div($html);
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
        $html = html_writer::div($html, "androidmarker_submission_view");
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
            self::COMPONENT_NAME,
            $area,
            $gradeid,
            'id',
            false);

        return count($files);
    }

    /**
     * Determine whether the plugin is being added to the front page.
     *
     * @return bool Whether the plugin is being added to the front page.
     */
    protected function isonfrontpage() {
        if (!empty($this->assignment) && $this->assignment instanceof \assign) {
            $coursectx = $this->assignment->get_course_context();
            $coursectxvalid = (!empty($coursectx) && $coursectx instanceof \context_course) ? true : false;
            if ($coursectxvalid === true && $coursectx->instanceid == SITEID) {
                return true;
            }
        }
        return false;
    }

    /**
     * Automatically disable plugin if we're on the front page.
     *
     * @return bool
     */
    public function is_enabled() {
        if ($this->isonfrontpage() === true) {
            return false;
        }
        return parent::is_enabled();
    }

    /**
     * Automatically hide the setting for the feedback plugin.
     *
     * @return bool
     */
    public function is_configurable() {
        if ($this->isonfrontpage() === true) {
            return false;
        }
        return parent::is_configurable();
    }

    /**
     * Display the list of feedback androidmarker files in the feedback status table.
     *
     * @param stdClass $grade
     * @return string
     */
    public function view(stdClass $grade) {
        $filearea = \local_androidmarker\api\base::ASSIGNFEEDBACK_ANDROIDMARKER_FILEAREA;
        return $this->assignment->render_area_files('assignfeedback_androidmarker', $filearea, $grade->id);
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

        $androidmarker = $DB->get_record(self::TABLE_ASSIGNFEEDBACK_ANDROIDMARKER, array('assignment_id' => $assignmentid), "id");

        if ($androidmarker) {
            //$this->delete_test_data($assignmentid, $androidmarker->id);
        }

        // Delete androidmarker assignment.
        $DB->delete_records(self::TABLE_ASSIGNFEEDBACK_ANDROIDMARKER, array("assignment_id" => $assignmentid));


        $wsbaseaddress = get_config(self::COMPONENT_NAME, "wsbase");
        if (empty($wsbaseaddress)) {
            \core\notification::error(get_string("wsbase_not_set", self::COMPONENT_NAME));
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

        $updateData = $DB->get_record(self::TABLE_ASSIGNFEEDBACK_ANDROIDMARKER, array("assignment_id" => $assignmentid, "user_id" => $userid), "id", IGNORE_MISSING);
        if (!$updateData) {
            return true;
        }

        // Update the assignfeedback_androidmarker table to Pending
        $updateData->status = get_string('pending', self::COMPONENT_NAME);
        $DB->update_record(self::TABLE_ASSIGNFEEDBACK_ANDROIDMARKER,
                            $updateData,
                            $bulk=false);

        // Delete compilation errors.
        //$DB->delete_records(self::TABLE_ANDROIDMARKER_COMPILATIONERROR, array("androidmarker_id" => $androidmarkerid));

        // Delete test results.
        $DB->delete_records(self::TABLE_ANDROIDMARKER_TESTRESULT, array("assignment_id" => $assignmentid, "user_id" => $userid));

        return true;
    }

    /**
     * Return true if there are no feedback androidmarker files.
     *
     * @param stdClass $grade
     */
    public function is_empty(stdClass $grade) {
      /*If a plugin has no submission data to show - it can return true from
      the is_empty function. This prevents a table row being added to the
      submission summary for this plugin. It is also used to check if a student
      has tried to save an assignment with no data.
      */
      return $this->count_files($grade->id, ASSIGNFEEDBACK_ANDROIDMARKER_FILEAREA_SUBMISSION) == 0;
    }

    /**
     * Get file areas returns a list of areas this plugin stores files in.
     *
     * @return array - An array of fileareas (keys) and descriptions (values)
     */
    public function get_file_areas() {
        return array(
          ASSIGNFEEDBACK_ANDROIDMARKER_FILEAREA_SUBMISSION => get_string("androidmarker_submissions_fa", self::COMPONENT_NAME),
          ASSIGNFEEDBACK_ANDROIDMARKER_FILEAREA_ZIP => get_string("androidmarker_zip_fa", self::COMPONENT_NAME),
          ASSIGNFEEDBACK_ANDROIDMARKER_FILEAREA_DOC => get_string("androidmarker_requirement_document_fa", self::COMPONENT_NAME)
        );
    }

    /*
     * This function marks the student's project
     *
     */
     public function mark_student_project($userid){
       global $CFG;

       $fs = get_file_storage();

       $fileDOC = $fs->get_area_files($this->assignment->get_context()->id,
           self::COMPONENT_NAME,
           ASSIGNFEEDBACK_ANDROIDMARKER_FILEAREA_DOC,
           0,
           'id',
           false);

       $fileZIP = $fs->get_area_files($this->assignment->get_context()->id,
           self::COMPONENT_NAME,
           ASSIGNFEEDBACK_ANDROIDMARKER_FILEAREA_ZIP,
           0,
           'id',
           false);

      // get the student's zip too
      $fileSTU = $fs->get_area_files($this->assignment->get_context()->id,
          self::COMPONENT_NAME,
          ASSIGNFEEDBACK_ANDROIDMARKER_FILEAREA_SUBMISSION,
          $userid,
          'id',
          false);

       if (empty($fileZIP) || empty($fileDOC) || empty($fileSTU)) {
           \core\notification::warning(get_string("no_files_warning", self::COMPONENT_NAME));
           return true;
       }

       $moodledata = $dbhost = get_config(self::COMPONENT_NAME, "moodledata");
       // Create an assignment directory everytime a submission is made
       // create assignment directory
       $path = $moodledata . DIRECTORY_SEPARATOR . $this->assignment->get_instance()->id."_".$userid;
       if(is_dir($path)){
         $this->remove_directory($path);
       }
       $this->create_secure_directory($path);

       $fileDOC = reset($fileDOC);
       $fileZIP = reset($fileZIP);
       $fileSTU = reset($fileSTU);

       $MarkingScriptsDir = $CFG->dirroot .DIRECTORY_SEPARATOR."mod/assign/feedback/androidmarker/MarkingScripts";
       $runTestOnEmulatorDir = $MarkingScriptsDir.DIRECTORY_SEPARATOR."runTestOnEmulator.sh";
       $MarkProjectDir = $MarkingScriptsDir.DIRECTORY_SEPARATOR."MarkProject.sh";
       $MarkDir = $MarkingScriptsDir.DIRECTORY_SEPARATOR."Mark.php";

       // Copies the 7 needed documents
       if(copy($runTestOnEmulatorDir,$path.DIRECTORY_SEPARATOR."runTestOnEmulator.sh") &&
       copy($MarkDir,$path.DIRECTORY_SEPARATOR."Mark.php") &&
       copy($MarkProjectDir,$path.DIRECTORY_SEPARATOR."MarkProject.sh")){
         file_put_contents($path.DIRECTORY_SEPARATOR."RequiredDocuments.txt",$fileDOC->get_content());
         file_put_contents($path.DIRECTORY_SEPARATOR."LecturerZip.zip",$fileZIP->get_content());
         file_put_contents($path.DIRECTORY_SEPARATOR."StudentZip.zip",$fileSTU->get_content());
         // Copies the 5 needed documents

         $this->mark_submission($path, $this->assignment->get_instance()->id."_".$userid, $userid);

       }
     }


    /*
     *  This function marks the lecturer's mark_lecturer_project
     *
     */
    public function mark_lecturer_project(){
      global $CFG, $USER;

      $fs = get_file_storage();

      $fileDOC = $fs->get_area_files($this->assignment->get_context()->id,
          self::COMPONENT_NAME,
          ASSIGNFEEDBACK_ANDROIDMARKER_FILEAREA_DOC,
          0,
          'id',
          false);

      $fileZIP = $fs->get_area_files($this->assignment->get_context()->id,
          self::COMPONENT_NAME,
          ASSIGNFEEDBACK_ANDROIDMARKER_FILEAREA_ZIP,
          0,
          'id',
          false);

      if (empty($fileZIP) || empty($fileDOC)) {
          \core\notification::warning(get_string("no_files_warning", self::COMPONENT_NAME));
          return true;
      }

      $moodledata = $dbhost = get_config(self::COMPONENT_NAME, "moodledata");
      // Create an assignment directory everytime a submission is made
      // create assignment directory
      $path = $moodledata . DIRECTORY_SEPARATOR . $this->assignment->get_instance()->id."_".$USER->id;
      if(is_dir($path)){
        $this->remove_directory($path);
      }
      $this->create_secure_directory($path);

      $fileDOC = reset($fileDOC);
      $fileZIP = reset($fileZIP);

      $MarkingScriptsDir = $CFG->dirroot .DIRECTORY_SEPARATOR."mod/assign/feedback/androidmarker/MarkingScripts";
      $runTestOnEmulatorDir = $MarkingScriptsDir.DIRECTORY_SEPARATOR."runTestOnEmulator.sh";
      $MarkProjectDir = $MarkingScriptsDir.DIRECTORY_SEPARATOR."MarkProject.sh";
      $MarkDir = $MarkingScriptsDir.DIRECTORY_SEPARATOR."Mark.php";

      // Copies the 7 needed documents
      if(copy($runTestOnEmulatorDir,$path.DIRECTORY_SEPARATOR."runTestOnEmulator.sh") &&
      copy($MarkDir,$path.DIRECTORY_SEPARATOR."Mark.php") &&
      copy($MarkProjectDir,$path.DIRECTORY_SEPARATOR."MarkProject.sh")){
        file_put_contents($path.DIRECTORY_SEPARATOR."RequiredDocuments.txt",$fileDOC->get_content());
        file_put_contents($path.DIRECTORY_SEPARATOR."LecturerZip.zip",$fileZIP->get_content());
        file_put_contents($path.DIRECTORY_SEPARATOR."StudentZip.zip",$fileZIP->get_content());
        // Copies the 5 needed documents
        //Creates a psuedo student submission

        $this->mark_submission($path, $this->assignment->get_instance()->id . "_".$USER->id, $USER->id);

      }
    }

    function mark_submission($path, $submissionPath, $userid){
      global $DB, $CFG;
      // Delete records
      $DB->delete_records(self::TABLE_ANDROIDMARKER_TESTRESULT, array("assignment_id" => $this->assignment->get_instance()->id, "user_id" => $userid));

      $wsbaseaddress = get_config(self::COMPONENT_NAME, "wsbase");

      $s = curl_init();

      curl_setopt($s,CURLOPT_URL, $wsbaseaddress.DIRECTORY_SEPARATOR.$submissionPath.DIRECTORY_SEPARATOR."Mark.php");
      // Enable the post response.
      curl_setopt($s, CURLOPT_POST, true);

      $dbhost = get_config(self::COMPONENT_NAME, "database_host");
      $dbuser = get_config(self::COMPONENT_NAME, "database_user");
      $dbpass = get_config(self::COMPONENT_NAME, "database_password");
      $db = get_config(self::COMPONENT_NAME, "database_database");
      $sdk = get_config(self::COMPONENT_NAME, "sdk_path");

      // Setup request to send json via POST
      $data = array('UserID' => $userid,
        'AssignmentID' => $this->assignment->get_instance()->id,
        'assignfeedback_androidmarker' => self::TABLE_ASSIGNFEEDBACK_ANDROIDMARKER,
        'androidmarker_testresult' => self::TABLE_ANDROIDMARKER_TESTRESULT,
        'androidmarker_errors' => self::TABLE_ANDROIDMARKER_COMPILATIONERROR,
        'dbhost' => $dbhost,
        'dbuser' => $dbuser,
        'dbpass' => $dbpass,
        'db' => $db,
        'sdk' => $sdk);

      // Attach encoded JSON string to the POST fields
      curl_setopt($s, CURLOPT_POSTFIELDS, json_encode($data));

      // Set the content type to application/json
      curl_setopt($s, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
      curl_exec($s);
      curl_close($s);

      /*
      $info = $curl->get_info();
      if ($info["http_code"] == 200 || $info["http_code"] == 201) {
          // Code 201 is for successful file transfer
          return $response;
      }

      // Something went wrong.
      debugging("ANDROIDMARKER: Post file to server was not successful: http_code=" . $info["http_code"]);

      switch($info['http_code']) {
      //  case 0:
      //    \core\notification::error("No Server Permission (Error: 0)");
        //  break;
        case 400:
          \core\notification::error(get_string("badrequesterror", self::COMPONENT_NAME));
          break;
        case 401:
          \core\notification::error("Unauthorized (Error: 401)");
          break;
        case 403:
          \core\notification::error("Forbidden (Error: 403)");
          break;
        case 404:
          \core\notification::error(get_string("notfounderror", self::COMPONENT_NAME));
          break;
        case 500:
          \core\notification::error("Internal Server Error (Error: 500)");
          break;
        case 501:
          \core\notification::error("Server does not support the functionality of the Android Marker");
        default:
          \core\notification::error($info["http_code"]);
          break;
      }
      */

      // Adds it to the queue
      //$hostAdd = explode(':', $wsbaseaddress);
      //$crontab = new Ssh2_crontab_manager($hostAdd[0], $hostAdd[1], "", "");
      //$crontab->append_cronjob("30 8 * * 6 $path/MarkProject.sh");

    /*  curl_setopt($s,CURLOPT_RETURNTRANSFER,1);
      $testresults=json_decode(curl_exec($s),true);
      curl_close($s);

      // Close the server when process is finished
      // shell_exec('kill $(ps aux | grep '.$wsbaseaddress." | awk '{print $2}')");

      $this->remove_directory($path);

      */
    }


    function create_directory($path) {
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
            return true;
        }
        return false;
    }

    function secure_directory($path) {
        $content = '# Don\'t list directory contents
    IndexIgnore *
    # Disable script execution
    AddHandler cgi-script .php .pl .jsp .asp .sh .cgi
    Options -ExecCGI -Indexes';
        file_put_contents($path . DIRECTORY_SEPARATOR . '.htaccess', $content);
    }

    function create_secure_directory($path) {
        $created = $this->create_directory($path);
        if ($created) {
            $this->secure_directory($path);
        }
    }

    function remove_directory($path) {
        if (!is_dir($path)) {return;}
        $files = glob($path . DIRECTORY_SEPARATOR . '{.,}*', GLOB_BRACE);
        @array_map('unlink', $files);
        @rmdir($path);
    }

}
