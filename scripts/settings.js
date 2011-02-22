jQuery(document).ready(function($){
    $('#save-login-settings-button').click(function(){
        var button = $(this);
        var spinner = $('#save-login-settings-spinner');
        var message = $('#login-settings-message');
        
        button.attr('disabled', 'diabled');
        spinner.show();
        message.empty().hide();
        
        var data = {};
        
        $.each($('#login-settings-form').serializeArray(), function(i, v){
            data[v.name] = v.value;    
        });
        
        $.post(
            im_ar_cfg.ajax_url,
            {
                action : 'im_ar_login_check',
                data : data
            },
            function (response) {
                button.removeAttr('disabled');
                spinner.hide();
                
                if (null != response && response.Success) {
                    message.append($(document.createElement('p')).append('Login settings validated and saved.')).show();
                } else {
                    message.append($(document.createElement('p')).append('An error has occured while saving your login settings.')).show();
                }
            },
            'json'
        );
    });
    
    $('#refresh-list-select-link').click(function(){  
        var list_select = $('#list-select');  
        var spinner = $('#refresh-list-select-spinner');
        var button = $('#save-list-settings-button');
        var message = $('#list-settings-message');
        
        list_select.attr('disabled', 'disabled') ;
        button.attr('disabled', 'disabled');
        spinner.show();
        message.empty().hide();             
        
        $.post(
            im_ar_cfg.ajax_url,
            {
                action : 'im_ar_login_check'
            },
            function (response) {
                if (null != response && response.Success) {
                    $.post(
                        im_ar_cfg.ajax_url,
                        {
                            action : 'im_ar_get_lists',
                            data : {session_id : response.SessionID}
                        },
                        function(response) {                
                            if (null != response && response.Success) {
                                list_select.removeAttr('disabled');
                                button.removeAttr('disabled');
                                spinner.hide();
        
                                $('#list-select').empty();
                                
                                $.each(response.Lists, function(i, v){   
                                    var option = $(document.createElement('option'))
                                        .appendTo('#list-select')
                                        .attr('value', v.ListID)
                                        .append(v.Name);          
                                    
                                    if (v.ListID == im_ar_cfg.list_id) {
                                        option.attr('selected', 'selected');
                                    }
                                })
                            } else {
                                message.append($(document.createElement('p')).append('An error has occured while getting the lists.')).show();    
                            }         
                        },
                        'json'
                    );
                } else {
                    list_select.removeAttr('disabled');
                    button.removeAttr('disabled');
                    spinner.hide();
                    message.append($(document.createElement('p')).append('Invalid login details, Please validate your login details then refresh the list selection again.')).show();
                }
            },
            'json'
        );       
        
        return false;
    });
    
    $('#save-list-settings-button').click(function(){
        var button = $(this);
        var spinner = $('#save-list-settings-spinner');
        var message = $('#list-settings-message');
        
        button.attr('disabled', 'disabled');
        spinner.show();
        message.empty().hide();
        
        $.post(
            im_ar_cfg.ajax_url,
            {
                action : 'im_ar_save_list',
                data : {list_id : $('#list-select').val()}
            },
            function(response) {
                button.removeAttr('disabled');
                spinner.hide();
                            
                if (null != response && response.Success) {
                    message.append($(document.createElement('p')).append('List settings successfully saved.')).show();
                } else {
                    message.append($(document.createElement('p')).append('An error has occured while saving the list settings.')).show();
                }         
            },
            'json'
        );
    });
});