import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
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
import { NfcIcon } from '../../shared/block-icons';

import metadata from './block.json';
import './style.scss';
import './editor.scss';

registerBlockType( metadata.name, {
	icon: NfcIcon,

	edit( { attributes, setAttributes, context } ) {
		const ref = useRef();
		const blockProps = useBlockProps( { ref } );
		const authorId = useCardAuthorId( attributes.authorId );
		const avatar = useAvatarUpload( { authorId, containerRef: ref } );
		useInertLinks( ref );
		const photoUrl = useAuthorPhotoUrl( authorId, avatar.bust );

		return (
			<>
				<InspectorControls>
					<PanelBody title={ __( 'Card', 'ghostlabs-undercard' ) }>
						<SelectControl
							label={ __( 'Variation', 'ghostlabs-undercard' ) }
							value={ attributes.variation }
							help={ __(
								'Separate designs rather than light and dark: both can appear on the same page.',
								'ghostlabs-undercard'
							) }
							options={ [
								{ label: __( 'Ember — light', 'ghostlabs-undercard' ), value: 'ember' },
								{ label: __( 'Onyx — dark', 'ghostlabs-undercard' ), value: 'onyx' },
							] }
							onChange={ ( variation ) => setAttributes( { variation } ) }
							__nextHasNoMarginBottom
						/>
						<SelectControl
							label={ __( 'Layout', 'ghostlabs-undercard' ) }
							value={ attributes.layout }
							options={ [
								{ label: __( 'Portrait', 'ghostlabs-undercard' ), value: 'vertical' },
								{ label: __( 'Banner', 'ghostlabs-undercard' ), value: 'horizontal' },
								{ label: __( 'Row', 'ghostlabs-undercard' ), value: 'row' },
								{ label: __( 'Chip', 'ghostlabs-undercard' ), value: 'chip' },
							] }
							onChange={ ( layout ) => setAttributes( { layout } ) }
							__nextHasNoMarginBottom
						/>
						<SelectControl
							label={ __( 'Portrait', 'ghostlabs-undercard' ) }
							value={ attributes.avatar }
							help={ __(
								'Onyx runs the photograph full-bleed behind a scrim; Ember insets it as a card.',
								'ghostlabs-undercard'
							) }
							options={ [
								{ label: __( 'Photograph', 'ghostlabs-undercard' ), value: 'photo' },
								{ label: __( 'Initials', 'ghostlabs-undercard' ), value: 'monogram' },
							] }
							onChange={ ( next ) => setAttributes( { avatar: next } ) }
							__nextHasNoMarginBottom
						/>
						<HeadingLevelControl
							value={ attributes.headingLevel }
							onChange={ ( headingLevel ) => setAttributes( { headingLevel } ) }
						/>
					</PanelBody>

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
											'Enlarges the photo past the frame, which is what gives the focal point room to move it. It stops where the photo would no longer cover the frame.',
											'ghostlabs-undercard'
										) }
										value={ attributes.photoScale ?? 1 }
										onChange={ ( photoScale ) =>
											setAttributes( { photoScale: photoScale ?? 1 } )
										}
										min={ 1 }
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
