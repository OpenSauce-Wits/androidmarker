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
 * Language strings for component 'assignfeedback_androidmarker'
 *
 * @package assignfeedback_androidmarker
 * @author Thando Peter (thandopeter8@gmail.com)
 */

 $string["pluginname"] = "Android Marker";
 $string["androidmarker"] = "Android Marker";
 $string["androidmarker_submissions_fa"] = "Android Marker Submissions";
 $string["androidmarker_zip_fa"] = "Android Marker Lecturer Zip Files";
 $string["androidmarker_requirement_document_fa"] = "Android Marker Requirement Documents";
 $string['enabled'] = "Android Marker";
 $string['enabled_help'] = "If enabled, students are able to upload one ZIP file containing their Java exercise which will then be tested against a teacher provided set of JUnit/Instrumented tests.";

 // This is the label and help text for the lecturer zip
 $string["setting_lecturer_project"] = "Complete Android project with tests";
 $string["setting_lecturer_project_help"] = "Lecturer Zip Submission";
 $string["setting_lecturer_project_help_help"] = "A single ZIP file containg the entire working Android project, which the students' submissions should be tested against.";

 // This IS the label and help text for the requiredDocuments text files
 $string["setting_required_documents"] = "Required Documents";
 $string["setting_required_documents_help"] = "Required Documents Submission";
 $string["setting_required_documents_help_help"] = "A text file with the names and extensions of the files the student should submit.";

 $string["wsbase_not_set"] = "The web service base URL is not configured.";
 $string["unexpectederror"] = "An unexpected error occured.";
 $string["badrequesterror"] = "The server could not process the request. Probably the submitted ZIP file is corrupted.";
 $string["notfounderror"] = "Web server URL not found.";
 $string["androidmarker_submission"] = "Excercise ZIP";
 $string["androidmarker_submission_help"] = "A single ZIP file containing all the relevant java files and resources for this exercise.";
 $string["no_files_warning"] = "Submission type is \"Android Marker\" but project zip file and requirements textfile are not uploaded.";
 $string["no_lecturer_zip"] = "Submission type is \"Android Marker\" but project zip file and requirements textfile are not uploaded.";
 $string["no_lecturer_doc"] = "Submission type is \"Android Marker\" but project zip file and requirements textfile are not uploaded.";
 $string["submissionstatus"] = "Status";
 $string["marked"] = "Marked";
 $string["marking"] = "Marking";
 $string["pending"] = "Pending";
 $string["built"] = "Built";
 $string["building"] = "Building";
 $string["status"] = "Status";
 $string["submissionfeedbackheading"] = "Feedback";

 // Admin Settings
 $string["default"] = "Enabled by default";
 $string['default_help'] = 'When set, this feedback method is enabled by default for all new assignments.';
 $string["wsbase"] = "Base URL to the web service";
 $string["wsbase_help"] = "The base URL to the web service, where all the tests and submissions will be sent and evaluated.";

 // Lecturer Server settings
 $string["runmarker"] = "Run Marker";
 $string["stopmarker"] = "Stop Marker";
 $string["remarkallprojects"] = "Remark All Projects";
 $string["remarkallnotice"] = "Remarking all the projects will take a long time.\nAre you sure you want to continue?";
 $string['remarkstudent'] = "Remark Submission";
 $string["remarkstudentnotice"] = "Remarking this submission will erase it's grade and results.\nAre you sure you want to continue?";
 $string['marksubmissions'] = "Mark Android project submissions";

$string['countfiles'] = 'OneNotes: {$a}';
$string['maxbytes'] = 'Maximum OneNote package size';
$string['maxfiles'] = 'Maximum number of uploaded OneNotes';
$string['maximumsize'] = 'Maximum OneNote package size';
$string['addfeedback'] = 'Add feedback';
$string['addfeedbackhelp'] = 'Click on the button above to add your feedback for the student\'s submission in OneNote. You can come back here later on to save your work back into Moodle.';
$string['signinhelp1'] = 'Click on the button above to sign in to OneNote so you can add your feedback to the student\'s submission there.';
$string['signinhelp2'] = 'Click on the button above to sign in to OneNote if you want to view the feedback there.';
$string['viewfeedback'] = 'View feedback';
$string['feedbackdownloadfailed'] = 'Could not download the feedback from OneNote. Please try again.';
$string['notsignedin'] = 'Please sign in to OneNote before attempting to save your changes.';
$string['feedbacknotstarted'] = 'Please add your feedback using OneNote and then save your changes.';
$string['feedbacklimitexceed'] = 'Your feedback size exceeds the acceptable limit.';
$string['privacy:path'] = 'Number of files';
$string['privacy:metadata:assignmentid'] = 'Assignment identifier';
$string['privacy:metadata:gradepurpose'] = 'The grade ID associated with the record';
$string['privacy:metadata:numfiles'] = 'Number of files';
$string['privacy:metadata:tablepurpose'] = 'Stores the number of files used for the feedback.';
