<?php namespace Bkwld\LaravelHaml;

// Dependencies
use MtHaml;
use Illuminate\View\Engines\CompilerEngine;

class ServiceProvider extends \Illuminate\Support\ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register() {

		// Bind the Haml compiler
		$this->app->bindShared('haml.compiler', function($app) {

			// Instantiate MtHaml, the brains of the operation
			$config = $app->make('config')->get('laravel-haml::config');
			$mthaml = new MtHaml\Environment($config['mthaml']['environment'], $config['mthaml']['options'], $config['mthaml']['filters']);

			// Instantiate our Laravel-style compiler
			$cache = $app['path.storage'].'/views';
			return new HamlCompiler($mthaml, $app['files'], $cache);
		});

	}

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot() {
		$this->package('bkwld/laravel-haml');
		$app = $this->app;

		// Add the .haml.php extension and register the Haml compiler with
		// Laravel's view engine resolver
		$app['view']->addExtension('haml.php', 'haml', function() use ($app) {
			return new CompilerEngine($app['haml.compiler']);
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides() {
		return array('haml.compiler');
	}

}