<footer id="page-footer">
    <!--<p class="helplink"><?php // echo page_doc_link(get_string('moodledocslink')) ?></p>-->
    
    <div class="footer-left">
        <a href="" title="Powerup">
            <img src="<?php echo $OUTPUT->pix_url('footer/MGH_logo', 'theme')?>" alt="MGH logo" />
        </a>
    </div>
    
    <?php
        if ($hasfootnote) { ?>
           <div class="footer-right">
           <?php echo $PAGE->theme->settings->footnote; ?>
           </div>
            <?php
        } 
    ?>

    <?php echo $OUTPUT->standard_footer_html(); ?>
</footer>

<!--    <div class="footer-left">
        <a href="http://moodle.org" title="Moodle">
            <img src="<?php // echo $OUTPUT->pix_url('footer/MGH_logo', 'theme') ?>" alt="MGH logo" />
        </a>
    </div>

    <div class="footer-right">
        <?php // echo $OUTPUT->login_info(); ?>
        <?php // if ($hasfootnote) { ?>
            <div id="footnote"><?php // echo $PAGE->theme->settings->footnote; ?></div>
        <?php // } ?>
    </div>-->
