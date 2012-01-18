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

		// Post a blog to twitter and whatnot
        Events::register('blog_article_published', array($this, 'post_status'));
     }
    
    // this will be triggered by the Events::trigger('save_authentication') code in modules/users/controllers/.php
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
		$this->ci->authentication_m->save(array(
			'user_id' 		=> $user_id,
			'provider' 		=> $token['provider'],
			'uid' 			=> $user_hash['uid'],
			'access_token' 	=> $token['access_token'],
			'secret' 		=> isset($token['secret']) ? $token['secret'] : null,
			'expires' 		=> isset($token['expires']) ? $token['expires'] : null,
			'refresh_token' => isset($token['refresh_token']) ? $token['refresh_token'] : null,
		));
    }

	public function post_status($article)
	{	
		$this->ci->load->model('social/credential_m');
		
		$url = site_url('blog/'.date('Y/m').'/'.$this->ci->input->post('slug'));
		
		// Try and post that shit to facebook!
		if (($credentials = $this->ci->credential_m->get_active_provider('facebook')))
		{
			$params = array(
				'access_token' => $credentials->access_token, 
				'name'=> $this->ci->input->post('title'),
				'message'=> html_entity_decode(strip_tags($this->ci->input->post('intro'))),
				'link' => $url,
			);
			
			log_message('info', 'Post status with Facebook: '.json_encode($params));
			
			$ch = curl_init();
			curl_setopt_array($ch, array(
				CURLOPT_URL => 'https://graph.facebook.com/me/feed',
				CURLOPT_POSTFIELDS => $params,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_VERBOSE => true
			));
			$result = curl_exec($ch);
		}
		
		// Twitter wants it too... yeah she does!
		if (($credentials = $this->ci->credential_m->get_active_provider('twitter')))
		{
			$this->ci->load->library('twitter', array(
				'consumer_key' => $credentials->client_key,
				'consumer_secret' => $credentials->client_secret,
				'oauth_token' => $credentials->access_token,
				'oauth_token_secret' => $credentials->secret,
			));
			
			$message = character_limiter(strip_tags($this->ci->input->post('title')), 130).' '.$url;
			
			log_message('info', 'Post status with Twitter: '.json_encode(array('status' => $message)));
			
			$this->ci->twitter->post('statuses/update', array('status' => $message));
		}
	}
}

/* End of file events.php */