import { useEffect } from '@wordpress/element';

const INTERACTIVE = 'a[href], button';

export function useInertLinks( containerRef ) {
	useEffect( () => {
		const root = containerRef.current;

		if ( ! root ) {
			return undefined;
		}

		const deaden = () => {
			for ( const node of root.querySelectorAll( INTERACTIVE ) ) {
				if ( ! node.hasAttribute( 'inert' ) && ! node.closest( '[data-uc-avatar]' ) ) {
					node.setAttribute( 'inert', '' );
				}
			}
		};

		deaden();

		const observer = new MutationObserver( deaden );
		observer.observe( root, { childList: true, subtree: true } );

		return () => observer.disconnect();
	}, [ containerRef ] );
}
