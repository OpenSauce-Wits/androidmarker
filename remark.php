<?php

require_once('../../../../config.php');
defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__) . "/lib.php");

$id = optional_param('id', 0, PARAM_INT);
$userid = optional_param('userid', 0, PARAM_INT);  // Course Module ID
$Assignmentid = optional_param('assignment', 0, PARAM_INT);   // Assignment ID
$confirm = optional_param('confirm', 0, PARAM_INT);   // Force to rejudge

if ($id) {
    if (!$cm = get_coursemodule_from_id('assign', $id)) {
        print_error('invalidcoursemodule');
    }

    if (!$assignment = $DB->get_record("assign", array("id" => $cm->instance))) {
        print_error('invalidid', 'assign');
    }

    if (!$course = $DB->get_record("course", array("id" => $assignment->course))) {
        print_error('coursemisconf', 'assign');
    }
} else {
    if (!$assignment = $DB->get_record("assign", array("id" => $Assignmentid))) {
        print_error('invalidid', 'assign');
    }
    if (!$course = $DB->get_record("course", array("id" => $assignment->course))) {
        print_error('coursemisconf', 'assign');
    }
    if (!$cm = get_coursemodule_from_instance("assign", $assignment->id, $course->id)) {
        print_error('invalidcoursemodule');
    }
}

require_login($course, true, $cm);

$context = context_course::instance($cm->course);
require_capability('mod/assign:grade', $context);

if($userid){
  $optionsno = array('id' => $id, 'action' => 'grader', 'userid' => $userid);
  $optionsyes = array('id' => $id, 'userid' => $userid, 'assignment' => $Assignmentid, 'confirm' => 1, 'sesskey' => sesskey());
  $NoURL = new moodle_url('../../view.php', $optionsno);
  if ($confirm == 1 && confirm_sesskey()) {
     mark_submission($userid, $Assignmentid, $cm, $course);
     redirect($NoURL);
  }
  else{

    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('remarkstudent', COMPONENT_NAME));
    echo $OUTPUT->confirm(get_string('remarkstudentnotice', COMPONENT_NAME), new moodle_url('remark.php', $optionsyes), $NoURL);
    echo $OUTPUT->footer();
  }
}
else{
  $optionsno = array('id' => $id);
  $optionsyes = array('id' => $id, 'assignment' => $Assignmentid,'confirm' => 1, 'sesskey' => sesskey());
  $NoURL = new moodle_url('../../view.php', $optionsno);
  if ($confirm == 1 && confirm_sesskey()) {
    // Mark All submissions
    // get all the andriodmarker records that do not have a priority of -1 and have assignmentid of $Assignid
    // Feed them to the mark_submission function.
    redirect($NoURL, get_string('remarkallsent', COMPONENT_NAME), 10, \core\output\notification::NOTIFY_SUCCESS);
  }
  else{
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('remarkallprojects', COMPONENT_NAME));
    echo $OUTPUT->confirm(get_string('remarkallnotice', COMPONENT_NAME), new moodle_url('remark.php', $optionsyes), $NoURL);
    echo $OUTPUT->footer();
  }
}

function mark_submission( $UserID, $Assignid, $cm, $course){
  global $DB;
  // Activate the submission event (update)
  $AssignSubmission = $DB->get_record('assign_submission', array('userid' => $UserID, 'assignment' => $Assignid));
  $filesubmission = $DB->get_record('assignsubmission_file', array('submission'=>$AssignSubmission->id));
  $groupname = $DB->get_field('groups', 'name', array('id' => $AssignSubmission->groupid));
  $params = array(
      'context' => context_module::instance($cm->id),
      'courseid' => $course->id,
      'objectid' => $filesubmission->id,
      'userid' => $UserID,
      'other' => array(
          'submissionid' => $filesubmission->id,
          'submissionattempt' => $AssignSubmission->attemptnumber,
          'submissionstatus' => $AssignSubmission->status,
          'filesubmissioncount' => $filesubmission->numfiles,
          'groupid' => $AssignSubmission->groupid,
          'groupname' => $groupname
      )
  );
  $event = \assignsubmission_file\event\submission_updated::create($params);
  //$event->set_assign($filesubmission);
  $event->trigger();
}
