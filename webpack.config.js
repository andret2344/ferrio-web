const Encore = require('@symfony/webpack-encore');

if (!Encore.isRuntimeEnvironmentConfigured()) {
	Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
	.setOutputPath('public/build/')
	.setPublicPath('/build')

	.addEntry('app', './assets/app.ts')

	.splitEntryChunks()
	.enableSingleRuntimeChunk()

	.cleanupOutputBeforeBuild()

	.configureWatchOptions(watchOptions => {
		watchOptions.ignored = ['**/node_modules', '**/public/build'];
		watchOptions.poll = false;
	})

	.enableSourceMaps(!Encore.isProduction())
	.enableVersioning(Encore.isProduction())

	.configureBabelPresetEnv((config) => {
		config.useBuiltIns = 'usage';
		config.corejs = '3.38';
	})

	.enableTypeScriptLoader()
	.enablePostCssLoader()
;

module.exports = Encore.getWebpackConfig();
