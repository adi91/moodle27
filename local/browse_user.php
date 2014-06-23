<?php

require_once('../config.php');
require_once('lib.php');
require_once($CFG->libdir . '/poweruplib.php');
require_once($CFG->dirroot . '/user/filters/lib.php');

require_once($CFG->libdir.'/coursecatlib.php');


$courseid       = required_param('id', PARAM_INT);
$firstname      = optional_param('firstname', false, PARAM_RAW);
$lastname       = optional_param('lastname', false, PARAM_RAW);
$classid        = optional_param('class', false, PARAM_RAW);
$instituteid    = optional_param('institute', false, PARAM_INT);
$cal_fromdate   = optional_param('fromdate', false, PARAM_RAW);
$cal_todate     = optional_param('todate', false, PARAM_RAW);
$role           = optional_param('role', false, PARAM_RAW);
$campus         = optional_param('campus', false, PARAM_RAW);
$campus         = ($campus) ? $campus : 0;

$groupid        = optional_param('group', false, PARAM_INT);
$userid         = optional_param('user', false, PARAM_INT);
$page           = optional_param('page', 0, PARAM_INT);
$perpage        = optional_param('perpage', 10, PARAM_INT);
$sort           = optional_param('sort', 'user', PARAM_RAW);
$dir            = optional_param('dir', 'ASC', PARAM_ALPHA);


$baseurl        = new moodle_url('/group/browse_user.php', array('id' =>$courseid, 'perpage' => $perpage, 'firstname'=> $firstname, 'lastname'=> $lastname, 'institute' => $instituteid, 'campus' => $campus, 'class' => $classid, 'fromdate'=>$cal_fromdate, 'todate'=>$cal_todate, 'role'=> $role, 'sort'=>$sort, 'dir'=>$dir));

$sort_baseurl   = new moodle_url('/group/browse_user.php', array('id' =>$courseid, 'perpage' => $perpage, 'firstname'=> $firstname, 'lastname'=> $lastname, 'institute' => $instituteid, 'campus' => $campus, 'class' => $classid, 'fromdate'=>$cal_fromdate, 'todate'=>$cal_todate, 'role'=> $role));


$adv_serach_status = 'block';

//if(!empty($instituteid) || !empty($classid) || !empty($cal_fromdate) || !empty($cal_todate) || !empty($campus) ){
//	$adv_serach_status = 'block';
//}else{
//	$adv_serach_status = 'none';
//}

// Support either single group= parameter, or array groups[]
if ($groupid) {
    $groupids = array($groupid);
} else {
    $groupids = optional_param_array('groups', array(), PARAM_INT);
}
$singlegroup = (count($groupids) == 1);
$returnurl = $CFG->wwwroot . '/group/index.php?id=' . $courseid;
// Get the course information so we can print the header and
// check the course id is valid
$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

$url = new moodle_url('/group/browse_user.php', array('id' => $courseid, 'group' => $groupid));
//if ($userid) {
//    $url->param('user', $userid);
//}
//if ($groupid) {
//    $url->param('group', $groupid);
//}
$PAGE->set_url($url);
// Make sure that the user has permissions to manage groups.
require_login($course);

$PAGE->requires->js('/group/clientlib.js');

$PAGE->requires->js(new moodle_url($CFG->wwwroot . "/jquery/jquery1.8.3.js"), false);
$PAGE->requires->js(new moodle_url($CFG->wwwroot . "/jquery/jquery.ui1.9.2.js"), false);
$PAGE->requires->js(new moodle_url($CFG->wwwroot . "/jquery/jquery.livequery.js"), false);

$PAGE->requires->js(new moodle_url($CFG->wwwroot . "/jquery/dialouge/dialouge.js"), false);
$PAGE->requires->js(new moodle_url($CFG->wwwroot . "/jquery/dialouge/lib.js"), false);

$PAGE->requires->css(new moodle_url($CFG->wwwroot . "/jquery/jquery-ui.css"));

