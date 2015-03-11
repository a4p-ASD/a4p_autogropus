<?php

/**
 *	@author:	a4p ASD / Andreas Dorner
 *	@company:	apps4print / page one GmbH, Nürnberg, Germany
 *
 *
 *	@version:	1.0.1
 *	@date:		11.03.2015
 *
 *
 *	metadata.php
 *
 *	apps4print - a4p_autogroups - neue Benutzer zu festgelegten Gruppen hinzufügen
 *
 */

// ------------------------------------------------------------------------------------------------
// apps4print
// ------------------------------------------------------------------------------------------------

$sMetadataVersion = '1.0';

$aModule = array(
	'id'			=> 'a4p_autogroups',
	'title'			=> 'apps4print - a4p_autogroups',
	'description'	=> array(
		'de'									=> 'neue Benutzer zu festgelegten Gruppen hinzufügen', 
		'en'									=> 'add new users to defined groups'
	),
	'lang'			=> 'de',
	'thumbnail'		=> 'out/img/apps4print/a4p_logo.jpg',
	'version'		=> '<a4p_VERSION> (1.0.1)',
	'author'		=> 'apps4print',
	'url'			=> 'http://www.apps4print.com',
	'email'			=> 'support@apps4print.com',
	'extend'	  	=> array(
		'oxcmp_user'							=> 'apps4print/a4p_autogroups/components/a4p_autogroups__oxcmp_user'
	),
	'files'			=> array(
	),
	'blocks'		=> array(
	),
	'settings'		=> array(
		array( 'group' => 'main',	'name' => 'a4p_autogroups_addToGroup',			'type' => 'str',	'value' => 'oxiddealer' ), 
		array( 'group' => 'main',	'name' => 'a4p_autogroups_addToDealer',			'type' => 'bool',	'value' => 'true' ), 
		array( 'group' => 'main',	'name' => 'a4p_autogroups_addToCountryGroup',	'type' => 'bool',	'value' => 'true' ) 
	),
	'templates'		=> array(
	)
);

// ------------------------------------------------------------------------------------------------
// apps4print
// ------------------------------------------------------------------------------------------------
