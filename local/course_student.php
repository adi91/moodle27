<?php

/**
 * Add/enrol members into course.
 * By: Aditya
 * Date Created: 14 june 14
 * Lase modifyed: 14 june 14
 * @package   Talentain
 */
require_once('../config.php');
require_once($CFG->dirroot.'/course/lib.php');


$courseid           = optional_param('course', 0, PARAM_INT);
$levelid            = optional_param('level', 0, PARAM_INT);
$userid             = optional_param('id', $USER->id, PARAM_INT);
$hiddenaddstudent   = optional_param('hiddenaddstudent', 0, PARAM_INT);
$password           = optional_param('password', null, PARAM_RAW);
$cancel             = optional_param('cancel', false, PARAM_BOOL);
$confirm            = optional_param('confirm', 0, PARAM_BOOL);
$firstname          = optional_param('firstname', false, PARAM_RAW);
$lastname           = optional_param('lastname', false, PARAM_RAW);
$action             = optional_param('change', false, PARAM_BOOL);

$page           = optional_param('page', 0, PARAM_INT);
$perpage        = optional_param('perpage', 5, PARAM_INT);
$sort           = optional_param('sort', 'firstname', PARAM_RAW);
$dir            = optional_param('dir', 'ASC', PARAM_ALPHA);
$params         = null;

$baseurl        = new moodle_url('/local/add_student.php', array('id' =>0, 'perpage' => $perpage, 'course' => $courseid, 'level'=> $levelid, 'sort'=>$sort, 'dir'=>$dir));
$sort_baseurl        = new moodle_url('/local/add_student.php', array('id' =>0, 'perpage' => $perpage, 'course' => $courseid, 'level'=> $levelid));

//$group              = $DB->get_record('groups', array('id'=>$groupid), '*', MUST_EXIST);
//$user               = $DB->get_record('user', array('id'=>$userid), '*', MUST_EXIST);
//$course             = $DB->get_record('course', array('id'=>$group->courseid), '*', MUST_EXIST);

//$group_institution  = $DB->get_record('campus_classes', array('groupid'=>$groupid), '*', MUST_EXIST);

$site = $DB->get_record('course', array('id' => SITEID), '*', MUST_EXIST);
require_login($site);

$sitecontext = context_system::instance();
$site = get_site();
if (!has_capability('moodle/user:update', $sitecontext) and !has_capability('moodle/user:delete', $sitecontext)) {
    print_error('nopermissions', 'error', '', 'edit/delete users');
}
$pageurl = new moodle_url($CFG->wwwroot.'/local/add_user.php');
$PAGE->set_url($pageurl);
$PAGE->set_pagelayout('base');




$returnurl = $CFG->wwwroot;


$PAGE->requires->js(new moodle_url($CFG->wwwroot."/local/jquery/jquery1.8.3.js"), false);
$PAGE->requires->js(new moodle_url($CFG->wwwroot."/local/jquery/jquery.ui1.9.2.js"), false);
$PAGE->requires->js(new moodle_url($CFG->wwwroot."/local/jquery/jquery.livequery.js"), false);

$PAGE->requires->js(new moodle_url($CFG->wwwroot."/local/jquery/dialouge/lib.js"), false);

if ($cancel) {
    redirect($returnurl);
}

if ($action == true && $password != null) {
}

echo $OUTPUT->header();

