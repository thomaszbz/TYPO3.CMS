<?php
$EM_CONF[$_EXTKEY] = array(
	'title' => 'User>Task Center',
	'description' => 'The Task Center is the framework for a host of other extensions, see below.',
	'category' => 'module',
	'state' => 'stable',
	'uploadfolder' => 0,
	'createDirs' => '',
	'clearCacheOnLoad' => 0,
	'author' => 'Kasper Skaarhoj',
	'author_email' => 'kasperYYYY@typo3.com',
	'author_company' => 'Curby Soft Multimedia',
	'version' => '7.5.0',
	'constraints' => array(
		'depends' => array(
			'typo3' => '7.5.0-7.5.99',
		),
		'conflicts' => array(),
		'suggests' => array(
			'sys_action' => '7.5.0-7.5.99',
		),
	),
);
