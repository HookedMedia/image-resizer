<?php

namespace ReviewPush\ImageResizer;

class ImageResizer {

	protected $file; // image resource
	protected $exif;
	protected $fullSavePath, $savedFilename, $savedFilenameWithExtension;
	protected $width, $height, $type;
	protected $quality;

	/**
	 * Constructor - file path can be passed here
	 * 
	 * @param string
	 * @return Object
	 */
	public function __construct($filename = NULL, $quality = 75)
	{
		if ($quality < 0 || $quality > 100)
		{
			throw new Exception\InvalidImageQualityException('The image quality is invalid');
		}
		
		$this->width = $this->height = 0;
		$this->quality = $quality;
		
		if ($filename != NULL)
		{
			$this->load($filename);
		}
	}

	/**
	 * Load an image file, type can be jpg, png or gif
	 * 
	 * @param string
	 * @return Object
	 */
	public function load($filename = NULL)
	{
		if ( ! extension_loaded('gd'))
		{
			throw new \Exception('The PHP GD extension does not exist!');
		}
		
		$this->exif = @exif_read_data($filename);
		list($this->width, $this->height, $this->type) = getimagesize($filename);

		switch ($this->type)
		{
			case IMAGETYPE_JPEG:
				$this->file = imagecreatefromjpeg($filename);
				break;
			case IMAGETYPE_PNG:
				$this->file = imagecreatefrompng($filename);
				break;
			case IMAGETYPE_GIF:
				$this->file = imagecreatefromgif($filename);
				break;
			default:
				throw new Exception\InvalidImageInputTypeException('Attempted to load a non-supported image');
		}

		return $this;
	}

	/**
	 * Crop the current image resource in the class
	 * 
	 * @param integer
	 * @param integer
	 * @param integer
	 * @param integer
	 * @return Object
	 */
	public function crop($left, $top, $width, $height)
	{
		if (($left + $width) > $this->width || ($top + $height) > $this->height)
		{
			throw new Exception\SelectionOutOfBoundsException('The cropping selection is out of bounds');
		}

		$canvas = imagecreatetruecolor($width, $height);
		imagecopy($canvas, $this->file,
			0, 0, // destination
			$left, $top, // source
			$width, $height);

		$this->file = $canvas;
		$this->setImageSize();

		return $this;
	}

	/**
	 * Resize the current image resource in the class from the center using ratios
	 * e.g. a 500x200 at a 1:1 (a square) size will result in a 200x200 image
	 * e.g. a 500x200 at a 3:4 size will result in a 150x200 image
	 * 
	 * @param integer
	 * @param string
	 * @return Object
	 */
	public function autoCrop($newWidthRatio = 1, $newHeightRatio = 1)
	{
		if ($newWidthRatio == 0)
		{
			throw new \InvalidArgumentException('The width ratio must be a non zero value');
		}
		
		if ($newHeightRatio == 0)
		{
			throw new \InvalidArgumentException('The height ratio must be a non zero value');
		}

		// original and new ratios
		$originalRatio = $this->width / $this->height;
		$newRatio = $newWidthRatio / $newHeightRatio;

		// no need to do any processing if ratios are the same!
		if ($newRatio == $originalRatio)
		{
			return $this;
		}

		// if the new ratio has a greater height
		if ($newRatio < $originalRatio)
		{
			$newWidth = ($this->height / $newHeightRatio) * $newWidthRatio;
			$newHeight = $this->height;
			$x = ($this->width / 2) - $newWidth / 2;
			$y = 0;
		}

		// if the new ratio has a greater width
		if ($newRatio > $originalRatio)
		{
			$newHeight = ($this->width / $newWidthRatio) * $newHeightRatio;
			$newWidth = $this->width;
			$x = 0;
			$y = ($this->height / 2) - $newHeight / 2;
		}

		// crop image from center
		$this->crop($x, $y, $newWidth, $newHeight);

		return $this;
	}

	/**
	 * Applies the contrast filter (-100 to +100)
	 * 
	 * @param integer
	 * @return Object
	 */
	public function contrast($level = 0)
	{
		if ($level < -100 || $level > 100)
		{
			throw new Exception\ContrastOutOfBoundsException('The contrast level is out of bounds');
		}
		
		imagefilter($this->file, IMG_FILTER_CONTRAST, $level);
		return $this;
	}

	/**
	 * Applies the brightness filter (-100 to +100)
	 * 
	 * @param integer
	 * @return Object
	 */
	public function brightness($level = 0)
	{
		if ($level < -100 || $level > 100)
		{
			throw new Exception\BrightnessOutOfBoundsException('The brightness level is out of bounds');
		}
		
		imagefilter($this->file, IMG_FILTER_BRIGHTNESS, $level);
		return $this;
	}

	/**
	 * Applies the smooth filter (-100 to +100)
	 * 
	 * @param integer
	 * @return Object
	 */
	public function smooth($level)
	{
		if ($level < -100 || $level > 100)
		{
			throw new Exception\SmoothingOutOfBoundsException('The smoothing level is out of bounds');
		}
		
		imagefilter($this->file, IMG_FILTER_SMOOTH, $level);
		return $this;
	}

	/**
	 * Applies the greyscale filter (-100 to +100)
	 * 
	 * @param integer
	 * @return Object
	 */
	public function greyscale($level = 0)
	{
		if ($level < -100 || $level > 100)
		{
			throw new Exception\GrayscaleOutOfBoundsException('The grayscale level is out of bounds');
		}
		
		imagefilter($this->file, IMG_FILTER_GRAYSCALE);
		return $this;
	}

