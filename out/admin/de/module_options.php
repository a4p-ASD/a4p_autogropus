<?php

/**
 *	@author:	a4p ASD / Andreas Dorner
 *	@company:	apps4print / page one GmbH, Nürnberg, Germany
 *
 *
 *	@version:	1.0.0
 *	@date:		09.04.2014
 *
 *
 *	module_options.php
 *
 *	apps4print - a4p_autogroups - add new users to defined groups
 *
 */

// -------------------------------
// RESOURCE IDENTIFIER = STRING
// -------------------------------
$aLang = array(

	'charset'									=> 'UTF-8',
	
	'SHOP_MODULE_GROUP_main'					=> 'apps4print Einstellungen',
	
	'SHOP_MODULE_a4p_autogroups_addToGroup'					=> 'Nach Registrierung in Gruppe aufnehmen',
	'HELP_SHOP_MODULE_a4p_autogroups_addToGroup'			=> 'Gruppenname(n) angeben (Komma-getrennt)<br>z.B.: oxidadmin, oxidblacklist, oxidblocked, oxidcustomer, oxiddealer, oxidforeigncustomer, oxidgoodcust, oxidmiddlecust, oxidnewcustomer, oxidnewsletter, oxidnotyetordered, oxidpowershopper, oxidpricea, oxidpriceb, oxidpricec, oxidsmallcust',
	
	'SHOP_MODULE_a4p_autogroups_addToDealer'				=> 'autom. in H&auml;ndler-Gruppe aufnehmen',
	'HELP_SHOP_MODULE_a4p_autogroups_addToDealer'			=> 'Registrierten Kunden bei Eingabe von Firma + UST-ID automatisch in Gruppe &quot;H&auml;ndler&quot; (oxiddealer) aufnehmen',
	
	'SHOP_MODULE_a4p_autogroups_addToCountryGroup'			=> 'autom. in Gruppe des gew. Landes aufnehmen',
	'HELP_SHOP_MODULE_a4p_autogroups_addToCountryGroup'		=> 'Registrierten Kunden bei Eingabe von Land automatisch in eine entspr. Gruppe (falls vorhanden) aufnehmen'
	
);
