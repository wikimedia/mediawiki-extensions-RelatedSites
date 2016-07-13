<?php

class RelatedSites {

	/**
	 * After parsing is done, store the related sites set in extension data.
	 *
	 * @param Parser $parser
	 * @param string $text
	 * @return bool
	 */
	public static function onParserBeforeTidy( Parser &$parser, &$text ) {
		global $wgRelatedSitesPrefixes;

		if ( !$wgRelatedSitesPrefixes ) {
			return true;
		}

		$relatedSitesSet = array();

		foreach ( $parser->getOutput()->getLanguageLinks() as $i => $languageLink ) {
			$tmp = explode( ':', $languageLink, 2 );

			if ( in_array( $tmp[0], $wgRelatedSitesPrefixes ) ) {
				unset( $parser->getOutput()->mLanguageLinks[$i] );
				$relatedSitesSet[] = $languageLink;
			}
		}

		if ( $relatedSitesSet ) {
			$parser->getOutput()->setExtensionData( 'RelatedSites', $relatedSitesSet );
		}

		return true;
	}

	/**
	 * @param OutputPage $out
	 * @param ParserOutput $parserOutput
	 * @return bool
	 */
	public static function onOutputPageParserOutput( OutputPage &$out, ParserOutput $parserOutput ) {
		$related = $parserOutput->getExtensionData( 'RelatedSites' );

		if ( $related ) {
			$out->setProperty( 'RelatedSites', $related );
		} elseif ( isset( $parserOutput->mCustomData['RelatedSites'] ) ) {
			// back-compat: Check for CustomData stuff
			$out->setProperty( 'RelatedSites', $parserOutput->mCustomData['RelatedSites'] );
		}

		return true;
	}


	/**
	 * @param array $relatedSites
	 * @return array
	 */
	protected static function getRelatedSitesUrls( array $relatedSites ) {
		$relatedSitesUrls = array();

		foreach ( $relatedSites as $site ) {
			$tmp = explode( ':', $site, 2 );

			$title = Title::newFromText( $site );
			if ( $title ) {
				// Use the same system message keys as the core $wgExtraInterlanguageLinkPrefixes feature
				$linkTextMsg = wfMessage( 'interlanguage-link-' . $title->getInterwiki() );
				$linkText = $linkTextMsg->isDisabled() ?
					( Language::fetchLanguageName( $title->getInterwiki() ) ?: $site ) :
					$linkTextMsg->text();

				// This logic is essentially copied from core SkinTemplate#getLanguages
				$linkTitle = null;
				$linkTitleMsg = wfMessage( 'interlanguage-link-sitename-' . $title->getInterwiki() );
				if ( !$linkTitleMsg->isDisabled() ) {
					if ( $title->getText() === '' ) {
						$linkTitle = wfMessage(
							'interlanguage-link-title-nonlangonly',
							$linkTitleMsg->text()
						)->text();
					} else {
						$linkTitle = wfMessage(
							'interlanguage-link-title-nonlang',
							$title->getText(),
							$linkTitleMsg->text()
						)->text();
					}
				}

				$relatedSitesUrls[] = array(
					'href' => $title->getFullURL(),
					'text' => $linkText,
					'title' => $linkTitle,
					'class' => 'interwiki-' . $tmp[0]
				);
			}
		}

		return $relatedSitesUrls;
	}

	/**
	 * Write out HTML-code.
	 *
	 * @param Skin $skin
	 * @param array $bar
	 * @return bool
	 */
	public static function onSkinBuildSidebar( $skin, &$bar ) {
		$relatedSites = $skin->getOutput()->getProperty( 'RelatedSites' );

		if ( !$relatedSites ) {
			return true;
		}

		$relatedSitesUrls = self::getRelatedSitesUrls( $relatedSites );

		// build relatedsites <li>'s
		$relatedSites = array();
		foreach ( (array) $relatedSitesUrls as $url ) {
			$relatedSites[] =
				Html::rawElement( 'li', array( 'class' => htmlspecialchars( $url['class'] ) ),
					Html::rawElement( 'a', array( 'href' => htmlspecialchars( $url['href'] ) ),
						$url['text']
					)
				);
		}

		// build complete html
		$bar[$skin->msg( 'relatedsites-title' )->text()] =
			Html::rawElement( 'ul', array(),
				implode( '', $relatedSites )
			);

		return true;
	}

	/**
	 * Write out HTML-code.
	 *
	 * @param SkinTemplate $skinTpl
	 * @return bool
	 */
	public static function onSkinTemplateToolboxEnd( &$skinTpl ) {
		global $wgSitename;

		$relatedSites = $skinTpl->getOutput()->getProperty( 'RelatedSites' );

		if ( !$relatedSites ) {
			return true;
		}

		$relatedSitesUrls = self::getRelatedSitesUrls( $relatedSites );

		// build relatedsites <li>'s
		$relatedSites = array();
		foreach ( (array) $relatedSitesUrls as $url ) {
			$attributes = array();
			$attributes['href'] = htmlspecialchars( $url['href'] );
			if ( !empty( $url['title'] ) ) {
				$attributes['title'] = htmlspecialchars( $url['title'] );
			}

			if ( $url['text'] == $wgSitename ) {
				$attributes['rel'] = 'nofollow';
			}

			$relatedSites[] =
				Html::rawElement( 'li', array( 'class' => htmlspecialchars( $url['class'] ) ),
					Html::rawElement( 'a', $attributes,
						$url['text']
					)
				);
		}

		// build complete html
		echo
			Html::closeElement( 'ul' ) .
			Html::closeElement( 'div' ) .
			Html::closeElement( 'div' ) .
			Html::openElement( 'div', array( 'id' => 'p-relatedsites', 'class' => 'portal' ) ) .
			Html::element( 'h3', array(), wfMessage( 'relatedsites-title' )->text() ) .
			Html::openElement( 'div', array( 'class' => 'body' ) ) .
			Html::openElement( 'ul' ) .
			implode( '', $relatedSites );

		return true;
	}
}
