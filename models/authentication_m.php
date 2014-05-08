<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * @author 		PyroCMS Development Team
 * @package 	PyroCMS
 * @subpackage 	Social Module
 * @since		v2.1
 *
 */
class Authentication_m extends MY_Model
{
	public function save($input)
	{
		$input['created_at'] = time();
		return $this->db->replace('authentications', $input);
	}
	
	public function get_token($user, $provider)
	{
		$token = $this->db->where('user_id', $user->id)
			->where('provider', $provider)
			->get($this->_table)
			->result();
		
		if ($token) return $token[0];
		
		return false;
			
	}
	
	public function remove_token($user, $provider)
	{
		return $this->db->where('user_id', $user->id)
			->where('provider', $provider)
			->delete($this->_table);
	}
}
