<?php

class Social extends Public_Controller
{
	protected $providers = array(
		'facebook' => 'oauth2',
		'twitter' => 'oauth',
		'dropbox' => 'oauth',
		'flickr' => 'oauth',
		'google' => 'oauth2',
		'github' => 'oauth2',
		'linkedin' => 'oauth',
		// 'openid' => 'OpenId',
		'windowslive' => 'oauth2',
		'youtube' => 'oauth2',
	);
	
	public function __construct()
	{
		parent::__construct();
		
		// $this->load->config('social');
		
		$this->load->model(array('authentication_m', 'credential_m'));
	}
	
	public function _remap($method, $args)
	{
		// Invalid method or no provider = BOOM
		if ( ! in_array($method, array('session', 'callback')) or empty($args))
		{
			show_404();
		}
		
		// Get the provider (facebook, twitter, etc)
		list($provider) = $args;
		
		// This provider is not supported by the module
		if ( ! isset($this->providers[$provider]))
		{
			show_404();
		}
		
		// Look to see if we have this provider in the db?
		if ( ! ($credentials = $this->credential_m->get_active_provider($provider)))
		{
			show_404();
		}
		
		// oauth or oauth 2?
		$strategy = $this->providers[$provider];
		
		switch ($strategy)
		{
			case 'oauth':
				include $this->module_details['path'].'/oauth/libraries/oauth.php';
				$oauth = new OAuth;
				$provider = $oauth->provider($provider, array(
					'key' => $credentials->client_key,
					'secret' => $credentials->client_secret,
					'scope' => $credentials->scope,
					'callback' => site_url('social/callback/'.$provider),
				));
				
			break;
			
			case 'oauth2':
				include $this->module_details['path'].'/oauth2/libraries/oauth2.php';
				$oauth2 = new OAuth2;
				
				$provider = $oauth2->provider($provider, array(
					'id' => $credentials->client_key,
					'secret' => $credentials->client_secret,
					'scope' => $credentials->scope,
				));
			break;
			
			default:
				exit('Something went properly wrong!');
		}
		
		// Call session or callback, with lots of handy details
		call_user_func(array($this, '_'.$method), $strategy, $provider);
	}
	
	// Build the session and redirect to provider
	private function _session($strategy, $provider)
	{
		switch ($strategy)
		{
			case 'oauth':
				// Redirect off to... where-ever
				$provider->authorize(array(
					'redirect_uri' => site_url('social/callback/'.$provider->name),
				));
				
			break;
			
			case 'oauth2':
				// Redirect off to... where-ever
				$provider->authorize(array(
					'redirect_uri' => site_url('social/callback/'.$provider->name),
				));
			break;
		}
	}
	
	// We've got back from the provider, so get smart and save stuff
	public function _callback($strategy, $provider)
	{
		switch ($strategy)
		{
			case 'oauth':
			
				// Grab the access token from the code
				$token = $provider->access($_GET['code']);

				// We got the token, let's get some user data
				$user_hash = $provider->get_user_info($token);
				
			break;
			
			case 'oauth2':
				
				// Grab the access token from the code
				$token = $provider->access($_GET['code']);

				// We got the token, let's get some user data
				$user_hash = $provider->get_user_info($token);
				
			break;
		}
	
		// Let's get ready to interact with users
		$this->load->model('authentication_m');
	
		// Are they logged in?
		if ($this->current_user)
		{
			// Do they have attached? It might matter
			$auth = $this->authentication_m->get_by(array(
				'user_id' => $this->current_user->id,
				'provider' => $provider->name,
			));
		
			// If there are no attachments, or they can have multiple
			// TODO: Remove hack - not sure about config yet
			if ( ! $auth or TRUE or $config['link_multiple_providers'] === true)
			{
				// If there is no uid we can't remember who this is
				if (empty($user_hash['uid']))
				{
					throw new Exception('No uid in response from '.$provider->name.'.');
				}

				// Attach this account to the logged in user
				$this->authentication_m->insert(array(
					'user_id' 		=> $this->current_user->id,
					'provider' 		=> $provider->name,
					'uid' 			=> $user_hash['uid'],
					'access_token' 	=> isset($token->access_token) ? $token->access_token : null,
					'secret' 		=> isset($token->secret) ? $token->secret : null,
					'expires' 		=> isset($token->expires) ? $token->expires : null,
					'refresh_token' => isset($token->refresh_token) ? $token->refresh_token : null,
					'created_at' 	=> time(),
				));
			
				// Attachment went ok so we'll redirect
				redirect('social/linked');
			}
		
			else
			{
				throw new Exception(sprintf('This user is already linked to "%s".', $auth->provider));
			}
		}
	
		// The user exists, so send him on his merry way as a user
		else if ($auth = $this->authentication_m->get_by(array(
			'uid' => $user_hash['uid'],
			'provider' => $provider->name,
		)))
		{
			// Force a login with this username
			if ( ! $this->ion_auth->force_login($auth->user_id))
			{
			    show_error('Failed to log you in.');
			}
		
			$this->session->set_flashdata('success', lang('user_logged_in'));
		    redirect('/');
		}

		// They aren't a user, so redirect to registration page
		else
		{
			$this->session->set_userdata(array(
				'user_hash' => $user_hash,
				'token' => array(
					'access_token' => $token->access_token,
					'expires' => $token->expires,
					'refresh_token' => $token->refresh_token,
					'provider' => $provider->name
				),
			));

			redirect('users/register');
		}
	}
}