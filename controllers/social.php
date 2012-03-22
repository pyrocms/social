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
		'mailchimp' => 'oauth2',
		'soundcloud' => 'oauth2',
		'tumblr' => 'oauth',
		// 'openid' => 'OpenId',
		'windowslive' => 'oauth2',
		'youtube' => 'oauth2',
	);
	
	public function __construct()
	{
		parent::__construct();
		
		$this->lang->load('social');
		
		$this->load->model(array('authentication_m', 'credential_m'));
	}
	
	public function _remap($method, $args)
	{
		if ($method == 'linked')
		{
			$this->linked();
			return;
		}

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
			$this->ion_auth->is_admin()
				? show_error('Social Integration: '.$provider.' is not supported, or not enabled.')
				: show_404();
		}
		
		// oauth or oauth 2?
		$strategy = $this->providers[$provider];
		
		switch ($strategy)
		{
			case 'oauth':
				include $this->module_details['path'].'/oauth/libraries/OAuth.php';
				$oauth = new OAuth;
				
				// Create an consumer from the config
				$consumer = $oauth->consumer(array(
					'key' => $credentials->client_key,
					'secret' => $credentials->client_secret,
				));

				// Load the provider
				$provider = $oauth->provider($provider);
				
			break;
			
			case 'oauth2':
				include $this->module_details['path'].'/oauth2/libraries/OAuth2.php';
				$oauth2 = new OAuth2;
				
				// OAuth2 is the honey badger when it comes to consumers - it just dont give a shit
				$consumer = null;
				
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
		call_user_func(array($this, '_'.$method), $strategy, $provider, $consumer);
	}
	
	// Build the session and redirect to provider
	private function _session($strategy, $provider, $consumer)
	{
		// Create the URL to return the user to
		$callback = site_url('social/callback/'.$provider->name);
		
		switch ($strategy)
		{
			case 'oauth':
				
				// Add the callback URL to the consumer
				$consumer->callback($callback);	

				// Get a request token for the consumer
				$token = $provider->request_token($consumer);

				// Store the token
				$this->session->set_userdata('oauth_token', base64_encode(serialize($token)));

				// Redirect to the twitter login page
				$provider->authorize($token, array(
					'oauth_callback' => $callback,
				));
				
			break;
			
			case 'oauth2':
				// Redirect off to... where-ever
				$provider->authorize(array(
					'redirect_uri' => $callback,
				));
			break;
		}
	}
	
	// We've got back from the provider, so get smart and save stuff
	private function _callback($strategy, $provider, $consumer)
	{
		switch ($strategy)
		{
			case 'oauth':
			
				if ($this->session->userdata('oauth_token'))
				{
					// Get the token from storage
					$token = unserialize(base64_decode($this->session->userdata('oauth_token')));
				}

				if ( ! empty($token) AND $token->access_token !== $this->input->get_post('oauth_token'))
				{	
					// Delete the token, it is not valid
					$this->session->unset_userdata('oauth_token');

					// Send the user back to the beginning
					exit('invalid token after coming back to site');
				}

				// Get the verifier
				$verifier = $this->input->get_post('oauth_verifier');

				// Store the verifier in the token
				$token->verifier($verifier);

				// Exchange the request token for an access token
				$token = $provider->access_token($consumer, $token);
			
				// We got the token, let's get some user data
				$user_hash = $provider->get_user_info($consumer, $token);
				
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
	
		// Are we taking this back to the admin?
		if ($this->session->userdata('social_admin_redirect'))
		{
			// Send the token to the admin controller after redirect
			$this->session->set_userdata('token', array(
				'access_token' => $token->access_token,
				'secret' => isset($token->secret) ? $token->secret : null,
				'expires' => isset($token->expires) ? $token->expires : null,
				'refresh_token' => isset($token->refresh_token) ? $token->refresh_token : null,
				'provider' => $provider->name,
			));
			
			$this->session->unset_userdata('social_admin_redirect');
			
			redirect('admin/social/token_save');
		}
	
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
				$this->authentication_m->save(array(
					'user_id' 		=> $this->current_user->id,
					'provider' 		=> $provider->name,
					'uid' 			=> $user_hash['uid'],
					'access_token' 	=> isset($token->access_token) ? $token->access_token : null,
					'secret' 		=> isset($token->secret) ? $token->secret : null,
					'expires' 		=> isset($token->expires) ? $token->expires : null,
					'refresh_token' => isset($token->refresh_token) ? $token->refresh_token : null,
				));
			
				// Attachment went ok so we'll redirect
				redirect($this->input->get('success_url') ? $this->input->get('success_url') : 'social/linked');
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
					'provider' => $provider->name,
				),
			));

			redirect('users/register');
		}
	}
	
	// List of Linked accounts
	public function linked()
	{
		$this->current_user or redirect('users/login/social/linked');
		
		$authentications = $this->authentication_m->get_many_by(array(
			'user_id' => $this->current_user->id,
		));
		
		// 
		$this->template->build('linked', array(
			'authentications' => $authentications,
		));
	}
}