/**
 * Results: shares per layout on LinkedIn, X and Facebook, and the visits each
 * brought in its first days, counted by NHR Post Insights.
 */
import { useState, useEffect } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';

import api from '../api';
import { Loading, Note, Panel, ScreenHeader } from '../components/ui';

const P = 'nhrrob-social-share-';

export default function Results() {
	const [ data, setData ] = useState( null );
	const [ failed, setFailed ] = useState( '' );

	useEffect( () => {
		api( '/results' )
			.then( setData )
			.catch( ( e ) => setFailed( e.message || String( e ) ) );
	}, [] );

	if ( failed ) {
		return <Note tone="warn">{ failed }</Note>;
	}

	if ( ! data ) {
		return <Loading />;
	}

	return (
		<>
			<ScreenHeader
				title={ __( 'Results', 'nhrrob-social-share' ) }
				lede={ sprintf(
					/* translators: %d: number of days */
					__(
						'Which layout brings readers back. A share counts the visits from its network on its day and the %d days after.',
						'nhrrob-social-share'
					),
					data.days - 1
				) }
			/>
			{ ! data.available && (
				<Note tone="warn">
					{ __(
						'Visits are counted by NHR Post Insights, which is not active. The shares are listed without them.',
						'nhrrob-social-share'
					) }
				</Note>
			) }
			{ data.networks.map( ( network ) => (
				<Panel
					key={ network.key }
					title={ network.name }
					icon={
						network.icon ||
						( network.key === 'linkedin' ? 'linkedin' : 'share' )
					}
					flush
				>
					<table className={ P + 'grid' }>
						<thead>
							<tr>
								<th>
									{ network.column ||
										__( 'Layout', 'nhrrob-social-share' ) }
								</th>
								<th className="is-number">
									{ __( 'Shares', 'nhrrob-social-share' ) }
								</th>
								<th className="is-number">
									{ __( 'Visits', 'nhrrob-social-share' ) }
								</th>
								<th className="is-number">
									{ __( 'Per share', 'nhrrob-social-share' ) }
								</th>
							</tr>
						</thead>
						<tbody>
							{ network.rows.map( ( row ) => (
								<tr key={ row.layout }>
									<td>{ row.label }</td>
									<td className="is-number">
										{ row.shares }
										{ row.counting > 0 &&
											' ' +
												sprintf(
													/* translators: %d: shares still in their first days */
													__(
														'(%d still counting)',
														'nhrrob-social-share'
													),
													row.counting
												) }
									</td>
									<td className="is-number">
										{ row.visits }
									</td>
									<td className="is-number">
										{ row.shares
											? (
													row.visits / row.shares
											  ).toFixed( 1 )
											: '' }
									</td>
								</tr>
							) ) }
						</tbody>
					</table>
				</Panel>
			) ) }
		</>
	);
}
