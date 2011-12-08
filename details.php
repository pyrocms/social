<?php defined('BASEPATH') or exit('No direct script access allowed');

class Module_Social extends Module {

	public $version = '1.0';

	public function info()
	{
		return array(
			'name' => array(
				'en' => 'Social'
			),
			'description' => array(
				'en' => 'Link user accounts with Twitter, Facebook, Google and many more providers.',
			),
			'frontend' => true,
			'backend'  => false,
			'skip_xss' => TRUE,
			'menu'	  => FALSE
		);
	}

	public function install()
	{
		$this->dbforge->drop_table('authentications');

		$settings = "	
			CREATE TABLE ".$this->db->dbprefix('authentications')." (
			  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			  `provider` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
			  `uid` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
			  `access_token` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
			  `secret` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
			  `created_at` int(11) DEFAULT NULL,
			  `updated_at` int(11) DEFAULT NULL,
			  `user_id` int(11) DEFAULT NULL,
			  `expires` int(12) DEFAULT '0',
			  `refresh_token` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
		";

		if ($this->db->query($settings))
		{
			return true;
		}
	}

	public function uninstall()
	{
		$this->dbforge->drop_table('authentications');
		
		return true;
	}

	public function upgrade($old_version)
	{
		// Your Upgrade Logic
		return TRUE;
	}

	public function help()
	{
		// Return a string containing help info
		// You could include a file and return it here.
		return "No documentation has been added for this module.";
	}
}
/* End of file details.php */