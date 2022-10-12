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
$f3->set('app_version', '0.6-WIP');

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

	static function getLinks($f3, $options)
	{
		$path = static::getPathBase();
		$links = [
			'about' => static::getFullURI($f3, $path),
			'_github_repo' => DEV_LINKS['_github_repo'],
			'_x11_colors' => DEV_LINKS['_x11_colors']
		];
		return $links;
	}

    static function getConstraints()
    {
        return;
    }
}

class BlankPicResponder extends Numwal\Responder
{
	protected const param_pattern = "@size/@color";
	public const summary = [
		"description" => "Return a blank picture of a selected colour.",
		"feature-1" => "Check links for preset sizes.",
		"feature-2" => "Use WxH notation for custom sizes.",
		"feature-3" => "Most X11 colour keywords are supported.",
		"feature-4" => "Use three- or six-digit Hex codes for custom colours.",
	];

	function respond($f3, $params)
	{
        try
        {
            $bp = new Numwal\BlankPic();
            $bp->setSpecs($params['size'], $params['color']);
            header($bp->getHeader());
            echo $bp->getBlankPicBlob();
        }
        catch (Exception $e)
        {
            $params['error-message'] = $e->getMessage();
            $params['base'] = static::getPathBase();
            $resp_err = new HelpResponder();
            $resp_err->respond($f3, $params);
        }
	}

	static function getLinks($f3, $options)
	{
		$base = static::getPathBase();
		$uris = [];
		// Get preset size examples
		// (only available if a valid sizes.json is present in app root)
		$bp = new Numwal\BlankPic();
		$sizes = array_keys($bp->getSizeNames());
		// Insert HxW free size example
		$base = static::getPathBase();
		$uris['blankpic_custom_3_digit'] = static::getFullURI($f3, "{$base}/666x999/abc");
		$uris['blankpic_custom_6_digit'] = static::getFullURI($f3, "{$base}/666x999/ddeeff");
		$color_example = 'gold';
		foreach($sizes as $s){
			$key = "{$base}_{$s}_{$color_example}";
            $path = "{$base}/{$s}/{$color_example}";
			$uris[$key] = static::getFullURI($f3, $path);
		}
		$uris['_x11_colors'] = DEV_LINKS['_x11_colors'];
		return $uris;
	}

    public static function getConstraints()
    {
        $c = [];
        $c['color-custom-pcre'] = ['[0-9a-fA-F]{3}', '[0-9a-fA-F]{6}'];
        return $c;
    }

}

class WallpaperResponder extends Numwal\Responder
{
	protected const param_pattern = "@style/@number";
	public const summary = [
		"description" => "Generate numbered wallpapers.",
		"feature-1" => "Choose from a range of styles."
	];
	protected static $style_info = [];
    protected $wallpaper;

	public function __construct()
	{
		$this->wallpaper = new Numwal\Wallpaper();
	}

	public function respond($f3, $params)
	{
        try
        {
            $this->wallpaper->setStyleByName($params['style']);
            header($this->wallpaper->getHeader());
            echo $this->wallpaper->getWallpaperBlob($params['number']);
        }
        catch (Exception $e)
        {
            $params['error-message'] = $e->getMessage();
            $params['base'] = static::getPathBase();
            $resp_err = new HelpResponder();
            $resp_err->respond($f3, $params);
        }
	}

    protected static function dumpStyles()
    {
        $wp = new Numwal\Wallpaper();
        $styles = [];
        foreach($wp->getStyleNames() as $n){
            $wp->setStyleByName($n);
            $info = [];
            $info['max-digits'] = $wp->max_digits;
            $styles[$n] = $info;
        }
        return $styles;
    }

	public static function getLinks($f3, $options)
	{
        if(!static::$style_info){
            static::$style_info = static::dumpStyles();
        }
		$uris = [];
		foreach(array_keys(static::$style_info) as $sname){
			// Add link to first wallpaper per style
            $base = static::getPathBase();
			$path_1 = str_replace('@number', '0', static::param_pattern);
			$path_1= str_replace('@style', $sname, $path_1);
			$key_first = "{$sname}_wp-zero";
			$uris[$key_first] = static::getFullURI($f3, "{$base}/{$path_1}");
		}
		return $uris;
	}

