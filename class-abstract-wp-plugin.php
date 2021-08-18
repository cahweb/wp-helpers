<?php
/**
 * Abstract WordPress Plugin Base Class
 *
 * Class providing some basic function signatures for setting up a WordPress Plugin.
 *
 * PHP Version 7
 *
 * @category UCF\CAH
 * @package  Lib\WordPress
 * @author   Mike W. Leavitt <michael.leavitt@ucf.edu>
 * @version  SVN: 1.0.0
 * @license  GNU General Public License, v3.0 (https://opensource.org/licenses/GPL-3.0)
 * @link     https://cah.ucf.edu/
 */
declare(strict_types = 1);

namespace UCF\CAH\Lib\WordPress;

abstract class WPPlugin
{
    abstract public static function setup();
    abstract public static function registerScripts();
    abstract public static function maybeLoadScripts();
}