if(isset($hiddenaddstudent) && $hiddenaddstudent ==1){
	//$arruser = optional_param('user', '', PARAM_TEXT);
	$arruser   = optional_param_array('user', array(), PARAM_INT);
	$form_courseid  = optional_param('course', 0, PARAM_INT);
    $course_context = context_course::instance($form_courseid);
    
	if($arruser){
		foreach($arruser as $key){
//			$record = new stdClass();
//			$record->userid = $key;
//			$record->timeadded = time();
//			$record->component = '';
//			$record->itemid = '0';
			
			$role_assignment = array(
                'roleid'        => 5,
                'contextid'     => $course_context->id,
                'userid'        => $key,
                'timemodified'  => time(),
                'modifierid'    => $USER->id,
                'course_level'  => $levelid,
            );
			
//			$DB->insert_record('groups_members', $record, false);
			$DB->insert_record('role_assignments', $role_assignment, false);
			
		}
		// echo '<span style="" >User added successfully</span>';
		// echo '<a class="redirectsuccess"></a>';
		// echo $OUTPUT->footer();
		// die;
		
		$url = $CFG->wwwroot.'/local/add_student.php?id=0&course='.$form_courseid;
        $url = new moodle_url($CFG->wwwroot.'/local/add_user.php', array('id'=>0, 'course'=>$form_courseid, 'level'=>$levelid));
		echo "<div class='notifysuccess_content' style='display:block;text-align:center;'>";
		echo '<div style="padding-bottom:15px;">User added successfully</div>';
		echo '<a class="btn" id="id_cancel_success" href="'.$url.'" >'.get_string('returntoadduser').'</a>';
		echo "</div>";
		echo $OUTPUT->footer();
		die;
	}
	
}


$sql = " select u.id, u.firstname, u.lastname, u.email from {user} u LEFT join {role_assignments} ra on u.id = ra.userid where ra.contextid = 1 and ra.roleid = 5";

//$sql = " select id, firstname, lastname from {user} where id in (select userid from {role_assignments} where contextid =15 and roleid = 5)
//and id not in (select userid from {groups_members}) and id not in (1,2) ";


if($courseid){
    $course_context = context_course::instance($courseid);
	$sqlwhere1 = "  and u.id not in (select userid from {role_assignments} where contextid =$course_context->id and roleid = 5)";  

}else{
	$sqlwhere1 = "";
}


//
//if($firstname){
//	$sqlwhere1 = "  and firstname like '".$firstname."%'";  
//}else{
//	$sqlwhere1 = "";
//}
//if($lastname){
//	$sqlwhere2 = "  and lastname like '".$lastname."%'";  
//}else{
//	$sqlwhere2 = "";
//}
//$sqlwhere3 = "";

if ($sort) {
    $sql_sort = " ORDER BY u.$sort $dir";
}

$sql = $sql.$sqlwhere1;

//$this->get_records_sql("SELECT $fields FROM {" . $table . "} $select $sort", $params, $limitfrom, $limitnum);
$users = $DB->get_records_sql("$sql $sql_sort", $params, $page*$perpage, $perpage);

$total_users = $DB->get_records_sql($sql);
$usercount = count($total_users);

if($dir <> 'ASC'){
	$diricon = 'ASC';
	$sort_icon = "<img class='iconsort' src=\"" . $OUTPUT->pix_url('t/' . "sort_asc") . "\" alt=\"\" />";
}else{
	$diricon = 'DESC';
	$sort_icon = "<img class='iconsort' src=\"" . $OUTPUT->pix_url('t/' . "sort_desc") . "\" alt=\"\" />";
}

//if ($column == "lastaccess") {
//    $columnicon = ($dir == "ASC") ? "sort_desc" : "sort_asc";
//} else {
//    $columnicon = ($dir == "ASC") ? "sort_asc" : "sort_desc";
//}
//$columnicon = "<img class='iconsort' src=\"" . $OUTPUT->pix_url('t/' . $columnicon) . "\" alt=\"\" />";

$table = new html_table();
$table->id = "class_users";
$table->attributes['class'] = 'generaltable width480';

$table->head = array ();
$table->colclasses = array('table_headr');
$table->size = array('40px', '220px', '220px');

$table->head[] = "<input type='checkbox' name='allcheck' id='allcheck' onclick='checked_all()' style='width:25px !important;' >";

