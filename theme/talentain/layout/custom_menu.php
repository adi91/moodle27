<?php
require_once($CFG->dirroot.'/local/talentain/talentain_lib.php');

global $CFG, $USER, $DB;
//echo new moodle_url('/local/report/admin/user.php', array());
?>
<ul class="nav">
    <?php
    if(is_student()){
        ?>
        <li>
            <a href="<?php echo new moodle_url('/course/index.php', array()); ?>" title="<?php echo get_string('home'); ?>"><?php echo get_string('home'); ?></a>
        </li>
        <li class="dropdown">
        <a title="<?php echo get_string('profile'); ?>" data-toggle="dropdown" class="dropdown-toggle" href="<?php echo new moodle_url('/user/profile.php', array('id'=>$USER->id)); ?>"><?php echo get_string('profile'); ?><b class="caret"></b></a>

        <ul class="dropdown-menu">
            <li>
                <a href="<?php echo new moodle_url('/user/profile.php', array('id' =>$USER->id)); ?>" title="<?php echo get_string('viewprofile'); ?>"><?php echo get_string('viewprofile'); ?></a>   
            </li>
            <li>
                <a href="<?php echo new moodle_url('/user/edit.php', array('id' =>$USER->id)); ?>" title="<?php echo get_string('editmyprofile'); ?>"><?php echo get_string('editmyprofile'); ?></a>   
            </li>
            <li>
                <a href="<?php echo new moodle_url('/login/change_password.php', array('id' =>$USER->id)); ?>" title="<?php echo get_string('changepassword'); ?>"><?php echo get_string('changepassword'); ?></a>   
            </li>
        </ul>
    </li>
    
    <li>
        <a href="#" title="<?php echo get_string('reports'); ?>"><?php echo get_string('reports'); ?></a>
    </li>
    <li>
        <a href="#" title="<?php echo get_string('help'); ?>"><?php echo get_string('help'); ?></a>
    </li>
    
        
    
    <?php
    }
    elseif(is_editingteacher()){
    ?>
        <li>
            <a href="<?php echo new moodle_url('/course/index.php', array()); ?>" title="<?php echo get_string('home'); ?>"><?php echo get_string('home'); ?></a>
        </li>
        <li class="dropdown">
            <a title="User Managment" data-toggle="dropdown" class="dropdown-toggle" href="<?php echo new moodle_url('/admin/user.php', array()); ?>">User Managment<b class="caret"></b></a>

            <ul class="dropdown-menu">
                <li>
                    <a href="<?php echo new moodle_url('/user/editadvanced.php', array('id' =>-1)); ?>" title="Add new users ">Add new users </a>
                </li>
                <li class="dropdown-submenu">
                    <a title="Moodle development" data-toggle="dropdown" class="dropdown-toggle" href="https://moodle.org/development">Moodle development</a>
                    <ul class="dropdown-menu">
                        <li>
                            <a href="http://docs.moodle.org" title="Moodle Docs">Moodle Docs</a>
                        </li>
                    </ul>
                </li>
            </ul>
        </li>
        
        <li class="dropdown">
        <a title="<?php echo get_string('profile'); ?>" data-toggle="dropdown" class="dropdown-toggle" href="<?php echo new moodle_url('/user/profile.php', array('id'=>$USER->id)); ?>"><?php echo get_string('profile'); ?><b class="caret"></b></a>

        <ul class="dropdown-menu">
            <li>
                <a href="<?php echo new moodle_url('/user/profile.php', array('id' =>$USER->id)); ?>" title="<?php echo get_string('viewprofile'); ?>"><?php echo get_string('viewprofile'); ?></a>   
            </li>
            <li>
                <a href="<?php echo new moodle_url('/user/edit.php', array('id' =>$USER->id)); ?>" title="<?php echo get_string('editmyprofile'); ?>"><?php echo get_string('editmyprofile'); ?></a>   
            </li>
            <li>
                <a href="<?php echo new moodle_url('/login/change_password.php', array('id' =>$USER->id)); ?>" title="<?php echo get_string('changepassword'); ?>"><?php echo get_string('changepassword'); ?></a>   
            </li>
        </ul>
    </li>
    
    <li>
        <a href="#" title="<?php echo get_string('reports'); ?>"><?php echo get_string('reports'); ?></a>
    </li>
    <li>
        <a href="#" title="<?php echo get_string('help'); ?>"><?php echo get_string('help'); ?></a>
    </li>
    
    <?php
    }
    else{
    ?>
    <li class="dropdown">
        <a title="User Managment" data-toggle="dropdown" class="dropdown-toggle" href="<?php echo new moodle_url('/admin/user.php', array()); ?>">User Managment<b class="caret"></b></a>

        <ul class="dropdown-menu">
            <li>
                <a href="<?php echo new moodle_url('/user/editadvanced.php', array('id' =>-1)); ?>" title="Add new users ">Add new users </a>
            </li>
            <li class="dropdown-submenu">
                <a title="Moodle development" data-toggle="dropdown" class="dropdown-toggle" href="https://moodle.org/development">Moodle development</a>
                <ul class="dropdown-menu">
                    <li>
                        <a href="http://docs.moodle.org" title="Moodle Docs">Moodle Docs</a>
                    </li>
                </ul>
            </li>
        </ul>
    </li>
    <li>
        <a href="http://moodle.com/" title="Moodle.com">Moodle.com</a>
    </li>
    <?php
    }
    ?>
</ul>