$context = context_course::instance($course->id);
if(!is_institute_admin()){
    if (!has_capability('moodle/course:managegroups', $context)) {
        redirect('/course/view.php', array('id' => $course->id)); // Not allowed to manage all groups
    }
}



$PAGE->set_pagelayout('noblock');
$PAGE->set_title(get_string('powerup') . ' : '.  get_string('browse_users'));
echo $OUTPUT->header();

if(has_capability('moodle/site:viewreports', $context) || is_institute_admin()){
    echo teacher_menue('Manage', $course->id);
}
// Add tabs
//$currenttab = 'groups';
//require('tabs.php');

$currenttab = 'browse_users';
$row = array();
$row[] = new tabobject('classmanagment', new moodle_url('/group/group_class.php', array('id' => $courseid)), get_string('classmanagment'));
$row[] = new tabobject('browse_users', new moodle_url('/group/browse_user.php', array('id' => $courseid)), get_string('browse_users'));
echo '<div class="groupdisplay">';
echo $OUTPUT->tabtree($row, $currenttab);
echo '</div>';


$displaylist = array();
$courselist = get_courses();

foreach ($courselist as $course) {
    if ($course->id != 1) {
        $courseid = $course->id;
    }
}

$groups = groups_get_all_groups($courseid);

foreach ($groups as $group) {
    $displaylist[$group->id] = $group->name;
}

$createnewclass_url = new moodle_url($CFG->wwwroot . "/group/group.php", array('courseid' => $courseid));


$deleteclass_url = new moodle_url($CFG->wwwroot . "/group/delete.php", array('courseid' => $courseid, 'groups' => $groupid));

//echo $OUTPUT->heading(format_string($course->shortname, true, array('context' => $context)) .' '.$strgroups, 3);
echo '<div class="tab_container">';

/* ss */
if($instituteid){
    $arrcampus = $DB->get_records_menu('campus', array('institution_id' => $instituteid), 'id', 'id,name');
}else{
    $arrcampus = $DB->get_records_menu('campus', array(), 'id', 'id,name');
}

if(is_institute_admin()){
    $arrcampus = $DB->get_records_menu('campus', array('institution_id' => $USER->institution), 'id', 'id,name');
}
natcasesort($arrcampus);

$arrclass = $DB->get_records_menu('groups', array(), 'id', 'id,name');

if(is_siteadmin() || is_institute_admin()){
    if($campus){
        $class_displaylist = campus_class_displaylist($campus, $instituteid);
    }elseif(!$campus && $instituteid){
        $class_displaylist = inst_class_displaylist($instituteid);
    }elseif(!$campus && !$instituteid){
        $class_displaylist = class_displaylist();
    }
}else{
    $class_displaylist = teachers_class($USER);
}


