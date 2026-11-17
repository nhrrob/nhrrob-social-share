/**
 * NHR Social Share, the share links in the Posts list: a link with
 * data-nhrrob-social-share-copy puts that text on the clipboard as it opens
 * the network's page, for the pages that cannot be given it in their address
 * (Facebook's text, Medium's link).
 */
( function () {
	document.addEventListener( 'click', function ( event ) {
		var link = event.target.closest ? event.target.closest( 'a[data-nhrrob-social-share-copy]' ) : null;

		if ( ! link || ! navigator.clipboard ) {
			return;
		}

		// Not awaited: the link opens its tab at once, and the copy finishes on this page.
		navigator.clipboard.writeText( link.getAttribute( 'data-nhrrob-social-share-copy' ) ).then( function () {
			if ( ! link.querySelector( '.nhrrob-social-share-copied' ) ) {
				var note = document.createElement( 'span' );

				note.className = 'nhrrob-social-share-copied';
				note.textContent = ' (' + link.getAttribute( 'data-nhrrob-social-share-copied' ) + ')';
				link.appendChild( note );
			}
		} ).catch( function () {} );
	} );
}() );
