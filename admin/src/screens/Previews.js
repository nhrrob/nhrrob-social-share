/**
 * Link previews: the Open Graph and X card tags, and the colours of the card
 * drawn for posts without a featured image.
 */
import { useState } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';

import { Button, Note, Panel, Pill, ScreenHeader } from '../components/ui';

const P = 'nhrrob-social-share-';

const COLORS = {
	background: __( 'Background', 'nhrrob-social-share' ),
	text: __( 'Title', 'nhrrob-social-share' ),
	muted: __( 'Address', 'nhrrob-social-share' ),
	accent: __( 'Label', 'nhrrob-social-share' ),
	dot: __( 'Dot', 'nhrrob-social-share' ),
};

export default function Previews( { state, save, saving } ) {
	const [ mode, setMode ] = useState( state.settings.previews );
	const [ card, setCard ] = useState( state.settings.card );
	const { site } = state;

	const choices = [
		[
			'auto',
			__( 'Automatic', 'nhrrob-social-share' ),
			__(
				'Print the tags unless an SEO plugin already does.',
				'nhrrob-social-share'
			),
		],
		[
			'on',
			__( 'Always', 'nhrrob-social-share' ),
			__(
				'Print them even next to an SEO plugin (pages then carry two sets).',
				'nhrrob-social-share'
			),
		],
		[
			'off',
			__( 'Never', 'nhrrob-social-share' ),
			__(
				'Leave the tags to the theme or another plugin.',
				'nhrrob-social-share'
			),
		],
	];

	return (
		<>
			<ScreenHeader
				title={ __( 'Link previews', 'nhrrob-social-share' ) }
				lede={ __(
					'What a shared link shows: the title, the excerpt and a picture. A post without a featured image gets a card drawn from its title.',
					'nhrrob-social-share'
				) }
			/>
			<div className={ P + 'two' }>
				<Panel
					title={ __( 'Preview tags', 'nhrrob-social-share' ) }
					icon="previews"
					meta={
						<Pill tone={ site.previews ? 'ok' : 'off' }>
							{ site.previews
								? __( 'Printed', 'nhrrob-social-share' )
								: __( 'Not printed', 'nhrrob-social-share' ) }
						</Pill>
					}
				>
					{ site.seo && (
						<Note>
							{ sprintf(
								/* translators: %s: name of an SEO plugin */
								__(
									'%s is active and prints its own tags.',
									'nhrrob-social-share'
								),
								site.seo
							) }
						</Note>
					) }
					<div className={ P + 'choices' }>
						{ choices.map( ( [ value, label, help ] ) => (
							<label
								key={ value }
								htmlFor={ P + 'previews-' + value }
							>
								<input
									id={ P + 'previews-' + value }
									type="radio"
									name="previews"
									checked={ mode === value }
									onChange={ () => setMode( value ) }
								/>
								<span>
									{ label }
									<small>{ help }</small>
								</span>
							</label>
						) ) }
					</div>
				</Panel>
				<Panel
					title={ __( 'The drawn card', 'nhrrob-social-share' ) }
					icon="previews"
				>
					{ ! site.card && (
						<Note tone="warn">
							{ __(
								'This server’s PHP has no FreeType in its GD library, so no card can be drawn. Posts without a featured image then show the site icon.',
								'nhrrob-social-share'
							) }
						</Note>
					) }
					{ site.preview && (
						<img
							className={ P + 'card' }
							src={ site.preview }
							alt={ __(
								'The card of the latest post',
								'nhrrob-social-share'
							) }
						/>
					) }
					<p className={ P + 'field__help' }>
						{ __(
							'The card of your latest post. It is drawn again with the new colours after saving.',
							'nhrrob-social-share'
						) }
					</p>
				</Panel>
			</div>
			<Panel
				title={ __( 'Card colours', 'nhrrob-social-share' ) }
				icon="previews"
			>
				<div className={ P + 'swatches' }>
					{ Object.keys( COLORS ).map( ( key ) => (
						<label key={ key } htmlFor={ P + 'color-' + key }>
							<input
								id={ P + 'color-' + key }
								type="color"
								value={ card[ key ] }
								onChange={ ( e ) =>
									setCard( {
										...card,
										[ key ]: e.target.value.toUpperCase(),
									} )
								}
							/>
							{ COLORS[ key ] }
							<code>{ card[ key ] }</code>
						</label>
					) ) }
				</div>
				<div className={ P + 'actions' }>
					<Button
						variant="primary"
						disabled={ saving }
						onClick={ () => save( { previews: mode, card } ) }
					>
						{ __( 'Save previews', 'nhrrob-social-share' ) }
					</Button>
				</div>
			</Panel>
		</>
	);
}
