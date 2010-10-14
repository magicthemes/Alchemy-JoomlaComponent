<?php
/**
 * Alchemy Theme Framework
 *
 * Features:
 *      * LessCSS development mode
 *      * Full support for HTML5
 *      * IE 8 below Partial support for CSS3 through PIE
 *      * Very Simple Helpers(for now)
 *
 * This file Contains the basic bootstrap helpers for Alchemy - this shouldn't change much
 *
 * @package Alchemy
 * @author Israel D. Canasa
 * @copyright (c) 2010 MagicThemes.com - Wiz Media, Inc
 * @license http://www.gnu.org/licenses/gpl-2.0.html 
 */
class Alchemy
{
	protected static $_root;
	protected static $_system;
	protected static $_context;
	protected static $_versions;
	protected static $_classes = array();
	
	/**
	 * Initialize Alchemy and sets up the version stack.  
	 * 		- Alchemy uses a Cascading Version System(CVS) for absolute compatibility with older versions
	 *			- The CVS makes it absolutely sure that a template will use the version it was built on.
	 *			- It means that upgrading Alchemy won't break anything
	 *		- When a template initializes Alchemy, it can specify the version that it will use.
	 *		- Version number format is YYMMDD which reflect the date of release. This is better than using cryptic version numbers.
	 *
	 * @param string $version 
	 * @return void
	 * @author Israel D. Canasa
	 */
	public static function start($context = NULL, $version = 'latest')
	{
		self::$_context = ($context) ? $context : stdclass;
		
		// Detect the System
        // TODO: One day, this should detect the environment for WordPress, Nooku 1.0 and other systems.
		if (_JEXEC) 
		{
			self::$_root = realpath(dirname(__FILE__)).DIRECTORY_SEPARATOR.'framework';
			$jversion = explode('.', JVERSION);array_pop($jversion);
			self::$_system = 'joomla'.implode('', $jversion);
		}

		self::$_versions = array($version);
		
		// Put the $version on top of the stack
		foreach(scandir(self::$_root.DS.'versions') as $dir) 
		{
			if ($dir == '.' OR $dir == '..') {
				continue;
			}

			if ( ! $version OR (int)$version > (int)$dir) 
			{
				self::$_versions[] = $dir;
			}
		}
		
		rsort(self::$_versions);
	}
	
	/**
	 * Loads, instantiates and returns Alchemy classes
	 *
	 * Class names are converted to file names by making the class name
	 * lowercase and converting underscores to slashes:
	 *
	 *     // Loads versions/[version]/helpers/html.php
	 *     Alchemy::call('helpers/html');
	 *
	 * @param   string   class relative path
	 * @return  object
	 */
	public static function call($class, $parameters = NULL)
	{
		if (isset(self::$_classes[$class])) 
		{
			return self::$_classes[$class];
		}
		
		if ($path = Alchemy::find_file('versions', $class))
		{
			// Load the class file
			require $path;
			
			$classname = 'alchemy_'.str_replace('/', '_', $class);
			
			$instance = new $classname($parameters);
			
			// Stores the instance for reuse and returns it
			return self::$_classes[$class] = $instance;
		}

		// Class is not in the filesystem
		return FALSE;
	}
	
	/**
	 * Load the configuration based on the current system and context
	 *
	 * @param string $key 
	 * @return mixed
	 * @author Israel D. Canasa
	 */
	public static function config($key = NULL)
	{
		static $config;

		if (is_null($config)) 
		{
            // Loads the config driver for the current system
			$config = Alchemy::call('system/'.self::$_system.'/config', self::$_context);
		}

		if ($key) 
		{
			return $config->get($key);
		}

		return $config;
	}

    public static function context()
    {
        return self::$_context;
    }
	
	/**
	 * Finds the path of a file by directory, filename. 
	 * 	- Uses a cascading versioning system. 
	 *	- The version called in Alchemy::start() will be on the top of the stack. 
	 *	- Newer versions will be ignored.
	 *
	 * @param   string   directory name (views, i18n, classes, extensions, etc.)
	 * @param   string   filename with subdirectory
	 * @return  string   single file path
	 */
	public static function find_file($dir, $file, $ext = NULL)
	{
		// Use the php extension by default
		$ext = (is_null($ext)) ? '.php': $ext;

		// Create a partial path of the filename
		$path = $file.$ext;

		if ($dir != 'versions') 
		{
			if (is_file($directory.$path))
			{
				// A path has been found
				return $directory.$path;

			}
		}

		foreach (self::$_versions as $version)
		{
			$directory = self::$_root.DS.$dir.DS.$version.DS;

			if (is_file($directory.$path))
			{
				// A path has been found
				return $directory.$path;
			}
		}

		return FALSE;
	}
}