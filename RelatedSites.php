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
$wgHooks['SkinTemplateOutputPageBeforeExec'][] = 'RelatedSites::onSkinTemplateOutputPageBeforeExec';
$wgHooks['ParserBeforeTidy'][] = 'RelatedSites::onParserBeforeTidy';

// @TODO Add a global to control these, and then probably use wgExtensionFunctions hook
// 2 same hooks, with different position though - enable what you want
// the first one is a "clean" solution, but has its content inserted _before_ the toolbox
//$wgHooks['SkinBuildSidebar'][] = 'RelatedSites::onSkinBuildSidebar';
// the second one is nasty: echo'ing raw html _after_ the regular toolbox
$wgHooks['SkinTemplateToolboxEnd'][] = 'RelatedSites::onSkinTemplateToolboxEnd';

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
