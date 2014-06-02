    <header id="page-header" class="clearfix black-header">

    <?php
        
//        if (!$haslogo) {
//            echo "<h1>$PAGE->heading</h1>";
//        }else {
//            echo '<a class="logo" href="'.$CFG->wwwroot.'" title="'.print_string('home').'"></a>';
//        }
        
    ?>
             
        <a class="logo" href="<?php echo $CFG->wwwroot; ?>" title="<?php print_string('home'); ?>"></a>
             
             
        <div class="headermenu"><?php
           if (isset($haslogininfo)) {
//               echo $OUTPUT->login_info();
               echo $OUTPUT->login_mgh_header();
           }
           if (!empty($PAGE->layout_options['langmenu'])) {
               echo $OUTPUT->lang_menu();
           }
           echo $PAGE->headingmenu
           ?>
        </div>

        <?php if (!empty($courseheader)) { ?>
            <div id="course-header"><?php echo $courseheader; ?></div>
        <?php } ?>
    </header>







<!--<div id="page-header" class="black-header ">
    <a class="logo" href="<?php // echo $CFG->wwwroot; ?>" title="<?php print_string('home'); ?>"></a>
    
    <div class="headermenu"><?php
//        if ($haslogininfo) {
//            echo $OUTPUT->login_mgh_header();
//        }
//        if (!empty($PAGE->layout_options['langmenu'])) {
//            echo $OUTPUT->lang_menu();
//        }
//        echo $PAGE->headingmenu
        ?>
    </div>
</div>-->