$firstname_link = "<a href=\"".new moodle_url($sort_baseurl, array('dir'=>$diricon, 'sort'=>'firstname' ))."\">".get_string('firstname')."</a>";
$firstname_link .= ($sort == 'firstname') ? $sort_icon : '';
$table->head[] = $firstname_link;
//$table->head[] = get_string('firstname');

//$table->head[] = html_writer::tag('a', get_string('lastname').($sort == 'lastname'? $sort_icon : ''), array('href'=>new moodle_url($sort_baseurl, array('dir'=>$diricon, 'sort'=>'lastname' )), 'class'=>'sort_link_style'));

$lastname_link = "<a href=\"".new moodle_url($sort_baseurl, array('dir'=>$diricon, 'sort'=>'lastname' ))."\">".get_string('lastname')."</a>";
$lastname_link .= ($sort == 'lastname') ? $sort_icon : '';
$table->head[] = $lastname_link;
//$table->head[] = get_string('lastname');

$email_link = "<a href=\"".new moodle_url($sort_baseurl, array('dir'=>$diricon, 'sort'=>'email' ))."\">".get_string('email')."</a>";
$email_link .= ($sort == 'email') ? $sort_icon : '';
$table->head[] = $email_link;
//$table->head[] = get_string('email');

foreach($users as $user){
	$row = array ();
	$row[] = '<input type="checkbox" class="stutoadd"  name="user[]" value="'.$user->id.'" style="width:25px !important;" />';
	$row[] = $user->firstname;
	$row[] = $user->lastname;
	$row[] = $user->email;
	$table->data[] = $row;
}


$displaylist = array();
$courselist = get_courses();

foreach($courselist as $course){
    if($course->id!=1){
        $displaylist[$course->id] = $course->fullname;
    }
}
    
?>

<div id="removemembersform">
    
	<form id="searchform" method="get" action="">
		<input type="hidden" name="group" id="group" value="<?php echo $groupid; ?>" />  
		<input type="hidden" name="id" id="id" value="<?php echo $userid; ?>" />
	</form>
<?php    
        
    $select = new single_select(new moodle_url('add_student.php', array('id' => $courseid)), 'course', $displaylist, $courseid, array(''=>'Select Course'));
    $select->set_label(get_string('course'));

    echo $OUTPUT->render($select);
    
    
    if($courseid){

        $course     = course_get_format($courseid)->get_course();
        $num_coursesection = 1;
        $level_displaylist = array();
        
        echo "<br><br>";
        
        while($num_coursesection <= $course->numsections){
            
            $section = $DB->get_record('course_sections', array('course' => $course->id, 'section' => $num_coursesection), '*', MUST_EXIST);
            $sectionnum = $section->section;
            $sectioninfo = get_fast_modinfo($course)->get_section_info($sectionnum);
            $level_displaylist[$sectioninfo->id] = $sectioninfo->name;
            
            $num_coursesection++;
            
        }
        
        $level_select = new single_select(new moodle_url('add_student.php', array('course' => $courseid)), 'level', $level_displaylist, $levelid, array(''=>'Select Level'));
        $level_select->set_label(get_string('level'));

        echo $OUTPUT->render($level_select);
    }
    
        
    echo '<form id="assignstudent" method="post" action="">';
        echo '<input type="hidden" name="group" id="group" value="'.$courseid.'" />';
        echo '<input type="hidden" name="id" id="id" value="'.$userid.'" />';
  
        echo '<div class="class_users_div">';
    
        echo html_writer::table($table); echo '</div>'; 
    
        echo '<div>';
        echo $OUTPUT->paging_bar($usercount, $page, $perpage, $baseurl);
        echo '</div>';
    
    ?>
        <div id="formaction">
            <input type="hidden" name="hiddenaddstudent" id="hiddenaddstudent" value="1" />
            <input type="submit" name="add" id="id_submitbutton" value="Add" />
            <input type="submit" class="btn-cancel" name="cancel" id="id_cancel" value="Cancel" />		
        </div>
	</form>
</div>

<?php

    echo $OUTPUT->footer();