echo '<form name="userfilter" action="' . $baseurl . '" method="get">';
echo '<input type="hidden" name="id" value="' . $courseid . '">';
echo '<fieldset class="browse-user-filter">
				<div class="left-browse-user">';
					

                    echo'<div class="browseruser_role" >';
						echo'<div class="fitemtitle"><label for="id_role" class="browse-user-label">Role :</label></div>';
                        $arrrole = array('active' => 'Active Students', 'deactive_user' => get_string('deactive_user'),);
                        
                        if(is_siteadmin() || is_institute_admin()){
                            $default_roles = array("institutionadmin", "editingteacher", "student");
                        }else{
                            $default_roles = array("student");
                        }

                        $roles = $DB->get_records('role');    
                        $role_displaylist = array();

                        $role_displaylist[''] = get_string('select_role');
                        foreach($roles as $sis_role){
                            if(in_array($sis_role->shortname, $default_roles)){
								if($sis_role->shortname == 'student' && !is_siteadmin() && !is_institute_admin()){
                                    $student_role = array();
                                    $student_role[$sis_role->id] = get_string('allstudent');
//									$arrrole[$sis_role->id] = get_string('allstudent');
								}else{
									$arrrole[$sis_role->id] = get_string($sis_role->shortname);
								}								
                            }
                        }
                        
						if(!is_siteadmin() && !is_institute_admin()){
                            ksort($arrrole);
                            $arrrole = array_merge($student_role, $arrrole);
							echo html_writer::select($arrrole, "role", $role, array(), array('style' => ""));
                        }else if(is_siteadmin()){
                            $arrrole[1] = get_string('super_admin');
                            natcasesort($arrrole);
							echo html_writer::select($arrrole, "role", $role, array('' => get_string('alluser')), array('style' => ""));
                        }else{
                            
                            natcasesort($arrrole);
							echo html_writer::select($arrrole, "role", $role, array('' => get_string('alluser')), array('style' => ""));
						}
                        
					echo'</div>';
                    
					echo '<div class="browseruser_firstname">
						<div>
							<label class="browse-user-label" for="id_firstname">First Name :</label>
						</div>
						<div class="felement ftext"><input type="text" id="id_firstname" name="firstname" value="' . (isset($firstname) ? $firstname : $firstname) . '" /></div>
					</div>
					<div class="browseruser_lasttname">
						<div>
							<label class="browse-user-label" for="id_firstname">Last Name :</label>
						</div>
						<div class="felement ftext"><input type="text" id="id_lastname" name="lastname" value="' . (isset($lastname) ? $lastname : $lastname) . '"></div>
					</div>';
                    
                    echo '<div>';
                        echo '<div style="float: left; padding: 0 11px 0 0;margin:0 0 2px 0;">';
    //                        echo '<input type="button" name="id_adv" class="id_adv" id="id_submitbutton" value="Advanced Search" >';
                        echo '</div>
                        <div>
                            <input type="submit" name="id_submit" id="id_submit" value="Search" />
                        </div>
                    </div>';
                    
					
				echo '</div>';
                    
                    
				
                
               echo '<div id="adv_search" style="clear:both; padding:14px 0 0 0; display:'.($adv_serach_status).'">';
							
                            if(is_siteadmin()){
                                
                                $coursecat = coursecat::make_categories_list();
                                $inst_displaylist = array();

                                foreach($coursecat as $key=>$cat){
                                    if($key!=1){
                                        $inst_displaylist[$key] = $cat;
                                    }
                                }
                                natcasesort($inst_displaylist);
                                echo '<div class="browseruser_class" >';
                                echo '<div class="fitemtitle">';
                                    echo '<label for="id_class" class="browse-user-label">'.get_string('institute').' :</label>';
                                echo '</div>';
                                
                                $inst_title = ($instituteid) ? $inst_displaylist[$instituteid] : 'Select...';
                                echo html_writer::select($inst_displaylist, "institute", $instituteid, array('' => 'Select...'), array('style' => "max-width: 142px; ", 'class' => 'manageuser_inst', 'title' => $inst_title));
                                echo '</div>';
                            }
                            
                            if(is_siteadmin() || is_institute_admin()){
                                echo '<div class="campus_select" >';
                                    echo '<div class="fitemtitle"><label for="id_class" class="browse-user-name">Campus :</label></div>';
                                    echo '<div class="felement fselect">';
                                    $campus_title = ($campus) ? $arrcampus[$campus] : 'Select...';
                                        echo '<select id="id_campus" class="manageuser_campus" name="campus" style="max-width: 142px;" title ="'.$campus_title.'">';
                                            echo '<option value="">Select...</option>';
                                           foreach ($arrcampus as $key => $val) {
                                               echo '<option value="' . $key . '"  '.($campus == $key ? 'selected': '').' >' . $val . '</option>';
                                           }
                                       echo '</select>';
                                    echo '</div>';
                                echo '</div>';
                            }
                            
                            echo '<div class="browseruser_class" >';
                            echo '<div class="fitemtitle"><label for="id_class" class="browse-user-label">Class :</label></div>';
                                $class_title = ($classid) ? $class_displaylist[$classid] : get_string('select_class');
                                echo html_writer::select($class_displaylist, "class", $classid, array('' => get_string('select_class')), array('style' => "max-width: 142px;", 'class' => 'manageuser_class', 'title' => $class_title));
							echo '</div>';
                            
                            echo '<div class="selectclass3">';
                                echo '<label style="float:left;">Last Access :</label>';
                                echo '<input id="user_fromdate" value="'.$cal_fromdate.'" name="fromdate" size="20" />';
                            echo '</div>';

                            echo '<div class="unitheme">';
                                echo '<label style="float:left;">to</label>';
                                echo '<input id="user_todate" value="'.$cal_todate.'" name="todate" size="20" />';
                            echo '</div>';
                        
                        
					echo '</div>';
                        
                    
			echo '</fieldset>';



