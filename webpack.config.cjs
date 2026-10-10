const fs = require( 'fs' );
const path = require( 'path' );
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

const MONOREPO_SHARED_SRC = path.resolve( __dirname, '../shared/src' );
const SHARED_SRC = fs.existsSync( MONOREPO_SHARED_SRC )
	? MONOREPO_SHARED_SRC
	: path.resolve( __dirname, 'src' );

function sharedBlockEntries() {
	const blocksDir = path.join( SHARED_SRC, 'blocks' );

	if ( ! fs.existsSync( blocksDir ) ) {
		return {};
	}

	const entries = {};

	for ( const name of fs.readdirSync( blocksDir ) ) {
		const metadata = path.join( blocksDir, name, 'block.json' );

		if ( ! fs.existsSync( metadata ) ) {
			continue;
		}

		const block = JSON.parse( fs.readFileSync( metadata, 'utf8' ) );

		for ( const field of [ 'editorScript', 'script', 'viewScript' ] ) {
			const declared = block[ field ];

			if ( typeof declared !== 'string' || ! declared.startsWith( 'file:' ) ) {
				continue;
			}

			const relative = declared.replace( 'file:', '' ).replace( /^\.\//, '' );
			const withoutExtension = relative.replace( /\.m?[jt]sx?$/, '' );

			const source = [ 'jsx', 'tsx', 'js', 'ts' ]
				.map( ( extension ) =>
					path.join( blocksDir, name, `${ withoutExtension }.${ extension }` )
				)
				.find( ( candidate ) => fs.existsSync( candidate ) );

			if ( ! source ) {
				// eslint-disable-next-line no-console
				console.warn( `[undercard] no source for ${ declared } in ${ metadata }` );
				continue;
			}

			entries[ `blocks/${ name }/${ withoutExtension }` ] = source;
		}
	}

	return entries;
}

const copyPlugin = defaultConfig.plugins.find(
	( plugin ) => plugin.constructor && plugin.constructor.name === 'CopyPlugin'
);

function transformBlockJson( content ) {
	const block = JSON.parse( content.toString() );
	const toBuiltExtension = ( value ) => value.replace( /\.m?[jt]sx?$/, '.js' );

	for ( const field of [ 'editorScript', 'script', 'viewScript', 'viewScriptModule' ] ) {
		const declared = block[ field ];

		if ( Array.isArray( declared ) ) {
			block[ field ] = declared.map( toBuiltExtension );
		} else if ( typeof declared === 'string' ) {
			block[ field ] = toBuiltExtension( declared );
		}
	}

	return JSON.stringify( block, null, 2 );
}

if ( copyPlugin ) {
	copyPlugin.patterns.push(
		{
			from: '**/block.json',
			context: path.join( SHARED_SRC, 'blocks' ),
			to: 'blocks',
			noErrorOnMissing: true,
			transform: transformBlockJson,
		},
		{
			from: '**/*.php',
			context: path.join( SHARED_SRC, 'blocks' ),
			to: 'blocks',
			noErrorOnMissing: true,
		}
	);
}

const READABLE = process.env.GHOSTLABS_MINIFY === '0';

function readableLoader( entry ) {
	const loader = typeof entry === 'string' ? entry : entry.loader || '';
	const options = ( typeof entry === 'object' && entry.options ) || {};

	if ( loader.includes( 'postcss-loader' ) && options.postcssOptions ) {
		const plugins = ( options.postcssOptions.plugins || [] ).filter(
			( plugin ) => ! ( plugin && plugin.constructor && plugin.constructor.name === 'Processor' )
		);

		return {
			...entry,
			options: { ...options, postcssOptions: { ...options.postcssOptions, plugins } },
		};
	}

	if ( loader.includes( 'sass-loader' ) ) {
		return {
			...entry,
			options: { ...options, sassOptions: { ...options.sassOptions, style: 'expanded' } },
		};
	}

	return entry;
}

function readableRules( rules ) {
	return rules.map( ( rule ) => {
		if ( rule.type === 'asset/inline' ) {
			return {
				...rule,
				type: 'asset/resource',
				generator: { filename: 'images/[name].[hash:8][ext]' },
			};
		}

		return Array.isArray( rule.use ) ? { ...rule, use: rule.use.map( readableLoader ) } : rule;
	} );
}

module.exports = {
	...defaultConfig,
	...( READABLE && {
		optimization: { ...defaultConfig.optimization, minimize: false },
		module: { ...defaultConfig.module, rules: readableRules( defaultConfig.module.rules ) },
	} ),
	resolve: {
		...defaultConfig.resolve,

		modules: [ path.resolve( __dirname, 'node_modules' ), 'node_modules' ],

		alias: {
			...( defaultConfig.resolve && defaultConfig.resolve.alias ),

			'@fonts': path.join( SHARED_SRC, '../assets/fonts' ),

			'@styles': path.join( SHARED_SRC, 'styles' ),

			'@shared': path.join( SHARED_SRC, 'shared' ),
		},
	},
	entry: {
		...( typeof defaultConfig.entry === 'function'
			? defaultConfig.entry()
			: defaultConfig.entry ),
		...sharedBlockEntries(),

		'editor/byline': path.join( SHARED_SRC, 'editor/byline.jsx' ),

		'admin/upgrade': path.join( __dirname, 'src/admin/upgrade/index.mjs' ),
		'admin/upsell-notice': path.join( __dirname, 'src/admin/upsell-notice/index.mjs' ),
	},
};
