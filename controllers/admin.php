<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 *
 * @package  	PyroCMS
 * @subpackage  Blog
 * @category  	Module
 */
class Admin extends Admin_Controller
{
	protected $providers = array(
		'facebook' => array('human' => 'Facebook', 'default_scope' => 'offline_access,email,publish_stream'),
		'twitter' => array('human' => 'Twitter'),
		'dropbox' => array('human' => 'Dropbox'),
		'flickr' => array('human' => 'Flickr'),
		'google' => array('human' => 'Google', 'default_scope' => 'https://www.googleapis.com/auth/userinfo.profile https://www.googleapis.com/auth/userinfo.email'),
		'github' => array('human' => 'GitHub'),
		'linkedin' => array('human' => 'LinkedIn'),
		'tumblr' => array('human' => 'Tumblr'),
		// 'openid' => array('human' => 'OpenId'),
		'windowslive' => array('human' => 'Windows Live'),
		'youtube' => array('human' => 'YouTube'),
	);
	
	public function __construct()
	{
		parent::__construct();
		$this->lang->load('social');
		$this->load->model(array('authentication_m', 'credential_m'));
	}
	
	public function index()
	{
		$providers = array();
		
		// Look for all oauth and oauth2 strategies
		foreach (array('oauth', 'oauth2') as $strategy)
		{		
			if (($libraries = glob($this->module_details['path'].'/'.$strategy.'/libraries/providers/*.php')))
			{
				// Build an array of what is available
				foreach ($libraries as $provider)
				{
					$name = strtolower(basename($provider, '.php'));
					
					$providers[$name] = array(
						'strategy' => $strategy,
						'human' => $this->providers[$name]['human'],
						'default_scope' => element('default_scope', $this->providers[$name], NULL),
					);
				}
			}
		}
		
		// Existing credentials, display id/key/secret/etc.
		$all_credentials = $this->credential_m->get_all();
		foreach ($all_credentials as $provider_credential)
		{
			// Why do you have a credential for a missing provider? Weird
			if (empty($providers[$provider_credential->provider]))
			{
				continue;
			}
			
			$providers[$provider_credential->provider]['credentials'] = $provider_credential;
		}
		unset($all_credentials, $provider_credentials);
		
		// Sort by provider. If oauth 1 and 2, it will use 2
		ksort($providers);
		
		$this->template->build('admin/index', array(
			'providers' => $providers,
		));
	}

	
	public function token_redirect($provider)
	{
		$this->session->set_userdata('social_admin_redirect', 'true');
		
		redirect('social/session/'.strtolower($provider));
	}
	
	
	public function token_save()
	{
		$token = $this->session->userdata('token');
		
		$this->credential_m->save_token($token['provider'], array(
			'access_token' => $token['access_token'],
			'secret' => $token['secret'],
			'expires' => $token['expires'],
			'refresh_token' => $token['refresh_token'],
			'is_active' => true,
		)) or show_error(lang('social:failed_save_authentication'));
		
		$this->session->unset_userdata('token');
		
		// Set a success message
		$this->session->set_flashdata('success', sprintf(lang('social:save_credentials'), $token['provider']));
		
		echo "<script>window.close();</script>";
	}
	
	
	public function save_credentials($provider)
	{
		if ( ! $this->input->post('client_key') or ! $this->input->post('client_secret'))
		{
			set_status_header(406);
			exit(json_encode(array('error' => lang('social:failed_save_credentials'))));
		}
		
		$result = $this->credential_m->save(array(
			'provider' => $provider,
			'client_key' => $this->input->post('client_key'),
			'client_secret' => $this->input->post('client_secret'),
			'scope' => $this->input->post('scope'),
		));
		
		if ( ! $result)
		{
			set_status_header(406);
			exit(json_encode(array('error' => lang('social:failed_save_credentials'))));
		}
		
		exit(json_encode(array('success' => true)));
	}
	
	public function save_status($provider)
	{
		$this->credential_m->save_status($provider, (bool) $this->input->post('status'));
	}
	
	public function remove_credentials()
	{
		$provider = $this->input->post('provider') or show_404();
		
		$this->credential_m->delete_by('provider', $provider);
		
		$this->session->set_flashdata('success', sprintf(lang('social:removed_credentials'), $provider));
	}
}