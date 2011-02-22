jQuery(document).ready(function($){
    $('.draggable').draggable({ 
        cursor: 'move',
        helper: 'clone',
        opacity: 0.75 
    });  
    
    var ui = {
        field_mapping_settings_form: $('#field-mapping-settings-form'),
        field_mapping_settings_message: $('#field-mapping-settings-message'),
        save_field_mapping_settings_spinner: $('#save-field-mapping-settings-spinner'),
        custom_fields_list: $('#custom_fields_list'),
        wp_variables: $('#wp_variables'),
        fields_spinner: $('#fields-spinner')
    }     
    
    
    ui.fields_spinner.show();
    
    $.post(
        im_ar_cfg.ajax_url,
        {
            action: 'im_ar_login_check'
        },
        function (response) {
            if (response.Success) {
                var login_session_id = response.SessionID;
                
                $.post(
                    im_ar_cfg.ajax_url,
                    {
                        action: 'im_ar_get_custom_fields',
                        data: {session_id: login_session_id}        
                    },
                    function (response) {
                        if (response.Success) {
                            var custom_fields = response.CustomFields;
                            
                            $.post(
                                im_ar_cfg.ajax_url,
                                {
                                    action: 'im_ar_get_field_mapping'
                                },
                                function(response){
                                    ui.fields_spinner.hide();
                                    
                                    var field_mapping = response;
                                    
                                    $.each(custom_fields, function(i, v){
                                        var li = $(document.createElement('li'))
                                            .addClass('droppable')      
                                            .append(
                                                $(document.createElement('div'))
                                                    .addClass('field_name')
                                                    .append(v.FieldName)
                                                    .append(
                                                        $(document.createElement('input'))
                                                            .attr('type', 'hidden')
                                                            .attr('name', 'field_id')
                                                            .val(v.CustomFieldID)
                                                    )
                                                    .append(
                                                        $(document.createElement('span'))
                                                            .addClass('wp_variable_remove')
                                                            .append('Clear')
                                                            .click(function(){
                                                                $(this)
                                                                    .parents('.droppable')
                                                                    .find('.wp_variable')
                                                                        .addClass('wp_variable_null')
                                                                        .empty()
                                                                        .append('None');
                                                            })
                                                    )
                                            )                                                                                                    
                                            .appendTo(ui.custom_fields_list);
                                        
                                        if ('undefined' == typeof field_mapping[v.CustomFieldID] || !field_mapping[v.CustomFieldID]) {
                                            li.append($(document.createElement('div')).addClass('wp_variable wp_variable_null').append('None'));        
                                        } else {  
                                            var wp_variable = field_mapping[v.CustomFieldID];
                                            var html = ui.wp_variables.find('.draggable input[value='+wp_variable+']:first').parents('.draggable').html(); 
                                            li.append($(document.createElement('div')).addClass('wp_variable').append(html));                                                                                           
                                        }  
                                        
                                        li.droppable({
                                            drop: function(event, ui){
                                                $(this)
                                                    .removeClass('droppable_over')
                                                    .find('.wp_variable')
                                                        .empty()
                                                        .removeClass('wp_variable_null')
                                                        .append(ui.draggable.html());
                                            },
                                            over: function(event, ui){
                                                $(this).addClass('droppable_over');
                                            },
                                            out: function(event, ui){
                                                $(this).removeClass('droppable_over');
                                            }
                                        });     
                                    });    
                                },
                                'json'
                            );
                        } else {
                            ui.fields_spinner.hide();
                            
                            // Manage Error
                            alert('Error');
                        }
                    },
                    'json'
                );  
                      
            } else {
                ui.fields_spinner.hide();     
                
                // Manage Error
                alert("Login validation failed.");
            }
        },
        'json'
    );
    
    
    /**
    * Save field mapping function.
    */
    
    ui.field_mapping_settings_form.submit(function(){
        var field_mapping = {};
        ui.custom_fields_list
            .find('.droppable')
                .each(function(i, v){
                    var field_id = $(v).find('input[name=field_id]:first').val();
                    var wp_variable = $(v).find('input[name=wp_variable]:first').val();
                    
                    if ('undefined' != wp_variable) {
                        field_mapping[field_id] = wp_variable;                        
                    }
                });
                
        ui.save_field_mapping_settings_spinner.show();
        ui.field_mapping_settings_message.empty().hide();
        
        $.post(
            im_ar_cfg.ajax_url,
            {
                action: 'im_ar_save_field_mapping',
                field_mapping: field_mapping
            },
            function(response){
                ui.save_field_mapping_settings_spinner.hide();
                
                if (response.Success) {
                    ui.field_mapping_settings_message.append(
                        $(document.createElement('p')).append('Successfully saved the field mapping settings.')
                    ).show();
                } else {
                    ui.field_mapping_settings_message.append(
                        $(document.createElement('p')).append('An error has occured while saving the field mapping settings.')
                    ).show();
                }
            },
            'json'
        );
        
        return false;
    });
});