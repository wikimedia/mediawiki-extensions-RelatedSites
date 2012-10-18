<?php

if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'This file is a MediaWiki extension, it is not a valid entry point' );
}

// autoloader
$wgAutoloadClasses['RelatedSites'] = __DIR__ . '/RelatedSites.class.php';

// extension & magic words i18n
$wgExtensionMessagesFiles['RelatedSites'] = __DIR__ . '/RelatedSites.i18n.php';

// hooks
$wgRelatedSites = new RelatedSites;
$wgHooks['SkinTemplateOutputPageBeforeExec'][] = array( &$wgRelatedSites, 'onSkinTemplateOutputPageBeforeExec' );
$wgHooks['ParserBeforeTidy'][] = array( &$wgRelatedSites, 'onParserBeforeTidy' );

// 2 same hooks, with different position though - enable what you want
// the first one is a "clean" solution, but has its content inserted _before_ the toolbox
//$wgHooks['SkinBuildSidebar'][] = array( &$wgRelatedSites, 'onSkinBuildSidebar' );
// the second one is nasty: echo'ing raw html _after_ the regular toolbox
$wgHooks['SkinTemplateToolboxEnd'][] = array( &$wgRelatedSites, 'onSkinTemplateToolboxEnd' );

// credits
$wgExtensionCredits['parserhook']['RelatedSites'] = array(
	'path' => __FILE__,
	'name' => 'RelatedSites',
	'url' => '//www.mediawiki.org/wiki/Extension:RelatedSites',
	'descriptionmsg' => 'relatedsites-desc',
	'author' => array( 'Roland Unger', 'Hans Musil', 'Matthias Mullie' ),
	'version' => '1.1'
);

// related interwiki prefixes that should show up on relatedsites sidebar
$wgRelatedSitesPrefixes = array();
