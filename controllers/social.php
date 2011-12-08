<?php

class Social extends Public_Controller
{
	public function session($provider)
	{
	    $this->load->config('social');
	    
		include APPPATH.'modules/social/oauth2/libraries/OAuth2.php';
		$this->oauth2 = new OAuth2;
		
		$config = $this->config->item('social');
		
		// This is not set up so 404
		if (empty($config['providers'][$provider]))
		{
			show_404();
		}
		
		$options = $config['providers'][$provider];
		
		// Who are we talking about?
	    $provider = $this->oauth2->provider($provider, $options);

		// Step 1, get an auth code
	    if ( ! isset($_GET['code']))
	    {
	        // By sending no options it'll come back here
	        $provider->authorize();
	    }
	
		// Step 2: Exchange that code for an access token
	    else
	    {
	        // Howzit?
	        try
	        {
	            $token = $provider->access($_GET['code']);

				// We got the token, let's get some user data
	            $user = $provider->get_user_info($token->access_token);
				
				dump($user);
				exit;
				
				$this->session->set_userdata('user_hash', $user);
				
				redirect('users/register');
	        }

	        catch (OAuth2_Exception $e)
	        {
	            show_error('That didnt work: '.$e);
	        }

	    }
	}
}