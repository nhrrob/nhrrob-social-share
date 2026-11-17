/**
 * Root component: the app shell, one screen per tab, and the settings they
 * share. Each screen saves what it changes and gets back what is saved.
 */
import { useState, useEffect } from '@wordpress/element';
import { applyFilters } from '@wordpress/hooks';
import { __ } from '@wordpress/i18n';

import api from './api';
import AppShell from './components/AppShell';
import { ToastProvider, useToast } from './components/ToastProvider';
import * as ui from './components/ui';
import Buttons from './screens/Buttons';
import Previews from './screens/Previews';
import Linkedin from './screens/Linkedin';
import Hand from './screens/Hand';
import Results from './screens/Results';

const { Loading, Note } = ui;

const SCREENS = {
	buttons: Buttons,
	previews: Previews,
	linkedin: Linkedin,
	hand: Hand,
	results: Results,
};

/**
 * Screens an add-on adds after the plugin's own, through the JS filter
 * `nhrrob_social_share.screens`: a list of { id, label, Screen }. The second
 * argument hands the add-on the app's building blocks, so its screens look
 * the same. Read once, at the first render, after every script has loaded.
 *
 * @return {Array} Added screens.
 */
function addedScreens() {
	const added = applyFilters( 'nhrrob_social_share.screens', [], {
		ui,
		useToast,
	} );

	return Array.isArray( added )
		? added.filter( ( s ) => s && s.id && s.Screen && ! SCREENS[ s.id ] )
		: [];
}

/**
 * The tab in the address, so a link (and LinkedIn's way back) can open one.
 *
 * @param {Object} screens Tab id => screen.
 * @return {string} Tab id.
 */
function fromAddress( screens ) {
	const tab = new URLSearchParams( window.location.search ).get( 'tab' );

	return screens[ tab ] ? tab : 'buttons';
}

/**
 * What a LinkedIn connect or disconnect left in the address, as a toast.
 *
 * @return {Array|null} [ message, type ] or null.
 */
function resultFromAddress() {
	const code = new URLSearchParams( window.location.search ).get(
		'nhrrob_social_share'
	);
	const messages = {
		connected: [ __( 'LinkedIn connected.', 'nhrrob-social-share' ) ],
		disconnected: [ __( 'LinkedIn disconnected.', 'nhrrob-social-share' ) ],
		denied: [
			__(
				'LinkedIn did not authorize the connection.',
				'nhrrob-social-share'
			),
			'error',
		],
		error: [
			__(
				'Could not connect to LinkedIn. Check the Client ID, Client Secret and redirect URL.',
				'nhrrob-social-share'
			),
			'error',
		],
	};

	return messages[ code ] || null;
}

function Screens( { boot } ) {
	const [ added ] = useState( addedScreens );
	const [ screens ] = useState( () => ( {
		...SCREENS,
		...Object.fromEntries( added.map( ( s ) => [ s.id, s.Screen ] ) ),
	} ) );
	const [ active, setActive ] = useState( () => fromAddress( screens ) );
	const [ state, setState ] = useState( null );
	const [ failed, setFailed ] = useState( '' );
	const [ saving, setSaving ] = useState( false );
	const toast = useToast();

	useEffect( () => {
		api( '/settings' )
			.then( setState )
			.catch( ( e ) => setFailed( e.message || String( e ) ) );

		const result = resultFromAddress();
		if ( result ) {
			toast( result[ 0 ], result[ 1 ] );
			const url = new URL( window.location.href );
			url.searchParams.delete( 'nhrrob_social_share' );
			window.history.replaceState( null, '', url );
		}
	}, [ toast ] );

	useEffect( () => {
		const url = new URL( window.location.href );
		url.searchParams.set( 'tab', active );
		window.history.replaceState( null, '', url );
	}, [ active ] );

	const save = ( patch, message ) => {
		setSaving( true );
		return api( '/settings', 'POST', patch )
			.then( ( next ) => {
				setState( next );
				toast(
					message || __( 'Settings saved.', 'nhrrob-social-share' )
				);
				return next;
			} )
			.catch( ( e ) => toast( e.message || String( e ), 'error' ) )
			.finally( () => setSaving( false ) );
	};

	const modules = [
		{ id: 'buttons', label: __( 'Share buttons', 'nhrrob-social-share' ) },
		{ id: 'previews', label: __( 'Link previews', 'nhrrob-social-share' ) },
		{ id: 'linkedin', label: __( 'LinkedIn', 'nhrrob-social-share' ) },
		{ id: 'hand', label: __( 'Share by hand', 'nhrrob-social-share' ) },
		{ id: 'results', label: __( 'Results', 'nhrrob-social-share' ) },
		...added.map( ( s ) => ( { id: s.id, label: s.label } ) ),
	];
	const Screen = screens[ active ];

	let content = <Loading />;
	if ( failed ) {
		content = <Note tone="warn">{ failed }</Note>;
	} else if ( state ) {
		content = (
			<Screen
				state={ state }
				save={ save }
				saving={ saving }
				boot={ boot }
			/>
		);
	}

	return (
		<AppShell
			modules={ modules }
			active={ active }
			onNavigate={ setActive }
			title={
				boot.pluginName || __( 'Social Share', 'nhrrob-social-share' )
			}
		>
			{ content }
		</AppShell>
	);
}

export default function App( { boot } ) {
	return (
		<ToastProvider>
			<Screens boot={ boot } />
		</ToastProvider>
	);
}
