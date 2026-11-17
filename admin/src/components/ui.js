/**
 * Shared building blocks: icons, panels, buttons, pills, fields and notes.
 * Hand-rolled on purpose — no component or icon library enters the bundle.
 */
const P = 'nhrrob-social-share-';

const PATHS = {
	menu: 'M4 6h16M4 12h16M4 18h16',
	bug: 'M8 7a4 4 0 018 0M6 10h12v5a6 6 0 01-12 0zM12 10v11M3 13h3M18 13h3M4 6l3 4M20 6l-3 4M4 21l3-3M20 21l-3-3',
	moon: 'M20 14.5A8 8 0 019.5 4 8 8 0 1020 14.5z',
	sun: 'M12 8a4 4 0 100 8 4 4 0 000-8zM12 2v2M12 20v2M2 12h2M20 12h2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4',
	report: 'M5 21V4h9l1 2h5v9h-6l-1-2H5',
	sent: 'M8 6h12M8 12h12M8 18h12M4 6h.01M4 12h.01M4 18h.01',
	settings:
		'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM9 12a3 3 0 106 0 3 3 0 00-6 0',
	alert: 'M12 4l9 16H3zM12 10v4M12 17h.01',
	info: 'M12 3a9 9 0 100 18 9 9 0 000-18zM12 11v5M12 8h.01',
	check: 'M5 12.5l4.5 4.5L19 7.5',
	key: 'M12 15a4 4 0 11-8 0 4 4 0 018 0zM11 12l9-9M16 7l3 3',
	repo: 'M6 3h12v15H7.5a1.5 1.5 0 000 3H18M6 3v16.5M10 7h5',
	search: 'M11 4a7 7 0 100 14 7 7 0 000-14zM20 20l-4-4',
	external:
		'M14 4h6v6M20 4l-9 9M18 14v5a1 1 0 01-1 1H5a1 1 0 01-1-1V7a1 1 0 011-1h5',
	refresh: 'M20 11a8 8 0 00-14.9-3M4 5v3h3M4 13a8 8 0 0014.9 3M20 19v-3h-3',
	overview: 'M4 20V10M10 20V4M16 20v-7M22 20H2',
	share: 'M18 8a3 3 0 100-6 3 3 0 000 6zM6 15a3 3 0 100-6 3 3 0 000 6zM18 22a3 3 0 100-6 3 3 0 000 6zM8.6 13.5l6.8 4M15.4 6.5l-6.8 4',
	buttons:
		'M18 8a3 3 0 100-6 3 3 0 000 6zM6 15a3 3 0 100-6 3 3 0 000 6zM18 22a3 3 0 100-6 3 3 0 000 6zM8.6 13.5l6.8 4M15.4 6.5l-6.8 4',
	previews: 'M3 5h18v14H3zM3 15l5-5 4 4 3-3 6 6M15 9h.01',
	linkedin: 'M4 9v11M4 4.5v.01M9 20v-6.5a3.5 3.5 0 017 0V20M9 9v11',
	hand: 'M8 13V5.5a1.5 1.5 0 013 0V12M11 11.5v-2a1.5 1.5 0 013 0V12M14 10.5a1.5 1.5 0 013 0V12M17 11.5a1.5 1.5 0 013 0V16a6 6 0 01-6 6h-2a6 6 0 01-5-2.7L4.3 15a1.5 1.5 0 012.5-1.7L8 15',
	results: 'M4 20V10M10 20V4M16 20v-7M22 20H2',
	up: 'M12 19V5M5 12l7-7 7 7',
	down: 'M12 5v14M19 12l-7 7-7-7',
	posts: 'M6 3h9l4 4v14H6zM14 3v5h5M9 13h7M9 17h7',
	referrers:
		'M10 14a4 4 0 005.7 0l3-3a4 4 0 00-5.7-5.7l-1 1M14 10a4 4 0 00-5.7 0l-3 3a4 4 0 005.7 5.7l1-1',
};

export function Icon( { name, size = 18, className = '' } ) {
	return (
		<svg
			className={ P + 'icon ' + className }
			width={ size }
			height={ size }
			viewBox="0 0 24 24"
			fill="none"
			stroke="currentColor"
			strokeWidth="1.8"
			strokeLinecap="round"
			strokeLinejoin="round"
			aria-hidden="true"
		>
			<path d={ PATHS[ name ] || PATHS.bug } />
		</svg>
	);
}

export function ScreenHeader( { title, lede } ) {
	return (
		<div className={ P + 'screen__head' }>
			<h1 className={ P + 'screen__title' }>{ title }</h1>
			{ lede && <p className={ P + 'screen__lede' }>{ lede }</p> }
		</div>
	);
}

export function Panel( { title, icon, meta, actions, flush, children } ) {
	return (
		<section className={ P + 'panel' }>
			{ ( title || meta || actions ) && (
				<header className={ P + 'panel__head' }>
					<h2 className={ P + 'panel__title' }>
						{ icon && <Icon name={ icon } /> }
						{ title }
					</h2>
					{ meta && (
						<span className={ P + 'panel__meta' }>{ meta }</span>
					) }
					{ actions }
				</header>
			) }
			<div className={ P + 'panel__body' + ( flush ? ' is-flush' : '' ) }>
				{ children }
			</div>
		</section>
	);
}

export function Pill( { tone = 'off', children } ) {
	return (
		<span className={ P + 'pill ' + P + 'pill--' + tone }>
			{ children }
		</span>
	);
}

export function Button( {
	variant = 'ghost',
	small,
	className = '',
	...props
} ) {
	return (
		<button
			type="button"
			className={
				P +
				'btn ' +
				P +
				'btn--' +
				variant +
				( small ? ' ' + P + 'btn--sm' : '' ) +
				' ' +
				className
			}
			{ ...props }
		/>
	);
}

/**
 * A labelled control with an optional help line under it.
 *
 * @param {Object} root0          Props.
 * @param {string} root0.id       Id of the control the label belongs to.
 * @param {string} root0.label    Label.
 * @param {string} root0.help     Explanation under the control.
 * @param {Object} root0.children The control.
 * @return {Object} Field element.
 */
export function Field( { id, label, help, children } ) {
	return (
		<div className={ P + 'field' }>
			<label className={ P + 'field__label' } htmlFor={ id }>
				{ label }
			</label>
			{ children }
			{ help && <p className={ P + 'field__help' }>{ help }</p> }
		</div>
	);
}

export function Note( { tone = 'info', children } ) {
	return (
		<div className={ P + 'note ' + P + 'note--' + tone }>
			<Icon name={ tone === 'warn' ? 'alert' : 'info' } size={ 16 } />
			<div>{ children }</div>
		</div>
	);
}

export function Loading() {
	return <div className={ P + 'loading' } aria-busy="true" />;
}

/**
 * A link that opens GitHub in a new tab.
 *
 * @param {Object} root0          Props.
 * @param {string} root0.href     Address.
 * @param {Object} root0.children Link text.
 * @return {Object} Link element.
 */
export function External( { href, children } ) {
	return (
		<a
			className={ P + 'external' }
			href={ href }
			target="_blank"
			rel="noopener noreferrer"
		>
			{ children }
			<Icon name="external" size={ 13 } />
		</a>
	);
}
