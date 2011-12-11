<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Sample Events Class
*
* @package 		PyroCMS
* @subpackage 	Social Module
* @category 	events
* @author 		PyroCMS Dev Team
*/
class Events_Social
{
    protected $ci;
    
    public function __construct()
    {
        $this->ci =& get_instance();
        
        // register the public_controller event when this file is autoloaded
        Events::register('post_user_register', array($this, 'save_authentication'));
     }
    
    // this will be triggered by the Events::trigger('public_controller') code in Public_Controller.php
    public function save_authentication($user_id)
    {
		// Let's get ready to interact with users
		$this->ci->load->model('social/authentication_m');
		
		$user_hash = $this->ci->session->userdata('user_hash');
		$token = $this->ci->session->userdata('token');
		
		// Remove the user_hash now that it's been set
		$this->ci->session->unset_userdata('user_hash');
		$this->ci->session->unset_userdata('token');
		
		// Attach this account to the logged in user
		$this->ci->authentication_m->insert(array(
			'user_id' 		=> $user_id,
			'provider' 		=> $token['provider'],
			'uid' 			=> $user_hash['uid'],
			'access_token' 	=> $token['access_token'],
			'secret' 		=> isset($token['secret']) ? $token['secret'] : null,
			'expires' 		=> isset($token['expires']) ? $token['expires'] : null,
			'refresh_token' => isset($token['refresh_token']) ? $token['refresh_token'] : null,
			'created_at' 	=> time(),
		));
    }
}

/* End of file events.php */