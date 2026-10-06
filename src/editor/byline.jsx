import { registerPlugin } from '@wordpress/plugins';
import * as editorPackage from '@wordpress/editor';
import * as editPostPackage from '@wordpress/edit-post';
import { useEntityProp } from '@wordpress/core-data';
import { useSelect } from '@wordpress/data';
import { store as editorStore } from '@wordpress/editor';
import { useEffect, useMemo, useRef, useState } from '@wordpress/element';
import {
	Button,
	ComboboxControl,
	ExternalLink,
	Flex,
	FlexBlock,
	FlexItem,
	Notice,
	SelectControl,
} from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';
import { __, sprintf } from '@wordpress/i18n';
import { speak } from '@wordpress/a11y';
import { arrowDown, arrowUp, closeSmall } from '@wordpress/icons';

import { candidateLabel, useAuthorCandidates } from '../shared/use-author-candidates';

import './byline.scss';

const PluginDocumentSettingPanel =
	editorPackage.PluginDocumentSettingPanel ??
	editPostPackage.PluginDocumentSettingPanel;

const CONFIG = window.GHOSTLABS_UNDERCARD_BYLINE || {
	field: 'ghostlabs_undercard_byline',
	postTypes: [],
	roles: [],
	restNamespace: '',
	upsell: null,
};

const UPSELL = CONFIG.upsell || null;

const MINIMUM_CREDITS_FOR_UPSELL = 2;

function Portrait( { entry } ) {
	if ( entry.avatar ) {
		return (
			<img
				className="ghostlabs-u-byline__portrait"
				src={ entry.avatar }
				alt=""
				width="24"
				height="24"
			/>
		);
	}

	return (
		<span className="ghostlabs-u-byline__portrait ghostlabs-u-byline__portrait--initial" aria-hidden="true">
			{ ( entry.name || '?' ).trim().charAt( 0 ).toUpperCase() }
		</span>
	);
}

function Row( { entry, index, total, onRole, onMove, onRemove } ) {
	return (
		<li className="ghostlabs-u-byline__row">
			<Flex align="center" gap={ 2 }>
				<FlexItem>
					<Portrait entry={ entry } />
				</FlexItem>
				<FlexBlock>
					<span className="ghostlabs-u-byline__name">{ entry.name }</span>
					{ entry.kind === 'guest' && (
						<span className="ghostlabs-u-byline__kind">
							{ __( 'Guest', 'ghostlabs-undercard' ) }
						</span>
					) }
				</FlexBlock>
				<FlexItem>
					<Button
						size="small"
						icon={ arrowUp }
						disabled={ index === 0 }
						accessibleWhenDisabled
						label={ sprintf(
							/* translators: %s: an author's name. */
							__( 'Move %s up', 'ghostlabs-undercard' ),
							entry.name
						) }
						onClick={ () => onMove( index, -1 ) }
					/>
				</FlexItem>
				<FlexItem>
					<Button
						size="small"
						icon={ arrowDown }
						disabled={ index === total - 1 }
						accessibleWhenDisabled
						label={ sprintf(
							/* translators: %s: an author's name. */
							__( 'Move %s down', 'ghostlabs-undercard' ),
							entry.name
						) }
						onClick={ () => onMove( index, 1 ) }
					/>
				</FlexItem>
				<FlexItem>
					<Button
						size="small"
						icon={ closeSmall }
						isDestructive
						className="ghostlabs-u-byline__remove"
						data-credit={ entry.id }
						label={ sprintf(
							/* translators: %s: an author's name. */
							__( 'Remove %s', 'ghostlabs-undercard' ),
							entry.name
						) }
						onClick={ () => onRemove( index ) }
					/>
				</FlexItem>
			</Flex>
			<SelectControl
				className="ghostlabs-u-byline__role"
				label={ sprintf(
					/* translators: %s: an author's name. */
					__( 'Role for %s', 'ghostlabs-undercard' ),
					entry.name
				) }
				hideLabelFromVision
				value={ entry.role }
				options={ CONFIG.roles }
				onChange={ ( role ) => onRole( index, role ) }
				__nextHasNoMarginBottom
				__next40pxDefaultSize
			/>
		</li>
	);
}

function UpsellNotice( { credits } ) {
	const [ dismissed, setDismissed ] = useState( UPSELL ? UPSELL.dismissed : true );

	if ( ! UPSELL || dismissed || credits < MINIMUM_CREDITS_FOR_UPSELL ) {
		return null;
	}

	const dismiss = () => {
		setDismissed( true );

		apiFetch( {
			path: '/wp/v2/users/me',
			method: 'POST',
			data: { meta: { [ UPSELL.metaKey ]: true } },
		} ).catch( () => {} );

		speak( __( 'Upgrade prompt dismissed.', 'ghostlabs-undercard' ) );

		let frames = 0;
		const settle = () => {
			if ( document.querySelector( '.ghostlabs-u-byline__upsell' ) && frames++ < 30 ) {
				window.requestAnimationFrame( settle );
				return;
			}

			document.querySelector( '.ghostlabs-u-byline__picker input[role="combobox"]' )?.focus();
		};

		window.requestAnimationFrame( settle );
	};

	return (
		<Notice
			className="ghostlabs-u-byline__upsell"
			status="info"
			onRemove={ dismiss }
			spokenMessage=""
		>
			{ }
			<p className="ghostlabs-u-byline__upsell-title">
				<strong>
					{ }
					{ __( 'More than one person is credited.', 'ghostlabs-undercard' ) }
				</strong>
			</p>
			<p>
				{ sprintf(
					/* translators: %s: the name of the paid edition. */
					__(
						'%s emits each of them in the article’s structured data, with their roles.',
						'ghostlabs-undercard'
					),
					UPSELL.editionName
				) }
			</p>
			{ }
			<ExternalLink href={ UPSELL.url }>
				{ __( 'See what that publishes', 'ghostlabs-undercard' ) }
			</ExternalLink>
		</Notice>
	);
}

