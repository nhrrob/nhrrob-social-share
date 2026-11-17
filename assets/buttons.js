/**
 * NHR Social Share: shows the "Copy link" button under a post and copies the
 * post's address when it is pressed.
 */
document.querySelectorAll( '.nhrrob-social-share button[data-url]' ).forEach( function ( button ) {
	var text = button.querySelector( 'span' );
	var label = text.textContent;

	if ( ! navigator.clipboard ) {
		return;
	}

	button.hidden = false;

	button.addEventListener( 'click', function () {
		navigator.clipboard.writeText( button.dataset.url ).then( function () {
			text.textContent = button.dataset.copied;

			setTimeout( function () {
				text.textContent = label;
			}, 2000 );
		} );
	} );
} );
