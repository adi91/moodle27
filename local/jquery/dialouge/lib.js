function checked_all(checked) {
    
    // add multiple select / deselect functionality
    $("#allcheck").click(function () {
          $('.stutoadd').attr('checked', this.checked);
    });
 
    // if all checkbox are selected, check the allcheck checkbox
    // and viceversa
    $(".stutoadd").click(function(){
 
        if($(".stutoadd").length == $(".stutoadd:checked").length) {
            $("#allcheck").attr("checked", "checked");
        } else {
            $("#allcheck").removeAttr("checked");
        }
 
    });
}

function addtoclass_form_validation(ele){
    alert('pop');

    
        var dialog = new Y.Panel({
            contentBox : Y.Node.create('<div id="dialog" />'),
            bodyContent: '<div class="message icon-warn">Are you sure you want to [take some action]?</div>',
            width      : 410,
            zIndex     : 6,
            centered   : true,
            modal      : false, // modal behavior
            render     : '.example',
            visible    : false, // make visible explicitly with .show()
            buttons    : {
                footer: [
                    {
                        name  : 'cancel',
                        label : 'Cancel',
                        action: 'onCancel'
                    },

                    {
                        name     : 'proceed',
                        label    : 'OK',
                        action   : 'onOK'
                    }
                ]
            }
        });

        dialog.onCancel = function (e) {
            e.preventDefault();
            this.hide();
            // the callback is not executed, and is
            // callback reference removed, so it won't persist
            this.callback = false;
        }

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

    
    // set the content you want in the message
    Y.one('#dialog .message').setHTML('Are you sure you want to [take some action]?');
    
    // set the icon (or none) that appears next to the message.
    // the Class 'message' also needs to be maintained.
    Y.one('#dialog .message').set('className', 'message icon-bubble');
    /* classnames and images provided in the CSS are:
        .icon-bubble
        .icon-error
        .icon-info
        .icon-question
        .icon-warn
        .icon-success
        .icon-none
     */
                                             
    // set the callback to reference a function
    dialog.callback = doSomething; 
    
    dialog.show();
}
//
//$( "#assignstudent" ).submit(function( event ) {
//  addtoclass_form_validation();
//  event.preventDefault();
//});