/* ss */


echo '<div style="clear:both;padding:10px;"></div>';


// if ($groupmemberroles = groups_get_members_by_role($groupid, $courseid, 'u.id, u.firstname, u.lastname')) {
echo '<div style="border: 1px solid #ccc; height: auto;">';


if($dir <> 'ASC'){
	$diricon = 'ASC';
	$sort_icon = "<img class='img-padding' src=\"" . $OUTPUT->pix_url('down','theme') . "\" alt=\"\" />";
}else{
	$diricon = 'DESC';
	$sort_icon = "<img class='img-padding' src=\"" . $OUTPUT->pix_url('up','theme') . "\" alt=\"\" />";
}


$table = new html_table();
$table->id = "class_users";

$table->attributes['class'] = 'generaltable';

$table->head = array();
$table->colclasses = array('table_headr');

$table->head[] = "<input type='checkbox' id='check_all' name='allcheck' onclick='checked_all(this.checked)' style='width:25px !important;' >";
$table->head[] = html_writer::tag('a', get_string('user').($sort == 'user'? $sort_icon : ''), array('href'=>new moodle_url($sort_baseurl, array('dir'=>$diricon, 'sort'=>'user' )), 'class'=>'sort_link_style'));
$table->head[] = html_writer::tag('a', get_string('email').($sort == 'email'? $sort_icon : ''), array('href'=>new moodle_url($sort_baseurl, array('dir'=>$diricon, 'sort'=>'email' )), 'class'=>'sort_link_style'));
$table->head[] = html_writer::tag('a', get_string('institution').($sort == 'institution'? $sort_icon : ''), array('href'=>new moodle_url($sort_baseurl, array('dir'=>$diricon, 'sort'=>'institution' )), 'class'=>'sort_link_style'));
$table->head[] = html_writer::tag('a', get_string('campus').($sort == 'campus'? $sort_icon : ''), array('href'=>new moodle_url($sort_baseurl, array('dir'=>$diricon, 'sort'=>'campus' )), 'class'=>'sort_link_style'));
$table->head[] = html_writer::tag('a', get_string('group').($sort == 'group'? $sort_icon : ''), array('href'=>new moodle_url($sort_baseurl, array('dir'=>$diricon, 'sort'=>'group' )), 'class'=>'sort_link_style'));
$table->head[] = html_writer::tag('a', get_string('role').($sort == 'role'? $sort_icon : ''), array('href'=>new moodle_url($sort_baseurl, array('dir'=>$diricon, 'sort'=>'role' )), 'class'=>'sort_link_style'));
$table->head[] = html_writer::tag('a', get_string('lastaccess').($sort == 'lastaccess'? $sort_icon : ''), array('href'=>new moodle_url($sort_baseurl, array('dir'=>$diricon, 'sort'=>'lastaccess' )), 'class'=>'sort_link_style'));

$table->head[] = '';

