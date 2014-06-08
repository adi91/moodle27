<?php

/*
 * Specfic custom library file for talentain application modyfication
 * By : Aditya
 * Created at : 7june14
 * Last modyfied : 7june14
 * 
 */

function is_manager(){
    global $CFG,$DB,$USER;
    
    $role = $DB->get_record('role', array('shortname' => 'manager'));
	$user_role = $DB->get_records('role_assignments', array('userid'=>$USER->id,'roleid'=>$role->id, 'contextid' =>SITEID));
	if($user_role){
		return true;
	}else{
		return false;
	}
}
function is_editingteacher(){
    global $CFG,$DB,$USER;
    
    $role = $DB->get_record('role', array('shortname' => 'editingteacher'));
	$user_role = $DB->get_records('role_assignments', array('userid'=>$USER->id,'roleid'=>$role->id, 'contextid' =>SITEID));
	if($user_role){
		return true;
	}else{
		return false;
	}
}
function is_teacher(){
    global $CFG,$DB,$USER;
    
    $role = $DB->get_record('role', array('shortname' => 'teacher'));
	$user_role = $DB->get_records('role_assignments', array('userid'=>$USER->id,'roleid'=>$role->id, 'contextid' =>SITEID));
	if($user_role){
		return true;
	}else{
		return false;
	}
}
function is_student(){
    global $CFG,$DB,$USER;
    
    $role = $DB->get_record('role', array('shortname' => 'student'));
	$user_role = $DB->get_records('role_assignments', array('userid'=>$USER->id,'roleid'=>$role->id, 'contextid' =>SITEID));
	if($user_role){
		return true;
	}else{
		return false;
	}
}

function is_authenticateduser(){
    global $CFG,$DB,$USER;
    
    $role = $DB->get_record('role', array('shortname' => 'user'));
	$user_role = $DB->get_records('role_assignments', array('userid'=>$USER->id,'roleid'=>$role->id, 'contextid' =>SITEID));
	if($user_role){
		return true;
	}else{
		return false;
	}
}


?>
