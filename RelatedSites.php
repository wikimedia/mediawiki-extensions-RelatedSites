<?php

if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'This file is a MediaWiki extension, it is not a valid entry point' );
}

// autoloader
$wgAutoloadClasses['RelatedSites'] = __DIR__ . '/RelatedSites.class.php';

// extension & magic words i18n
$wgMessagesDirs['RelatedSites'] = __DIR__ . '/i18n';
$wgExtensionMessagesFiles['RelatedSites'] = __DIR__ . '/RelatedSites.i18n.php';

// hooks
$wgHooks['ParserBeforeTidy'][] = 'RelatedSites::onParserBeforeTidy';
$wgHooks['OutputPageParserOutput'][] = 'RelatedSites::onOutputPageParserOutput';
$wgHooks['SidebarBeforeOutput'][] = 'RelatedSites::onSidebarBeforeOutput';

// credits
$wgExtensionCredits['parserhook'][] = array(
	'path' => __FILE__,
	'name' => 'RelatedSites',
	'url' => 'https://www.mediawiki.org/wiki/Extension:RelatedSites',
	'descriptionmsg' => 'relatedsites-desc',
	'author' => array( 'Roland Unger', 'Hans Musil', 'Matthias Mullie' ),
	'version' => '1.1'
);

// related interwiki prefixes that should show up on relatedsites sidebar
$wgRelatedSitesPrefixes = array();
