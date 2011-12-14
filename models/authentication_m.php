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
}