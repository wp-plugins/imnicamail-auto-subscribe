<div class="wrap">
    <h2>Login Settings</h2>
    <div style="display: none;" class="updated below-h2" id="login-settings-message"><p></p></div>
    <form id="login-settings-form" onsubmit="return false">
        <table class="form-table">
            <tr valign="top">
                <th scope="row">
                    <b>Username</b>
                </th>
                <td>
                     <input size="48" type="text" name="username" value="<?php echo $options['login']['username']; ?>" />
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <b>Password</b>
                </th>
                <td>
                    <input size="48" type="password" name="password" value="<?php echo $options['login']['password']; ?>" />
                </td>
            </tr>  
        </table>
        <p class="submit">
            <input id="save-login-settings-button" type="button" class="button-primary" value="<?php _e('Save Login Settings') ?>" />
            <img id="save-login-settings-spinner" style="display: none; vertical-align: middle;" src="<?php echo admin_url('images/wpspin_light.gif'); ?>" />
        </p>
    </form>
    <h2>List Settings</h2>
    <div style="display: none;" class="updated below-h2" id="list-settings-message"></div>
    <form id="list-settings-form" onsubmit="return false"> 
        <table class="form-table"> 
            <tr valign="top">
                <th scope="row">
                    <b>Selected List</b><br />
                    <a id="refresh-list-select-link" href="">Refresh Selection</a> 
                </th>
                <td>
                    <select id="list-select" name="list_id">
                    </select>
                    <img id="refresh-list-select-spinner" style="display: none; vertical-align: middle;" src="<?php echo admin_url('images/wpspin_light.gif'); ?>" /><br />
                    <i>Choose the list where to send the auto subscriptions.</i>
                </td>
            </tr>
        </table>    
        <p class="submit">
            <input id="save-list-settings-button" type="submit" class="button-primary" value="<?php _e('Save List Settings') ?>" />
            <img id="save-list-settings-spinner" style="display: none; vertical-align: middle;" src="<?php echo admin_url('images/wpspin_light.gif'); ?>" />
        </p>
    </form>
</div>
