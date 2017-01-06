<?php

include(PATH_THIRD.'/reegion_select/config.php');		
return array(
	'author' => 'Amphibian',
	'author_url' => 'http://amphibian.info',
	'description' => 'Display dropdown/multiselect menus for countries, US states, Canadian provinces, and UK counties as custom fields and in your templates.',
	'docs_url' => 'https://github.com/amphibian/reegion_select.ee_addon',
	'fieldtypes' => array(
		'reegion_select' => array(
			'name' => 'Reegion Select',
			'compatibility' => 'text'
		)
	),
	'name' => 'Reegion Select',
	'namespace' => 'Amphibian\ReegionSelect',
	'version' => REEGION_SELECT_VERSION
);