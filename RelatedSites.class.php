<?php
/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2 as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 */

class RelatedSites {

	/**
	 * After parsing is done, store the related sites set in extension data.
	 *
	 * @param Parser &$parser
	 * @param string &$text
	 */
	public static function onParserAfterTidy( Parser &$parser, &$text ) {
		global $wgRelatedSitesPrefixes;

		if ( !$wgRelatedSitesPrefixes ) {
			return;
		}

		$po = $parser->getOutput();
		$relatedSitesSet = $po->getExtensionData( 'RelatedSites' ) ?: [];
		foreach ( $po->getLanguageLinks() as $i => $languageLink ) {
			$tmp = explode( ':', $languageLink, 2 );
			if ( in_array( $tmp[0], $wgRelatedSitesPrefixes ) ) {
				unset( $po->mLanguageLinks[$i] );
				$relatedSitesSet[] = $languageLink;
			}
		}

		if ( $relatedSitesSet ) {
			$po->setExtensionData( 'RelatedSites', $relatedSitesSet );
			$parser->addTrackingCategory( 'relatedsites-tracking-category' );
		}
	}

	/**
	 * @param OutputPage &$out
	 * @param ParserOutput $parserOutput
	 */
	public static function onOutputPageParserOutput( OutputPage &$out, ParserOutput $parserOutput ) {
		$related = $parserOutput->getExtensionData( 'RelatedSites' );

		if ( $related ) {
			$out->setProperty( 'RelatedSites', $related );
		}
	}

	/**
	 * @param array $relatedSites
	 * @return array
	 */
	protected static function getRelatedSitesUrls( array $relatedSites ) {
		$relatedSitesUrls = [];

		foreach ( $relatedSites as $site ) {
			$tmp = explode( ':', $site, 2 );

			$title = Title::newFromText( $site );
			if ( $title ) {
				// HACK: DMOZ is dead, don't display links to it
				if ( $title->getInterwiki() === 'dmoz' ) {
					continue;
				}

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

				$relatedSitesUrls[] = [
					'href' => $title->getFullURL(),
					'text' => $linkText,
					'title' => $linkTitle,
					'class' => 'interwiki-' . $tmp[0]
				];
			}
		}

		return $relatedSitesUrls;
	}

	/**
	 * Write out HTML code.
	 *
	 * @param Skin $skin
	 * @param array &$bar
	 */
	public static function onSidebarBeforeOutput( $skin, &$bar ) {
		$relatedSites = $skin->getOutput()->getProperty( 'RelatedSites' ) ?: [];

		$relatedSitesUrls = self::getRelatedSitesUrls( $relatedSites );

		if ( !$relatedSitesUrls ) {
			return;
		}

		// build relatedsites <li>'s
		$relatedSites = [];
		foreach ( (array)$relatedSitesUrls as $url ) {
			$relatedSites[] =
				Html::rawElement( 'li', [ 'class' => $url['class'] ],
					Html::rawElement( 'a', [ 'href' => $url['href'] ],
						$url['text']
					)
				);
		}

		// build complete HTML
		$bar[$skin->msg( 'relatedsites-title' )->text()] =
			Html::rawElement( 'ul', [],
				implode( '', $relatedSites )
			);
	}
}
