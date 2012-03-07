<?php defined('BASEPATH') or exit('No direct script access allowed');

class Module_Social extends Module
{
	public $version = '1.0.3';

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
			'backend'  => true,
			'menu'	  => 'utilities',
			'skip_xss' => TRUE,
		);
	}

	public function install()
	{
		$this->dbforge->drop_table('authentications');
		$this->dbforge->drop_table('credentials');

		$authentications = "	
			CREATE TABLE ".$this->db->dbprefix('authentications')." (
			  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			  `provider` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
			  `uid` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
			  `access_token` text COLLATE utf8_unicode_ci DEFAULT NULL,
			  `secret` text COLLATE utf8_unicode_ci DEFAULT NULL,
			  `created_at` int(11) DEFAULT NULL,
			  `updated_at` int(11) DEFAULT NULL,
			  `user_id` int(11) DEFAULT NULL,
			  `expires` int(12) DEFAULT '0',
			  `refresh_token` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
			  PRIMARY KEY (`id`),
			  UNIQUE KEY `unique` (`user_id`,`provider`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
		";
		
		$credentials = "	
			CREATE TABLE ".$this->db->dbprefix('credentials')." (
			  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			  `provider` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
			  `client_key` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
			  `client_secret` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
			  `scope` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
			  `access_token` text COLLATE utf8_unicode_ci DEFAULT NULL,
			  `secret` text COLLATE utf8_unicode_ci DEFAULT NULL,
			  `expires` int(12) DEFAULT '0',
			  `refresh_token` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
			  `uid` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
			  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
			  `is_active` tinyint(1) DEFAULT '1',
			  PRIMARY KEY (`id`),
			  UNIQUE KEY `unique` (`provider`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
		";

		if ($this->db->query($authentications) and $this->db->query($credentials))
		{
			return true;
		}
	}

	public function uninstall()
	{
		$this->dbforge->drop_table('authentications');
		$this->dbforge->drop_table('credentials');
		
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