<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class OAuth2_Provider_Foursquare extends OAuth2_Provider
{  
	public $name = 'foursquare';

	protected $method = 'POST';

	public function url_authorize()
	{
		return 'https://foursquare.com/oauth2/authorize';
	}

	public function url_access_token()
	{
		return 'https://foursquare.com/oauth2/access_token';
	}
	
	/*
	* Get access to the API
	*
	* @param	string	The access code
	* @return	object	Success or failure along with the response details
	*/	
	public function access($code, $options = array())
	{
		if ($code === null)
		{
			throw new OAuth2_Exception(array('message' => 'Expected Authorization Code from '.ucfirst($this->name).' is missing'));
		}

		return parent::access($code, $options);
	}

	public function get_user_info(OAuth2_Token_Access $token)
	{
		// Create a response from the request
		return array(
			'uid' => $token->access_token,
		);
	}
}
