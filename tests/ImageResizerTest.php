<?php

use WookieMonster\ImageResizer\ImageResizer;

class ImageResizerTest extends PHPUnit_Framework_Testcase {
	
	public $testJpgImage; // 600x300
	public $testGifImage; // 300x400
	public $testPngImage; // 800x500
	public $invalidImage;
	
	public function setUp()
	{
		$this->testJpgImage = 'public/test.jpg';
		$this->testGifImage = 'public/test.gif';
		$this->testPngImage = 'public/test.png';
		$this->invalidImage = 'public/test.txt';
	}
	
	public function tearDown()
	{
		unset($this->testJpgImage);
		unset($this->testGifImage);
		unset($this->testPngImage);
		unset($this->invalidImage);
	}
	
	/**
     * @expectedException WookieMonster\ImageResizer\Exception\InvalidImageQualityException
     */
	public function testConstructorWithInvalidQuality()
	{
		$resizer = new ImageResizer(NULL, 150);
	}
	
	/**
     * @expectedException WookieMonster\ImageResizer\Exception\InvalidImageInputTypeException
     */
	public function testInvalidImageFormat()
	{
		$resizer = new ImageResizer($this->invalidImage);
	}
	
	public function testJpgImageResourceIsLoaded()
	{
		$resizer = new ImageResizer($this->testJpgImage);
		$this->assertInternalType('resource', $resizer->getFile());
	}
	
	public function testGifImageResourceIsLoaded()
	{
		$resizer = new ImageResizer($this->testGifImage);
		$this->assertInternalType('resource', $resizer->getFile());
	}
	
	public function testPngImageResourceIsLoaded()
	{
		$resizer = new ImageResizer($this->testPngImage);
		$this->assertInternalType('resource', $resizer->getFile());
	}
	
	/**
     * @expectedException WookieMonster\ImageResizer\Exception\SelectionOutOfBoundsException
     */
	public function testCroppingOutOfBounds()
	{
		$resizer = new ImageResizer($this->testJpgImage);
		$resizer->crop(50, 50, 800, 800); // crop selection too large
	}
	
	public function testCroppedResourceIsCorrectWidthAndHight()
	{
		$resizer = new ImageResizer($this->testJpgImage);
		$resizer->crop(10, 20, 50, 50);
		$this->assertEquals(50, imagesx($resizer->getFile()));
		$this->assertEquals(50, imagesy($resizer->getFile()));
	}

	public function testResizeWidth()
	{
		$resizer = new ImageResizer($this->testJpgImage);
		$resizer->resizeWidth(50);
		$this->assertEquals(50, imagesx($resizer->getFile()));
		$this->assertEquals(25, imagesy($resizer->getFile()));
	}

	public function testResizeHeight()
	{
		$resizer = new ImageResizer($this->testJpgImage);
		$resizer->resizeHeight(100);
		$this->assertEquals(200, imagesx($resizer->getFile()));
		$this->assertEquals(100, imagesy($resizer->getFile()));
	}
	
	public function testAutoCropLandscapeToPortrait()
	{
		$resizer = new ImageResizer($this->testJpgImage);
		// ratio should produce an image 150x300
		$resizer->autoCrop(1, 2);
		$this->assertEquals(150, imagesx($resizer->getFile()));
		$this->assertEquals(300, imagesy($resizer->getFile()));
	}
	
	public function testAutoCropPortraitToLandscape()
	{
		$resizer = new ImageResizer($this->testGifImage);
		// ratio should produce an image 300x150
		$resizer->autoCrop(2, 1);
		$this->assertEquals(300, imagesx($resizer->getFile()));
		$this->assertEquals(150, imagesy($resizer->getFile()));
	}
	
	public function testRotateImage()
	{
		$resizer = new ImageResizer($this->testJpgImage);
		$resizer->rotate(90);
		$this->assertEquals(300, imagesx($resizer->getFile()));
		$this->assertEquals(600, imagesy($resizer->getFile()));
	}
	
	/**
     * @expectedException WookieMonster\ImageResizer\Exception\InvalidAngleArgumentException
     */
	public function testRotatedAtInvalidAngle()
	{
		$resizer = new ImageResizer($this->testJpgImage);
		$resizer->rotate(100);
	}
	
	/**
     * @expectedException WookieMonster\ImageResizer\Exception\ContrastOutOfBoundsException
     */
	public function testContrastOutOfBounds()
	{
		$resizer = new ImageResizer($this->testJpgImage);
		$resizer->contrast(150);
	}
	
	/**
     * @expectedException WookieMonster\ImageResizer\Exception\BrightnessOutOfBoundsException
     */
	public function testBrightnssOutOfBounds()
	{
		$resizer = new ImageResizer($this->testJpgImage);
		$resizer->brightness(150);
	}
	
	/**
     * @expectedException WookieMonster\ImageResizer\Exception\SmoothingOutOfBoundsException
     */
	public function testSmoothingOutOfBounds()
	{
		$resizer = new ImageResizer($this->testJpgImage);
		$resizer->smooth(150);
	}
	
	/**
     * @expectedException WookieMonster\ImageResizer\Exception\GrayscaleOutOfBoundsException
     */
	public function testGreyscaleOutOfBounds()
	{
		$resizer = new ImageResizer($this->testJpgImage);
		$resizer->greyscale(150);
	}
	
	/**
     * @expectedException WookieMonster\ImageResizer\Exception\GaussianOutOfBoundsException
     */
	public function testGaussianOutOfBounds()
	{
		$resizer = new ImageResizer($this->testJpgImage);
		$resizer->gaussian(150);
	}
	
	public function testRandomStringGeneratesCorrectLength()
	{
		$length = 40;
		$resizer = new ImageResizer();
		$string = $resizer->randString($length);
		$this->assertEquals($length, strlen($string));
	}
	
	public function testExportedJpgExists()
	{
		$resizer = new ImageResizer($this->testJpgImage);
		$resizer->export('public');
		$this->assertFileExists($resizer->getFullSavePath());
		unlink($resizer->getFullSavePath());
	}
	
	public function testExportedPngExists()
	{
		$resizer = new ImageResizer($this->testJpgImage);
		$resizer->export('public', FALSE, 'png');
		$this->assertFileExists($resizer->getFullSavePath());
		unlink($resizer->getFullSavePath());
	}
	
	public function testExportedGifExists()
	{
		$resizer = new ImageResizer($this->testJpgImage);
		$resizer->export('public', FALSE, 'gif');
		$this->assertFileExists($resizer->getFullSavePath());
		unlink($resizer->getFullSavePath());
	}
}