	/**
	 * Applies the gaussian blur filter (-100 to +100)
	 * 
	 * @param integer
	 * @return Object
	 */
	public function gaussian($level = 0)
	{
		if ($level < -100 || $level > 100)
		{
			throw new Exception\GaussianOutOfBoundsException('The gaussian blur level is out of bounds');
		}
		
		imagefilter($this->file, IMG_FILTER_GAUSSIAN_BLUR, $level);
		return $this;
	}

	/**
	 * Resize the current image resource using its width
	 * 
	 * @param integer
	 * @return Object
	 */
	public function resizeWidth($newWidth)
	{
		$newHeight = ($newWidth / $this->width) * $this->height;
		$canvas = imagecreatetruecolor($newWidth, $newHeight);

		imagecopyresampled($canvas, $this->file,
			0, 0,
			0, 0,
			$newWidth, $newHeight,
			$this->width, $this->height);

		$this->file = $canvas;
		$this->setImageSize();

		return $this;
	}

	/**
	 * Resize the current image resource using its width
	 * 
	 * @param integer
	 * @return Object
	 */
	public function resizeHeight($newHeight)
	{
		$newWidth = ($newHeight / $this->height) * $this->width;
		$canvas = imagecreatetruecolor($newWidth, $newHeight);

		imagecopyresampled($canvas, $this->file,
			0, 0,
			0, 0,
			$newWidth, $newHeight,
			$this->width, $this->height);

		$this->file = $canvas;
		$this->setImageSize();

		return $this;
	}

	/**
	 * Resize the current image resource using its height
	 * 
	 * @param integer
	 * @return Object
	 */
	/*
	public function resizeHeight($newLength)
	{
		$newWidth = floor(($newLength / $this->height) * $this->width);
		$canvas = imagecreatetruecolor($newLength, $newWidth);

		imagecopyresized($canvas, $this->file,
			0, 0, // destination
			0, 0, // source
			$newLength, $newWidth,
			$this->width, $this->height);

		$this->file = $canvas;
		$this->width = $newWidth;
		$this->height = $newLength;

		return $this;
	}
	*/

	/**
	 * Roates the image through an appropriate angle
	 * 
	 * @param integer
	 * @return Object
	 */
	public function rotate($angle = 90)
	{
		if ($angle % 90 > 0)
		{
			throw new Exception\InvalidAngleArgumentException('The image can only be rotated at 90 degree intervals');
		}

		$this->file = imagerotate($this->file, $angle, 0);
		$this->setImageSize();

		return $this;
	}

	/**
	 * Save the image file to disk
	 * 
	 * @param string
	 * @param boolean
	 * @param string
	 * @return Object
	 */
	public function export($directory = '/dev/null/', $filename = FALSE, $type = 'jpg')
	{
		$this->setImageSize();

		if (substr($directory, -1) != '/')
		{
			$directory = $directory.'/';
		}
		
		if ($filename === FALSE)
		{
			$rand = $this->randString();

			$this->fullSavePath = $directory.$rand.'.'.$type;
			$this->savedFilename = $rand;
			$this->savedFilenameWithExtension = $rand.'.'.$type;
		}
		else
		{
			$this->fullSavePath = $directory.$filename.'.'.$type;
			$this->savedFilename = $filename;
			$this->savedFilenameWithExtension = $filename.'.'.$type;
		}

		switch ($type)
		{
			case 'jpg':
				if ( ! imagejpeg($this->file, $this->fullSavePath, $this->quality))
					throw new Exception\FileNotWritableException('jpg file could not be saved!');
				break;
			case 'png':
				imagealphablending($this->file, FALSE);
				imagesavealpha($this->file, TRUE);
				if ( ! imagepng($this->file, $this->fullSavePath))
					throw new Exception\FileNotWritableException('png file could not be saved!');
				break;
			case 'gif':
				if ( ! imagegif($this->file, $this->fullSavePath, $this->quality))
					throw new Exception\FileNotWritableException('gif file could not be saved!');
				break;
			default:
				throw new Exception\InvalidImageOutputTypeException('Bad filetype given, must be jpg, png or gif');
		}

		return $this;
	}

	/**
	 * Set image width and height attributes for the current image resource
	 * 
	 * @return null
	 */
	protected function setImageSize()
	{
		$this->width = imagesx($this->file);
		$this->height = imagesy($this->file);
	}

	/**
	 * Dump the image to the browser as a jpg
	 * 
	 * @return null
	 */
	public function dump()
	{
		header('Content-Type: image/jpeg');
		imagejpeg($this->file);
		imagedestroy($this->file);
	}

	/**
	 * Generates a random string of alphanumeric characters
	 * 
	 * @param integer
	 * @return string
	 */
	public function randString($length = 32, $pool = 'abcdefghijklmnopqrstuvwxqz1234567890')
	{
		$str = '';

		while ($length --> 0)
		{
			$rand = rand(0, strlen($pool) - 1);
			$str .= $pool[$rand];
		}

		return $str;
	}

	// Getters
	// ----------------------------------------------------------------------
	
	public function getFile() { return $this->file; }
	public function getExif() { return $this->exif; }
	public function getWidth() { return $this->width; }
	public function getHeight() { return $this->height; }
	public function getFullSavePath() { return $this->fullSavePath; }
	public function getSavedFilename() { return $this->savedFilename; }
	public function getSavedFilenameWithExtension() { return $this->savedFilenameWithExtension; }
}
