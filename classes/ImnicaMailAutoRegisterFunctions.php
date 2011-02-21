<?php

    if (!class_exists('ImnicaMailAutoRegisterFunctions')) {
        class ImnicaMailAutoRegisterFunctions {
            static function loadView($view_name, $data = array(), $echo = true) {
                extract($data);
                ob_start();
                include(IM_AR_PLUGIN_DIR."/views/{$view_name}.php");
                $string = ob_get_contents();
                ob_end_clean();
                
                if ($echo) 
                    echo $string;
                else 
                    return $string;
            } 
            
            static function install() {
                global $wpdb;
                require_once(ABSPATH.'wp-admin/includes/upgrade.php');
                
                $tables = array(
                    "{$wpdb->prefix}im_auto_register" => "CREATE TABLE {$wpdb->prefix}im_auto_register (
                        id mediumint(9) NOT NULL AUTO_INCREMENT,
                        name text NOT NULL,
                        email text NOT NULL,
                        UNIQUE KEY id (id)
                    );"
                );
                
                foreach ($tables as $table_name => $table_schema) {
                    if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}';") != $table_name) {   
                        dbDelta($table_schema);
                    }
                }
            }
            
            static function uninstall() {
                global $wpdb; 
                
                $tables = array(
                    "{$wpdb->prefix}im_auto_register"
                ); 
                
                $tables_str = implode($tables, ', ');
                
                $wpdb->query("DROP TABLE {$tables_str};");
            }       
        }      
    }
    
?>