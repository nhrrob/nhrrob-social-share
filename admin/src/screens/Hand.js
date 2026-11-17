/**
 * Share by hand: the networks whose own page opens from the Posts list with
 * the post filled in, the default Reddit targets, and the rotating layouts.
 */
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

import { Button, Field, Note, Panel, ScreenHeader } from '../components/ui';

const P = 'nhrrob-social-share-';

const NETWORKS = [
	[
		'x',
		__( 'X', 'nhrrob-social-share' ),
		__(
			'Opens the post box with the X text (or the title) and the link. Without X Premium a post can have at most 280 characters, a link counting as 23.',
			'nhrrob-social-share'
		),
	],
	[
		'facebook',
		__( 'Facebook', 'nhrrob-social-share' ),
		__(
			'Facebook lets no link fill in a post’s text, so the click copies the LinkedIn text; paste it in. It shares to your personal profile.',
			'nhrrob-social-share'
		),
	],
	[
		'reddit',
		__( 'Reddit', 'nhrrob-social-share' ),
		__(
			'One link per subreddit or profile below (or the post’s own list), each opening its submit page with the link and title filled in.',
			'nhrrob-social-share'
		),
	],
	[
		'medium',
		__( 'Medium', 'nhrrob-social-share' ),
		__(
			'Opens “Import a story”, which copies the whole post and names this site as the original. The click copies the link to paste in. Works on Medium’s desktop site only.',
			'nhrrob-social-share'
		),
	],
];

export default function Hand( { state, save, saving } ) {
	const [ hand, setHand ] = useState( state.settings.hand );
	const [ reddit, setReddit ] = useState(
		state.settings.reddit.join( ', ' )
	);
	const [ rotate, setRotate ] = useState( state.settings.rotate );

	const toggle = ( name ) =>
		setHand(
			hand.includes( name )
				? hand.filter( ( n ) => n !== name )
				: [ ...hand, name ]
		);

	return (
		<>
			<ScreenHeader
				title={ __( 'Share by hand', 'nhrrob-social-share' ) }
				lede={ __(
					'Where an API costs money, is not allowed or removes links, the Posts list opens the network’s own page with the post filled in, and you press Post. Once used, a link reads “again”; paste where it was posted into the post’s “Shared on” box to link to it.',
					'nhrrob-social-share'
				) }
			/>
			<Panel
				title={ __(
					'Networks in the Posts list',
					'nhrrob-social-share'
				) }
				icon="hand"
			>
				<div className={ P + 'choices' }>
					{ NETWORKS.map( ( [ name, label, help ] ) => (
						<label key={ name } htmlFor={ P + 'hand-' + name }>
							<input
								id={ P + 'hand-' + name }
								type="checkbox"
								checked={ hand.includes( name ) }
								onChange={ () => toggle( name ) }
							/>
							<span>
								{ label }
								<small>{ help }</small>
							</span>
						</label>
					) ) }
				</div>
			</Panel>
			<div className={ P + 'two' }>
				<Panel
					title={ __( 'Reddit targets', 'nhrrob-social-share' ) }
					icon="hand"
				>
					<Field
						id={ P + 'reddit' }
						label={ __(
							'Subreddits and profiles for every post',
							'nhrrob-social-share'
						) }
						help={ __(
							'Comma separated: r/Wordpress for a subreddit, u/yourname for your own profile. A post can name its own in the editor. Empty: one link to Reddit’s submit page, where you pick.',
							'nhrrob-social-share'
						) }
					>
						<input
							id={ P + 'reddit' }
							className={ P + 'input is-mono' }
							value={ reddit }
							placeholder="u/yourname, r/Wordpress"
							onChange={ ( e ) => setReddit( e.target.value ) }
						/>
					</Field>
					<Note>
						{ __(
							'Most subreddits remove links from accounts that only post their own. A profile post reaches your followers.',
							'nhrrob-social-share'
						) }
					</Note>
				</Panel>
				<Panel
					title={ __( 'Layouts', 'nhrrob-social-share' ) }
					icon="results"
				>
					<div className={ P + 'choices' }>
						<label htmlFor={ P + 'rotate' }>
							<input
								id={ P + 'rotate' }
								type="checkbox"
								checked={ rotate }
								onChange={ () => setRotate( ! rotate ) }
							/>
							<span>
								{ __(
									'Take turns between three layouts',
									'nhrrob-social-share'
								) }
								<small>
									{ __(
										'On LinkedIn, X and Facebook: A link card; B picture, link in the text; C picture, link in the first comment (on X, in a reply). Results shows which brings readers. Off: always A.',
										'nhrrob-social-share'
									) }
								</small>
							</span>
						</label>
					</div>
				</Panel>
			</div>
			<div className={ P + 'actions' }>
				<Button
					variant="primary"
					disabled={ saving }
					onClick={ () =>
						save( {
							hand,
							reddit: reddit
								.split( ',' )
								.map( ( s ) => s.trim() )
								.filter( Boolean ),
							rotate,
						} ).then( ( next ) =>
							next
								? setReddit( next.settings.reddit.join( ', ' ) )
								: null
						)
					}
				>
					{ __( 'Save', 'nhrrob-social-share' ) }
				</Button>
			</div>
		</>
	);
}
