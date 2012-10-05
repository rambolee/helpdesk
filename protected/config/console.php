<?php
require_once('mail_config.php') ;

// This is the configuration for yiic console application.
// Any writable CConsoleApplication properties can be configured here.
return array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'HelpDesk Console',
	// preloading 'log' component
	'preload'=>array('log') ,

	// autoloading model and component classes
	'import'=>array(
		'application.models.*',
		'application.components.*',
		'application.lib.*',
	),
	// application components
	'components'=>array(
		// uncomment the following to use a MySQL database
		'db'=>array(
			'connectionString' => 'mysql:host=localhost;dbname=help_desk',
			'username' => 'helpdesk_admin',
			'password' => 'helpdesk_passwd',
			'charset' => 'utf8',
		),
		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'error, warning',
				),
			),
		),
	),
	'modules'=>array(
		'helpdesk' ,
	),
);
