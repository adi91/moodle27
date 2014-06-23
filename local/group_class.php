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
 * The main group management user interface.
 *
 * @copyright 2006 The Open University, N.D.Freear AT open.ac.uk, J.White AT open.ac.uk
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package   core_group
 */
require_once('../config.php');
require_once('lib.php');
require_once($CFG->libdir .'/poweruplib.php');

require_once($CFG->libdir.'/coursecatlib.php');

$courseid       = required_param('id', PARAM_INT);
$groupid        = optional_param('group', false, PARAM_INT);
$instituateid   = optional_param('instituate', 0, PARAM_INT);
$campusid       = optional_param('campus', 0, PARAM_INT);
$userid         = optional_param('user', false, PARAM_INT);
$action         = groups_param_action();

$page           = optional_param('page', 0, PARAM_INT);
$perpage        = optional_param('perpage', 10, PARAM_INT);
$sort = optional_param('sort', 'firstname', PARAM_RAW);
$dir  = optional_param('dir', 'ASC', PARAM_ALPHA);
$sort_baseurl        = new moodle_url('/group/group_class.php', array('id' =>$courseid, 'group'=>$groupid, 'instituate'=>$instituateid, 'campus'=>$campusid, 'user'=>$userid  ));
// Support either single group= parameter, or array groups[]

if($groupid){
    $group = $DB->get_record('groups', array('id' => $groupid));
    $groupid        = ($group) ? $group->id : false;
}
if($instituateid){
    $instituate = $DB->get_record('course_categories', array('id' => $instituateid));
    $instituateid   = ($instituate) ? $instituate->id : false;
}
if($campusid){
    $campus = $DB->get_record('campus', array('id' => $campusid));
    $campusid       = ($campus) ? $campus->id : false;
}

$course = $DB->get_record('course', array('id'=>$courseid), '*', MUST_EXIST);
require_login($course);

if(!is_siteadmin()){
    $instituateid = $USER->institution;
}
if ($groupid) {
    $groupids = array($groupid);
} else {
    $groupids = optional_param_array('groups', array(), PARAM_INT);
}

$baseurl        = new moodle_url('/group/group_class.php', array('id' => $courseid, 'perpage' => $perpage, 'instituate' => $instituateid, 'campus' => $campusid, 'group' => $groupid,'sort'=>$sort, 'dir'=>$dir));



$singlegroup = (count($groupids) == 1);
$returnurl = $CFG->wwwroot.'/group/index.php?id='.$courseid;
// Get the course information so we can print the header and
// check the course id is valid

$url = new moodle_url('/group/group_class.php', array('id'=>$courseid, 'group' => $groupid));
if ($userid) {
    $url->param('user', $userid);
}
if ($groupid) {
    $url->param('group', $groupid);
}
$PAGE->set_url($url);
// Make sure that the user has permissions to manage groups.

$PAGE->requires->js('/group/clientlib.js');

    $PAGE->requires->js(new moodle_url($CFG->wwwroot."/jquery/jquery1.8.3.js"), false);
    $PAGE->requires->js(new moodle_url($CFG->wwwroot."/jquery/jquery.ui1.9.2.js"), false);
    $PAGE->requires->js(new moodle_url($CFG->wwwroot."/jquery/jquery.livequery.js"), false);
    
    $PAGE->requires->js(new moodle_url($CFG->wwwroot."/jquery/dialouge/dialouge.js"), false);
    $PAGE->requires->js(new moodle_url($CFG->wwwroot."/jquery/dialouge/lib.js"), false);
    
    $PAGE->requires->css(new moodle_url($CFG->wwwroot."/jquery/jquery-ui.css"));

