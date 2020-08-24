<?php

  require_once('../../../../config.php');
  defined('MOODLE_INTERNAL') || die();

  require_once(dirname(__FILE__) . "/lib.php");

  $inputJSON = file_get_contents('php://input');  // Get input from the client

  $input = json_decode($inputJSON, TRUE);        // Decode the JSON object

  $FeedbackType = $input['feedbacktype'];
  $UserID = $input['userid'];
  $AssignmentID = $input['assignment'];

  if($FeedbackType == "UpdateStatus"){
    $updateData = $DB->get_record(TABLE_ASSIGNFEEDBACK_ANDROIDMARKER, array('userid' => $UserID, 'assignment' => $AssignmentID));

    // Update the Assignment Submission record
    if ($updateData) {
      // Update the assignfeedback_androidmarker table to Pending
      $updateData->status = get_string( $input['status'], COMPONENT_NAME);
      $DB->update_record(TABLE_ASSIGNFEEDBACK_ANDROIDMARKER, $updateData);
    }
  }
  else if($FeedbackType == "UpdateMark"){
    $updateData = $DB->get_record(TABLE_ASSIGNFEEDBACK_ANDROIDMARKER, array('userid' => $UserID, 'assignment' => $AssignmentID));
    $Results = $input['results'];
    // This code should be executed when the server returns the marked results
    $NumTests = 0;
    $PassedTests = 0;
    foreach ($Results as $tr) {
        $res = explode(' ',$tr);
        // Test result.
        $testData = new \stdClass();
        $testData->userid = $updateData->userid;
        $testData->assignment = $updateData->assignment;
        $testData->testname = $res[0];
        $testData->result = $res[1];
        $DB->insert_record(TABLE_ANDROIDMARKER_TESTRESULT, $testData);

        if($res[1] == 'passed'){
          $PassedTests+=1;
        }
        $NumTests+=1;
    }

    // Update the assignfeedback_androidmarker table to Marked
    $updateData->status = get_string( 'marked', COMPONENT_NAME);
    $DB->update_record(TABLE_ASSIGNFEEDBACK_ANDROIDMARKER,
                        $updateData,
                        $bulk=false);

    // $compilationerrors = $results->compilationErrors;
    // foreach ($compilationerrors as $ce) {
    // 		// Compilation error.
    // 		$compilationerror = new stdClass();
    // 		$compilationerror->columnnumber = $ce->columnNumber;
    // 		$compilationerror->linenumber = $ce->lineNumber;
    // 		$compilationerror->message = $ce->message;
    // 		$compilationerror->position = $ce->position;
    // 		$compilationerror->filename = $ce->javaFileName;
    // 		$compilationerror->androidmarker_id = $androidmarkersubmission->id;
    //
    // 		$compilationerror->id = $DB->insert_record(TABLE_ANDROIDMARKER_COMPILATIONERROR, $compilationerror);
    // }

    $updateData = $DB->get_record('assign_submission', array('userid' => $UserID, 'assignment' => $AssignmentID));
    //$updateData->status = 'marked';
    $updateData->attemptnumber = 0;
    $updateData->latest = 1;
    $DB->update_record('assign_submission', $updateData);

    /*| id | itemid | userid | rawgrade  | rawgrademax | rawgrademin
    | rawscaleid | usermodified | finalgrade | hidden | locked
    | locktime | exported | overridden | excluded | feedback
    | feedbackformat | information | informationformat | timecreated
    | timemodified | aggregationstatus | aggregationweight |*/
    if($input['submissiontype'] == 'StudentSubmission'){
      $Percentage = ($PassedTests*100)/$NumTests;
      $cmid = $input['grade'];
      $gradeInsert = $DB->get_record('grade_grades', array('userid' => $UserID, 'itemid' => $cmid));
      if($gradeInsert){
        $gradeInsert->rawgrade = $Percentage;
        $gradeInsert->timemodified = time();
        $gradeInsert->timecreated = NULL;
        $gradeInsert->aggregationweight = 1;
        $gradeInsert->aggregationstatus = 'used';
        $gradeInsert->finalgrade = $Percentage;
        $DB->update_record('grade_grades',
                            $gradeInsert);
      }
      else{
        $gradeInsert = array(
          'itemid' => $cmid,
          'userid' => $UserID,
          'rawgrade' => $Percentage,
          'rawgrademax' => 100,
          'rawgrademin' => 0,
          'finalgrade' => $Percentage,
          'aggregationweight' => 1,
          'aggregationstatus' => 'used',
          'feedbackformat' => 1,
          'usermodified' => 2,
          'timemodified' => time()
        );
        $DB->insert_record('grade_grades',
                            $gradeInsert);
      }

      $assignGradeInsert = $DB->get_record('assign_grades', array('userid' => $UserID, 'assignment' => $AssignmentID));
      if($assignGradeInsert){
        $assignGradeInsert->grade = $Percentage;
        $assignGradeInsert->timemodified = time();
        $assignGradeInsert->grader = 2;
        $assignGradeInsert->attemptnumber = 0;
        $DB->update_record('assign_grades',
                            $assignGradeInsert);
      }
      else{
        $assignGradeInsert = array(
          'assignment' => $AssignmentID,
          'userid' => $UserID,
          'grade' => $Percentage,
          'grader' => 2,
          'attemptnumber' => 0,
          'timecreated' => time(),
          'timemodified' => time()
        );
        $DB->insert_record('assign_grades',
                            $assignGradeInsert);
      }

      /*
      $urlparams = array('id' => $cmid->id, 'action' => 'grading');
      $url = new moodle_url('/mod/assign/view.php', $urlparams);
      redirect($url, get_string('marked',COMPONENT_NAME));*/
    }
  }
?>
