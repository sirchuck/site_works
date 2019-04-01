<?php
	// This should normally be your first line if you are passing a variable array.
	$q = json_decode( base64_decode( getopt("q:")['q'] ) );

	// You access your variables passed like this:
	// echo $q->key1;

	// Do you want to use the framework?
	// For multi-server setup create a symbolic link in your config folder pointing to that servers real config file.
	// Here I'll call it joint_confing.pconf.php
	// Once you set the config file to use, require the site_works_essentials.php file.
	// You can now access most framework items with
	// $_s->_tool->dmsg('hello'); instead of the usual $this->_tool->dmsg('hello');
	// $_s also includes most of the configuration variables if you need them.
	$use_config = 'joint_config.pconf.php';
    require_once(dirname(__DIR__, 2) . '/site_works_essentials.php');



?>