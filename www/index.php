<?php

/** 
 * Numwal--HTTP-Operated Numbered Wallpaper Generator
 * Main Module
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
 */

require '../vendor/autoload.php';
require 'blankpic.php';
require 'wallpaper.php';
require 'responder.php';

/**
 * Fat Free Framework and App Setup
 */
$f3 = \Base::instance();  
$f3->set('app_version', '0.5-WIP');

const DEV_LINKS = [
	'_github_repo' => 'https://github.com/mounaiban/numwal',
	'_x11_colors'=> 'https://www.w3.org/TR/css-color-3/#svg-color',
];

class AboutResponder extends Numwal\Responder
{
	/** 
	 * NOTE: Responder Summaries will be used in lieu of 
	 * class-scope code comments in the main module for now...
	 */
	public const summary = [
		'description' => 'Show application information (such as version).',
		'feature-1' => 'View developer and admin notes.',
		'feature-2' => 'View copyright and licensing information.',
		'protip' => 'This merely displays a pretty version of README.md.',
	];

	function respond($f3, $params)
	{
		$md = \Markdown::instance();
		$text = file_get_contents('README.md');
		echo $md->convert($text);
	}

	static function getLinks($options)
	{
		/**
		* TODO: Find out how to generate URIs with the host
		* and protocol intact
		**/
		$path = static::getPathBase();
		$links = [
			'about' => "/{$path}",
			'_github_repo' => DEV_LINKS['_github_repo'],
			'_x11_colors' => DEV_LINKS['_x11_colors']
		];
		return $links;
	}
}

class BlankPicResponder extends Numwal\Responder
{
	protected const param_pattern = "@size/@color";
	public const summary = [
		"description" => "Return a blank picture of a selected colour.",
		"feature-1" => "Choose from a list of preset sizes.",
		"feature-2" => "Use WxH notation for custom sizes.",
		"feature-3" => "Most X11 colour keywords are supported.",
		"feature-4" => "Use three- or six-digit Hex codes for custom colours.",
	];

	function respond($f3, $params)
	{
		$bp = new Numwal\BlankPic();
		$bp->setSpecs($params['size'], $params['color']);
		header($bp->getHeader());
		echo $bp->getBlankPicBlob();
	}

	static function getLinks($options)
	{
		$base = static::getPathBase();
		$paths = [];
		// Get preset size examples
		// (only available if a valid sizes.json is present in app root)
		$bp = new Numwal\BlankPic();
		$color_example = 'gold';
		$sizes = array_keys($bp->getSizeNames());
		foreach($sizes as $s){
			$key = "{$base}_{$s}_{$color_example}";
			$paths[$key] = "/{$base}/{$s}/{$color_example}";
		}
		// Insert HxW free size example
		$base = static::getPathBase();
		$color_example = 'limegreen';
		$freesize_example = '666x999';
		$key = "{$base}_freesize_{$color_example}";
		$paths[$key] = "/{$base}/{$freesize_example}/{$color_example}"; 
		$paths['_x11_colors'] = DEV_LINKS['_x11_colors'];
		return $paths;
	}

}

class WallpaperResponder extends Numwal\Responder
{
	protected const param_pattern = "@style/@number";
	public const summary = [
		"description" => "Generate numbered wallpapers.",
		"feature-1" => "Choose from a range of styles."
	];
	protected $wallpaper;

	public function __construct()
	{
		$this->wallpaper = new Numwal\Wallpaper();
	}

	public function respond($f3, $params)
	{
		$this->wallpaper->setStyleByName($params['style']); 
		header($this->wallpaper->getHeader());
		echo $this->wallpaper->getWallpaperBlob($params['number']);
	}

	public static function getLinks($options)
	{
		$base = static::getPathBase();
		$wp = new Numwal\Wallpaper();
		$paths = [];
		$style_names = $wp->getStyleNames();
		foreach($style_names as $n){
			$wp->setStyleByName($n);
			$n_last = $wp->max_number;
			// Add link to first wallpaper per style
			$path_first = str_replace('@number', '0', static::param_pattern);
			$path_first = str_replace('@style', $n, $path_first);
			$key_first = "{$base}_{$n}_first";
			$paths[$key_first] = "/{$base}/{$path_first}";
			// Add link to last wallpaper per style
			$path_last = str_replace('@number', $n_last, static::param_pattern);
			$path_last = str_replace('@style', $n, $path_last);
			$key_last = "{$base}_{$n}_last";
			$paths[$key_last] = "/{$base}/{$path_last}";
		}
		return $paths;
	}
}

class HelpResponder extends Numwal\Responder
{
	protected const param_pattern = '@base';	
	public const summary = [
		'description' => 'Review usage information for available features',
		'welcome' => 'Welcome to the wallpaper generator.',
		'features' => 'Navigate to a help resource in links for info.'
	];

