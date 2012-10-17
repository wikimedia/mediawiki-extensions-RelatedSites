<?php

if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'This file is a MediaWiki extension, it is not a valid entry point' );
}

require_once( dirname( __FILE__ ) . "/../CustomData/CustomData.php" );

$dir = dirname( __FILE__ ) . '/';
$wgExtensionMessagesFiles['RelatedSites'] = $dir . 'RelatedSites.i18n.php';

$wgExtensionFunctions[] = 'wfSetupRelatedSites';

$wgExtensionCredits['parserhook']['RelatedSites'] = array(
	'path' => __FILE__,
	'name' => 'RelatedSites',
	'url' => 'http://wikivoyage.org/tech/RelatedSites-Extension',
	'author' => array( 'Roland Unger', 'Hans Musil' ),
	'descriptionmsg' => 'relatedsites-desc'
);


class RelatedSites {
	function RelatedSites() {
		# error_log( "Call RelatedSites constructor:" . $this->hmcounter, 0);
	}

	/**
	 * Picks out formal interlanguage links that are actualy related sites.
	 */
	function onParserBeforeTidy( &$parser, &$text ) {
		global $wgCustomData, $wgRelatedSitesPrefixes;

		if ( !$wgRelatedSitesPrefixes ) {
			return true;
		}
		;

		# $wgRelatedSitesPrefixes = array( 'wikipedia', 'wikitravel', 'dmoz');
		$relsiteset = array();

		foreach ( $parser->mOutput->mLanguageLinks as $idx => $ll ) {
			# wfDebug( "ll: $idx => $ll\n");

			$tmp = explode( ':', $ll, 2 );
			if ( in_array( $tmp[0], $wgRelatedSitesPrefixes ) ) {
				unset( $parser->mOutput->mLanguageLinks[$idx] );
				$relsiteset[] = $ll;
			}
		}

		if ( $relsiteset ) {
			$wgCustomData->setParserData( $parser->mOutput, 'RelatedSites', $relsiteset );
		}

		return true;
	}

	/**
	 * Hooked in from hook SkinTemplateOutputPageBeforeExec.
	 * Preprocess related sites links.
	 */
	function onSkinTemplateOutputPageBeforeExec( &$SkTmpl, &$QuickTmpl ) {
		global $wgCustomData, $wgOut;
		global $wgContLang;

		$relatedsites_urls = array();

		#
		# Fill the RelatedSites array.
		#
		$rs = $wgCustomData->getPageData( $wgOut, 'RelatedSites' );
		foreach ( $rs as $l ) {
			$tmp = explode( ':', $l, 2 );
			$class = 'interwiki-' . $tmp[0];
			$nt = Title::newFromText( $l );
			$relatedsites_urls[] = array(
				'href' => $nt->getFullURL(),
				'text' => ( $wgContLang->getLanguageName( $nt->getInterwiki() ) != '' ? $wgContLang->getLanguageName( $nt->getInterwiki() ) : $l ),
				'class' => $class
			);
		}
		$wgCustomData->setSkinData( $QuickTmpl, 'RelatedSites', $relatedsites_urls );

		return true;
	}

	/**
	 * Write out HTML-code.
	 */
	function onSkinTemplateToolboxEnd( &$skTemplate ) {
		global $wgCustomData;

		$rs = $wgCustomData->getSkinData( $skTemplate, 'RelatedSites' );
		if ( $rs ) {
			?>
		</ul>
		</div>
		</div>
		<div id="p-lang" class="portal">
				<h5><?php $skTemplate->msg( 'relatedsites-sidebartext' ) ?></h5>
				<div class="body">
						<ul>
<?php

			foreach ( $rs as $rslink ) {
				?>
				<li class="<?php echo htmlspecialchars( $rslink['class'] )?>"><?php
					?><a href="<?php echo htmlspecialchars( $rslink['href'] )
					?>" <?php if ( $rslink['text'] == 'Wikitravel' ) {
					echo 'rel="nofollow"';
				}
					?>><?php echo $rslink['text'] ?></a></li>
				<?php
			}

		}

		return true;
	}

}

function wfSetupRelatedSites() {
	global $wgParser, $wgHooks, $wgExtraLanguageNames;

	global $wgRelatedSites;
	$wgRelatedSites = new RelatedSites;

	$wgHooks['SkinTemplateToolboxEnd'][] =
		array( &$wgRelatedSites, 'onSkinTemplateToolboxEnd' );
	$wgHooks['SkinTemplateOutputPageBeforeExec'][] =
		array( &$wgRelatedSites, 'onSkinTemplateOutputPageBeforeExec' );
	$wgHooks['ParserBeforeTidy'][] = array( &$wgRelatedSites, 'onParserBeforeTidy' );

	$wgExtraLanguageNames['wikitravel'] = 'Wikitravel';
	$wgExtraLanguageNames['wikipedia'] = 'Wikipedia';
	$wgExtraLanguageNames['WikiPedia'] = 'Wikipedia';
	$wgExtraLanguageNames['citizendium'] = 'Citizendium';
	$wgExtraLanguageNames['dmoz'] = 'Open Directory';
	$wgExtraLanguageNames['Radreise-Wiki'] = 'Radreise-Wiki';
	$wgExtraLanguageNames['rezepte'] = 'Rezepte-Wiki';
	$wgExtraLanguageNames['commons'] = 'Wikimedia Commons';
	$wgExtraLanguageNames['wmc'] = 'Wikimedia Commons';
	$wgExtraLanguageNames['wtp'] = 'Wikitravel Press';

	$wgExtraLanguageNames['shared'] = 'Shared';
	$wgExtraLanguageNames['wts'] = 'Shared';
	$wgExtraLanguageNames['gen'] = 'General';
	$wgExtraLanguageNames['tech'] = 'Technical';
	$wgExtraLanguageNames['assoc'] = 'Association';
	$wgExtraLanguageNames['ldbwiki'] = 'Location Database Wiki';

	return true;
}

