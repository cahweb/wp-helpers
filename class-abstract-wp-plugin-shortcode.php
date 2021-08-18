<?php
/**
 * Abstract WordPress Shortcode Plugin Base Class
 *
 * Class providing some basic boilerplate for a basic WordPress plugin, with methods
 * meant to be overridden by child classes as needed to encompass specific functionality.
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

abstract class WPShortcodePlugin extends WPPlugin
{
    /**
     * The handle to attach scripts and other things to. Should be overridden in
     * subclass. Use hyphens as separators.
     *
     * @var string
     */
    protected static $handle = "my-plugin-handle";

    /**
     * The shortcode to register with WordPress. Should be overridden in subclass.
     * Use hyphens as separators, rather than underscores.
     *
     * @var string
     */
    protected static $shortcode = "my-shortcode";

    /**
     * The URI of the plugin folder on the site. Should be overridden in subclass
     * and replaced with manually-entered string or plugin constant.
     *
     * @var string
     */
    protected static $pluginUri = "";

    /**
     * The file path of the plugin folder on the server. Should be overridden in
     * subclass and replaced with manually-entered string or plugin constant.
     *
     * @var string
     */
    protected static $pluginPath = "";

    /**
     * Sets up actions to register and conditionally enqueue the plugin's JavaScript,
     * as well as registers the plugin's shortcode and callback function. Can be
     * overridden in child classes to add additional or alternate functionality.
     *
     * @return void
     */
    public static function setup()
    {
        add_action('wp_enqueue_scripts', [__CLASS__, 'registerScripts'], 5, 0);
        add_action('wp_enqueue_scripts', [__CLASS__, 'maybeLoadScripts'], 10, 0);
        
        add_shortcode(self::$shortcode, [__CLASS__, 'shortcode']);
    }

    /**
     * Registers the plugin's JavaScript file, so we can enqueue it later if our
     * shortcode is present.
     *
     * @return void
     */
    public static function registerScripts()
    {
        $uri  = self::$pluginUri  . "/js";
        $path = self::$pluginPath . "/js";

        $handle = self::$handle;

        wp_register_script(
            "$handle-script",
            "$uri/$handle.js",
            [],
            filemtime("$path/$handle.js"),
            true
        );
    }

    /**
     * Checks the post content (before "the_content" filter is applied) to see
     * if our shortcode is present, and enqueues our JavaScript if so.
     *
     * @return void
     */
    public static function maybeLoadScripts()
    {
        global $post;
        if (!isset($post) || !is_object($post)) {
            return;
        }
        
        $handle = self::$handle;
        $shortcode = self::$shortcode;

        if (stripos($post->post_content, "[$shortcode") !== false) {
            wp_enqueue_script("$handle-script");

            if (wp_style_is("$handle-style", 'registered')) {
                wp_enqueue_style("$handle-style");
            }
        }
    }

    /**
     * Generates the HTML output for the shortcode. Use shortcode_atts()
     * function to set default values for any attributes you may need.
     *
     * @param array $atts  Any attributes included with the shortcode.
     *
     * @return void
     */
    abstract public static function shortcode(array $atts = []) : string;
}
