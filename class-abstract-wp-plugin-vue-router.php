<?php
/**
 * Abstract WordPress Plugin Base Class for Vue Router Applications
 *
 * Class providing boilerplate for adding a front-end Vue application as a WordPress
 * plugin that utilizes the Vue Router extension. Methods can (and should) be overridden
 * as needed to add/replace functionality.
 *
 * PHP Version 7
 *
 * @category UCF\CAH
 * @package  Lib\WordPress
 * @author   Mike W. Leavitt <michael.leavitt@ucf.edu>
 * @version  SVN: 1.0.0
 * @license  GNU Lesser General Public License, v3.0 (https://opensource.org/licenses/LGPL-3.0)
 * @link     https://cah.ucf.edu/
 */
declare(strict_types = 1);

namespace UCF\CAH\Lib\WordPress;

abstract class WPVueRouterPlugin extends WPVuePlugin
{
    /**
     * The path to the template file you want to use to replace the 404 template.
     * Should be overridden in subclass.
     *
     * @var string
     */
    protected static $templateFile = "my-template.php";

    /**
     * The slug for your page that will run on Vue Router. Should be overridden
     * in subclass.
     *
     * @var string
     */
    protected static $pageSlug = "my-page-slug";

    // Override
    public static function setup()
    {
        parent::setup();

        add_action('template_redirect', [__CLASS__, 'handle404'], 10, 0);
        add_action('template_include', [__CLASS__, 'setTemplate']);
        add_action('the_title', [__CLASS__, 'changeTitle']);
    }

    /**
     * Checks to see if we've got a 404 request, and if we do, checks to see
     * if it's for a subpage of our Vue Router application. If so, it hijacks
     * the $post and replaces it with our base Vue Router post data, so the
     * Vue scripts will enqueue properly.
     *
     * @return void
     */
    public static function handle404()
    {
        if (!is_404()) {
            return;
        }

        $slug = self::$pageSlug;

        if (stripos($_SERVER['REQUEST_URI'], "/$slug/") !== false) {
            global $post;

            $args = [
                'name'        => $slug,
                'post_type'   => 'page',
                'post_status' => 'publish',
                'numberpost'  => 1,
            ];

            $results = get_posts($args);

            if ($results) {
                $post = $results[0];
            }
        }
    }

    /**
     * Replaces the 404 template with our custom template, or a template in
     * the current theme directory.
     *
     * @param string $template  The current template file path.
     *
     * @return string
     */
    public static function setTemplate(string $template): string
    {
        global $post;

        $slug = self::$pageSlug;

        if ($slug === $post->post_name
            && (is_404()
                || stripos($template, "page-$slug.php") === false
                || !file_exists(get_stylesheet_directory() . "/page-$slug.php")
            )
        ) {
            $template = self::$templateFile;
        }

        return $template;
    }

    /**
     * Changes the displayed title from "404 Not Found" to the title of the Vue
     * Router application.
     *
     * @param string $title  The current title
     *
     * @return string
     */
    public static function changeTitle(string $title): string
    {
        global $post;

        if (is_404() && self::$pageSlug === $post->post_name) {
            return $post->post_title;
        }

        return $title;
    }

    // Override
    protected static function getWpData(): array
    {
        global $post;

        $data = [
            'baseUrl' => self::getFullSlug($post)
        ];

        self::$wpData = array_merge(self::$wpData, $data);

        return self::$wpData;
    }

    /**
     * Given a WP_Post object, recursively finds its root parent's slug and
     * assembles a full URI beneath the WordPress site's hostname.
     *
     * @param \WP_Post $post  The current post object.
     * @param string   $slug  The slug we've assembled so far, if any.
     *
     * @return string
     */
    final protected static function getFullSlug(\WP_Post &$post, string $slug = ""): string
    {
        $currentSlug = !empty($slug) ? "$post->post_name/$slug" : $post->post_name;

        if ($post->post_parent === 0) {
            return $currentSlug;
        } else {
            return self::getFullSlug($post, $currentSlug);
        }
    }
}