$context = context_course::instance($course->id);
if (!has_capability('moodle/course:managegroups', $context) && !is_institute_admin()) {
    redirect('/course/view.php', array('id'=>$course->id)); // Not allowed to manage all groups
}
// Check for multiple/no group errors
if (!$singlegroup) {
    switch($action) {
        case 'ajax_getmembersingroup':
        case 'showgroupsettingsform':
        case 'showaddmembersform':
        case 'updatemembers':
            print_error('errorselectone', 'group', $returnurl);
    }
}
switch ($action) {
    case false: //OK, display form.
        break;
    case 'ajax_getmembersingroup':
        $roles = array();
        if ($groupmemberroles = groups_get_members_by_role($groupids[0], $courseid, 'u.id, u.firstname, u.lastname')) {
            foreach($groupmemberroles as $roleid=>$roledata) {
                $shortroledata = new stdClass();
                $shortroledata->name = $roledata->name;
                $shortroledata->users = array();
                foreach($roledata->users as $member) {
                    $shortmember = new stdClass();
                    $shortmember->id = $member->id;
                    $shortmember->name = fullname($member, true);
                    $shortroledata->users[] = $shortmember;
                }
                $roles[] = $shortroledata;
            }
        }
        echo json_encode($roles);
        die;  // Client side JavaScript takes it from here.
    case 'deletegroup':
        if (count($groupids) == 0) {
            print_error('errorselectsome','group',$returnurl);
        }
        $groupidlist = implode(',', $groupids);
        redirect(new moodle_url('/group/delete.php', array('courseid'=>$courseid, 'groups'=>$groupidlist)));
        break;
    case 'showcreateorphangroupform':
        redirect(new moodle_url('/group/group.php', array('courseid'=>$courseid)));
        break;
    case 'showautocreategroupsform':
        redirect(new moodle_url('/group/autogroup.php', array('courseid'=>$courseid)));
        break;
    case 'showimportgroups':
        redirect(new moodle_url('/group/import.php', array('id'=>$courseid)));
        break;
    case 'showgroupsettingsform':
        redirect(new moodle_url('/group/group.php', array('courseid'=>$courseid, 'id'=>$groupids[0])));
        break;
    case 'updategroups': //Currently reloading.
        break;
    case 'removemembers':
        break;
    case 'showaddmembersform':
        redirect(new moodle_url('/group/members.php', array('group'=>$groupids[0])));
        break;
    case 'updatemembers': //Currently reloading.
        break;
    default: //ERROR.
        print_error('unknowaction', '', $returnurl);
        break;
}
// Print the page and form
$strgroups = get_string('groups');
$strparticipants = get_string('participants');
/// Print header
$PAGE->set_title(get_string('powerup') . ' : '.  get_string('classmanagment'));
$PAGE->set_heading($course->fullname);
$PAGE->set_pagelayout('noblock');
echo $OUTPUT->header();

if(has_capability('moodle/site:viewreports', $context) || is_institute_admin()){
    echo teacher_menue('Manage', $course->id);
}
// Add tabs
//$currenttab = 'groups';
//require('tabs.php');

    $currenttab = 'classmanagment';
    $row = array();
    $row[] = new tabobject('classmanagment',
                           new moodle_url('/group/group_class.php', array('id' => $courseid)),
                           get_string('classmanagment'));
	$row[] = new tabobject('browse_users',
                           new moodle_url('/group/browse_user.php', array('id' => $courseid)),
                           get_string('browse_users'));
    echo '<div class="groupdisplay">';
    echo $OUTPUT->tabtree($row, $currenttab);
    echo '</div>';
    
    
$disabled = 'disabled="disabled"';
if (ajaxenabled()) {
    // Some buttons are enabled if single group selected
    $showaddmembersform_disabled = $singlegroup ? '' : $disabled;
    $showeditgroupsettingsform_disabled = $singlegroup ? '' : $disabled;
    $deletegroup_disabled = count($groupids)>0 ? '' : $disabled;
} else {
    // Do not disable buttons. The buttons work based on the selected group,
    // which you can change without reloading the page, so it is not appropriate
    // to disable them if no group is selected.
    $showaddmembersform_disabled = '';
    $showeditgroupsettingsform_disabled = '';
    $deletegroup_disabled = '';
}