    public static function getConstraints()
    {
        if(!static::$style_info){
            static::$style_info = static::dumpStyles();
        }
        $out = [];
        foreach(array_keys(static::$style_info) as $sname){
            foreach(array_keys(static::$style_info[$sname]) as $k){
                $out["{$sname}_{$k}"] = static::$style_info[$sname][$k];
            }
        }
        return $out;
    }
}

class HelpResponder extends Numwal\Responder
{
	protected const param_pattern = '@feature';
	public const summary = [
		'description' => 'Review of app features and usage information.',
		'feature-1' => 'Welcome to the wallpaper generator.',
		'feature-2' => 'Follow links for usage info.'
	];

	public function respond($f3, $params)
	{
        $resps = getResponderInfo($by_base=true);
        $names = array_keys($resps);
		$b = $params['feature'];
        $cls;
		if($b==NULL| in_array($b, $names)===FALSE| $b==static::getPathBase()){
            $cls = get_called_class();
			// Special response for /help/help
			// PROTIP: Base is NULL when /help route is taken
			$msgs = static::summary;
			$uris = static::getLinks($f3, []);
            $usage = [];
		}
		else{
			$cls = $resps[$b]['class-name'];
			$msgs = $cls::summary;
			$uris = $cls::getLinks($f3, []);
            $usage = $cls::getConstraints();
		}
        $err = $params['error-message'];
        if($err){
            $msgs['error-message'] = $err;
        }
        $debug_lvl = $f3->get('DEBUG');
        if($debug_lvl){
            $msgs['_debug_level'] = $debug_lvl;
        }
        $msgs['uri-format'] = $cls::getFullURIPattern($f3);
		$jr = new JSONResponse($msgs, $uris, $usage);
		$jr->respond();
	}

	public static function getLinks($f3, $options)
	{
		$uris = [];
		$base = static::getPathBase();
		$pp = static::param_pattern;
		$resps = getResponderInfo($by_base=true);
		$names = array_keys($resps);
		foreach($names as $n){
			$path = str_replace('@feature', $n, $pp);
			$uris["help-{$n}"] = static::getFullURI($f3, "{$base}/{$path}");
		}
		return $uris;
	}

    public static function getConstraints()
    {
        return;
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
	protected $suggested_links;
    protected $constraints;

	function __construct($messages, $links, $constraints, $code=405)
	{
		$this->http_response_code = $code;
		$this->messages = $messages;
		$this->suggested_links = $links;
        $this->constraints = $constraints;
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
        $u = $this->constraints;
        if($u){
            $out['constraints'] = $u;
        }
		header($this->content_type);
		http_response_code($this->http_response_code);
		echo json_encode($out);
	}
}

function getResponderInfo($by_base=false)
{
	/**
	 * Return an array containing information on the Responders in
	 * this main module.
	 *
	 * Format (default):
     * [
     *    ResponderClassName => ['class-name' => 'name', ... ],
     *    ...
     * ]
     *
	 * Format (By Base):
     * [
     *    responderBaseName => ['class-name' => 'name', ... ],
     *    ...
     * ]
     *
	 * The array contains sub-arrays containing information about one
     * responde each. Each sub-array's key is either the class name,
     * or the path base name if by_base is true.
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
				'class-name' => $cls,
				'base' => $cls::getPathBase(),
				'route-pattern' => $cls::getF3RoutePattern()
			];
            if($by_base) $resps[$cls::getPathBase()] = $info;
            else $resps[$cls] = $info;
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
	$f3->route("GET @default: /@feature*","HelpResponder->respond");
	$f3->route("GET @index: /","HelpResponder->respond");
}

/**
 *  Start the app.
 */
appSetup($f3);
$f3->set('DEBUG', intval(getenv('NUMWAL_DEBUG')));
$f3->run();

?>
