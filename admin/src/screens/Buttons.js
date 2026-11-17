/**
 * Share buttons: which networks a reader sees under a post, and in what order.
 */
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

import { Button, Icon, Panel, ScreenHeader } from '../components/ui';

const P = 'nhrrob-social-share-';

export const BUTTONS = {
	linkedin: __( 'LinkedIn', 'nhrrob-social-share' ),
	x: __( 'X', 'nhrrob-social-share' ),
	reddit: __( 'Reddit', 'nhrrob-social-share' ),
	facebook: __( 'Facebook', 'nhrrob-social-share' ),
	whatsapp: __( 'WhatsApp', 'nhrrob-social-share' ),
	email: __( 'Email', 'nhrrob-social-share' ),
	copy: __( 'Copy link', 'nhrrob-social-share' ),
};

/**
 * Every button, the chosen ones first in their order, then the others.
 *
 * @param {Array} chosen Chosen buttons.
 * @return {Array} Button names.
 */
function ordered( chosen ) {
	return [
		...chosen,
		...Object.keys( BUTTONS ).filter(
			( name ) => ! chosen.includes( name )
		),
	];
}

export default function Buttons( { state, save, saving } ) {
	const [ chosen, setChosen ] = useState( state.settings.buttons );
	const list = ordered( chosen );

	const toggle = ( name ) =>
		setChosen(
			chosen.includes( name )
				? chosen.filter( ( n ) => n !== name )
				: [ ...chosen, name ]
		);
	const move = ( name, step ) => {
		const next = [ ...chosen ];
		const from = next.indexOf( name );
		const to = from + step;
		if ( from < 0 || to < 0 || to >= next.length ) {
			return;
		}
		next.splice( from, 1 );
		next.splice( to, 0, name );
		setChosen( next );
	};

	return (
		<>
			<ScreenHeader
				title={ __( 'Share buttons', 'nhrrob-social-share' ) }
				lede={ __(
					'The buttons under each post. Each one opens the network’s own share page: no script, cookie or counter from any network is loaded, so they track no one.',
					'nhrrob-social-share'
				) }
			/>
			<Panel
				title={ __( 'Buttons and their order', 'nhrrob-social-share' ) }
				icon="buttons"
			>
				<div className={ P + 'checks' }>
					{ list.map( ( name ) => {
						const on = chosen.includes( name );
						return (
							<div
								key={ name }
								className={
									P + 'checks__row' + ( on ? '' : ' is-off' )
								}
							>
								<label htmlFor={ P + 'button-' + name }>
									<input
										id={ P + 'button-' + name }
										type="checkbox"
										checked={ on }
										onChange={ () => toggle( name ) }
									/>
									{ BUTTONS[ name ] }
								</label>
								{ on && (
									<span className={ P + 'checks__move' }>
										<Button
											small
											aria-label={ __(
												'Move up',
												'nhrrob-social-share'
											) }
											disabled={
												chosen.indexOf( name ) === 0
											}
											onClick={ () => move( name, -1 ) }
										>
											<Icon name="up" size={ 14 } />
										</Button>
										<Button
											small
											aria-label={ __(
												'Move down',
												'nhrrob-social-share'
											) }
											disabled={
												chosen.indexOf( name ) ===
												chosen.length - 1
											}
											onClick={ () => move( name, 1 ) }
										>
											<Icon name="down" size={ 14 } />
										</Button>
									</span>
								) }
							</div>
						);
					} ) }
				</div>
				<p className={ P + 'field__help' }>
					{ __(
						'With none ticked, no buttons are shown. “Copy link” appears only in browsers that can copy.',
						'nhrrob-social-share'
					) }
				</p>
				<div className={ P + 'actions' }>
					<Button
						variant="primary"
						disabled={ saving }
						onClick={ () => save( { buttons: chosen } ) }
					>
						{ __( 'Save buttons', 'nhrrob-social-share' ) }
					</Button>
				</div>
			</Panel>
		</>
	);
}