//echo $OUTPUT->heading(format_string($course->shortname, true, array('context' => $context)) .' '.$strgroups, 3);
	echo '<div class="tab_container">';
        
    
    /********************Instituate section for super admin**********************/
    if(is_siteadmin()){
        
        $coursecat = coursecat::make_categories_list();
        $active_coursecat = $DB->get_records('course_categories', array('status' => '1'));
        $expired_coursecat = $DB->get_records('course_categories', array('status' => '0'));
        
        $active_inst_displaylist = array();
        $expired_inst_displaylist = array();

        foreach($active_coursecat as $a_cat){
            if($a_cat->id!=1){
                $active_inst_displaylist[$a_cat->id] = $a_cat->name;
            }
        }
        foreach($expired_coursecat as $e_cat){
            if($e_cat->id!=1){
                $expired_inst_displaylist[$e_cat->id] = $e_cat->name;
            }
        }
        natcasesort($active_inst_displaylist);
        natcasesort($expired_inst_displaylist);
        
//        print_object($active_inst_displaylist);
//        print_object($expired_inst_displaylist);
        
        $createnewinst_url  = new moodle_url($CFG->wwwroot."/course/editcategory.php", array('parent' => 0));
        $editinst_url       = new moodle_url($CFG->wwwroot."/course/editcategory.php", array('id' => $instituateid));
        $deleteinst_url     = new moodle_url($CFG->wwwroot."/group/delete_instituate.php", array('course' => $courseid, 'id' => $instituateid));
        
        $inst_r_url         = new moodle_url('/group/group_class.php', array('id'=>$courseid));
        
        
        
        echo '<div style="border: 1px solid #dddddd; height: 90px;border-radius:4px 4px;">';
            echo '<div id="class-div">'.get_string('institute').'</div>';
                echo '<div>';
                    echo '<div style="float:left;padding:15px 0 0 11px;">';
                        echo '<div class="selectclass1">';                            
                            echo '<div class="singleselect">';
                            // Single select raw html source gor group institution change @ 30 jan 14 @ditya
                                echo '<form method="get" action="group_class.php" id="single_select_f52e8f59ebcc621">';
                                    echo '<div>';
                                    echo '<input type="hidden" name="id" value="2">';
                                    echo '<label for="single_select52e8f59ebcc622">Institute</label>';
                                    echo '<select id="single_select52e8f59ebcc622" class="select singleselect" onchange="this.form.submit()" name="instituate">';
                                        echo '<option value="">Select Institute</option>';
                                        
                                        echo '<optgroup label="Active Institute">';
                                            foreach($active_inst_displaylist as $key=>$a_inst){
                                                $selected = ($key == $instituateid)?"selected":"";
                                                echo '<option value="'.$key.'" '.$selected.'>'.$a_inst.'</option>';
                                            }
                                        echo '</optgroup>';
                                        
                                        echo '<optgroup label="Expired Institute">';
                                            foreach($expired_inst_displaylist as $key=>$e_inst){
                                                $selected = ($key == $instituateid)?"selected":"";
                                                echo '<option value="'.$key.'" '.$selected.'>'.$e_inst.'</option>';
                                            }
                                        echo '</optgroup>';
                                        
                                    echo '</select>';
                                    echo '<noscript class="inline">&lt;div&gt;&lt;input type="submit" value="Go" /&gt;&lt;/div&gt;</noscript>';
                                    echo '</div>';
                                echo '</form>';
                                
                                
                            echo '</div>';
                            
                            
                        echo '</div>';
                    echo '</div>';
                    
                    echo '<div style="  float: right;padding: 15px 10px 0 0;">';
                        

                        if($instituateid){
                            echo '<a class="dialouge btn btn-grey inst_dialouge" href="'.$deleteinst_url.'" return_url="'.$inst_r_url.'" data-title="'.get_string('remove').'" data-width="500" data-height="230">'.get_string('remove').'</a>';
                            
                            echo '<a class="dialouge btn btn-grey inst_dialouge" href="'.$editinst_url.'" return_url="'.$inst_r_url.'" data-title="'.get_string('edit_institute').'" data-width="700" data-height="600">'.get_string('edit_institute').'</a>';
                            
                        }else{
                            echo '<a class="btn btn-grey" onclick="alert(\''.get_string('selectainstitute').'\')" data-title="'.get_string('remove').'" data-width="500" data-height="230">'.get_string('remove').'</a>';
                            
                            echo '<a class="btn btn-grey" onclick="alert(\''.get_string('selectainstitute').'\')" data-title="'.get_string('edit_institute').'" data-width="450" data-height="335">'.get_string('edit_institute').'</a>';
                        }
                        
                        echo '<a class="dialouge btn btn-grey inst_dialouge" href="'.$createnewinst_url.'" data-title="'.get_string('add_institute').'" data-width="700" return_url="'.$inst_r_url.'" data-height="600">'.get_string('new_institute').'</a>';
                    echo '</div>';
                    
            echo '</div>';
        echo '</div>';  
    }
    
    
    /********************Instituate section for super admin**********************/
    
    
    /********************Campus section for super admin**********************/
    if(is_siteadmin() || is_institute_admin()){
        
        if($instituateid){
            $campuses = $DB->get_records('campus', array('institution_id' => $instituateid));
        }else{
            $campuses = $DB->get_records('campus');
        }
        
        $campus_displaylist = array();

        foreach($campuses as $campus){
            $campus_displaylist[$campus->id] = $campus->name;
        }
        
        natcasesort($campus_displaylist);
        
        $createnew_campus_url  = new moodle_url($CFG->wwwroot."/group/campus.php", array('instituateid' => $instituateid, 'courseid' => $courseid));
        $edit_campus_url       = new moodle_url($CFG->wwwroot."/group/campus.php", array('instituateid' => $instituateid, 'id' => $campusid));
        $delete_campus_url     = new moodle_url($CFG->wwwroot."/group/delete_campus.php", array('course' => $courseid, 'id' => $campusid));
        $inst_cam_url          = new moodle_url('/group/group_class.php', array('id'=>$courseid, 'instituate' => $instituateid));
        
        if(is_institute_admin() && !count($campus_displaylist) > 0){
            
            echo "<div class='alertstyle'>";
                echo '<span>ALERT : </span> No campus available. To create a campus, click <span class="alertlink">';
                echo '<a class="dialouge campus_dialouge" href="'.$createnew_campus_url.'" data-title="'.get_string('add_campus').'" return_url="'.$inst_cam_url.'" data-width="600" return_url="'.$url.'" data-height="600">here</a>';
                echo '</span> or use the New Campus button below.';

            echo "</div>";
        }
        
        
        
        
        echo '<div style="border: 1px solid #dddddd; height: 90px;border-radius:4px 4px;">';
            echo '<div id="class-div">'.get_string('campus').'</div>';
                echo '<div>';
                    echo '<div style="float:left;padding:15px 0 0 11px;">';
                        echo '<div class="selectclass1">';
                            $select = new single_select(new moodle_url('group_class.php', array('id' => $courseid, 'instituate' => $instituateid)), 'campus', $campus_displaylist, $campusid, array(''=>  get_string('select_campus')));
                            $select->set_label(get_string('campus'));
                            echo $OUTPUT->render($select);
                        echo '</div>';
                    echo '</div>';
                    
                    echo '<div style="  float: right;padding: 15px 10px 0 0;">';
                        

                        if($campusid){
                            echo '<a class="dialouge btn btn-grey campus_dialouge" href="'.$delete_campus_url.'" data-title="'.get_string('remove_campus').'" return_url="'.$inst_cam_url.'" data-width="500" data-height="190">'.get_string('remove').'</a>';
                            
                            echo '<a class="dialouge btn btn-grey campus_dialouge" href="'.$edit_campus_url.'" data-title="'.get_string('edit_campus').'" return_url="'.$inst_cam_url.'" data-width="700" data-height="600">'.get_string('edit_campus').'</a>';
                            
                        }else{
                            echo '<a class="btn btn-grey" onclick="alert(\''.get_string('selectacampus').'\')" data-title="'.get_string('remove').'" data-width="500" data-height="190">'.get_string('remove').'</a>';
                            
                            echo '<a class="btn btn-grey" onclick="alert(\''.get_string('selectacampus').'\')" data-title="'.get_string('edit_campus').'" data-width="450" data-height="335">'.get_string('edit_campus').'</a>';
                            
                        }
                        
                        echo '<a class="dialouge btn btn-grey campus_dialouge" href="'.$createnew_campus_url.'" data-title="'.get_string('add_campus').'" return_url="'.$inst_cam_url.'" data-width="700" return_url="'.$url.'" data-height="600">'.get_string('new_campus').'</a>';
                        
                    echo '</div>';
                    
            echo '</div>';
        echo '</div>';  
    }
    
    
    /********************Campus section for super admin**********************/
    
    
    /********************Class section for super admin**********************/
    
    $displaylist = array();
    $courselist = get_courses();
    
    foreach($courselist as $course){
        if($course->id!=1){
            $courseid = $course->id;
        }
    }
    
    if(is_siteadmin() || is_institute_admin()){
        if($instituateid || $campusid){
            $sql = "SELECT cam.*, g.name FROM {campus_classes} cam left join {groups} g on cam.groupid=g.id ";
            if($instituateid && !$campusid){

                $sql .= "WHERE instituate_id = $instituateid";
            }else if(!$instituateid && $campusid){

                $sql .= "WHERE campusid = $campusid";
            }else if($instituateid && $campusid){

                $sql .= "WHERE instituate_id = $instituateid and campusid = $campusid";
            }
            $sql .= " order by g.name asc ";
            
            
            
            $groups = $DB->get_records_sql($sql);

            $displaylist = array();
            if($groups){
                foreach($groups as $group){
                    if($group->name){
                        $displaylist[$group->groupid] = $group->name;
                    }
                }
            }

        }else{
            $groups = groups_get_all_groups($courseid);

            $displaylist = array();
            if($groups){
                foreach($groups as $group){
                    if($group->name){
                        $displaylist[$group->id] = $group->name;
                    }
                }
            }
        }
        
    }else{
        $displaylist = teachers_class($USER);
    }
    
    
    
    
    
    $createnewclass_url = new moodle_url($CFG->wwwroot."/group/group.php", array('courseid' => $courseid, 'institution_id' => $instituateid));        
    $deleteclass_url    = new moodle_url($CFG->wwwroot."/group/delete.php", array('courseid' => $courseid, 'groups' => $groupid));
	$addstudent_url     = new moodle_url($CFG->wwwroot."/group/add_student.php", array('id'=>$courseid,'group' => $groupid));
    
    
    echo '<div style="border: 1px solid #dddddd; height: 90px;border-radius:4px 4px;">
		<div id="class-div">'.get_string('class').'</div>';
		echo '<div>';
			echo '<div style="float:left;padding:15px 0 0 11px;">';
					
                echo '<div class="selectclass1">';
                $select = new single_select(new moodle_url('group_class.php', array('id' => $courseid, 'instituate' => $instituateid, 'campus' => $campusid)), 'group', $displaylist, $groupid, array(''=>'Select Class'));
                $select->set_label(get_string('group'));
                echo $OUTPUT->render($select);
            echo '</div>';
			echo '</div>';
			echo '<div style="  float: right;padding: 15px 10px 0 0;">';
                
                
                if($groupid){
                    $group_insti = $DB->get_record('campus_classes', array('groupid' =>$groupid));
                    
                    $group_instituateid = $group_insti->instituate_id;
                    
                    $edit_class_url     = new moodle_url($CFG->wwwroot."/group/group.php", array('courseid' => $courseid, 'id' => $groupid, 'institution_id' => $group_instituateid));
                    
                    echo '<a class="dialouge btn btn-grey" href="'.$deleteclass_url.'" data-title="'.get_string('removeclass').'" data-width="450" data-height="190">'.get_string('remove').'</a>';
					
                    echo '<a class="dialouge btn btn-grey" href="'.$edit_class_url.'" data-title="'.get_string('editclass').'" data-width="700" return_url="'.$url.'" data-height="335">'.get_string('editclass').'</a>';
                }else{
                    
                    echo '<a class="btn btn-grey" onclick="alert(\''.get_string('selectaclass').'\')" data-title="'.get_string('removeclass').'" data-width="450" data-height="190">'.get_string('remove').'</a>';
                    
                    echo '<a class="btn btn-grey" onclick="alert(\''.get_string('selectaclass').'\')" data-title="'.get_string('editclass').'" data-width="500" data-height="335">'.get_string('editclass').'</a>';
                }
                
                if($instituateid){                    
                    echo '<a class="dialouge btn btn-grey" href="'.$createnewclass_url.'" data-title="'.get_string('createnewclass').'" data-width="700" data-height="335">'.get_string('newclass').'</a>';
                }else{                    
                    echo '<a class="btn btn-grey" onclick="alert(\''.get_string('selectainstitute').'\')" data-title="'.get_string('createnewclass').'" data-width="700" data-height="335">'.get_string('newclass').'</a>';
                }              

			echo '</div>';
		echo '</div>';
	echo '</div>';
    /********************Class section for super admin**********************/
    
