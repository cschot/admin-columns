let path = require( 'path' );

let config = {
	entry : {
		'admin-general' : './js/admin-general.ts',
		'admin-page-columns' : ['./js/admin-page-columns.js'],
		'admin-page-addons' : './js/admin-page-addons.ts',
		'message-review' : './js/message-review.ts',
		'notice-dismissible' : './js/notice-dismissible.js',
		'table' : './js/table.ts'
	},
	output : {
		path : path.resolve( __dirname, '../assets/js' ),
		filename : '[name].js',
	},
	module : {
		rules : [
			{
				test : /\.(t|j)sx?$/,
				exclude : /node_modules(?!(\/|\\)query-string)/,
				use : [
					{ loader : 'babel-loader' },
					{ loader : 'ts-loader' }
				]
			}
		]
	},
	resolve : {
		extensions : ['.ts', '.js'],
	},
	externals : {
		jquery : 'jQuery',
		jQuery : 'jQuery'
	},
	stats : {
		colors : true
	}

};

module.exports = ( env, argv ) => {

	if ( argv.mode === 'development' ) {

		config.devtool = 'source-map';
		config.watch = true;
		config.watchOptions = {
			ignored : /node_modules/
		}

	}

	return config;
};