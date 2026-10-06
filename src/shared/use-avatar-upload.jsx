import { useCallback, useEffect, useRef, useState } from '@wordpress/element';
import { MediaUpload } from '@wordpress/block-editor';
import { useSelect } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';

export function useCardAuthorId( attributeAuthorId ) {
	return useSelect(
		( select ) => {
			if ( attributeAuthorId ) {
				return attributeAuthorId;
			}

			const editor = select( 'core/editor' );

			return editor ? editor.getEditedPostAttribute( 'author' ) : 0;
		},
		[ attributeAuthorId ]
	);
}

export function useAuthorPhotoUrl( authorId, bust = 0 ) {
	return useSelect(
		( select ) => {
			if ( ! authorId ) {
				return '';
			}

			const { getEntityRecord } = select( coreStore );
			const user = getEntityRecord( 'root', 'user', authorId );
			const attachmentId = Number( user?.meta?.undercard_avatar_id ?? 0 );

			if ( ! attachmentId ) {
				return '';
			}

			const media = getEntityRecord( 'postType', 'attachment', attachmentId );

			return media?.source_url ?? '';
		},
		[ authorId, bust ]
	);
}

export function useAvatarUpload( { authorId, containerRef } ) {
	const [ bust, setBust ] = useState( 0 );
	const openRef = useRef( null );

	const fallbackRef = useRef( null );

	const canEdit = useSelect(
		( select ) =>
			authorId
				? select( coreStore ).canUser( 'update', 'users', authorId ) === true
				: false,
		[ authorId ]
	);

	const save = useCallback(
		async ( attachmentId ) => {
			if ( ! authorId ) {
				return;
			}

			await apiFetch( {
				path: `/wp/v2/users/${ authorId }`,
				method: 'POST',
				data: { meta: { undercard_avatar_id: attachmentId } },
			} );

			setBust( ( n ) => n + 1 );
		},
		[ authorId ]
	);

	useEffect( () => {
		const root = containerRef.current;

		if ( ! root || ! canEdit ) {
			return undefined;
		}

		const decorate = () => {
			const node = root.querySelector( '[data-uc-avatar]' );

			if ( ! node || node.dataset.ucUploadReady ) {
				return;
			}

			node.dataset.ucUploadReady = '1';
			node.classList.add( 'ghostlabs-u-avatar-upload' );
			node.setAttribute( 'role', 'button' );
			node.setAttribute( 'tabindex', '0' );
			node.setAttribute(
				'aria-label',
				__( 'Upload a profile photo', 'ghostlabs-undercard' )
			);
			node.removeAttribute( 'aria-hidden' );
		};

		decorate();

		const observer = new MutationObserver( decorate );
		observer.observe( root, { childList: true, subtree: true } );

		return () => observer.disconnect();
	}, [ canEdit, containerRef, bust ] );

	useEffect( () => {
		const root = containerRef.current;

		if ( ! root || ! canEdit ) {
			return undefined;
		}

		const hit = ( event ) =>
			typeof event.target?.closest === 'function'
				? event.target.closest( '[data-uc-avatar]' )
				: null;

		const onClick = ( event ) => {
			if ( ! hit( event ) ) {
				return;
			}

			event.preventDefault();
			event.stopPropagation();
			openRef.current ? openRef.current() : fallbackRef.current?.();
		};

		const onKeyDown = ( event ) => {
			if ( ( event.key !== 'Enter' && event.key !== ' ' ) || ! hit( event ) ) {
				return;
			}

			event.preventDefault();
			openRef.current ? openRef.current() : fallbackRef.current?.();
		};

		root.addEventListener( 'click', onClick, true );
		root.addEventListener( 'keydown', onKeyDown, true );

		return () => {
			root.removeEventListener( 'click', onClick, true );
			root.removeEventListener( 'keydown', onKeyDown, true );
		};
	}, [ canEdit, containerRef ] );

	const control = canEdit ? (
		<MediaUpload
			onSelect={ ( media ) => save( media.id ) }
			allowedTypes={ [ 'image' ] }
			title={ __( 'Choose a profile photo', 'ghostlabs-undercard' ) }
			render={ ( { open } ) => {
				openRef.current = open;

				return null;
			} }
		/>
	) : null;

	const open = useCallback( () => {
		if ( openRef.current ) {
			openRef.current();

			return;
		}

		const media = window.wp?.media;

		if ( ! media ) {
			return;
		}

		const frame = media( {
			title: __( 'Choose a profile photo', 'ghostlabs-undercard' ),
			button: { text: __( 'Use this photo', 'ghostlabs-undercard' ) },
			library: { type: 'image' },
			multiple: false,
		} );

		frame.on( 'select', () => {
			const selected = frame.state().get( 'selection' ).first();

			if ( selected ) {
				save( selected.toJSON().id );
			}
		} );

		frame.open();
	}, [ save ] );

	fallbackRef.current = open;

	return {
		canEdit,
		control,
		bust,
		queryArgs: { _uc_avatar: bust },
		open,
		remove: () => save( 0 ),
	};
}
