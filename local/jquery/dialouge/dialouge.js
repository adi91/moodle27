function show_ok_modal_message(message, classname, evt){
    var dialog = new Y.Panel({
        contentBox : Y.Node.create('<div id="dialog" />'),
        bodyContent: '<div class="message"></div>',
        width      : 410,
        zIndex     : 6,
        centered   : true,
        modal      : false, // modal behavior
        render     : '.example',
        visible    : false, // make visible explicitly with .show()
        buttons    : {
            footer: [
                {
                    name     : 'proceed',
                    label    : 'OK',
                    action   : 'onOK'
                }
            ]
        }
    });

    dialog.onOK = function (e) {
        e.preventDefault();
        this.hide();
        // code that executes the user confirmed action goes here
        if(this.callback){
           this.callback();
        }
        // callback reference removed, so it won't persist
        this.callback = false;
    }
    Y.one('#dialog .message').setHTML(message);
    
    /* classnames and images provided in the CSS are:
        .modal-bubble
        .modal-error
        .modal-info
        .modal-question
        .modal-warn
        .modal-success
        .modal-none
     */
    classname = 'message '+classname
    Y.one('#dialog .message').set('className', classname);
    
    dialog.show();
    evt.preventDefault(evt);
}

//Y.one('#addstudenttoclass_submitbutton').on('click', function(evt){
//    var message = "Please select course and respective level.";
//    var classname = "modal-info";
//    show_ok_modal_message(message, classname, evt);
//});

if( $('.addstudenttoclass_submitbutton').length ){
    $( ".addstudenttoclass_submitbutton" ).click(function(evt) {
        var message = "Please select course and respective level.";
        var classname = "modal-info";
        show_ok_modal_message(message, classname, evt);
   });
}

$( ".addstudenttoclass_submitbutton_form" ).click(function(evt) {
    if ( $('.stutoadd:checked').length < 1 ) {
          var message = "Please select a candidate.";
          var classname = "modal-warn";
          show_ok_modal_message(message, classname, evt);
      event.preventDefault();
    }else{
        $( "#assignstudent" ).submit();
    }
    return true;
});

function user_added_to_course_success(){
    var message = "User added successfully.";
    var classname = "modal-success";
    show_ok_modal_message(message, classname, evt);
    window.href = window.href;
}

if($('div').hasClass('student_added_tocorse_success')){
    user_added_to_course_success();
}

$( "#id_remove_student_button" ).click(function(evt) {
    
    var course = $('#id_course').val();
    var level = $('#id_level').val();
    
    if (course != 0) {
        if ( $('.stutoremove:checked').length < 1 ) {
              var message = "Please select a candidate.";
              var classname = "modal-info";
              show_ok_modal_message(message, classname, evt);
          event.preventDefault();
        }else{
            var dialog = new Y.Panel({
            contentBox : Y.Node.create('<div id="dialog" />'),
            bodyContent: '<div class="message"></div>',
            width      : 410,
            zIndex     : 6,
            centered   : true,
            modal      : false, // modal behavior
            render     : '.example',
            visible    : false, // make visible explicitly with .show()
            buttons    : {
                    footer: [
                        {
                            name     : 'proceed',
                            label    : 'OK',
                            action   : 'onOK'
                        },
                        {
                            name     : 'cancel',
                            label    : 'Cancel',
                            action   : 'onCancel'
                        }
                    ]
                }
            });

            dialog.onOK = function (e) {
                this.hide();
                document.forms["removestudent"].submit();
            }
            dialog.onCancel = function (e) {
                this.hide();
                evt.preventDefault(evt);
            }
            Y.one('#dialog .message').setHTML("Are you sure, you want to remove these students.");

            classname = 'message modal-warn';
            Y.one('#dialog .message').set('className', classname);

            dialog.show();
            evt.preventDefault(evt);
            
        }
    }else{
        var message = "Please select course and respective level.";
        var classname = "modal-info";
        show_ok_modal_message(message, classname, evt); 
        event.preventDefault();
    }

    return true;
});
