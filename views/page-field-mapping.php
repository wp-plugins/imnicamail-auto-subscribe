<div class="wrap">
    <h2><?php _e('Field Mapping'); ?></h2>
    <div style="display: none;" class="updated below-h2" id="field-mapping-settings-message"></div> 
    <form id="field-mapping-settings-form" onsubmit="return false">
        <table class="form-table">
            <tr valign="top">
                <th scope="row">
                    <b><?php _e('Fields'); ?></b>
                    <img id="fields-spinner" style="display: none; vertical-align: middle;" src="<?php echo admin_url('images/wpspin_light.gif'); ?>" /><br />
                    <span class="description">
                        Click clear to remove the variable.
                    </span>
                </th>
                <td>
                    <ul id="custom_fields_list">
                    </ul>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <b><?php _e('Variables'); ?></b><br />
                    <span class="description">
                        Drag and drop variables over the fields.
                    </span>
                </th>
                <td>
                    <ul id="wp_variables">
                        <?php foreach ($im_ar->wp_variables as $user_meta_name => $user_meta_description) : ?>
                            <li class="draggable"><input name="wp_variable" type="hidden" value="<?php echo $user_meta_name; ?>" /><span><?php echo $user_meta_description; ?></span></li>
                        <?php endforeach; ?>
                    </ul>
                </td>
            </tr>
        </table>
        <p class="submit">
            <input id="save-field-mapping-settings-button" type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
            <img id="save-field-mapping-settings-spinner" style="display: none; vertical-align: middle;" src="<?php echo admin_url('images/wpspin_light.gif'); ?>" />
        </p>
    </form>          
</div>