import apiFetch from '@wordpress/api-fetch';
import { useEffect, useState } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import { addQueryArgs } from '@wordpress/url';

const SEARCH_DELAY_MS = 250;
const SEARCH_RESULTS = 10;

export function useAuthorCandidates( restNamespace, term ) {
	const [ candidates, setCandidates ] = useState( [] );

	useEffect( () => {
		let canceled = false;

		const timer = setTimeout( () => {
			apiFetch( {
				path: addQueryArgs( `/${ restNamespace }/authors`, {
					search: term,
					per_page: SEARCH_RESULTS,
				} ),
			} )
				.then( ( found ) => {
					if ( ! canceled ) {
						setCandidates( Array.isArray( found ) ? found : [] );
					}
				} )
				.catch( () => {
					if ( ! canceled ) {
						setCandidates( [] );
					}
				} );
		}, SEARCH_DELAY_MS );

		return () => {
			canceled = true;
			clearTimeout( timer );
		};
	}, [ restNamespace, term ] );

	return candidates;
}

export function candidateLabel( candidate ) {
	return candidate.kind === 'guest'
		? sprintf(
				/* translators: %s: a guest author's name. */
				__( '%s (guest)', 'ghostlabs-undercard' ),
				candidate.name
		  )
		: candidate.name;
}