$sql = " select u.*,mrs.roleid, mcc.name as institute, mcc.status as institute_status, g.name as classname, mr.shortname from {user} u 
		LEFT JOIN {groups_members} gm ON gm.userid = u.id
		left join {groups} g on g.id = gm.groupid
		LEFT JOIN {course_categories} mcc ON mcc.id = u.institution
		left join {campus_classes} mcamp on mcamp.groupid = g.id 
		left join {campus} camp on camp.id = mcamp.campusid
		left join {role_assignments} mrs on mrs.userid = u.id
		left join {role} mr on mr.id = mrs.roleid
		where u.deleted = 0 ";

$sql_role = '';

if(is_siteadmin() || is_institute_admin()){
    if($role == 9){
        $sql_role = " and u.id in (select distinct userid from {role_assignments} where contextid != 1 and roleid = 9) ";
    }elseif($role == 10){
        $sql_role = " and u.id in (select distinct userid from {role_assignments} where roleid = 10) ";
    }elseif($role == 5 || $role == "active" || $role == "deactive_user"){
        $sql_role = " and u.id in (select distinct userid from {role_assignments} where  contextid =15  and roleid = 5) ";
    }elseif($role == 1){
        $sql_role = " and u.id in ($CFG->siteadmins) ";
    }
}else{
    $sql_role = " and u.id in (select distinct userid from {role_assignments} where  contextid =15  and roleid = 5) ";
}

//
//$sqlstud = " contextid =15  and roleid = 5) ";
//$sqlteacher = " contextid != 1 and roleid = 9) ";
//$sqlinstadmin = " roleid = 10) ";
        
$sqlwhere = '';

if($instituteid){
	$sqlwhere .= "  and u.institution = ".$instituteid;  
}
if($role){
    if($role == "active"){
        $sqlwhere .= "  and u.status = 1"; 
    }else if($role == "deactive_user"){
        $sqlwhere .= "  and u.status = 0"; 
    }
}
if($firstname){
	$sqlwhere .= "  and u.firstname like '".$firstname."%'";  
}
if($lastname){
	$sqlwhere .= "  and u.lastname like '".$lastname."%'";  
}
if($classid){
	$sqlwhere .= "  and gm.groupid = ".$classid;  
}

if($campus){
	$sqlwhere .= "  and mcamp.campusid  = ".$campus; 
}

if($cal_fromdate && $cal_todate) {
    $fromdate = strtotime($cal_fromdate);
    $todate = strtotime($cal_todate);
    
    $todate = $todate + (24*60*60);
    
    if($fromdate && $todate){
        $sqlwhere .= " and (u.lastaccess between $fromdate and $todate) ";  
    }
}

if(!is_siteadmin()){
    $sqlwhere .= " and institution = $USER->institution";
}

$sqlwhere .= " and u.id != $USER->id ";
switch($sort){
	case 'user':
	$sql_order = " order by u.firstname ";
	$sql_order .=$dir;
	break;
	case 'email':
	$sql_order = " order by u.email ";
	$sql_order .=$dir;
	break;
	case 'institution':
	$sql_order = " order by mcc.name ";
	$sql_order .=$dir;
	break;
	case 'campus':
	$sql_order = " order by camp.name ";
	$sql_order .=$dir;
	break;
	case 'group':
	$sql_order = " order by classname ";
	$sql_order .=$dir;
	break;
	case 'role':
	$sql_order = " order by mr.shortname ";
	$sql_order .=$dir;
	break;
	case 'lastaccess':
	$sql_order = " order by u.lastaccess ";
	$sql_order .=$dir;
	break;
	default:
	$sql_order = " order by u.firstname asc ";
	break;
	
}

$sql_final    = $sql.$sql_role.$sqlwhere.$sql_order;


$users = $DB->get_records_sql($sql_final);