echo '<div style="clear:both;padding:10px;"></div>';	

    $user_count = 0;
     if($dir <> 'ASC'){
		$diricon = 'ASC';
		$sort_icon = "<img class='img-padding' src=\"" . $OUTPUT->pix_url('down','theme') . "\" alt=\"\" />";
	}else{
		$diricon = 'DESC';
		$sort_icon = "<img class='img-padding' src=\"" . $OUTPUT->pix_url('up','theme') . "\" alt=\"\" />";
	}
    
    if($groupid){
        if ($groupmemberroles = groups_get_members_by_role($groupid, $courseid, 'u.id, u.firstname, u.lastname',$sort.' '.$dir)) {
            echo '<div style="border: 1px solid #ccc; height: auto;">';
            $table = new html_table();
            $table->id = "class_users";
            $table->attributes['class'] = 'generaltable';

            $table->head = array ();
            $table->colclasses = array('table_headr');

            $table->head[] = html_writer::tag('a', get_string('student').($sort == 'firstname'? $sort_icon : ''), array('href'=>new moodle_url($sort_baseurl, array('dir'=>$diricon, 'sort'=>'firstname' )), 'class'=>'sort_link_style'));

            $table->head[] = '';

            $group_students = array();

            foreach($groupmemberroles as $roleid=>$roledata) {
                if($roleid == 5){
                    foreach($roledata->users as $member) {
                        $group_students[] = $member;
                    }
                }
            }
            //print_object($group_students);

    //        natcasesort($group_students);
            $usercount = count($group_students);

            if (isset($page) && isset($perpage)) {
                $grou_user_chunk = array_chunk($group_students, $perpage);
                $arr_iterative = $grou_user_chunk[$page];
            } else {
                $arr_iterative = $group_students;
            }


            if (count($arr_iterative) > 0) {
                foreach($arr_iterative as $member) {
                    $row = array ();
                    $row[] = fullname($member, true);

                    $removefrom_class_url = new moodle_url($CFG->wwwroot.'/group/remove_group_member.php', array('group'=>$groupid, 'user'=>$member->id));

                    $reset_password_url = new moodle_url($CFG->wwwroot.'/group/reset_password.php', array('id'=>$member->id, 'group'=>$groupid));

                    $edit_user_url = new moodle_url($CFG->wwwroot . '/user/editadvanced.php', array('id' => $member->id, 'courseid' => $courseid, 'edit' => 1));


                    $links = html_writer::link($edit_user_url, get_string('edit'), array('class' => 'dialouge', 'id' => 'edituser', 'data-title' => get_string('edit'), 'data-width' => '1080', 'data-height' => '600', 'return_url' => $url));
                    $links .= ' | ';

                    $links .= html_writer::link($reset_password_url, get_string('resetpassword'), array('class' => 'dialouge', 'data-title' => get_string('resetpassword'), 'data-width'=> '600', 'data-height' => '200', 'return_url' => $url));
                    $links .= ' | ';

                    $links .= html_writer::link($removefrom_class_url, get_string('removefromclass'), array('class' => 'dialouge', 'data-title' => get_string('remove_student'), 'data-width'=> '600', 'data-height' => '200', 'return_url' => $url));






                    $row[] = $links;


                    $table->data[] = $row;
                    $user_count++;
                }

            echo html_writer::table($table);

            echo '<div>';
            echo $OUTPUT->paging_bar($usercount, $page, $perpage, $baseurl);
            echo '</div>';


            } else {
                echo "<div class='alertstyle margin-5'>".get_string('norecordfound', 'moodle')."</div>";
                $usercount = count($users);
            }

            echo '</div>';


        }else{
            echo "<div class='alertstyle margin-5'>".get_string('norecordfound', 'moodle')."</div>";
        }
    }else{
        echo "<div class='noticestyle margin-5'>".get_string('noclass_selected', 'moodle')."</div>";
    }
    
    
    




