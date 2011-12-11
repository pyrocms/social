<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * @author 		PyroCMS Development Team
 * @package 	PyroCMS
 * @subpackage 	Social Module
 * @since		v2.1
 *
 */
class Credential_m extends MY_Model
{
	public function save($input)
	{
		return $this->db->replace('credentials', $input);
	}
	
	public function get_active_provider($provider)
	{
		return $this->db
			->select('client_key, client_secret, scope')
			->where('provider', $provider)
			->where('client_key IS NOT NULL')
			->where('client_secret IS NOT NULL')
			->get('credentials')
			->row();
	}
}