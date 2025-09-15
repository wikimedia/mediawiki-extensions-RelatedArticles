/* eslint-disable indent, quotes */
// eslint-disable-next-line spaced-comment
/// <reference path="./codex.ts" />

/**
 * @param {Object} options
 * @param {string} options.heading
 * @param {boolean} options.isContainerSmall
 * @param {Codex.ListTitleObject[]} options.cards
 * @param {string} [options.clickEventName]
 * @return {string}
 */
const RelatedArticles = ( options ) => [
		`<div class="read-more-container ${ ( options.isContainerSmall ) ? 'read-more-container-small' : 'read-more-container-large' }">`,
			`<aside class="noprint">`,
				( options.heading ) ?
				`<h2 class="read-more-container-heading">${ mw.html.escape( options.heading ) }</h2>` : ``,
				`<ul class="read-more-container-card-list">`,
					options.cards.map( ( card ) => `<li title="${ mw.html.escape( card.label ) }">
					<a href="${ mw.html.escape( card.url ) }" ${ options.clickEventName ? `data-event-name="${ mw.html.escape( options.clickEventName ) }"` : '' }><span class="cdx-card">
						<span class="cdx-card__thumbnail cdx-thumbnail">
						${ ( card.thumbnail && card.thumbnail.url ) ?
							`<span class="cdx-thumbnail__image" style="background-image: url('${ mw.html.escape( card.thumbnail.url ) }')"></span>` :
							`<span class="cdx-thumbnail__placeholder">
								<span class="cdx-thumbnail__placeholder__icon"></span>
							</span>` }
						</span>
						<span class="cdx-card__text">
							<span class="cdx-card__text__title">${ mw.html.escape( card.label ) }</span>
							<span class="cdx-card__text__description">${ mw.html.escape( card.description ) }</span>
						</span>
					</a>
				</li>` ).join( '\n' ),
				`</ul>`,
			`</aside>`,
		`</div>`
	].join( '\n' );

module.exports = RelatedArticles;
