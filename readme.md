# Simple Image Resizer for Laravel

>    Fork from https://github.com/douyasi/Laravel-Image-Resizer


composer require code

```json
"require": {
		"reviewpush/image-resizer" : "~1.0.*"
	}
```

## Key features:

* Resize while retaining current proportions based on width or height
* Autocrop images from the center using the shortest side
* Rotate
* Filters (contrast, brightness, smooth, greyscale, guassian)
* Supports jpg, png and gif loading and exporting
* Autogenerated filenames or custom filenames

After installing the package make sure to add 'WookieMonster\ImageResizer\ImageResizerServiceProvider' to your providers array in app/config/app.php. The package already contains an alias defined as ImageResizer.

## Examples:

Loading an image using the facade:

	$resizer = ImageResizer::load('path/to/image.jpg');

Auto crop the image to a 2:1 rectangle:

	$resizer->autocrop(2, 1)->export('/path/to/save/destination');

Auto crop the image to 1:1 (square) and resize the width to 200 maintaining aspect ratio:

	$resizer->autocrop(1, 1)->resizeWidth(200)->export('/path/to/save/destination');

Make a crop from x = 50, y = 20, 100 pixels wide and heigh:

	$resizer->crop(50, 20, 100, 100)->greyscale()->export('/path/to/save/destination');

Proportionally scale the image using its width or its height:
	
	// using the width and scaling the height proportionally:
	$resizer->resizeWidth(100);
	// OR using the height and scaling the width proportionally:
	$resizer->resizeHeight(100);

Rotate the image:

	$resizer->rotate(90)->export('/path/to/save/destination', 'myfilename', 'png');

By default the export creates a random 32 character filename:

	resizer->export('/path/to/save/destination');

To specify the filename include it in the second parameter:

	resizer->export('/path/to/save/destination', 'myfilename');

Specify the exported filetype with the third parameter:

	resizer->export('/path/to/save/destination', FALSE, 'png'); // saves png format

After the image is exported use the getters for useful information about the file:

	$resizer->getExif(); // exif data about the original file
	$resizer->getWidth(); // exported width
	$resizer->getHeight(); // exported height
	$resizer->getFullSavePath(); // full absolute path "/absolute/path/to/myfilename.jpg"
	$resizer->getSavedFilename(); // saved filename e.g. "myfilename"
	$resizer->getSavedFilenameWithExtension(); // saved filename including extension e.g. "myfilename.jpg"

License: [http://opensource.org/licenses/gpl-license.php](http://opensource.org/licenses/gpl-license.php) GNU Public License
