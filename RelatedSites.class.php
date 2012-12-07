<?php

class RelatedSites {
	/**
	 * @throws MWException
	 * @return CustomData
	 */
	public function getCustomData() {
		global $wgCustomData;

		if ( !$wgCustomData instanceof CustomData ) {
			throw new MWException( 'CustomData extension is not properly installed.' );
		}

		return $wgCustomData;
	}

	/**
	 * After parsing is done, store the $mRelatedSitesSet in $wgCustomData.
	 *
	 * @param Parser $parser
	 * @param string $text
	 * @return bool
	 */
	public function onParserBeforeTidy( Parser &$parser, &$text ) {
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
			$this->getCustomData()->setParserData( $parser->getOutput(), 'RelatedSites', $relatedSitesSet );
		}

		return true;
	}

	/**
	 * Preprocess relatedsites links.
	 *
	 * @param SkinTemplate $skinTpl
	 * @param QuickTemplate $quickTpl
	 * @return bool
	 */
	public function onSkinTemplateOutputPageBeforeExec( SkinTemplate &$skinTpl, &$quickTpl ) {
		global $wgOut;

		$customData = $this->getCustomData();

		// Fill the RelatedSites array.
		$relatedSites = $customData->getPageData( $wgOut, 'RelatedSites' );
		$customData->setSkinData( $quickTpl, 'RelatedSites', $relatedSites );

		return true;
	}

	/**
	 * @param array $relatedSites
	 * @return array
	 */
	protected function getRelatedSitesUrls( array $relatedSites ) {
		$relatedSitesUrls = array();

		foreach ( $relatedSites as $site ) {
			$tmp = explode( ':', $site, 2 );

			$title = Title::newFromText( $site );
			if ( $title ) {
				$relatedSitesUrls[] = array(
					'href' => $title->getFullURL(),
					'text' => Language::fetchLanguageName( $title->getInterwiki() ) ?: $site,
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
	public function onSkinBuildSidebar( $skin, &$bar ) {
		$out = $skin->getOutput();
		$relatedSites = $this->getCustomData()->getParserData( $out, 'RelatedSites' );

		if ( count( $relatedSites ) == 0 ) {
			return true;
		}

		$relatedSitesUrls = $this->getRelatedSitesUrls( $relatedSites );

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
	 * @param SkinTemplate|VectorTemplate $skinTpl
	 * @return bool
	 */
	public function onSkinTemplateToolboxEnd( &$skinTpl ) {
		global $wgSitename;

		$relatedSites = $this->getCustomData()->getSkinData( $skinTpl, 'RelatedSites' );

		if ( count( $relatedSites ) == 0 ) {
			return true;
		}

		$relatedSitesUrls = $this->getRelatedSitesUrls( $relatedSites );

		// build relatedsites <li>'s
		$relatedSites = array();
		foreach ( (array) $relatedSitesUrls as $url ) {
			$attributes = array();
			$attributes['href'] = htmlspecialchars( $url['href'] );

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
			Html::openElement( 'div', array( 'id' => 'p-lang', 'class' => 'portal' ) ) .
			Html::element( 'h5', array(), wfMessage( 'relatedsites-title' )->text() ) .
			Html::openElement( 'div', array( 'class' => 'body' ) ) .
			Html::openElement( 'ul', array( 'class' => 'body' ) ) .
			implode( '', $relatedSites );

		return true;
	}
}
