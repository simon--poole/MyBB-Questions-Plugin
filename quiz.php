<?php
session_start();
//Functions used
define('IN_MYBB', 1); // Are we in MyBB? Yep!
require "./global.php"; // We need this to get the templates and database functions.
function get_random_numbers($num, $max){
	$array = array();
	while($num--){
		do {
			$c = rand(1, $max);
		} while (in_array($c, $array));
		$array[] = $c;
	}
	return $array;
}
function time_diff_conv($start, $s) {
    $t = array( //suffixes
        'd' => 86400,
        'h' => 3600,
        'm' => 60,
    );
    $s = abs($s - $start);
    foreach($t as $key => &$val) {
        $$key = floor($s/$val);
        $s -= ($$key*$val);
        $string .= ($$key==0) ? '' : $$key . "$key ";
    }
    return $string . $s. 's';
}
add_breadcrumb("Quiz", "quiz.php"); // This is the navbit part. People like to know where they are ;)
if(!$mybb->user['uid'] || $mybb->settings['enabled_t10q'] != 1){
	error_no_permission();
} else {
	$reg = "/".$mybb->settings['t10q_to_group']."/";
	$user2= $mybb->user['additionalgroups'];
	if($user2!=""){
		if(preg_match($reg,$user2)) error("<br />You already have access to the protected forum, you do not need to re-do the quiz.<br /><br />", "Quiz Already Complete!");
	}
}
$act = $mybb->input['action'];
if(!$act || $act=="start"){
	$uid = intval($mybb->user['uid']);
	$query = $db->simple_select("t10q_sessions", "*", "`uid`='".$uid."'");
	$session = $db->num_rows($query);
	$result = $db->fetch_array($query);
	if($session > 0){
		$complete = $result['completed'];
		if($complete == "true"){
			$time = $result['time'];
			$timeleft = time_diff_conv(time(), $time);
			if(time() < $time) error("You have already entered the quiz recently. You will be able to re-enter the quiz in $timeleft.");
			else $db->delete_query("t10q_sessions","`uid`='".$uid."'");
		}
	}
	$query = $db->simple_select("t10q_questions","*");
	$rows = $db->num_rows($query);
	$questions = $mybb->settings['t10q_num_questions'];
	if($questions > $rows) error('<br />Not enough questions added yet! The setting for number of questions is higher than the number of questions added.<br /><br />', 'Not enough questions!');
	$_SESSION['questions'] =  get_random_numbers($mybb->settings['t10q_num_questions'], intval($rows+1));
	$startmsg = $mybb->settings['t10q_start_msg'];
	$content = "<tr><td class='trow1'><center><br />
		$startmsg<br /><br />
	</center></td></tr><tr><td class='trow2'><center><br />
	<form action='quiz.php' method='POST'>
	<input type='hidden' name='action' value='savequestions' />
	<input type='submit' value='Start!' /><br /><br />
	</form></center>
	</td></tr>";
}
if($act == "savequestions"){
	$uid = $mybb->user['uid'];
	$query = $db->query("SELECT * FROM  `".TABLE_PREFIX."t10q_sessions` WHERE `uid`=$uid");
	$num = $db->num_rows($query);
	if($num == 0 ){
		$questions = implode("|",$_SESSION['questions']);
		$array = array(
			"uid"=>$uid,
			"questions"=>$db->escape_string($questions),
		);
		$db->insert_query("t10q_sessions", $array);
	} else {
		$session = $db->fetch_array($query);
		$_SESSION['questions'] = explode("|", $session['questions']);
	}
	header("Location: quiz.php?action=doquiz");
}
if($act == "doquiz"){
	$i=0;
	if(!isset($_SESSION['questions'])) header('Location: quiz.php');
	foreach($_SESSION['questions'] as $qid){
		$td = ($i++%2 == 0 ? 1 : 2);
		$query = $db->simple_select("t10q_questions", "*", "`qid`='".$qid."'");
		$question = $db->fetch_array($query);
		if($db->num_rows($query) > 0){
			$echo .= "<tr><td class='trow".$td."'><br />�<b>".$question['question']."</b><br /><br />";
			$an = 1;
			foreach(explode("\n",$question['answers']) as $answer){
				$echo .= "���<input type='radio' name='".$qid."' value='".$an."'/> $answer<br /><br />";
				$an++;
			}
			$echo .= "</td></tr>";
		}
	}
	$content = "<tr><td class='trow1' style='margin-left: 50px!important;'><center><form action='quiz.php?action=mark' method='POST'><br />
		$echo <tr><td class='trow1'><input type='submit' value='Submit!' onclick='return check()'/></td></tr>
	<br /><br /></form></center></td></tr>";
}
if($act == "mark"){
	if(!isset($_SESSION['questions'])) header('Location: quiz.php');
	$correct = 0;
	$incorrect = 0;
	$uid = intval($mybb->user['uid']);
	foreach($_SESSION['questions'] as $qid){
		$uanswer = $mybb->input[$qid];
		$query = $db->simple_select("t10q_questions","*","`qid`='".$qid."'");
		$answer1 = $db->fetch_array($query);
		$answer = $answer1['answer'];
		if($answer == $uanswer){
			$correct++;
			$array = array(
				"correct"=>$answer1['correct'] + 1,
			);
			$db->update_query("t10q_questions",$array,"`qid`=$qid");
		}
		else {
			$array = array(
				"incorrect"=>$answer1['incorrect'] + 1,
			);
			$db->update_query("t10q_questions",$array,"`qid`=$qid");
			$incorrect++;
		}
	}
	$pass = $mybb->settings['t10q_passrate'];
	if($correct >= $pass){
		$result = $mybb->user['additionalgroups'];
		if(empty($result)){
			$groups = $mybb->settings['t10q_to_group'];
		} else {
			$groups = $result.",".$mybb->settings['t10q_to_group'];
		}
		$array = array(
			"additionalgroups" => $groups,
		);
		$db->update_query("users",$array,"`uid`='".$uid."'");
		$content = "<tr><td class='trow1'>".$mybb->settings['t10q_start_msg']."</td></tr>";#
		unset($_SESSION['questions']);
	} else {
		$setting = $mybb->settings['t10q_cooldown'];
		$time = time() + ($setting * 60);
		$array = array(
			"completed"=>"true",
			"time"=>$time,
			"result"=>$correct,
		);
		$db->update_query("t10q_sessions",$array,"`uid`='".$uid."'");
		$content = "<tr><td class='trow1'>".$mybb->settings['t10q_fail_msg']."</td></tr>";
		unset($_SESSION['questions']);
	}
}
$message = "
<html>
<head>
<title>{$mybb->settings['bbname']} - Quiz</title>
{$headerinclude}
<script>
function check(){
	return confirm('Are you sure you want to submit? Please make sure you have answered all the questions to the best of your ability.');
}
</script>
</head>
<body>
{$header}
<table border='0' cellspacing='1' cellpadding='4' class='tborder'>
<thead>
<tr>
<td class='thead' colspan='5'>
<div><strong>Quiz</strong><br /><div class='smalltext'></div></div>
</td>
</tr>
</thead>
<tbody>
$content
</tbody>
</table>
{$footer}
</body>
</html>
";
output_page($message);
?>