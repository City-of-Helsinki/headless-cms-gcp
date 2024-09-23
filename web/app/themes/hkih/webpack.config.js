const path = require( 'path' );
const webpack = require( 'webpack' );
const MiniCssExtractPlugin = require( 'mini-css-extract-plugin' );
const TerserPlugin = require( 'terser-webpack-plugin' );

// Check for production mode.
const isProduction = process.env.NODE_ENV === 'production';

// Theme paths.
const themeName = path.basename( __dirname );
const themePublicPath = `/app/themes/${ themeName }/assets/dist/`;
const themeFullPath = `${ path.resolve( __dirname ) }`;

// Entries
const themeEditorEntry = `${ themeFullPath }/assets/scripts/editor.js`;
const themeAdminEntry = `${ themeFullPath }/assets/scripts/admin.js`;
const themeOutput = `${ themeFullPath }/assets/dist`;

// All loaders to use on assets.
const allModules = {
    rules: [
        {
            test: /\.js$/,
            exclude: /node_modules/,
            use: {
                loader: 'babel-loader',
                options: {
                    // Do not use the .babelrc configuration file.
                    babelrc: false,

                    // The loader will cache the results of the loader in node_modules/.cache/babel-loader.
                    cacheDirectory: true,

                    // Enable latest JavaScript features.
                    presets: [ '@babel/preset-env' ],

                    plugins: [
                        '@babel/plugin-syntax-dynamic-import', // Enable dynamic imports.
                        '@babel/plugin-syntax-top-level-await', // Enable await functions on js context top level (in addition to async functions)
                    ],
                },
            },
        },
        {
            test: /\.scss$/,
            use: [
                MiniCssExtractPlugin.loader,
                {
                    loader: 'css-loader',
                    options: {
                        sourceMap: true,
                    },
                },
                {
                    loader: 'postcss-loader',
                    options: {
                        sourceMap: true,
                    },
                },
                {
                    loader: 'sass-loader',
                    options: {
                        sourceMap: true,
                    },
                },
            ],
        },
        {
            test: /\.(gif|jpe?g|png|svg)(\?[a-z0-9=\.]+)?$/,
            exclude: [ /assets\/fonts/, /assets\/icons/, /node_modules/ ],
            type: 'asset/resource',
            use: [
                {
                    loader: 'image-webpack-loader',
                    options: {
                        // Disable imagemin for development build.
                        disable: ! isProduction,
                        mozjpeg: {
                            quality: 70,
                        },
                        optipng: {
                            enabled: false,
                        },
                        pngquant: {
                            quality: [ 0.7, 0.7 ],
                        },
                        gifsicle: {
                            interlaced: false,
                        },
                    },
                },
            ],
        },
    ],
};

// All optimizations to use.
const allOptimizations = {
    runtimeChunk: false,
    splitChunks: {
        cacheGroups: {
            vendor: {
                test: /[\\/]node_modules[\\/]/,
                name: 'vendor',
                chunks: 'all',
            },
        },
    },
};

// All plugins to use.
const allPlugins = [
    // Convert JS to CSS.
    new MiniCssExtractPlugin( {
        filename: '[name].css',
        chunkFilename: '[name]-[contenthash].css',
    } ),

    // Provide jQuery instance for all modules.
    new webpack.ProvidePlugin( {
        jQuery: 'jquery',
    } ),
];

// Use only for production build.
if ( isProduction ) {
    allOptimizations.minimizer = [
        // Optimize for production build.
        new TerserPlugin( {
            parallel: true,
            terserOptions: {
                output: {
                    comments: false,
                },
                compress: {
                    warnings: false,
                    drop_console: true, // eslint-disable-line camelcase
                },
            },
        } ),
    ];
}

const experiments = {
    topLevelAwait: true,
};

module.exports = [
    {
        entry: {
            admin: [ themeAdminEntry ],
            editor: [ themeEditorEntry ],
        },
        output: {
            path: themeOutput,
            publicPath: themePublicPath,
            filename: '[name].js',
            clean: true,
        },

        module: allModules,

        optimization: allOptimizations,

        plugins: allPlugins,

        experiments,

        externals: {
        // Set jQuery to be an external resource.
            jquery: 'jQuery',
        },

        // Disable source maps for production build.
        devtool: isProduction ? undefined : 'source-map',
    },
];
