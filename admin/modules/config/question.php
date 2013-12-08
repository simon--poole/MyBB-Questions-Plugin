<?php
//MyBB 10 Questions Admin Section
//Disallow Direct initialization For Security Reasons
if(!defined("IN_MYBB"))
{
	die("You Cannot Access This File Directly. Please Make Sure IN_MYBB Is Defined.");
}
$page->add_breadcrumb_item("Automated Quiz", "index.php?module=config-t10q");
$query = $db->query("SELECT * FROM `".TABLE_PREFIX."t10q_questions");
$length = $db->num_rows($query);
$sub_tabs['existing'] = array(
            'title' => "Existing Questions",
            'link' => "index.php?module=config-t10q&action=view",
            'description' => "View existing questions. Total $length questions.",
);
$sub_tabs['add'] = array(
            'title' => "Add Question",
            'link' => "index.php?module=config-t10q&action=add",
            'description' => "Add a question.",
        );
$sub_tabs['settings'] = array(
            'title' => "Settings",
            'link' => "index.php?module=config-t10q&action=settings",
            'description' => "Edit settings.",
        );
$page->output_header("Question System");
if($mybb->settings['enabled_t10q'] == 0) flash_message("Automatic Quiz is currently disabled. You can change fix this by activating the plugin or through Settings -> Automatic Quiz..", 'error');
$act = $mybb->input['action'];
if(($act=="view") || ($act=="") || (!$act)){
	$page->output_nav_tabs($sub_tabs, 'existing');
	$form = new Form("index.php?module=config-t10q", "post", "t10q");
	$existing = new FormContainer("Existing Questions");
	$existing->output_row_header("Question");
	$existing->output_row_header("Answers");
	$existing->output_row_header("Correct");
	$existing->output_row_header("Edit");
	$query = $db->query("SELECT * FROM `".TABLE_PREFIX."t10q_questions`");
	$amt = $db->num_rows($query);
	while($result=$db->fetch_array($query)){
		$question = $result['question'];
		$ans = explode("\n", $result['answers']);
		$answers = "<ol style='display: list-item!important;overflow:hidden!important;'>";
		for($i=0;$i<count($ans);$i++){
			if($i+1 == $result['answer']) $ans[$i] = "<b>".$ans[$i]."</b>";
			$answers .= "<li>".$ans[$i]."</li>";
		}
		$answers.="</ol>";
		$correct = $result['correct'];
		$incorrect = $result['incorrect'];
		$total = $correct + $incorrect;
		$perc="$correct / $total answered correctly";
		$existing->construct_row();
		$existing->output_cell($question);
		$existing->output_cell($answers);
		$existing->output_cell($perc);
		$existing->output_cell("<a href='index.php?module=config-t10q&action=edit&qid=".$result['qid']."'>Edit</a>");
	}
	$result=$db->fetch_array($query);
	$question = $result['question'];
	$ans = explode("\n", $result['answers']);
	$answers = "<ol style='display: list-item!important;overflow:hidden!important;'>";
	for($i=0;$i<count($ans);$i++){
		if($i+1 == $result['answer']) $ans[$i] = "<b>".$ans[$i]."</b>";
		$answers .= "<li>".$ans[$i]."</li>";
	}
	$answers.="</ol>";
	$correct = $result['correct'];
	$incorrect = $result['incorrect'];
	$total = $correct + $incorrect;
	$perc="$correct / $total answered correctly";
	$existing->construct_row();
	$existing->output_cell($question);
	$existing->output_cell($answers);
	$existing->output_cell($perc);
	$existing->output_cell("<a href='index.php?module=config-t10q&action=edit&qid=".$result['qid']."'>Edit</a>");
	$existing->end();
}
else if ($act=="add"){
	$page->output_nav_tabs($sub_tabs, 'add');
	$submit = $mybb->input['submit'];
	if($submit){
		$question = $db->escape_string($_POST['question']);
		$answers = $db->escape_string($_POST['answers']);
		$answer = $db->escape_string($_POST['correct']);
		$array = array(
			"qid"=>'NULL',
			"question"=>$question,
			"answers"=>$answers,
			"answer"=>$answer,
			"correct"=>"0",
			"incorrect"=>"0",
		);
		$db->insert_query("t10q_questions", $array);
		if(!$db->error){
			flash_message("Question added successfully!", 'success');
			redirect("index.php?module=config-t10q&action=view","Redirecting you back to list of questions.","Question added successfully.");
		} else {
			flash_message("$db->error", 'error');
			redirect("index.php?module=config-t10q&action=view","There was unfortunately an error adding the question.","Error.");
		}
	} else {
		$form = new Form("index.php?module=config-t10q&action=add&submit=true", "post", "t10q");
		$existing = new FormContainer("Add Question");
		$existing->output_row("Question", $form->generate_text_box("question","Your Question Here"));
		$existing->output_row("Possible Answers", "Seperate each answer with a newline.<br />".$form->generate_text_area("answers","Answer 1\nAnswer 2\nEtc"));
		$existing->output_row("Correct Answer Number", "Correct answer number from answers above.<br />".$form->generate_text_box("correct","1"));
		$existing->output_row("", $form->generate_submit_button("Add"));
		$existing->end();
	}
}
else if ($act=="edit"){
	$confirm = $mybb->input['confirm'];
	$qid = intval($mybb->input['qid']);
	if($confirm){
		$question = $db->escape_string($_POST['question']);
		$answers = $db->escape_string($_POST['answers']);
		$answer = $db->escape_string($_POST['correct']);
		$array = array(
			"question"=>$question,
			"answers"=>$answers,
			"answer"=>$answer,
		);
		$db->update_query("t10q_questions", $array,"`qid`=$qid");
		if(!$db->error){
			flash_message("Question updated successfully!", 'success');
			redirect("index.php?module=config-t10q&action=view","Redirecting you back to list of questions.","Question edited successfully.");
		} else {
			flash_message("$db->error", 'error');
			redirect("index.php?module=config-t10q&action=view","There was unfortunately an error editing the question.","Error.");
		}
	} else {
		$query = $db->query("SELECT * FROM `".TABLE_PREFIX."t10q_questions` where `qid`='".$qid."'");
		$results = $db->fetch_array($query);
		$question = $results['question'];
		$answers = $results['answers'];
		$answer = $results['answer'];
		$form = new Form("index.php?module=config-t10q&action=edit&confirm=true&qid=$qid", "post", "t10q");
		$existing = new FormContainer("Edit Questions");
		$existing->output_row("Question", $form->generate_text_box("question",$question));
		$existing->output_row("Possible Answers", "Seperate each answer with a newline.<br />".$form->generate_text_area("answers",$answers));
		$existing->output_row("Correct Answer Number", "Correct answer number from answers above.<br />".$form->generate_text_box("correct",$answer));
		$existing->output_row("", $form->generate_submit_button("Edit"));
		$existing->end();
	}
}
else if ($act=="settings"){
	$page->output_nav_tabs($sub_tabs, 'settings');
	$submit = $mybb->input['submit'];
	if($submit){
		$array = array(
			"value" => $db->escape_string($mybb->input['t10q_to_group']),
		);
		$db->update_query("settings",$array,"`name`='t10q_to_group'");
		$array = array(
			"value" => $db->escape_string($mybb->input['t10q_passrate']),
		);
		$db->update_query("settings",$array,"`name`='t10q_passrate'");
		$array = array(
			"value" => $db->escape_string($mybb->input['t10q_num_questions']),
		);
		$db->update_query("settings",$array,"`name`='t10q_num_questions'");
		$array = array(
			"value" => $db->escape_string($mybb->input['t10q_protected_fid']),
		);
		$db->update_query("settings",$array,"`name`='t10q_protected_fid'");
		$array = array(
			"value" => $db->escape_string($mybb->input['t10q_error_msg']),
		);
		$db->update_query("settings",$array,"`name`='t10q_error_msg'");
		$array = array(
			"value" => $db->escape_string($mybb->input['t10q_start_msg']),
		);
		$db->update_query("settings",$array,"`name`='t10q_start_msg'");
		$array = array(
			"value" => $db->escape_string($mybb->input['t10q_fail_msg']),
		);
		$db->update_query("settings",$array,"`name`='t10q_fail_msg'");
		$array = array(
			"value" => $db->escape_string($mybb->input['t10q_pass_msg']),
		);
		$db->update_query("settings",$array,"`name`='t10q_pass_msg'");
		$array = array(
			"value" => $db->escape_string($mybb->input['t10q_cooldown']),
		);
		$db->update_query("settings",$array,"`name`='t10q_cooldown'");
		rebuild_settings();
		if(!$db->error){
			flash_message("Settings updated successfully!", 'success');
			redirect("index.php?module=config-t10q&action=view","Redirecting you back to list of questions.","Settings edited successfully.");
		} else {
			flash_message("$db->error", 'error');
			redirect("index.php?module=config-t10q&action=view","There was unfortunately an error editing the settings.","Error.");
		}
	} else {
		$form = new Form("index.php?module=config-t10q&action=settings&submit=true", "post", "t10q");
		$existing = new FormContainer("Settings");
		$existing->output_row("Completion Group","The group members are moved to after successfully completing the quiz.<br /><br />".
		$form->generate_text_box("t10q_to_group", $mybb->settings['t10q_to_group']));
		$existing->output_row("Pass Score","Entrants must answer this many questions correctly to pass. For 100%, set to the same as number of questions.<br /><br />".
		$form->generate_text_box("t10q_passrate", $mybb->settings['t10q_passrate']));
		$existing->output_row("Number of questions","Number of questions to show on quiz page.<br /><br />".
		$form->generate_text_box("t10q_num_questions", $mybb->settings['t10q_num_questions']));
		$existing->output_row("Protected Forum ID","Forum to deny entry to.<br /><br />".
		$form->generate_text_box("t10q_protected_fid", $mybb->settings['t10q_protected_fid']));
		$existing->output_row("Quiz Start Message","Message to show at start of the quiz.<br /><br />".
		$form->generate_text_area("t10q_start_msg", $mybb->settings['t10q_start_msg']));
		$existing->output_row("Quiz Fail Message","Message to show at the end of the quiz if the entrant fails.<br /><br />".
		$form->generate_text_area("t10q_fail_msg", $mybb->settings['t10q_fail_msg']));
		$existing->output_row("Quiz Pass Message","Message to show at the end of the quiz if the entrant passes.<br /><br />".
		$form->generate_text_area("t10q_pass_msg", $mybb->settings['t10q_pass_msg']));
		$existing->output_row("Error Message","Error to show upon denied access. HTML can be used.<br /><br />".
		$form->generate_text_area("t10q_error_msg", $mybb->settings['t10q_error_msg']));
		$existing->output_row("Time after unsuccessful attempt","Time after unsuccessful attempt (in minutes).<br /><br />".
		$form->generate_text_box("t10q_cooldown", $mybb->settings['t10q_cooldown']));
		$existing->output_row($form->generate_submit_button("Save Settings"));
		$existing->end();
		rebuild_settings();
	}
}
?>