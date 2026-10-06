import { SelectControl } from '@wordpress/components';
import { __, sprintf } from '@wordpress/i18n';

export default function HeadingLevelControl( { value, onChange } ) {
	return (
		<SelectControl
			label={ __( 'Name heading level', 'ghostlabs-undercard' ) }
			value={ String( value ?? 2 ) }
			help={ __(
				'Match the page around the card. A card inside a post usually sits at 2, below the post title; one nested under a section heading may need a lower level.',
				'ghostlabs-undercard'
			) }
			options={ [ 2, 3, 4, 5, 6 ].map( ( level ) => ( {
				label: sprintf(
					/* translators: %d: a heading level, 2 to 6. */
					__( 'Heading %d', 'ghostlabs-undercard' ),
					level
				),
				value: String( level ),
			} ) ) }
			onChange={ ( level ) => onChange( Number( level ) ) }
			__nextHasNoMarginBottom
		/>
	);
}