if (count($users) > 0) {
    if (isset($page) && isset($perpage)) {
        $array_chunk = array_chunk($users, $perpage);
        $arr_iterative = $array_chunk[$page];
    } else {
        $arr_iterative = $users;
    }
    $usercount = count($users);

//    echo '<pre>';print_r($arr_iterative);exit;
//    die;
    
    foreach ($arr_iterative as $member) {
//        print_object($member);
        $member = array_to_object($member);

        $row = array();
        
        
        if($member->institute_status == 1){
            $row[] = '<input type="checkbox" class="user_chk"  name="user[]" value="'.$member->id.'"  style="width:25px !important;" />';
        }else{
            $row[] = '<input type="checkbox" class="user_chk"  name="user[]" value="'.$member->id.'" disabled = "disabled"  style="width:25px !important;" />';
        }
        
        $row[] = fullname($member, true);
        $row[] = $member->email;
        $row[] = (!empty($member->institute) ? $member->institute : 'NA' );
        
        
        $institution_sql = 'SELECT gm.userid,gs.name as institution,cm.name as campus FROM {groups_members} gm left join {groupings_groups} gg on gm.groupid = gg.groupid left join {groupings} gs on gg.groupingid = gs.id left join {campus_classes} cc on gm.groupid = cc.groupid left join {campus} cm on cc.campusid = cm.id where gm.userid = ' . $member->id;
        
        if ($institutions = $DB->get_records_sql($institution_sql)) {
            foreach ($institutions as $institution) {
               if ($institution->campus)
                    $row[] = $institution->campus;
                else
                    $row[] = 'NA';
            }
        }
        else {
            $row[] = 'NA';
        }

        $row[] = (!empty($member->classname) ? $member->classname : 'NA' );
        
        $row[] = (!empty($member->shortname) ? get_string($member->shortname) : 'NA' );
        
        if ($member->lastaccess) {
            $row[] = date('l d M Y', $member->lastaccess);
        } else {
            $row[] = 'NA';
        }


        $edit_user_url = new moodle_url($CFG->wwwroot . '/user/editadvanced.php', array('id' => $member->id, 'courseid' => $courseid, 'edit' => 1));

        $links = html_writer::link($edit_user_url, get_string('edit'), array('class' => 'dialouge', 'id' => 'edituser', 'data-title' => get_string('edit'), 'data-width' => '1080', 'data-height' => '600', 'return_url' => $baseurl));

		$deactive_url_ind = new moodle_url($CFG->wwwroot . "/group/user_action.php", array('command' => 'deactive', 'userids'=>$member->id));
		$active_url_ind = new moodle_url($CFG->wwwroot . "/group/user_action.php", array('command' => 'active', 'userids'=>$member->id));
        
		if($member->status == 1){
			$statuslinks = html_writer::link($deactive_url_ind, get_string('deactiveuserind'), array('class' => 'dialouge', 'data-title' => get_string('deactiveuserind'), 'data-width' => '500', 'data-height' => '250', 'return_url' => $url));
		}else{
			$statuslinks = html_writer::link($active_url_ind, get_string('activeuserind'), array('class' => 'dialouge', 'data-title' => get_string('activeuserind'), 'data-width' => '500', 'data-height' => '250', 'return_url' => $url));
		}


        if($member->institute_status == 1){
            if($member->roleid != 5){
                $row[] = $links;
            }else{
                $row[] = $links.'&nbsp;|&nbsp;'.$statuslinks;
            }
        }else{
            $row[] = get_string('inst_expired');
        }
        

        $table->data[] = $row;
    }


    $active_url = new moodle_url($CFG->wwwroot . "/group/user_action.php", array('command' => 'active'));
    $deactive_url = new moodle_url($CFG->wwwroot . "/group/user_action.php", array('command' => 'deactive'));
    $delete_user_url = new moodle_url($CFG->wwwroot . "/group/user_action.php", array('command' => 'delete'));

    echo '<div style="  float: right;padding: 15px 9px 6px 0;">';
    echo '<a class="dialouge btn btn-grey" id ="activate_users" href="' . $active_url . '" data-title="' . get_string('activeuser') . '" data-width="500" data-height="250" return_url = "'.$url.'" >' . get_string('activeuser') . '</a>';

    echo '<a class="dialouge btn btn-grey" id ="deactivate_users" href="' . $deactive_url . '" data-title="' . get_string('deactiveuser') . '" data-width="500" data-height="250"  return_url = "'.$url.'">' . get_string('deactiveuser') . '</a>';

    echo '<a class="dialouge btn btn-grey" id ="delete_users" href="' . $delete_user_url . '" data-title="' . get_string('deleteuser') . '" data-width="500" data-height="250"  return_url = "'.$url.'">' . get_string('deleteuser') . '</a>';
    echo '</div>';

    echo html_writer::table($table);
} else {
	echo "<div class='alertstyle margin-5'>".get_string('norecordfound', 'moodle')."</div>";
	$usercount = count($users);
}
echo '<div>';
echo $OUTPUT->paging_bar($usercount, $page, $perpage, $baseurl);
echo '</div>';
//}