	public function respond($f3, $params)
	{
	 	$resps = getResponderInfoByBase();
		$names = array_keys($resps);
		$b = $params['base'];
		if($b==NULL| in_array($b, $names)===FALSE| $b==static::getPathBase()){
			// TODO: Special response for /help/help
			// PROTIP: Base is NULL when /help route is taken
			$msgs = static::summary;
			$paths = static::getLinks([]);
		}
		else{
			$cls = $resps[$b]['class-name'];
			$msgs = $cls::summary;
			$paths = $cls::getLinks([]);
		}
        $debug_lvl = $f3->get('DEBUG');
        if($debug_lvl){
            $msgs['_debug_level'] = $debug_lvl;
        }
		$resp = new JSONResponse($msgs, $paths);
		$resp->respond();
	}

	public static function getLinks($options)
	{
		$paths = [];
		$base = static::getPathBase();
		$pp = static::param_pattern;
		$resps = getResponderInfoByBase();
		$names = array_keys($resps);
		foreach($names as $n){
			$path = str_replace('@base', $n, $pp);
			$paths["help-{$n}"] = "/{$base}/{$path}";
		}
		return $paths;
	}

}

class JSONResponse
{
	/**
	 * JSON-formatted response with one or more messages and 
	 * zero or more links.
	 *
	 * NOTE: This response format is an attempt at providing
	 * feedback that is both human and machine-readable.
	 */
	protected $content_type = "content-type:application/json";
	protected $http_response_code;
	protected $messages;
	protected $links;

	function __construct($messages, $links, $code=405)
	{
		$this->http_response_code = $code;
		$this->messages = $messages;
		$this->suggested_links = $links;
	}
		
	function respond()
	{
		/**
		 * Response format: 
		 *
		 * Messages have a one-word subject which also acts as a JSON key.
		 * Messages are delivered as top-level attributes.
		 * Links are placed in a sub-object named 'links', with their
		 * relation as the key, and the URI or relative path as the value.
		 */
		$msgnames = array_keys($this->messages);
		foreach($msgnames as $mn){
			$out[$mn] = $this->messages[$mn];
		}
		$out['links'] = $this->suggested_links;
		header($this->content_type);
		http_response_code($this->http_response_code);
		echo json_encode($out);
	}
}

function getResponderInfo()
{
	/**
	 * Return an array containing information on the Responders in
	 * this main module.
	 * 
	 * Format:
	 * The array contains top-level sub-arrays, each containing 
	 * information about one responder, with the *name of the class* as
	 * the key.
	 *
	 * Each sub-array in turn contains elements that represent various
	 * properties of the Responder, such as path bases and routing
	 * patterns.
	 *
	 * This method uses introspection/reflection to search for
	 * subclasses of the Responder class in this module.
	 */
	$resps = [];
	$cls_names = get_declared_classes();
	foreach($cls_names as $cls){
		if(get_parent_class($cls) == "Numwal\Responder"){
			// PROTIP: Here's the format of the Responder information 
			// in greater detail.
			$info = [
				'base' => $cls::getPathBase(),
				'route-pattern' => $cls::getF3RoutePattern()
			];
			$resps[$cls] = $info;
		}
	}
	return $resps;
}

function getResponderInfoByBase()
{
	/**
	 * Return an array containing information on the Responders in
	 * this main module. This is a variation of getResponderInfo(),
	 * except that responders are organised by their path base rather
	 * than class name.
	 *
	 * Format:
	 * Top-level contains sub-arrays, each containing information about one
	 * responder, with the *path base* as the key.
	 *
	 * Each sub-array in turn contains elements that represent various
	 * responder information.
	 *
	 */
	$resps = [];
	$cls_names = get_declared_classes();
	foreach($cls_names as $cls){
		if(get_parent_class($cls) == "Numwal\Responder"){
			$info = [
				'class-name' => $cls,
				'route-pattern' => $cls::getF3RoutePattern()
			];
			$resps[$cls::getPathBase()] = $info;
		}
	}
	return $resps;
}

function appSetup($f3)
{
	$resps = getResponderInfo();
	$cls_names = array_keys($resps);
	foreach($cls_names as $cls){
			$fn_name = "{$cls}->respond";
			$pattern = $resps[$cls]['route-pattern'];
			$f3->route($pattern, $fn_name);
		}
	$f3->route("GET @default: /@base*","HelpResponder->respond");
	$f3->route("GET @index: /","HelpResponder->respond");
}

/**
 *  Start the app.
 */
appSetup($f3);
$f3->set('DEBUG', intval(getenv('NUMWAL_DEBUG')));
$f3->run();

?>
