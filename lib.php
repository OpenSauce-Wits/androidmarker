<?PHP
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
 * This file contains the moodle hooks for the feedback androidmarker plugin
 * @package   assignfeedback_androidmarker
 * @author Thando Peter (thandopeter8@gmail.com)
 */
defined('MOODLE_INTERNAL') || die();

const TABLE_ASSIGNFEEDBACK_ANDROIDMARKER = "assignfeedback_androidmarker";
const TABLE_ANDROIDMARKER_TESTRESULT = "androidmarker_testresult";
const TABLE_ANDROIDMARKER_COMPILATIONERROR = "androidmarker_errors";

const COMPONENT_NAME = "assignfeedback_androidmarker";

/**
 * Serves assignment feedback and other files.
 *
 * @param mixed $course course or id of the course
 * @param mixed $cm course module or id of the course module
 * @param context $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @return bool false if file not found, does not return if found - just send the file
 */
function assignfeedback_androidmarker_pluginfile($course, $cm, context $context, $filearea, $args, $forcedownload) {
    global $USER, $DB;

    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    require_login($course, false, $cm);
    $itemid = (int)array_shift($args);
    $record = $DB->get_record('assign_grades', array('id' => $itemid), 'userid,assignment', MUST_EXIST);
    $userid = $record->userid;

    if (!$assign = $DB->get_record('assign', array('id' => $cm->instance))) {
        return false;
    }

    if ($assign->id != $record->assignment) {
        return false;
    }

    // Check is users feedback or has grading permission.
    if ($USER->id != $userid and !has_capability('mod/assign:grade', $context)) {
        return false;
    }

    $relativepath = implode('/', $args);

    $fullpath = "/{$context->id}/assignfeedback_androidmarker/$filearea/$itemid/$relativepath";

    $fs = get_file_storage();
    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        return false;
    }
    // Download MUST be forced - security!
    send_stored_file($file, 0, 0, true);
}

function send_submission($data){
  // json_encode the object to send to the marker
  $url = get_config(COMPONENT_NAME, "wsbase").DIRECTORY_SEPARATOR. "Submission_Manager.php" ;

  $s = curl_init();
  curl_setopt($s, CURLOPT_URL, $url);
  // Enable the post response.
  curl_setopt($s, CURLOPT_POST, true);

  // Attach encoded JSON string to the POST fields
  curl_setopt($s, CURLOPT_POSTFIELDS, json_encode($data));

  // Set the content type to application/json
  curl_setopt($s, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
  curl_exec($s);
  curl_close($s);
}
