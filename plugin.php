<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Social Plugin
 *
 * Create lists of posts
 *
 * @package		PyroCMS
 * @author		PyroCMS Dev Team
 * @copyright	Copyright (c) 2008 - 2011, PyroCMS
 *
 */
class Plugin_Social extends Plugin
{
	/**
	 * Provider List
	 *
	 * Creates a list of social network providers (twitter, facebook, etc)
	 *
	 * Usage:
	 * {{ social:providers }}
	 *		<h2>{{ name }}</h2>
	 *		<p>{{ theme:image_path }}</p>
	 * {{ /social:providers }}
	 *
	 * @param	array
	 * @return	array
	 */
	public function providers()
	{
		$this->load->model('social/credential_m');
		return $this->credential_m->get_active_providers();
	}
}

/* End of file plugin.php */