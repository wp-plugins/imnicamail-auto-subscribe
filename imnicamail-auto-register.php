<?php

    /**
    * Plugin Name: ImnicaMail Auto Register
    * Description: {description}
    * Author: ImnicaMail
    * Author URI: http://www.imnicamail.com/ 
    */
    
    $im_ar_folder = basename(dirname(__FILE__));
    define('IM_AR_PLUGIN_URL', WP_PLUGIN_URL."/{$im_ar_folder}");
    define('IM_AR_PLUGIN_DIR', WP_PLUGIN_DIR."/{$im_ar_folder}");
    
    require_once(IM_AR_PLUGIN_DIR.'/classes/ImnicaMailAutoRegisterFunctions.php');
    require_once(IM_AR_PLUGIN_DIR.'/classes/ImnicaMailAutoRegisterOptions.php');
    require_once(IM_AR_PLUGIN_DIR.'/classes/ImnicaMailAutoRegister.php');
    
    register_activation_hook(__FILE__, array('ImnicaMailAutoRegisterFunctions', 'install'));
    register_deactivation_hook(__FILE__, array('ImnicaMailAutoRegisterFunctions', 'uninstall'));
    
    $im_ar_instance = new ImnicaMailAutoRegister();
    
?>