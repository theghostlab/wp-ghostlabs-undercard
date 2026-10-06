import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { Button, PanelBody, SelectControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { useRef } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

import {
	useAvatarUpload,
	useCardAuthorId,
} from '../../shared/use-avatar-upload';
import { useInertLinks } from '../../shared/use-inert-links';
import HeadingLevelControl from '../../shared/heading-level-control';
import { MeridianIcon } from '../../shared/block-icons';

import metadata from './block.json';
import './style.scss';
import './editor.scss';

registerBlockType( metadata.name, {
	icon: MeridianIcon,

	edit( { attributes, setAttributes, context } ) {
		const ref = useRef();
		const blockProps = useBlockProps( { ref } );
		const authorId = useCardAuthorId( attributes.authorId );
		const avatar = useAvatarUpload( { authorId, containerRef: ref } );
		useInertLinks( ref );

		return (
			<>
				<InspectorControls>
					<PanelBody title={ __( 'Card', 'ghostlabs-undercard' ) }>
						<SelectControl
							label={ __( 'Layout', 'ghostlabs-undercard' ) }
							value={ attributes.layout }
							options={ [
								{ label: __( 'Vertical', 'ghostlabs-undercard' ), value: 'vertical' },
								{ label: __( 'Horizontal', 'ghostlabs-undercard' ), value: 'horizontal' },
								{ label: __( 'Row', 'ghostlabs-undercard' ), value: 'row' },
								{ label: __( 'Chip', 'ghostlabs-undercard' ), value: 'chip' },
							] }
							onChange={ ( layout ) => setAttributes( { layout } ) }
							__nextHasNoMarginBottom
						/>
						<SelectControl
							label={ __( 'Avatar', 'ghostlabs-undercard' ) }
							value={ attributes.avatar }
							help={ __(
								'Monogram always renders; Photo needs an uploaded avatar and falls back to the monogram.',
								'ghostlabs-undercard'
							) }
							options={ [
								{ label: __( 'Monogram', 'ghostlabs-undercard' ), value: 'monogram' },
								{ label: __( 'Photo', 'ghostlabs-undercard' ), value: 'photo' },
							] }
							onChange={ ( avatar ) => setAttributes( { avatar } ) }
							__nextHasNoMarginBottom
						/>
						<SelectControl
							label={ __( 'Theme', 'ghostlabs-undercard' ) }
							value={ attributes.scheme }
							help={ __(
								'System follows the visitor’s device preference.',
								'ghostlabs-undercard'
							) }
							options={ [
								{ label: __( 'Midnight', 'ghostlabs-undercard' ), value: 'dark' },
								{ label: __( 'Ivory', 'ghostlabs-undercard' ), value: 'light' },
								{ label: __( 'System', 'ghostlabs-undercard' ), value: 'system' },
							] }
							onChange={ ( scheme ) => setAttributes( { scheme } ) }
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
