<?php
/**
 * Numwal--HTTP-Operated Numbered Wallpaper Generator
 * Responder Class
 *
 * Copyright 2020 Mounaiban
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

namespace Numwal;

abstract class Responder
{
	/**
	* The Responder class contains information and methods on how to
	* respond to requests sent to specific routes.
	* 
	* PROTIP: Responder classes must have a suffix 'Responder' to
	* work correctly.
	*
	* PROTIP: Responders in NumJ are analogues to Controllers in the
	* Model-View-Controller (MVC) style architecture, but are closer
	* in implementation to Resources of the Resource-Method-Representation
	* (RMR) model. NumJ is more RMR than MVC, but isn't strictly either.
	*/
	protected $f3;
	protected const param_pattern = NULL; // parameter pattern, see below
	public const summary = 'No summary';

	abstract public static function getLinks($options);
	/* Get a list of suggested links in response to an invalid request or
	 * an explicit request for usage hints.
	 */

	abstract public function respond($f3, $params);

	public static function getPathBase()
	{
		/** 
		 * Return the first part of the URI path. This is equivalent to
		 * the name of the responder class in lowercase, with the 
		 * 'Responder' suffix removed.
		 *
		 * Example: TickleResponder has a path base of /tickle.
		 *
		 * TODO: Handle cases where the 'Responder' suffix wasn't applied
		 * to the names of Responder subclasses.
		 */
		$name = get_called_class();
		$len = strrpos($name, 'Responder');
		$path_base = substr($name, 0, $len);
		/* Due to limitations, the ltrim() function doesn't work in
		 * this case. Instead, the position of the suffix is discovered
		 * and the name truncated at this position.
		 */
		return strtolower($path_base);
	}

	public static function getF3RoutePattern()
	{
		/**
		 * Return the complete routing pattern for use with the F3
		 * routing engine. Route patterns are derived by joining
		 * the path base with the parameter pattern.
		 *
		 * Example: TickleResponder with a parameter pattern of
		 * @strength/@length will have a route pattern of
		 * /tickle/@strength/@length.
		 *
		 * NOTE: Only GET routes are supported at this time.
		 */
		$path_base = static::getPathBase();
		$pp = static::param_pattern;
		if($pp != NULL){
			return "GET @{$path_base}: /{$path_base}/{$pp}";
		}
		else{
			return "GET @{$path_base}: /{$path_base}";
		}
	}
}

?>
