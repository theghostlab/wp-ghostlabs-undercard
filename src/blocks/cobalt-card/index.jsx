import { registerBlockType } from '@wordpress/blocks';
import {
	InspectorControls,
	PanelColorSettings,
	useBlockProps,
} from '@wordpress/block-editor';
import {
	Button,
	FocalPointPicker,
	PanelBody,
	RangeControl,
	SelectControl,
} from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { useRef } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

import {
	useAuthorPhotoUrl,
	useAvatarUpload,
	useCardAuthorId,
} from '../../shared/use-avatar-upload';
import { useInertLinks } from '../../shared/use-inert-links';
import HeadingLevelControl from '../../shared/heading-level-control';
import { CobaltIcon } from '../../shared/block-icons';

import metadata from './block.json';
import './style.scss';
import './editor.scss';

const LAYOUTS = {
	ii: [
		{ label: __( 'Column', 'ghostlabs-undercard' ), value: 'vertical' },
		{ label: __( 'Photo Split', 'ghostlabs-undercard' ), value: 'horizontal' },
	],
	v: [
		{ label: __( 'Bottom Panel', 'ghostlabs-undercard' ), value: 'vertical' },
		{ label: __( 'Photo Backdrop', 'ghostlabs-undercard' ), value: 'horizontal' },
	],
};

function minScale( variation, layout ) {
	return variation === 'v' && layout === 'horizontal' ? 560 / 900 : 1;
}

const COMPACT_LAYOUTS = [
	{ label: __( 'Row', 'ghostlabs-undercard' ), value: 'row' },
	{ label: __( 'Chip', 'ghostlabs-undercard' ), value: 'chip' },
];

registerBlockType( metadata.name, {
	icon: CobaltIcon,

	edit( { attributes, setAttributes, context } ) {
		const ref = useRef();
		const blockProps = useBlockProps( { ref } );
		const authorId = useCardAuthorId( attributes.authorId );
		const avatar = useAvatarUpload( { authorId, containerRef: ref } );
		useInertLinks( ref );
		const photoUrl = useAuthorPhotoUrl( authorId, avatar.bust );
		const variation = attributes.variation === 'v' ? 'v' : 'ii';

		return (
			<>
				<InspectorControls>
					<PanelBody title={ __( 'Card', 'ghostlabs-undercard' ) }>
						<SelectControl
							label={ __( 'Variation', 'ghostlabs-undercard' ) }
							value={ variation }
							help={ __(
								'Variations are separate designs, not light and dark. Both can appear on the same page.',
								'ghostlabs-undercard'
							) }
							options={ [
								{ label: __( 'Cobalt II — ink panel', 'ghostlabs-undercard' ), value: 'ii' },
								{ label: __( 'Cobalt V — ivory panel', 'ghostlabs-undercard' ), value: 'v' },
							] }
							onChange={ ( next ) => setAttributes( { variation: next } ) }
							__nextHasNoMarginBottom
						/>
						<SelectControl
							label={ __( 'Layout', 'ghostlabs-undercard' ) }
							value={ attributes.layout }
							options={ [ ...LAYOUTS[ variation ], ...COMPACT_LAYOUTS ] }
							onChange={ ( layout ) => setAttributes( { layout } ) }
							__nextHasNoMarginBottom
						/>
						<SelectControl
							label={ __( 'Portrait', 'ghostlabs-undercard' ) }
							value={ attributes.avatar }
							help={ __(
								'The initials are drawn over the portrait either way. Without one they sit on a tinted panel.',
								'ghostlabs-undercard'
							) }
							options={ [
								{ label: __( 'Tinted panel', 'ghostlabs-undercard' ), value: 'monogram' },
								{ label: __( 'Photograph', 'ghostlabs-undercard' ), value: 'photo' },
							] }
							onChange={ ( avatar ) => setAttributes( { avatar } ) }
							__nextHasNoMarginBottom
						/>
						<HeadingLevelControl
							value={ attributes.headingLevel }
							onChange={ ( headingLevel ) => setAttributes( { headingLevel } ) }
						/>
					</PanelBody>

					<PanelColorSettings
						title={ __( 'Accent', 'ghostlabs-undercard' ) }
						initialOpen={ false }
						colorSettings={ [
							{
								label: __( 'Accent', 'ghostlabs-undercard' ),
								value: attributes.accent,
								onChange: ( accent ) => setAttributes( { accent: accent || '' } ),
							},
						] }
						__experimentalIsRenderedInSidebar
					>
						<p>
							{ __(
								'Tints the portrait and sets the role, site and rules. Leave empty for the variation’s own accent.',
								'ghostlabs-undercard'
							) }
						</p>
					</PanelColorSettings>
					{ avatar.canEdit && (
						<PanelBody
							title={ __( 'Profile photo', 'ghostlabs-undercard' ) }
							initialOpen={ false }
						>
							<p>
								{ __(
									'Saved to the author’s profile, not to this block — every card showing them updates.',
									'ghostlabs-undercard'
								) }
							</p>
							<Button variant="secondary" onClick={ avatar.open }>
								{ __( 'Choose photo', 'ghostlabs-undercard' ) }
							</Button>{ ' ' }
							<Button variant="tertiary" isDestructive onClick={ avatar.remove }>
								{ __( 'Remove', 'ghostlabs-undercard' ) }
							</Button>
							{ }
							{ photoUrl && attributes.avatar === 'photo' && (
								<div className="ghostlabs-u-focal-panel">
									<FocalPointPicker
										label={ __( 'Focal point', 'ghostlabs-undercard' ) }
										help={ __(
											'Which part of the photo to keep in frame on this card. Applies to this block only.',
											'ghostlabs-undercard'
										) }
										url={ photoUrl }
										value={ attributes.focalPoint ?? { x: 0.5, y: 0.5 } }
										onChange={ ( focalPoint ) =>
											setAttributes( { focalPoint } )
										}
										__nextHasNoMarginBottom
									/>
									<RangeControl
										label={ __( 'Scale', 'ghostlabs-undercard' ) }
										help={ __(
											'Enlarges the photo past the frame, which is what gives the focal point room to move it. It stops where the photo would no longer cover the card.',
											'ghostlabs-undercard'
										) }
										value={ attributes.photoScale ?? 1 }
										onChange={ ( photoScale ) =>
											setAttributes( { photoScale: photoScale ?? 1 } )
										}
										min={ minScale( variation, attributes.layout ) }
										max={ 4 }
										step={ 0.05 }
										initialPosition={ 1 }
										resetFallbackValue={ 1 }
										allowReset
										__nextHasNoMarginBottom
										__next40pxDefaultSize
									/>
									{ ( attributes.focalPoint ||
										( attributes.photoScale ?? 1 ) !== 1 ) && (
										<Button
											variant="tertiary"
											onClick={ () =>
												setAttributes( {
													focalPoint: undefined,
													photoScale: 1,
												} )
											}
										>
											{ __( 'Reset framing', 'ghostlabs-undercard' ) }
										</Button>
									) }
								</div>
							) }
						</PanelBody>
					) }
				</InspectorControls>

				<div { ...blockProps }>
					{ avatar.control }
					<ServerSideRender
						block={ metadata.name }
						attributes={ attributes }
						urlQueryArgs={ {
							...avatar.queryArgs,
							...( context?.postId
								? { post_id: context.postId }
								: {} ),
						} }
					/>
				</div>
			</>
		);
	},

	save() {
		return null;
	},
} );
