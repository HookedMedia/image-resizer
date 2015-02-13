<?php namespace WookieMonster\ImageResizer\Facades;
 
use Illuminate\Support\Facades\Facade;
 
class ImageResizer extends Facade {
 
  /**
   * Get the registered name of the component.
   *
   * @return string
   */
	protected static function getFacadeAccessor()
	{
		return 'imageresizer';
	}

}