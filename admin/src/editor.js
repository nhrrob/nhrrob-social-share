/**
 * NHR Social Share — the panel in the block editor's sidebar: the texts and
 * the Reddit targets this post is shared with.
 */
import { registerPlugin } from '@wordpress/plugins';
import {
	TextareaControl,
	TextControl,
	SelectControl,
} from '@wordpress/components';
import { useState } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';
import {
	PluginDocumentSettingPanel,
	store as editorStore,
} from '@wordpress/editor';
import { __, sprintf } from '@wordpress/i18n';

const boot = window.nhrrobSocialShareEditor || { keys: {} };
const { keys } = boot;

/**
 * Characters as X counts them: a link is always 23.
 *
 * @param {string} text Text.
 * @return {number} Length.
 */
function xLength( text ) {
	return Array.from(
		( text || '' ).replace( /https?:\/\/\S+/g, 'x'.repeat( 23 ) )
	).length;
}

function Panel() {
	const meta = useSelect(
		( select ) =>
			select( editorStore ).getEditedPostAttribute( 'meta' ) || {},
		[]
	);
	const { editPost } = useDispatch( editorStore );
	const set = ( key, value ) => editPost( { meta: { [ key ]: value } } );
	// The field keeps what is typed; the saved list drops the empty pieces.
	const [ reddit, setReddit ] = useState(
		( meta[ keys.reddit ] || [] ).join( ', ' )
	);

	const xText = meta[ keys.xText ] || '';
	// Without its own link, X adds the post's link, which counts 23.
	const used = xLength( xText ) + ( /https?:\/\//.test( xText ) ? 0 : 24 );

	const layouts = [
		{
			value: '',
			label: __( 'Take turns (default)', 'nhrrob-social-share' ),
		},
		{ value: 'a', label: __( 'A: link card', 'nhrrob-social-share' ) },
		{
			value: 'b',
			label: __( 'B: picture, link in the text', 'nhrrob-social-share' ),
		},
		{
			value: 'c',
			label: __(
				'C: picture, link in the first comment',
				'nhrrob-social-share'
			),
		},
	];

	return (
		<PluginDocumentSettingPanel
			name="nhrrob-social-share"
			title={ __( 'Social Share', 'nhrrob-social-share' ) }
		>
			<div
				style={ { display: 'flex', flexDirection: 'column', gap: 16 } }
			>
				<TextareaControl
					__nextHasNoMarginBottom
					label={ __( 'LinkedIn text', 'nhrrob-social-share' ) }
					help={ __(
						'Empty: the title and the excerpt. A link of its own is posted as written.',
						'nhrrob-social-share'
					) }
					rows={ 6 }
					value={ meta[ keys.linkedinText ] || '' }
					onChange={ ( value ) => set( keys.linkedinText, value ) }
				/>
				<TextareaControl
					__nextHasNoMarginBottom
					label={ __( 'Facebook text', 'nhrrob-social-share' ) }
					help={ __(
						'Empty: the LinkedIn text. Write one for a different audience or language.',
						'nhrrob-social-share'
					) }
					rows={ 4 }
					value={ meta[ keys.facebookText ] || '' }
					onChange={ ( value ) => set( keys.facebookText, value ) }
				/>
				<TextareaControl
					__nextHasNoMarginBottom
					label={ __( 'X text', 'nhrrob-social-share' ) }
					help={ sprintf(
						/* translators: %d: characters used of 280 */
						__(
							'%d of 280 characters, with the link. Empty: the title.',
							'nhrrob-social-share'
						),
						xText ? used : 0
					) }
					rows={ 3 }
					value={ xText }
					onChange={ ( value ) => set( keys.xText, value ) }
				/>
				<TextControl
					__nextHasNoMarginBottom
					__next40pxDefaultSize
					label={ __( 'Reddit', 'nhrrob-social-share' ) }
					help={
						boot.reddit && boot.reddit.length
							? sprintf(
									/* translators: %s: default Reddit targets */
									__(
										'Comma separated, r/name or u/name. Empty: %s.',
										'nhrrob-social-share'
									),
									boot.reddit.join( ', ' )
							  )
							: __(
									'Comma separated, r/name or u/name. Empty: Reddit’s submit page.',
									'nhrrob-social-share'
							  )
					}
					value={ reddit }
					onChange={ ( value ) => {
						setReddit( value );
						set(
							keys.reddit,
							value
								.split( ',' )
								.map( ( name ) => name.trim() )
								.filter( Boolean )
						);
					} }
				/>
				{ boot.rotate && (
					<SelectControl
						__nextHasNoMarginBottom
						__next40pxDefaultSize
						label={ __( 'LinkedIn layout', 'nhrrob-social-share' ) }
						value={ meta[ keys.linkedinLayout ] || '' }
						options={ layouts }
						onChange={ ( value ) =>
							set( keys.linkedinLayout, value )
						}
					/>
				) }
			</div>
		</PluginDocumentSettingPanel>
	);
}

registerPlugin( 'nhrrob-social-share', { render: Panel } );
