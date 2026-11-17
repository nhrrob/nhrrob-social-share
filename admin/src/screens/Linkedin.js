/**
 * LinkedIn: the site's own LinkedIn app, the connection, and whether posts
 * are shared by themselves or from the Posts list.
 */
import { useState } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';

import {
	Button,
	External,
	Field,
	Note,
	Panel,
	Pill,
	ScreenHeader,
} from '../components/ui';
import { useToast } from '../components/ToastProvider';

const P = 'nhrrob-social-share-';

export default function Linkedin( { state, save, saving, boot } ) {
	const { linkedin } = state;
	const [ mode, setMode ] = useState( linkedin.mode );
	const [ clientId, setClientId ] = useState( linkedin.client_id );
	const [ secret, setSecret ] = useState( '' );
	const toast = useToast();

	let status = (
		<Pill tone="off">{ __( 'Not connected', 'nhrrob-social-share' ) }</Pill>
	);
	if ( linkedin.connected ) {
		status = (
			<Pill tone={ linkedin.expiring ? 'warn' : 'ok' }>
				{ linkedin.expiring
					? __( 'Expires soon', 'nhrrob-social-share' )
					: __( 'Connected', 'nhrrob-social-share' ) }
			</Pill>
		);
	} else if ( linkedin.expires ) {
		status = (
			<Pill tone="bad">{ __( 'Expired', 'nhrrob-social-share' ) }</Pill>
		);
	}

	const copy = () =>
		window.navigator.clipboard &&
		window.navigator.clipboard
			.writeText( linkedin.redirect )
			.then( () => toast( __( 'Copied.', 'nhrrob-social-share' ) ) );

	return (
		<>
			<ScreenHeader
				title={ __( 'LinkedIn', 'nhrrob-social-share' ) }
				lede={ __(
					'Shares a post to your LinkedIn profile through your own LinkedIn app. The keys and the token stay on this site; nothing passes through another service.',
					'nhrrob-social-share'
				) }
			/>
			<div className={ P + 'two' }>
				<Panel
					title={ __( 'Connection', 'nhrrob-social-share' ) }
					icon="linkedin"
					meta={ status }
				>
					{ linkedin.connected ? (
						<p>
							{ sprintf(
								/* translators: 1: LinkedIn member name, 2: date */
								__(
									'Connected as %1$s. LinkedIn ends the connection on %2$s; reconnect before then.',
									'nhrrob-social-share'
								),
								linkedin.name,
								linkedin.expires
							) }
						</p>
					) : (
						<p>
							{ linkedin.client_id && linkedin.secret
								? __(
										'The app is saved. Connect to let it share as you.',
										'nhrrob-social-share'
								  )
								: __(
										'Save the Client ID and Client Secret first, then connect.',
										'nhrrob-social-share'
								  ) }
						</p>
					) }
					{ 'production' !== linkedin.environment &&
						'auto' === mode && (
							<Note tone="warn">
								{ sprintf(
									/* translators: %s: environment type, such as staging */
									__(
										'This site’s environment type is “%s”, so posts are not shared by themselves here. Only a production site shares automatically.',
										'nhrrob-social-share'
									),
									linkedin.environment
								) }
							</Note>
						) }
					<div className={ P + 'actions' }>
						{ linkedin.client_id && linkedin.secret && (
							<a
								className={ P + 'btn ' + P + 'btn--primary' }
								href={ boot.connect }
							>
								{ linkedin.connected || linkedin.expires
									? __(
											'Reconnect LinkedIn',
											'nhrrob-social-share'
									  )
									: __(
											'Connect LinkedIn',
											'nhrrob-social-share'
									  ) }
							</a>
						) }
						{ linkedin.connected && (
							<a
								className={ P + 'btn ' + P + 'btn--danger' }
								href={ boot.disconnect }
							>
								{ __( 'Disconnect', 'nhrrob-social-share' ) }
							</a>
						) }
					</div>
				</Panel>
				<Panel
					title={ __( 'Sharing', 'nhrrob-social-share' ) }
					icon="share"
				>
					<div className={ P + 'choices' }>
						<label htmlFor={ P + 'mode-auto' }>
							<input
								id={ P + 'mode-auto' }
								type="radio"
								name="mode"
								checked={ 'auto' === mode }
								onChange={ () => setMode( 'auto' ) }
							/>
							<span>
								{ __( 'Automatic', 'nhrrob-social-share' ) }
								<small>
									{ __(
										'Share each post the first time it is published, scheduled posts included.',
										'nhrrob-social-share'
									) }
								</small>
							</span>
						</label>
						<label htmlFor={ P + 'mode-manual' }>
							<input
								id={ P + 'mode-manual' }
								type="radio"
								name="mode"
								checked={ 'manual' === mode }
								onChange={ () => setMode( 'manual' ) }
							/>
							<span>
								{ __( 'Manual', 'nhrrob-social-share' ) }
								<small>
									{ __(
										'Share with “Share on LinkedIn” under a post in the Posts list.',
										'nhrrob-social-share'
									) }
								</small>
							</span>
						</label>
					</div>
					<p className={ P + 'field__help' }>
						{ __(
							'A post is shared once. Its text is the LinkedIn text from the post’s Social Share panel, or its title and excerpt.',
							'nhrrob-social-share'
						) }
					</p>
				</Panel>
			</div>
			<Panel
				title={ __( 'Your LinkedIn app', 'nhrrob-social-share' ) }
				icon="settings"
			>
				<ol className={ P + 'steps' }>
					<li>
						<External href="https://www.linkedin.com/developers/apps/new">
							{ __(
								'Create an app on LinkedIn Developers',
								'nhrrob-social-share'
							) }
						</External>{ ' ' }
						{ __(
							'(it has to be linked to a LinkedIn Page).',
							'nhrrob-social-share'
						) }
					</li>
					<li>
						{ __(
							'On its Products tab, add “Share on LinkedIn” and “Sign In with LinkedIn using OpenID Connect”.',
							'nhrrob-social-share'
						) }
					</li>
					<li>
						{ __(
							'On its Auth tab, add this redirect URL:',
							'nhrrob-social-share'
						) }
						<div className={ P + 'copyrow' }>
							<code>{ linkedin.redirect }</code>
							<Button small onClick={ copy }>
								{ __( 'Copy', 'nhrrob-social-share' ) }
							</Button>
						</div>
					</li>
					<li>
						{ __(
							'Copy the Client ID and Client Secret here, save, then connect.',
							'nhrrob-social-share'
						) }
					</li>
				</ol>
				<Field
					id={ P + 'client-id' }
					label={ __( 'Client ID', 'nhrrob-social-share' ) }
				>
					<input
						id={ P + 'client-id' }
						className={ P + 'input is-mono' }
						value={ clientId }
						autoComplete="off"
						onChange={ ( e ) => setClientId( e.target.value ) }
					/>
				</Field>
				<Field
					id={ P + 'client-secret' }
					label={ __( 'Client Secret', 'nhrrob-social-share' ) }
					help={
						linkedin.secret
							? __(
									'Saved. Leave empty to keep it; it is never shown again.',
									'nhrrob-social-share'
							  )
							: ''
					}
				>
					<input
						id={ P + 'client-secret' }
						type="password"
						className={ P + 'input is-mono' }
						value={ secret }
						autoComplete="new-password"
						onChange={ ( e ) => setSecret( e.target.value ) }
					/>
				</Field>
				<div className={ P + 'actions' }>
					<Button
						variant="primary"
						disabled={ saving }
						onClick={ () =>
							save( {
								linkedin: {
									mode,
									client_id: clientId,
									client_secret: secret,
								},
							} ).then( () => setSecret( '' ) )
						}
					>
						{ __(
							'Save LinkedIn settings',
							'nhrrob-social-share'
						) }
					</Button>
				</div>
			</Panel>
		</>
	);
}
