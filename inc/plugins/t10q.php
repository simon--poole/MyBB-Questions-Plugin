<?php
//Disallow Direct initialization For Security Reasons
if(!defined("IN_MYBB"))
{
    die("You Cannot Access This File Directly. Please Make Sure IN_MYBB Is Defined.");
}

//Hooks
$plugins->add_hook('admin_config_menu', 't10q_admin_tools_menu');
$plugins->add_hook('admin_config_action_handler', 't10q_admin_tools_action_handler');
$plugins->add_hook('admin_config_permissions', 't10q_admin_tools_permissions');
$plugins->add_hook('newthread_start', 't10q_check_permissions');

//Info
function t10q_info(){
return array(
        "name"  => "Automated Quiz",
        "description"=> "Automated Quiz",
        "website"        => "",
        "author"        => "Simon Poole",
        "authorsite"    => "",
        "version"        => "0.8",
        "guid"             => "7491314ec6e2b615f75c5ceb14e21ca3",
        "compatibility" => "16*"
    );
}

function t10q_activate()
{
	global $db;
	$t10q_setting = array(
        	'value'        => '1',
    	);
    	$db->update_query("settings", $t10q_setting, "name = 'enabled_t10q'");
}
function t10q_install(){
	global $db;
	$t10q_group = array(
       		'gid'    => 'NULL',
        	'name'  => 't10q',
        	'title'      => "Automated Quiz",
        	'description'    => "Automated Quiz",
        	'disporder'    => "1",
        	'isdefault'  => 'no',
    	);
    	$db->insert_query('settinggroups', $t10q_group);
 	$gid = $db->insert_id();
 	$t10q_setting = array(
        	'sid'            => 'NULL',
        	'name'        => 'enabled_t10q',
        	'title'            => "Quiz Enabled",
        	'description'    => "Enable or disable the questions system without removing saved questions.",
        	'optionscode'    => 'radio \n 1=Enable \n 0=Disable',
        	'value'        => '1',
        	'disporder'        => 1,
        	'gid'            => intval($gid),
    	);
	$t10q_setting2 = array(
        	'sid'            => 'NULL',
        	'name'        => 't10q_to_group',
        	'title'            => "Completion Group",
        	'description'    => "The group members are moved to after successfully completing the quiz.",
        	'optionscode'    => 'text',
        	'value'        => '1',
        	'disporder'        => 2,
        	'gid'            => intval($gid),
    	);
    	$t10q_setting3 = array(
        	'sid'            => 'NULL',
        	'name'        => 't10q_passrate',
        	'title'            => "Pass Score",
        	'description'    => "Entrants must answer this many questions correctly to pass. For 100%, set to the same as number of questions.",
        	'optionscode'    => 'text',
        	'value'        => '10',
        	'disporder'        => 3,
        	'gid'            => intval($gid),
    	);
    	$t10q_setting4 = array(
        	'sid'            => 'NULL',
        	'name'        => 't10q_num_questions',
        	'title'            => "Number of questions",
        	'description'    => "Number of questions to show on quiz page.",
        	'optionscode'    => 'text',
        	'value'        => '10',
        	'disporder'        => 3,
        	'gid'            => intval($gid),
    	);
    	$t10q_setting5 = array(
        	'sid'            => 'NULL',
        	'name'        => 't10q_protected_fid',
        	'title'            => "Protected Forum ID",
        	'description'    => "Forum to deny entry to.",
        	'optionscode'    => 'text',
        	'value'        => '10',
        	'disporder'        => 4,
        	'gid'            => intval($gid),
    	);
    	$t10q_setting6 = array(
        	'sid'            => 'NULL',
        	'name'        => 't10q_error_msg',
        	'title'            => "Error Message",
        	'description'    => "Error to show upon denied access. HTML can be used.",
        	'optionscode'    => 'textarea',
        	'value'        => 'You are not allowed to access this forum.',
        	'disporder'        => 5,
        	'gid'            => intval($gid),
    	);
    	$t10q_setting7 = array(
        	'sid'            => 'NULL',
        	'name'        => 't10q_start_msg',
        	'title'            => "Quiz Start Message",
        	'description'    => "Message to show at start of the quiz.",
        	'optionscode'    => 'textarea',
        	'value'        => 'If you complete this quiz successfully...',
        	'disporder'        => 5,
        	'gid'            => intval($gid),
    	);
    	$t10q_setting8 = array(
        	'sid'            => 'NULL',
        	'name'        => 't10q_fail_msg',
        	'title'            => "Quiz Fail Message",
        	'description'    => "Message to show at the end of the quiz if the entrant fails.",
        	'optionscode'    => 'textarea',
        	'value'        => 'Unfortunately, you failed. You must have atleast X% correct to pass.',
        	'disporder'        => 5,
        	'gid'            => intval($gid),
    	);
    	$t10q_setting9 = array(
        	'sid'            => 'NULL',
        	'name'        => 't10q_pass_msg',
        	'title'            => "Quiz Pass Message",
        	'description'    => "Message to show at the end of the quiz if the entrant passes.",
        	'optionscode'    => 'textarea',
        	'value'        => 'Congratulations! You passed the quiz!',
        	'disporder'        => 5,
        	'gid'            => intval($gid),
    	);
    	$t10q_setting10 = array(
        	'sid'            => 'NULL',
        	'name'        => 't10q_cooldown',
        	'title'            => "Time after unsuccessful attempt",
        	'description'    => "Time after unsuccesful attempt (in minutes).",
        	'optionscode'    => 'text',
        	'value'        => '10',
        	'disporder'        => 5,
        	'gid'            => intval($gid),
    	);
    	$db->insert_query('settings', $t10q_setting);
    	$db->insert_query('settings', $t10q_setting2);
    	$db->insert_query('settings', $t10q_setting3);
    	$db->insert_query('settings', $t10q_setting4);
    	$db->insert_query('settings', $t10q_setting5);
    	$db->insert_query('settings', $t10q_setting6);
    	$db->insert_query('settings', $t10q_setting7);
    	$db->insert_query('settings', $t10q_setting8);
    	$db->insert_query('settings', $t10q_setting9);
    	$db->insert_query('settings', $t10q_setting10);
    	$db->query("CREATE TABLE ".TABLE_PREFIX."t10q_questions (qid int NOT NULL AUTO_INCREMENT,question text,answers text,answer int,correct int,incorrect int,UNIQUE (qid))");
    	$db->query("CREATE TABLE ".TABLE_PREFIX."t10q_sessions (uid int,questions text,completed text,result text,time text,UNIQUE (uid))");
  	rebuild_settings();
}
function t10q_uninstall(){
	global $db;
	$db->delete_query("settinggroups", "name = 't10q'");
	$db->delete_query("settings", "name = 'enabled_t10q'");
	$db->delete_query("settings", "name = 't10q_to_group'");
	$db->delete_query("settings", "name = 't10q_passrate'");
	$db->delete_query("settings", "name = 't10q_num_questions'");
	$db->delete_query("settings", "name = 't10q_protected_fid'");
	$db->delete_query("settings", "name = 't10q_error_msg'");
	$db->delete_query("settings", "name = 't10q_start_msg'");
	$db->delete_query("settings", "name = 't10q_fail_msg'");
	$db->delete_query("settings", "name = 't10q_pass_msg'");
	$db->delete_query("settings", "name = 't10q_cooldown'");
	$db->query("DROP TABLE ".TABLE_PREFIX."t10q_questions");
	$db->query("DROP TABLE ".TABLE_PREFIX."t10q_sessions");
}
function t10q_deactivate()
{
	global $db;
	$t10q_setting = array(
        	'value'        => '0',
    	);
    	$db->update_query("settings", $t10q_setting, "name = 'enabled_t10q'");
}
function t10q_is_installed(){
	global $db;
	return $db->table_exists("t10q_questions");
}


function t10q_admin_tools_menu(&$sub_menu)
{
	$sub_menu[] = array('id' => 't10q', 'title' => 'Automated Quiz', 'link' => 'index.php?module=config-t10q');
}

function t10q_admin_tools_action_handler(&$actions)
{
    $actions['t10q'] = array('active' => 't10q', 'file' => 'questions.php');
}

function t10q_admin_tools_permissions(&$admin_permissions)
{
	$admin_permissions['t10q'] = "Automated Quiz";
}
function t10q_check_permissions(){
	global $mybb;
	if($mybb->settings['enabled_t10q'] == 1){
		$reg = "/".$mybb->settings['t10q_to_group']."/";
		$user2= $mybb->user['additionalgroups'];
		$fid = $mybb->input['fid'];
		if($fid == $mybb->settings['t10q_protected_fid']){
				if($user2!=""){
					if(!preg_match($reg,$user2)) error($mybb->settings['t10q_error_msg']);
				}
				else error($mybb->settings['t10q_error_msg']);
		}
	}
}
?>