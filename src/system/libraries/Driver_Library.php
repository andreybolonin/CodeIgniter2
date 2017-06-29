<?php  
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package		CodeIgniter
 * @author		EllisLab Dev Team
 * @copyright	Copyright (c) 2006 - 2012, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://codeigniter.com
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

namespace Codeigniter2\system\libraries;

/**
 * CodeIgniter Driver Library Class
 *
 * This class enables you to create "Driver" libraries that add runtime ability
 * to extend the capabilities of a class via additional driver objects
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Libraries
 * @author		EllisLab Dev Team
 * @link
 */
class Driver_Library {

	protected $valid_drivers	= array();
	protected $lib_name;

	// The first time a child is used it won't exist, so we instantiate it
	// subsequents calls will go straight to the proper child.
	function __get($child)
	{
		if ( ! isset($this->lib_name))
		{
			$this->lib_name = get_class($this);
		}

		// The class will be prefixed with the parent lib
		$child_class = $this->lib_name.'_'.$child;
	
		// Remove the CI_ prefix and lowercase
		$lib_name = ucfirst(strtolower(str_replace('CI_', '', $this->lib_name)));
		$driver_name = strtolower(str_replace('CI_', '', $child_class));
		
		if (in_array($driver_name, array_map('strtolower', $this->valid_drivers)))
		{
			// check and see if the driver is in a separate file
			if ( ! class_exists($child_class))
			{
				// check application path first
				foreach (get_instance()->load->get_package_paths(TRUE) as $path)
				{
					// loves me some nesting!
					foreach (array(ucfirst($driver_name), $driver_name) as $class)
					{
						$filepath = $path.'libraries/'.$lib_name.'/drivers/'.$class.'.php';

						if (file_exists($filepath))
						{
							include_once $filepath;
							break;
						}
					}
				}

				// it's a valid driver, but the file simply can't be found
				if ( ! class_exists($child_class))
				{
					log_message('error', "Unable to load the requested driver: ".$child_class);
					show_error("Unable to load the requested driver: ".$child_class);
				}
			}

			$obj = new $child_class;
			$obj->decorate($this);
			$this->$child = $obj;
			return $this->$child;
		}

		// The requested driver isn't valid!
		log_message('error', "Invalid driver requested: ".$child_class);
		show_error("Invalid driver requested: ".$child_class);
	}

	// --------------------------------------------------------------------

}
// END CI_Driver_Library CLASS


