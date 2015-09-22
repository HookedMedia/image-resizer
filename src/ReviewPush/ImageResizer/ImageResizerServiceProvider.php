<?php namespace ReviewPush\ImageResizer;

use Illuminate\Support\ServiceProvider;

class ImageResizerServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('wookie-monster/image-resizer');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->bind('imageresizer', function(){
			return new ImageResizer;
		});
		
		$this->app->booting(function(){
			$loader = \Illuminate\Foundation\AliasLoader::getInstance();
			$loader->alias('ImageResizer', 'ReviewPush\ImageResizer\Facades\ImageResizer');
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('imageresizer');
	}
}