function Byline() {
	const postType = useSelect(
		( select ) => select( editorStore ).getCurrentPostType(),
		[]
	);

	const [ stored, setStored ] = useEntityProp( 'postType', postType, CONFIG.field );
	const byline = useMemo( () => ( Array.isArray( stored ) ? stored : [] ), [ stored ] );

	const [ term, setTerm ] = useState( '' );
	const candidates = useAuthorCandidates( CONFIG.restNamespace, term );

	const options = useMemo( () => {
		const taken = new Set( byline.map( ( entry ) => entry.id ) );

		return candidates
			.filter( ( candidate ) => ! taken.has( candidate.id ) )
			.map( ( candidate ) => ( {
				value: candidate.id,
				label: candidateLabel( candidate ),
			} ) );
	}, [ candidates, byline ] );

	if ( ! postType || ! CONFIG.postTypes.includes( postType ) ) {
		return null;
	}

	const add = ( id ) => {
		const candidate = candidates.find( ( each ) => each.id === id );

		if ( ! candidate ) {
			return;
		}

		setStored( [ ...byline, { ...candidate, role: 'author' } ] );
		setTerm( '' );

		speak(
			sprintf(
				/* translators: 1: an author's name. 2: their position. 3: how many people the byline names. */
				__( '%1$s added to the byline, position %2$d of %3$d.', 'ghostlabs-undercard' ),
				candidate.name,
				byline.length + 1,
				byline.length + 1
			)
		);
	};

	const move = ( index, delta ) => {
		const next = [ ...byline ];
		const target = index + delta;

		if ( target < 0 || target >= byline.length ) {
			return;
		}

		[ next[ index ], next[ target ] ] = [ next[ target ], next[ index ] ];

		setStored( next );

		speak(
			sprintf(
				/* translators: 1: an author's name. 2: their new position. 3: how many people the byline names. */
				__( '%1$s moved to position %2$d of %3$d.', 'ghostlabs-undercard' ),
				byline[ index ].name,
				target + 1,
				next.length
			)
		);
	};

	const setRole = ( index, role ) => {
		setStored(
			byline.map( ( entry, at ) => ( at === index ? { ...entry, role } : entry ) )
		);

		speak(
			sprintf(
				/* translators: 1: an author's name. 2: the role they hold on this post. */
				__( '%1$s is now credited as %2$s.', 'ghostlabs-undercard' ),
				byline[ index ].name,
				CONFIG.roles.find( ( each ) => each.value === role )?.label ?? role
			)
		);
	};

	const pendingFocus = useRef( null );

	const remove = ( index ) => {
		const going = byline[ index ];
		const next = byline[ index + 1 ] ?? byline[ index - 1 ] ?? null;

		pendingFocus.current = { gone: going.id, next: next ? next.id : null };
		setStored( byline.filter( ( _, at ) => at !== index ) );

		speak(
			sprintf(
				/* translators: %s: an author's name. */
				__( '%s removed from the byline.', 'ghostlabs-undercard' ),
				going.name
			),
			'assertive'
		);
	};

	useEffect( () => {
		const pending = pendingFocus.current;

		if ( ! pending ) {
			return;
		}

		pendingFocus.current = null;

		const button = ( id ) =>
			document.querySelector( `.ghostlabs-u-byline__remove[data-credit="${ window.CSS.escape( id ) }"]` );
		let frames = 0;

		const settle = () => {
			if ( button( pending.gone ) && frames++ < 30 ) {
				window.requestAnimationFrame( settle );
				return;
			}

			const target = pending.next
				? button( pending.next )
				: document.querySelector( '.ghostlabs-u-byline__picker input[role="combobox"]' );

			target?.focus();
		};

		window.requestAnimationFrame( settle );
	}, [ byline ] );

	return (
		<PluginDocumentSettingPanel
			name="ghostlabs-undercard-byline"
			title={ __( 'Byline', 'ghostlabs-undercard' ) }
			className="ghostlabs-u-byline"
		>
			{ byline.length === 0 && (
				<p className="ghostlabs-u-byline__empty">
					{ __(
						'No byline set, so this post follows its author. Add somebody to credit them instead.',
						'ghostlabs-undercard'
					) }
				</p>
			) }

			{ byline.length > 0 && (
				<ul className="ghostlabs-u-byline__list">
					{ byline.map( ( entry, index ) => (
						<Row
							key={ entry.id }
							entry={ entry }
							index={ index }
							total={ byline.length }
							onRole={ setRole }
							onMove={ move }
							onRemove={ remove }
						/>
					) ) }
				</ul>
			) }

			<ComboboxControl
				className="ghostlabs-u-byline__picker"
				label={ __( 'Add somebody', 'ghostlabs-undercard' ) }
				help={ __(
					'Registered users and guest authors, in one list.',
					'ghostlabs-undercard'
				) }
				value={ null }
				options={ options }
				onFilterValueChange={ setTerm }
				onChange={ add }
				__nextHasNoMarginBottom
				__next40pxDefaultSize
			/>

			<UpsellNotice credits={ byline.length } />
		</PluginDocumentSettingPanel>
	);
}

registerPlugin( 'ghostlabs-undercard-byline', { render: Byline } );
