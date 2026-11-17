/**
 * NHR Social Share — admin app entry.
 *
 * React and every @wordpress/* package are provided by WordPress and stay out
 * of the bundle.
 */
import { createRoot } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

import App from './app';
import './style.scss';

const boot = window.nhrrobSocialShareApp || {};
if ( boot.nonce ) {
	apiFetch.use( apiFetch.createNonceMiddleware( boot.nonce ) );
}
if ( boot.restRoot ) {
	apiFetch.use( apiFetch.createRootURLMiddleware( boot.restRoot ) );
}

document.addEventListener( 'DOMContentLoaded', () => {
	const el = document.getElementById( 'nhrrob-social-share-app' );
	if ( el ) {
		createRoot( el ).render( <App boot={ boot } /> );
	}
} );
