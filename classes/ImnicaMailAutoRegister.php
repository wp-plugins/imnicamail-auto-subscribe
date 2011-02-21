<?php
    
    if (!class_exists('ImnicaMailAutoRegister')) {
        
        class ImnicaMailAutoRegister {
            var $options = null;
            var $page_hooks = array();
            var $imnicamail_url = 'http://www.imnicamail.com/v4';
            var $wp_variables = array(
                'first_name'    => 'First Name',
                'last_name'     => 'Last Name',
                'nickname'      => 'Nickname',
                'aim'           => 'AOL Instant Messenger',
                'yim'           => 'Yahoo Instant Messenger',
                'jabber'        => 'Google Talk'
            );
            
            /**
            * Constructor.
            */
            
            function __construct() {
                $this->options = new ImnicaMailAutoRegisterOptions(array(
                    'field_mapping' => array(
                    ),
                    'login' => array(
                        'username' => 'user@imnica.com',
                        'password' => 'password'
                    ),
                    'list' => array(
                        'id' => '0'
                    )
                ));
                
                $this->options->load();
                $this->initialize();
            }    
            
            
            /**
            * Initialize the plugin.
            */
            
            function initialize() {
                /**
                * @todo check hook sequence.
                */
                
                add_action('user_register', array($this, 'autoSubscriberSubscribe'));   
//                add_action('personal_options_update', array($this, 'handlerPersonalOptionsUpdate'), 99, 1);
//                add_action('edit_user_profile_update', array($this, 'handlerPersonalOptionsUpdate'), 99, 1);
                add_action('profile_update', array($this, 'handlerPersonalOptionsUpdate'), 99, 1);
                
                if (is_admin()) {
                    add_action('admin_menu', array($this, 'addAdminMenus'));
                    
                    /**
                    * @todo: Rebuild.
                    */
                    
                    /*add_action('load-toplevel_page_im-ar-settings', array($this, 'handlerLoadPageSettings'));
                    add_action('load-im-auto-register_page_im-ar-field-mapping', array($this, 'handlerLoadPageFieldMapping'));*/
                    
                    /**
                    * @todo change the hook names.
                    */
                    
                    add_action('wp_ajax_im_ar_login_check', array($this, 'ajaxUserLogin'));
                    add_action('wp_ajax_im_ar_get_lists', array($this, 'ajaxListsGet'));
                    add_action('wp_ajax_im_ar_get_custom_fields', array($this, 'ajaxCustomFieldsGet'));
                    add_action('wp_ajax_im_ar_save_list', array($this, 'ajaxSaveList'));
                    
                    add_action('wp_ajax_im_ar_save_field_mapping', array($this, 'ajaxSaveFieldMapping'));
                    add_action('wp_ajax_im_ar_get_field_mapping', array($this, 'ajaxGetFieldMapping'));
                } else {
                }
            }
            
            
            /**
            * Handle user profile update.
            * 
            * @param mixed $user_id
            */
            
            function handlerPersonalOptionsUpdate($user_id) {
                global $wpdb;
                $user = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}users WHERE ID = '{$user_id}' LIMIT 1;");
//                var_dump($user);
                
                if (is_object($user)) {
                    $login_check = $this->userLogin();
                    
                    if ($login_check->Success) {
                        $get_subsriber = $this->subscriberGet($login_check->SessionID, $user->user_email);  
                        
                        if ($get_subsriber->Success) {
                            
                            $custom_fields = $this->customFieldsGet($login_check->SessionID);
                            $new_custom_fields = array();
                            
                            foreach ($custom_fields->CustomFields as $custom_field) {
                                $wp_user_meta_key = $this->options->values['field_mapping'][$custom_field->CustomFieldID];
                                if ($wp_user_meta_key) {
                                    $new_custom_fields[$custom_field->CustomFieldID] = get_user_meta($user->ID, $wp_user_meta_key, true);
                                } else {
                                    $new_custom_fields[$custom_field->CustomFieldID] = null;    
                                }
                            }

//                            var_dump($new_custom_fields);
                            $update_subscriber = $this->subscriberUpdate($login_check->SessionID, $get_subsriber->SubscriberInformation->SubscriberID, $user->user_email, $new_custom_fields);
//                            var_dump($update_subscriber);
                        } else {
                        }
                    }
                } else {
                }
            }
            
            
            /**
            * Subscribe a wp user to the server.
            * 
            * @param mixed $user_id
            */
            
            function autoSubscriberSubscribe($user_id) {
                global $wpdb;
                $user = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}users WHERE ID = '{$user_id}' LIMIT 1;");
                $wpdb->insert(
                    "{$wpdb->prefix}im_auto_register", 
                    array('email' => $user->user_email, 'name' => $user->user_login)
                );
                
                $login = $this->userLogin();
                
                if ($login->Success) {
                    $custom_variables = array();
                
                    foreach ($this->options->values['field_mapping'] as $field_id => $wp_variable) {
                        $custom_variables[$field_id] = get_user_meta($user->ID, $wp_variable, true);
                    }
                    
                    $result = $this->subscriberSubscribe($login->SessionID, $this->options->values['list']['id'], $user->user_email, $custom_variables);    
                } else {
                }
            }
            

            /**
            * Add the adminitration menus.
            */
            
            function addAdminMenus() {
                add_menu_page('IM Auto Register', 'IM Auto Register', 'manage_options', 'im-ar-settings', array($this, 'pageSettings'));
                $this->page_hooks['im-ar-settings'] = add_submenu_page('im-ar-settings', __('Settings'), __('Settings'), 'manage_options', 'im-ar-settings', array($this, 'pageSettings'));                    
                $this->page_hooks['im-ar-field-mapping'] = add_submenu_page('im-ar-settings', __('Field Mapping'), __('Field Mapping'), 'manage_options', 'im-ar-field-mapping', array($this, 'pageFieldMapping'));  
                
                add_action("load-{$this->page_hooks['im-ar-settings']}", array($this, 'handlerLoadPageSettings'));
                add_action("load-{$this->page_hooks['im-ar-field-mapping']}", array($this, 'handlerLoadPageFieldMapping'));  
            }         
            
            
            /**
            * Handle loading settings page.
            */
            
            function handlerLoadPageSettings() {
                wp_enqueue_script('im-ar-settings', IM_AR_PLUGIN_URL.'/scripts/settings.js', array('jquery'));
                wp_localize_script('im-ar-settings', 'im_ar_cfg', array(
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'list_id' => $this->options->values['list']['id']
                ));
            }
           
           
            /**
            * Show settings page.
            */
            
            function pageSettings() {
                ImnicaMailAutoRegisterFunctions::loadView('page-settings', array('options' => $this->options->values));
            }
            
            
            /**
            * Handle loading field mapping page.
            */
            
            function handlerLoadPageFieldMapping() {
                wp_enqueue_script('im-ar-field-mapping', IM_AR_PLUGIN_URL.'/scripts/field-mapping.js', array('jquery', 'jquery-ui-draggable', 'jquery-ui-droppable'));
                wp_localize_script('im-ar-field-mapping', 'im_ar_cfg', array(
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'field_mapping' => json_encode($this->options->values['field_mapping'])
                )); 
                
                wp_enqueue_style('im-ar-field-mapping', IM_AR_PLUGIN_URL.'/styles/field-mapping.css');
            }
            
            
            /**
            * Show field mapping page. 
            */
            
            function pageFieldMapping() {
                ImnicaMailAutoRegisterFunctions::loadView('page-field-mapping', array('im_ar' => $this));    
            }
            
            
            /**
            * Save the field mapping values.
            */
            
            function ajaxSaveFieldMapping() {
                $field_mapping = array();
                
                if (is_array($_POST['field_mapping'])) {
                    foreach ($_POST['field_mapping'] as $field_id => $wp_variable) {
                        $field_mapping[$field_id] = $wp_variable;                    
                    }
                }   
                                     
                $this->options->values['field_mapping'] = $field_mapping;
                $result = $this->options->save();
                
                $this->options->load();
                
                die(json_encode(array(
                    'Success' => ($field_mapping == $this->options->values['field_mapping'])
                )));
            }
            
            
            /**
            * Get the field mapping values.
            */
            
            function ajaxGetFieldMapping() {
                die(json_encode($this->options->values['field_mapping']));
            }                      
            
            
            /**
            * Subscribe a subscriber to the server.
            * 
            * @param mixed $session_id
            * @param mixed $list_id
            * @param mixed $email
            * @param mixed $custom_variables
            * @return mixed
            */
            
            function subscriberSubscribe($session_id, $list_id, $email, $custom_variables = array()) {
                $post_parameters = array();
                $post_parameters[] = "Command=Subscriber.Subscribe";
                $post_parameters[] = "ResponseFormat=JSON";
                $post_parameters[] = "SessionID={$session_id}";
                $post_parameters[] = "ListID={$list_id}";
                $post_parameters[] = "EmailAddress={$email}";
                $post_parameters[] = "IPAddress={$_SERVER['REMOTE_ADDR']}";
                
                foreach ($custom_variables as $id => $value) {
                    $post_parameters[] = "CustomField{$id}={$value}";
                }

                $response = $this->postData("{$this->imnicamail_url}/api.php?", $post_parameters);
                
                if (empty($response[0])) {           
                    return false;
                } else {
                    return json_decode($response[1]);      
                }
            }
            
            
            /**
            * Check the login details of the user.
            * @return mixed
            */
            
            function userLogin($username = null, $password = null) {                                                             
                $un = isset($username) ? $username :  $this->options->values['login']['username'];
                $pw = isset($password) ? $password :  $this->options->values['login']['password'];
                
                $post_parameters = array();
                $post_parameters[] = "Command=User.Login";
                $post_parameters[] = "ResponseFormat=JSON";
                $post_parameters[] = "Username={$un}";
                $post_parameters[] = "Password={$pw}";
                
                $response = $this->postData("{$this->imnicamail_url}/api.php?", $post_parameters);
                
                if (empty($response[0])) {
                    return false;
                } else {
                    $response_1 = json_decode($response[1]);
                    
                    if ($response_1->Success) {
                        $this->options->values['login']['username'] = $un;
                        $this->options->values['login']['password'] = $pw;
                        $this->options->save();
                    }
                    
                    return $response_1;
                }   
            } 
            
            
            /**
            * Ajax user login.
            */
            
            function ajaxUserLogin() {
                $username = $_POST['data']['username'];
                $password = $_POST['data']['password']; 
                $response = $this->userLogin($username, $password);
                die(json_encode($response));
            }                      
            
            
            /**
            * Ajax save list id.
            */
            
            function ajaxSaveList() {
                $list_id = $_POST['data']['list_id'];
                $this->options->values['list']['id'] = $list_id;
                $this->options->save();  
                 
                die(json_encode(array(
                    'Success' => true
                )));     
            }                      
            
            
            /**
            * Get lists from server.
            * 
            * @param string $session_id
            * @return mixed
            */
            
            function listsGet($session_id) {
                $post_parameters = array();
                $post_parameters[] = "Command=Lists.Get";
                $post_parameters[] = "ResponseFormat=JSON";
                $post_parameters[] = "SessionID={$session_id}";
                $post_parameters[] = "OrderField=ListID";
                $post_parameters[] = "OrderType=ASC";

                $response = $this->postData("{$this->imnicamail_url}/api.php?", $post_parameters);    
                
                if (empty($response[0])) {
                    return false;
                } else {
                    return json_decode($response[1]);                        
                }
            }   
            
            
            /**
            * Ajax listsGet.
            */
            
            function ajaxListsGet() {
                $session_id = $_POST['data']['session_id'];
                $response = $this->listsGet($session_id);
                die(json_encode($response));
            }               
            
            
            /**
            * Get custom fields from server.
            * 
            * @param mixed $session_id
            * @return mixed
            */
            
            function customFieldsGet($session_id) {
                $post_parameters = array();
                $post_parameters[] = "Command=CustomFields.Get";
                $post_parameters[] = "ResponseFormat=JSON";
                $post_parameters[] = "SessionID={$session_id}";
                $post_parameters[] = "SubscriberListID={$this->options->values['list']['id']}";
                $post_parameters[] = "OrderField=FieldName";
                $post_parameters[] = "OrderType=ASC";

                $response = $this->postData("{$this->imnicamail_url}/api.php?", $post_parameters);
                
                if (empty($response[0])) {
                    return false;
                } else {
                    return json_decode($response[1]);                        
                }
            }
            
            
            /**
            * Ajax customFieldsGet.
            */
            
            function ajaxCustomFieldsGet() {
                $session_id = $_POST['data']['session_id'];
                $response = $this->customFieldsGet($session_id);
                die(json_encode($response));
            }
            
            
            /**
            * Get a subsriber from the server.
            * 
            * @param mixed $session_id
            * @param mixed $email
            * @return mixed
            */
            
            function subscriberGet($session_id, $email) {
                $post_parameters = array();
                $post_parameters[] = "Command=Subscriber.Get";
                $post_parameters[] = "ResponseFormat=JSON";
                $post_parameters[] = "SessionID={$session_id}";
                $post_parameters[] = "EmailAddress={$email}";
                $post_parameters[] = "ListID={$this->options->values['list']['id']}";

                $response = $this->postData("{$this->imnicamail_url}/api.php?", $post_parameters);
                
                if (empty($response[0])) {
                    return false;
                } else {
                    return json_decode($response[1]);                        
                }  
            }
            
            
            /**
            * Update a subscriber in the server.
            * 
            * @param mixed $session_id
            * @param mixed $subscriber_id
            * @param mixed $subscriber_email
            * @param mixed $custom_fields
            * @return mixed
            */
            
            function subscriberUpdate($session_id, $subscriber_id, $subscriber_email, $custom_fields) {
                $post_parameters = array();
                $post_parameters[] = "SessionID={$session_id}";
                $post_parameters[] = "Command=Subscriber.Update";
                $post_parameters[] = "ResponseFormat=JSON";
                $post_parameters[] = "SubscriberID={$subscriber_id}";
                $post_parameters[] = "SubscriberListID={$this->options->values['list']['id']}";
                $post_parameters[] = "EmailAddress={$subscriber_email}";
                $post_parameters[] = "Access=admin";

                foreach ($custom_fields as $custom_field_id => $wp_user_meta_value) {
                    $post_parameters[] = "Fields[CustomField{$custom_field_id}]={$wp_user_meta_value}";    
                } 

                $response = $this->postData("{$this->imnicamail_url}/api.php?", $post_parameters, 'POST', false, '', '', 3600);
//                var_dump($response);
                
                if (empty($response[0])) {
                    return false;
                } else {
                    return json_decode($response[1]);                        
                }
            }
            
            
            /**
            * Post to a remote url via curl.
            * 
            * @param mixed $URL
            * @param mixed $post_parameters
            * @param mixed $HTTPRequestType
            * @param mixed $HTTPAuth
            * @param mixed $HTTPAuthUsername
            * @param mixed $HTTPAuthPassword
            * @param mixed $ConnectTimeOutSeconds
            * @param mixed $ReturnHeaders
            * @return mixed
            */
            
            function postData($URL, $post_parameters, $HTTPRequestType = 'POST', $HTTPAuth = false, $HTTPAuthUsername = '', $HTTPAuthPassword = '', $ConnectTimeOutSeconds = 60, $ReturnHeaders = false) {
                $PostParameters = implode('&', $post_parameters);
            
                $CurlHandler = curl_init();
                curl_setopt($CurlHandler, CURLOPT_URL, $URL);

                if ($HTTPRequestType == 'GET') {
                    curl_setopt($CurlHandler, CURLOPT_HTTPGET, true);
                } elseif ($HTTPRequestType == 'PUT') {
                    curl_setopt($CurlHandler, CURLOPT_PUT, true);
                } elseif ($HTTPRequestType == 'DELETE') {
                    curl_setopt($CurlHandler, CURLOPT_CUSTOMREQUEST, 'DELETE');
                } else {
                    curl_setopt($CurlHandler, CURLOPT_POST, true);
                    curl_setopt($CurlHandler, CURLOPT_POSTFIELDS, $PostParameters);
                }

                curl_setopt($CurlHandler, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($CurlHandler, CURLOPT_CONNECTTIMEOUT, $ConnectTimeOutSeconds);
                curl_setopt($CurlHandler, CURLOPT_TIMEOUT, $ConnectTimeOutSeconds);
                curl_setopt($CurlHandler, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.3) Gecko/2008092417 Firefox/3.0.3');

                if ((ini_get('safe_mode') != false) && (ini_get('open_basedir') != false)) {
                    curl_setopt($CurlHandler, CURLOPT_FOLLOWLOCATION, true);
                }

                if ($ReturnHeaders == true) {
                    curl_setopt($CurlHandler, CURLOPT_HEADER, true);
                } else {
                    curl_setopt($CurlHandler, CURLOPT_HEADER, false);
                }

                if ($HTTPAuth == true) {
                    curl_setopt($CurlHandler, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                    curl_setopt($CurlHandler, CURLOPT_USERPWD, $HTTPAuthUsername.':'.$HTTPAuthPassword);
                }

                $RemoteContent = curl_exec($CurlHandler);

                if (curl_error($CurlHandler) != '') {
                    return array(false, curl_error($CurlHandler));
                }

                curl_close($CurlHandler);

                return array(true, $RemoteContent);
            }
        }
    }
    
?>