<?php

    if (!class_exists('ImnicaMailAutoRegisterOptions')) {
        class ImnicaMailAutoRegisterOptions {
            var $name = 'ImnicaMailAutoRegisterOptions';
            var $values = array();
            
            
            /**
            * Constructor.
            * 
            * @param mixed Default option values.
            * @param mixed Option name to be used for database reference.
            * @return ImnicaMailAutoRegisterOptions
            */
            
            function __construct($default = array(), $name = null) {
                $this->values = $default;
                
                if (isset($name)) {
                    $this->name = $name;
                }    
            }
            
            
            /**
            * Load the values from the datbase.
            * 
            * @todo Improve values merging.
            */
            
            function load() {
                $values = get_option($this->name);
                
                if (false === $values) {
                    $this->save();    
                } else {
                    $this->values = array_merge($this->values, $values);     
                }
            }
            
            
            /**
            * Save the values on the database.
            * 
            * @return bool
            */
            
            function save() {
                return update_option($this->name, $this->values);
            }
        }
    }
  
?>
