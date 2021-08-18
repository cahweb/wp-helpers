<?php
/**
 * Abstract WordPress Plugin Base Class for Vue Applications
 *
 * Class providing boilerplate for adding a front-end Vue application as a WordPress
 * plugin. Methods can (and should) be overridden as needed to add/replace functionality.
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

abstract class WPVuePlugin extends WPShortcodePlugin
{
    /**
     * Additional data beyond AJAX URL and a WordPress nonce to send to the front-end
     * JavaScript when the scripts are enqueued. If necessary, should be overridden
     * in subclass.
     *
     * @var array
     */
    protected static $wpData = [];

    // Override
    public static function registerScripts()
    {
        $uri  = self::$pluginUri  . "/dist";
        $path = self::$pluginPath . "/dist";

        $handle = self::$handle;

        // Register our chunk script first
        wp_register_script(
            "$handle-script-chunk",
            "$uri/js/chunk-$handle.js",
            [],
            filemtime("$path/js/chunk-$handle.js"),
            true
        );

        // Register our main script, with the chunk script as a dependency
        wp_register_script(
            "$handle-script",
            "$uri/js/$handle.js",
            ["$handle-script-chunk"],
            filemtime("$path/js/$handle.js"),
            true
        );

        // Register our stylesheet.
        wp_register_style(
            "$handle-style",
            "$uri/css/$handle.css",
            [],
            filemtime("$path/css/$handle.css"),
            'all'
        );
    }

    // Override
    public static function maybeLoadScripts()
    {
        // Run the parent script to enqueue the scripts and styles
        parent::maybeLoadScripts();

        $handle = self::$handle;

        // Pass data to the front-end if the script is enqueued.
        if (wp_script_is("$handle-script")) {
            // Create the basic data pretty much every one of these plugins will need
            $baseData = [
                'ajaxurl' => admin_url('admin-ajax.php'),
                '_wpnonce' => wp_create_nonce($handle)
            ];

            // Merge with any custom data the user has defined in self::$wpData
            $localizeData = array_merge($baseData, self::getWpData());

            // Send the data
            wp_localize_script("$handle-script", "wpData", $localizeData);
        }
    }

    // Override
    public static function shortcode(array $atts = []): string
    {
        ob_start();
        ?>
        <div id="<?= self::$handle ?>-app"></div>
        <?php
        return ob_get_clean();
    }

    /**
     * Wrapper for the self::$wpData member variable. Can be overridden in
     * subclass to create more fine-tuned, programmatic values to be passed
     * to the front-end, rather than just overriding the property itself.
     *
     * @return void
     */
    protected static function getWpData(): array
    {
        return self::$wpData;
    }
}
