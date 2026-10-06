const EMBER = '#E8501F';
const INK = '#0F1B2D';
const IVORY = '#F9F7F2';

const backCard = (
	<g transform="rotate(14 16.107 11.85)">
		<rect x="10.945" y="4.01" width="10.325" height="15.68" rx="1.737" fill={ EMBER } />
		<rect x="14.37" y="7.85" width="5.404" height="1.6" rx="0.8" fill={ INK } />
		<rect x="14.37" y="11.05" width="3.908" height="1.6" rx="0.8" fill={ INK } />
		<rect x="14.37" y="14.25" width="4.921" height="1.6" rx="0.8" fill={ INK } />
	</g>
);

const markWithFace = ( face ) => (
	<svg
		width="24"
		height="24"
		viewBox="0 0 24 24"
		fill="none"
		xmlns="http://www.w3.org/2000/svg"
		aria-hidden="true"
		focusable="false"
	>
		{ backCard }

		{ }
		<g transform="rotate(-6 6.941 12.67)">
			<rect x="1.778" y="4.83" width="10.325" height="15.68" rx="1.737" fill={ INK } />
			{ face }
		</g>
	</svg>
);

export const MeridianIcon = /*#__PURE__*/ markWithFace(
	<>
		<rect x="3.49" y="6.9" width="6.9" height="2.2" rx="1.1" fill={ IVORY } />
		<rect x="3.49" y="10.9" width="6.2" height="1.6" rx="0.8" fill={ IVORY } />
		<rect x="3.49" y="14.1" width="4.4" height="1.6" rx="0.8" fill={ IVORY } />
		<rect x="3.49" y="17.3" width="5.6" height="1.6" rx="0.8" fill={ IVORY } />
	</>
);

export const AuroraIcon = /*#__PURE__*/ markWithFace(
	<>
		<circle cx="6.941" cy="9.4" r="2.34" fill={ IVORY } />
		<rect x="3.49" y="13.2" width="6.9" height="1.6" rx="0.8" fill={ IVORY } />
		<rect x="3.49" y="16.6" width="1.9" height="1.6" rx="0.4" fill={ IVORY } />
		<rect x="5.99" y="16.6" width="1.9" height="1.6" rx="0.4" fill={ IVORY } />
		<rect x="8.49" y="16.6" width="1.9" height="1.6" rx="0.4" fill={ IVORY } />
	</>
);

export const CobaltIcon = /*#__PURE__*/ markWithFace(
	<>
		<rect x="3.49" y="6.4" width="6.9" height="6.2" rx="1" fill={ IVORY } />
		<circle cx="6.941" cy="8.9" r="1.45" fill={ INK } />
		<path d="M 4.55 12.6 V 12.48 A 2.39 2.39 0 0 1 9.33 12.48 V 12.6 Z" fill={ INK } />

		<rect x="3.49" y="14.2" width="6.9" height="1.9" rx="0.95" fill={ IVORY } />
		<rect x="3.49" y="17.4" width="4.6" height="1.6" rx="0.8" fill={ IVORY } />
	</>
);

export const NfcIcon = /*#__PURE__*/ markWithFace(
	<g stroke={ IVORY } strokeWidth="1.4" strokeLinecap="round" fill="none">
		<path d="M 4.26 10.974 A 2 2 0 0 1 4.26 14.366" />
		<path d="M 5.638 8.769 A 4.6 4.6 0 0 1 5.638 16.571" />
		<path d="M 7.015 6.564 A 7.2 7.2 0 0 1 7.015 18.776" />
	</g>
);
