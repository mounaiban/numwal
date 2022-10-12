<?php
/** 
 * Numwal--HTTP-Operated Numbered Wallpaper Generator
 * Wallpaper Class 
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

use Imagick;
use ImagickDraw;
use ImagickPixel;
use Exception;

class Wallpaper
{
	/**
	 * The drawing object uses pixel objects to draw
	 * while the canvas object retains the results of a
	 * drawing object 
	 */
	protected $background_image;
	protected $canvas;
	protected $draw;
	protected $num_x = 32; // position of number
	protected $num_y = 32; // on wallpaper
    protected $font_size = 36;
    protected $font_size_min = 12;
    protected $font_dsf = 0; // digit shrink factor
    protected $font_dcsf = 0;
	protected $px_fill; 
	protected $styles_dir = 'styles';
	protected $style_filename = 'style.json';
	protected $max_digits_default = 2;
	public $format = 'png';
	public $max_digits = 2;

	public $messages = [
		"number-too-large" => "Number requested has too many digits",
        "unsupported-digits" => "Only ASCII printable charaters are supported",
	];
	
	public function __construct()
	{
		$this->background_image = new Imagick();
		$this->canvas = new Imagick(); 
		$this->draw = new ImagickDraw();  
		$this->px_fill = new ImagickPixel(); 
	}

	protected function compose($number)
	{
		/**
		 * Set up the canvas and draw the number on it.
		 * NOTE: This method is intended to be run only after
		 * the style has been set using setStyle().
		 */
		// TODO: Handle case where setStyle() has not been run
        $shrink = $this->font_dsf * (strlen($number)-1);
        $fsize = $this->font_size - $this->font_size * $shrink;
        if($fsize < $this->font_size_min){
            $fsize = $this->font_size_min;
        }
        $this->draw->setFontSize($fsize);
		$this->draw->annotation($this->num_x, $this->num_y, $number);
		$this->canvas->drawImage($this->draw);
	}

	protected function buildPathList($style_fqname)
	{
		/**
		 * Returns an array of paths to the different style sheets
		 * in a style chain. The default style sheet appears first,
		 * followed by the master sheet, then followed by its
		 * derivative substyle sheet.
		 *
		 * PROTIP: To allow substyles to inherit from master styles,
		 * and in turn, inherit from the default style, all relevant
		 * style sheets must be read.
		 */
		$style_fqparts = explode('-', $style_fqname);
		$dir = $this->styles_dir;
		$file = $this->style_filename;
		$path_default = "{$dir}/default/{$file}";
		$paths = [$path_default,];
		for($n=1; $n<=count($style_fqparts); $n++){
			$sc_inter = array_slice($style_fqparts,0,$n);
			$sc_name = implode('-',$sc_inter);
			$path = "{$dir}/{$sc_name}/{$file}";
			array_push($paths, $path);
		}		
		return $paths;
	}

    protected static function handleError($n, $s, $file, $line, $context)
    {
        restore_error_handler();
        if(strpos($s, 'No such file')){
            $path = explode(DIRECTORY_SEPARATOR, $s);
            throw new Exception("Wallpaper style not found: {$path[1]}");
        }
    }

	public function loadStyle($style_fqname)
	{
		/**
		 * Read style sheet files and return an array which 
		 * contains the complete style definitions.
		 *
		 * TODO: Document the CSS-like (but not quite) format
		 * in use here.
		 */
		$style_data = [];
		$paths = $this->buildPathList($style_fqname);
        set_error_handler('static::handleError');
		foreach($paths as $path){
			$str_data = file_get_contents($path);
			$temp = json_decode($str_data, TRUE, 3);
			/**
			 * Level 0: Style Sheet Selectors
			 */
			$selectors = array_keys($temp);
			foreach($selectors as $s){
				$properties = array_keys($temp[$s]);
				foreach($properties as $pr){
					if(strrpos($pr, '-file', -1) !== FALSE){
							/**
							 * Assume that properties whose key ends with
							 * '-file' is a filename, and convert it to an
							 * absolute path.
							 *
							 * PROTIP: The directory is derived from the
							 * path, with the number of characters equal to the
							 * style sheet filename plus the last directory
							 * separator (e.g. '/' or '\'), cut off from its end.
							 */
							$filename = $temp[$s][$pr];
							$len = -(strlen($this->style_filename)+1);
							$dir = substr($path, 0, $len);
							$p_path = "{$dir}/{$filename}";
							$style_data[$s][$pr] = $p_path;
					}
					else{
							$style_data[$s][$pr] = $temp[$s][$pr];
					}
				}
			}
		}
        restore_error_handler();
		return $style_data;
	}

	public function setStyle($style_data)
	{
		/**
		 * Apply settings from a style sheet to the current wallpaper
		 */
		$sel_names = array_keys($style_data);
		foreach($sel_names as $sn){
			/**
			 * Delegate style setting to methods that handle one
			 * selector each.
			 *
			 * PROTIP: The handler method is set____Style, where ____
			 * is the name of the selector with the first letter capitalised.
			 * 'hair' => 'setHairStyle'
			 */
			$sn_ucf = ucfirst($sn);
			$fn = "set{$sn_ucf}Style";
			$this->$fn($style_data[$sn]);
		}
		return;
	}

	public function setStyleByName($style_fqname)
	{
		$style_data = $this->loadStyle($style_fqname);
		$this->setStyle($style_data);
	}

	protected function setWallpaperStyle($style_props)
	{
		// Set background colour
		$background_color = $style_props['background-color'];
		$this->px_fill->setColor($background_color);

		// Set wallpaper background colour and size
		$width = $style_props['width-px'];
		$height = $style_props['height-px'];
		$this->canvas->newImage($width, $height, $this->px_fill);

		// Set background image
		$bgfile = $style_props['background-image-file'];
		if($bgfile != NULL){
			$this->background_image->readImage($bgfile);
			// Crop background image area according to specs
			$crop_x = $style_props['background-crop-left-px'] ?? 0;
			$crop_y = $style_props['background-crop-top-px'] ?? 0;
			$crop_w = $style_props['background-crop-width-px'] ?? $width;
			$crop_h = $style_props['background-crop-height-px'] ?? $height;
			$this->background_image->cropImage($crop_w,$crop_h,$crop_x,$crop_y);
			// Scale background image
			$this->background_image->scaleImage($width, $height);
			// Paste background image back into canvas
			$mode = Imagick::COMPOSITE_ATOP;
			$this->canvas->compositeImage($this->background_image,$mode,0,0);
			$this->format = $this->background_image->getImageFormat();
		}

		// Set image format (PROTIP: match format of background image)
		$this->canvas->setImageFormat($this->format);
	}

	protected function setNumberStyle($style_props)
	{
		$maxd = $style_props['max-digits'];
		if($maxd !== NULL){
			$this->max_digits = $maxd;
		}
		else{
			$this->max_digits = $this->max_digits_default;
		}

		// Set font colour
		$this->px_fill->setColor($style_props['color']);
		$this->draw->setFillColor($this->px_fill);
		$this->draw->setTextUnderColor($style_props['background-color']);

		// Set font (only point size is supported)
		// TODO: Font-setting fallback mechanism could really be improved.
        // TODO: Clean up this section, storing style data in object
        //  from setStyleByName() likely needed.
		if($style_props['font-family'] != NULL){
			$this->draw->setFontFamily($style_props['font-family']);
		}
		elseif($style_props['font-file'] != NULL){
			$this->draw->setFont($style_props['font-file']);
		}
        if($style_props['font-dsf']){
            $this->font_dsf = $style_props['font-dsf'];
        }
        if($style_props['font-size-min-pt']){
            $this->font_size_min = $style_props['font-size-min-pt'];
        }
		$this->draw->setFontWeight($style_props['im-font-weight']);
        $this->font_size = $style_props['font-size-pt'];

		// Set position
		$this->draw->setGravity($style_props['im-gravity']);
		$this->draw->setTextAlignment(Imagick::ALIGN_CENTER);
		$this->num_x = $style_props['left-px']; 
		$this->num_y = $style_props['top-px'];
	}

	public function getStyleNames()
	{
		/**
		 * Find directories that may contain style sheets
		 * Returns a list of directories under the styles directory that
		 * have a filename equivalent to $this->style_filename
		 */
		$styles = [];
		$styles_realpath = realpath($this->styles_dir);
		$original_dir = getcwd();
		try{
			chdir($styles_realpath);
			$dirs = scandir('.');
			foreach($dirs as $d){
				if(is_dir($d) === TRUE){
					$contents = scandir($d);
					if(in_array($this->style_filename, $contents)){
						array_push($styles, $d);
					}
				}
			}
		}
		catch(Exception $e){
			// pass; # Nothing here yet
		}
		finally{
			chdir($original_dir); 
			/**
			 * This try block automatically resets the PHP working directory
			 * to the one set before this method was run.
			 * 
			 * TODO: The use of chdir() feels really dodgy. Maybe rewrite
			 * this method to avoid chdir()?
			 * 
			 * PROTIP: The effects of chdir() persist until the script stops.
			 * Routines that use chdir() may leave PHP pointing at the wrong working
			 * directory if they encounter difficulties (e.g. exceptions) causing
			 * mysterious bugs if there is no mitigation.
			 */
		}
		return $styles;
	}

	public function getWallpaperBlob($number)
	{
		/**
		 * Initiates image composition and returns the composed
		 * image in a PHP binary string
		 */
        for($i=0; $i<strlen($number); $i++){
            if(ord($number[$i]) < 32 or ord($number[$i]) > 126){
                throw new Exception($this->messages['unsupported-digits']);
            }
        }
		if(strlen($number) > $this->max_digits){
            throw new Exception($this->messages['number-too-large']);
		}
		else{
			$this->compose($number);
		}
		$this->canvas->setImageFormat($this->format);
		return $this->canvas->getImageBlob();
	}

	public function getHeader()
	{
		$header = "content-type:image/{$this->canvas->getImageFormat()}";
		return $header;
	}
}

?>
