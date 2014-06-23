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

Y.one('#addstudenttoclass_submitbutton').on('click', function(evt){
    var message = "Please select course and respective level.";
    var classname = "modal-info";
    show_ok_modal_message(message, classname, evt);
});

Y.one('.addstudenttoclass_submitbutton_form').on('click', function(evt){
     alert('here');
    var message = "Please select course and respective level.";
    var classname = "modal-info";
    show_ok_modal_message(message, classname, evt);
});

//Y.one('#assignstudent').on('submit', function(evt){
//    alert($('.stutoadd:checked').length);
//    return false;
//    var message = "Please select a candidate.";
//    var classname = "modal-warn";
//    show_ok_modal_message(message, classname, evt);
//});


//$( "#id_submitbutton" ).click(function() {
//    alert('here');
//  if ( $('.stutoadd:checked').length < 1 ) {
//        var message = "Please select a candidate.";
//        var classname = "modal-warn";
//        show_ok_modal_message(message, classname, evt);
//    event.preventDefault();
//  }
// return;
//});

function user_added_to_course_success(){
    var message = "User added successfully.";
    var classname = "modal-success";
    show_ok_modal_message(message, classname, evt);
    window.href = window.href;
}

if($('a').hasClass('download_complete')){
    p.location = p.location;
}