$create_user_url = new moodle_url($CFG->wwwroot.'/user/editadvanced.php', array('id'=>'-1', 'class' => $groupid));

$upload_roaster_url = new moodle_url($CFG->wwwroot.'/admin/tool/uploaduser/index.php', array('id'=>'-1', 'institution' => $instituateid, 'campus' => $campusid, 'class' => $groupid));
$download_roaster_url = new moodle_url($CFG->wwwroot.'/admin/user/download_roster.php', array('id'=>$groupid, 'course'=>$courseid));

echo '<div style="clear:both;padding:2px;"></div>';	

echo '<div style=" float: right;padding: 6px 0;text-align: right;">';

    if($groupid){
        echo html_writer::tag('div', '<a class="dialouge btn btn-grey" id="dialouge" href="'.$addstudent_url.'" return_url="'.$url.'" data-title="'.get_string('addstud').'" data-width="500" data-height="335">'.get_string('addstud').'</a>', array('style'=>'float:left;padding:0 10px 0 0;'));
    }else{
        echo html_writer::tag('div', '<a class="btn btn-grey" onclick="alert(\''.get_string('selectaclass').'\')" id="dialouge" data-title="'.get_string('addstud').'" data-width="500" data-height="335">'.get_string('addstud').'</a>', array('style'=>'float:left;padding:0 10px 0 0;'));
    }
                
   echo html_writer::tag('div', '<a class="dialouge btn btn-grey" id="dialouge" href="'.$create_user_url.'" return_url="'.$url.'" data-title="'.get_string('createnewuser').'" data-width="1080" data-height="600">'.get_string('createuser').'</a>', array('style'=>'float:left;padding:0 10px 0 0;'));
   
   
   if($instituateid){
        echo html_writer::tag('div', '<a class="dialouge btn btn-grey" id="dialouge" href="'.$upload_roaster_url.'" return_url="'.$url.'" data-title="'.get_string('uploadroster').'" data-width="1080" data-height="600">'.get_string('uploadroster').'</a>', array('style'=>'float:left;padding:0 10px 0 0;'));
    }else{
        echo html_writer::tag('div', '<a class="btn btn-grey" onclick="alert(\''.get_string('selectainstitute').'\')" id="dialouge" return_url="'.$url.'" data-title="'.get_string('uploadroster').'" data-width="1080" data-height="600">'.get_string('uploadroster').'</a>', array('style'=>'float:left;padding:0 10px 0 0;'));
    }
    if($groupid && $user_count){
		echo html_writer::tag('div', '<a class="dialouge btn btn-grey" id="dialouge" href="'.$download_roaster_url.'" return_url="'.$url.'" data-title="'.get_string('downloadroster').'" data-width="400" data-height="200">'.get_string('downloadroster').'</a>', array('style'=>'float:left;padding:0 10px 0 0;'));
        
//		echo html_writer::tag('div', '<a class="dialouge btn btn-grey" id="dialouge" href="'.$download_roaster_url.'" return_url="'.$url.'" data-title="'.get_string('printroster').'" data-width="400" data-height="200">'.get_string('printroster').'</a>', array('style'=>'float:left;padding:0 10px 0 0;'));
	}else{
        $alert_msg = ($groupid) ? get_string('norecordfound') : get_string('selectaclass');
        
		echo html_writer::tag('div', '<a class="btn btn-grey" id="dialouge" onclick="alert(\''.$alert_msg.'\')" data-title="'.get_string('downloadroster').'" data-width="600" data-height="600">'.get_string('downloadroster').'</a>', array('style'=>'float:left;padding:0 10px 0 0;'));
        
//		echo html_writer::tag('div', '<a class="btn btn-grey" id="dialouge" onclick="alert(\''.$alert_msg.'\')" data-title="'.get_string('printroster').'" data-width="600" data-height="600">'.get_string('printroster').'</a>', array('style'=>'float:left;padding:0 10px 0 0;'));
	}
     
	echo '</div> ';
echo '</div>';
echo $OUTPUT->footer();
/**
 * Returns the first button action with the given prefix, taken from
 * POST or GET, otherwise returns false.
 * @see /lib/moodlelib.php function optional_param().
 * @param string $prefix 'act_' as in 'action'.
 * @return string The action without the prefix, or false if no action found.
 */
function groups_param_action($prefix = 'act_') {
    $action = false;
//($_SERVER['QUERY_STRING'] && preg_match("/$prefix(.+?)=(.+)/", $_SERVER['QUERY_STRING'], $matches)) { //b_(.*?)[&;]{0,1}/
    if ($_POST) {
        $form_vars = $_POST;
    }
    elseif ($_GET) {
        $form_vars = $_GET;
    }
    if ($form_vars) {
        foreach ($form_vars as $key => $value) {
            if (preg_match("/$prefix(.+)/", $key, $matches)) {
                $action = $matches[1];
                break;
            }
        }
    }
    if ($action && !preg_match('/^\w+$/', $action)) {
        $action = false;
        print_error('unknowaction');
    }
    ///if (debugging()) echo 'Debug: '.$action;
    return $action;
}