$create_user_url = new moodle_url($CFG->wwwroot . '/user/editadvanced.php', array('id' => '-1', 'class' => $classid));

$upload_roaster_url = new moodle_url($CFG->wwwroot . '/admin/tool/uploaduser/index.php', array('id' => '-1'));

//$download_roaster_url = new moodle_url($CFG->wwwroot . '/admin/user/download_roster.php', array('id' => $groupid, 'course' => $courseid));

$download_roaster_url = new moodle_url($CFG->wwwroot . '/admin/user/manage_download_roster.php', array('id' => '2', 'perpage' => $perpage, 'firstname'=> $firstname, 'lastname'=> $lastname, 'institute' => $instituteid, 'campus' => $campus, 'class' => $classid, 'fromdate'=>$cal_fromdate, 'todate'=>$cal_todate, 'role'=> $role));

echo '<div style="clear:both;padding:2px;"></div>';


echo '<div style=" float: right;padding: 6px 0;text-align: right;">';
echo html_writer::tag('div', '<a class="dialouge btn btn-grey" id="dialouge" href="' . $create_user_url . '" return_url="' . $url . '" data-title="' . get_string('createnewuser') . '" data-width="1080" data-height="600">' . get_string('createuser') . '</a>', array('style' => 'float:left;padding:0 10px 0 0;'));

echo html_writer::tag('div', '<a class="dialouge btn btn-grey" id="dialouge" href="' . $upload_roaster_url . '" return_url="' . $url . '" data-title="' . get_string('uploadroster') . '" data-width="1080" data-height="600">' . get_string('uploadroster') . '</a>', array('style' => 'float:left;padding:0 10px 0 0;'));


if(count($users) > 0){
    echo html_writer::tag('div', '<a class="dialouge btn btn-grey" id="dialouge" href="' . $download_roaster_url . '" return_url="' . $url . '" data-title="' . get_string('downloadroster') . '" data-width="400" data-height="200">' . get_string('downloadroster') . '</a>', array('style' => 'float:left;padding:0 10px 0 0;'));

//    echo html_writer::tag('div', '<a class="dialouge btn btn-grey" id="dialouge" href="' . $download_roaster_url . '" return_url="' . $url . '" data-title="' . get_string('printroster') . '" data-width="400" data-height="200">' . get_string('printroster') . '</a>', array('style' => 'float:left;padding:0 10px 0 0;'));
}else{
    $alert_msg = get_string('norecordfound');
        
		echo html_writer::tag('div', '<a class="btn btn-grey" id="dialouge" onclick="alert(\''.$alert_msg.'\')" data-title="'.get_string('downloadroster').'" data-width="600" data-height="600">'.get_string('downloadroster').'</a>', array('style'=>'float:left;padding:0 10px 0 0;'));
        
//		echo html_writer::tag('div', '<a class="btn btn-grey" id="dialouge" onclick="alert(\''.$alert_msg.'\')" data-title="'.get_string('printroster').'" data-width="600" data-height="600">'.get_string('printroster').'</a>', array('style'=>'float:left;padding:0 10px 0 0;'));
}



echo '</div> ';
echo '</div> ';


echo '</div>';
echo $OUTPUT->footer();
