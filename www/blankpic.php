<?php
/**
 * Numwal--HTTP-Operated Numbered Wallpaper Generator
 * Blank Picture Class
 *
 * Copyright 2020-2022 Mounaiban
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 *
 * NOTE: This module uses ImageMagick via the Imagick wrapper.
 * Please install both of them in order to use it.
 * ImageMagick should be available from the operating system's
 * package manager, while Imagick may be installed from the
 * OS's package manager or the PHP Extension Community Library.
 *
 */
namespace Numwal;

use Exception;
use Imagick;
use ImagickDraw;
use ImagickPixel;
use ImagickPixelException;

class BlankPic
{
	/**
	 * PROTIP: To oversimplify things, Imagick objects are
	 * like canvases, while ImagickDraw objects output results
	 * on them. ImagickDraw objects often use ImagickPixel
	 * objects to output their results onto the canvases.
	 */
	protected $canvas;
	protected $draw;
	protected $px_fill;
	protected $px;
	protected $size_list_file = 'sizes.json';
	public $format = 'png';
	
	public function __construct()
	{
		/**
		 * PROTIP: PHP (as of 7.x) cannot set class constants
		 * that cannot be resolved at compile time. Creating
		 * Imagick objects are only possible after compiling.
		 * As a workaround, Imagick-related objects are only
		 * initialised during object construction.
		 * See StackOverflow Question #40171546
		 */
		$this->canvas = new Imagick();
	}

	public function getBlankPicBlob()
	{
		return $this->canvas->getImageBlob();
	}

	public function getSizeNames()
	{
		return json_decode(file_get_contents($this->size_list_file), TRUE);
	}

	public function getHeader()
	{
		/**
		 * Get strings needed to set the content type HTTP
		 * header attribute, to prepare receiving applicaions
		 * for receiving images of the appropriate format.
		 *
		 * PROTIP: This method is meant to be run only after
		 * setSpecs() has been run at least once.
		 */
		$format = $this->canvas->getImageFormat();
		return "content-type: image/{$format}";
	}

	public function setSpecs($size, $color)
	{
		$sizes = $this->getSizeNames();
		$preset = $sizes[$size];
		/**
		 * Interpret size param as a WxH specification
		 * if size name isn't found; e.g. "999x666"
		 * will be read as: $width=999; $height=666;
         * Both upper and lowercase 'x' are supported.
		 */
		if($preset == NULL){
			$s = explode('x',$size);
            if(count($s) <= 1){
                $s = explode('X',$size);
                if(count($s) <= 1){
                    throw new Exception('Unrecognised custom size');
                }
            }
			$width = $s[0];
			$height = $s[1];
			// PROTIP: anything after a second x will be ignored
		}
		else{
			$width = $preset[0];
			$height = $preset[1];
		}
        try
        {
            $this->px = new ImagickPixel($color);
        }
        catch (ImagickPixelException $e)
        {
            // attempt to interpret colour as hex code
            $slen = strlen($color);
            if($slen != 3 and $slen != 6) {
                throw new Exception('Invalid colour or RGB code');
            }
            elseif(strspn($color, 'abcdef0123456789') != $slen) {
                throw new Exception('Invalid or unsupported RGB hex code');
            }
			$hxcolor = "#{$color}";
			$this->px = new ImagickPixel($hxcolor);
        }
		$this->canvas->newImage($width, $height, $this->px);
		$this->canvas->setImageFormat($this->format);
	}

}

?>
