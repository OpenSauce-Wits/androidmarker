<?php
ignore_user_abort(true);
set_time_limit(0);

ob_start();
// do initial processing here
header('Connection: close');
header('Content-Length: '.ob_get_length());
ob_end_flush();
ob_flush();
flush();

// Input Variables
$data = json_decode(file_get_contents('php://input'), true);
$userid = $data['UserID'];
$AssignmentID = $data['AssignmentID'];

// Database table names.
$TABLE_ASSIGNFEEDBACK_ANDROIDMARKER = $data['assignfeedback_androidmarker'];
$TABLE_ANDROIDMARKER_TESTRESULT = $data['androidmarker_testresult'];
$TABLE_ANDROIDMARKER_COMPILATIONERROR = $data['androidmarker_errors'];
$dbhost = $data['dbhost'];
$dbuser = $data['dbuser'];
$dbpass = $data['dbpass'];
$db = $data['db'];
$SDK = $data['sdk'];
$DB = new mysqli($dbhost, $dbuser, $dbpass,$db) or die("Connect failed: %s\n". $DB -> error);

// Check connection
if (!$DB) {
	error_log( "No connection mate!" );
  	//die("Connection failed: " . mysqli_connect_error());
}

// Update the Assignment Submission record
$sql = "UPDATE mdl_".$TABLE_ASSIGNFEEDBACK_ANDROIDMARKER." SET status='Marking' WHERE assignment_id=$AssignmentID AND user_id=$userid";
if ($DB->query($sql) !== TRUE) {
	error_log("Error updating record: " . $DB->error);
}

// Make a view that shows build log
// For some reason the sdk root is not passed into the script. So we need to pass it ourselves
print shell_exec('ANDROID_SDK_ROOT="'.$SDK.'" bash MarkProject.sh > /dev/null 2>&1');

// Stores the results from all the shards in an array
$shardResults = array();
$shardCount = 0;
while(is_dir("$shardCount")){
	$shardResults = extract_results_from_html( "$shardCount/report.html", $shardResults);
	remove_directory("$shardCount");
	$shardCount+=1;
}


ob_end_clean();
// Would be best if the server could update the data.
// Then we wouldn't have to wait and cron jobs could work

// This code should be executed when the server returns the marked results
foreach ($shardResults as $tr) {
		$res = explode(' ',$tr);
		// Test result.

		$sql = 'INSERT INTO mdl_'.$TABLE_ANDROIDMARKER_TESTRESULT." (assignment_id, user_id, testname, result) VALUES($AssignmentID, $userid, '$res[0]', '$res[1]')";
		if ($DB->query($sql) !== TRUE) {
		  error_log("Error: " . $sql . "<br>" . $DB->error);
		}
}

// Update the Assignment Submission record
$sql = "UPDATE mdl_".$TABLE_ASSIGNFEEDBACK_ANDROIDMARKER." SET status='Marked' WHERE assignment_id=$AssignmentID AND user_id=$userid";
if ($DB->query($sql) !== TRUE) {
  error_log("Error updating record: " . $DB->error);
}

/*$compilationerrors = $results->compilationErrors;
foreach ($compilationerrors as $ce) {
		// Compilation error.
		$compilationerror = new stdClass();
		$compilationerror->columnnumber = $ce->columnNumber;
		$compilationerror->linenumber = $ce->lineNumber;
		$compilationerror->message = $ce->message;
		$compilationerror->position = $ce->position;
		$compilationerror->filename = $ce->javaFileName;
		$compilationerror->androidmarker_id = $androidmarkersubmission->id;

		$compilationerror->id = $DB->insert_record(self::TABLE_ANDROIDMARKER_COMPILATIONERROR, $compilationerror);
}*/

$DB -> close();
chdir('..');
remove_directory($AssignmentID."_".$userid);

function remove_directory($path) {
    if (!is_dir($path)) {return;}
    $files = glob($path . DIRECTORY_SEPARATOR . '{.,}*', GLOB_BRACE);
    @array_map('unlink', $files);
    @rmdir($path);
}

function extract_results_from_html($html, $result){
		$prev = "";
		$myfile = fopen($html, "r") or die("Unable to open report file!");
		while(!feof($myfile)) {
			$line = fgets($myfile);
			$clean_line = rtrim(html_entity_decode(strip_tags($line)));
			$arr = explode(" ",$clean_line);
			foreach($arr as $a){
				if($a === "passed" || $a === "failed"){
					$res = $prev." ".$a;
					if( !in_array($res, $result)) array_push( $result, $res);
				 }
				$prev = $a;
			}
		}
		fclose($myfile);
		return $result;
	}

?>
