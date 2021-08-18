<?php
/**
 * Abstract WordPress Custom Post Type Class
 *
 * Class providing some basic boilerplate for a WordPress Custom Post Type, with methods
 * meant to be overridden by child classes as needed to encompass specific functionality.
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

abstract class WPCustomPostType
{
    /**
     * The slug for the post type. Should be overridden in subclass.
     *
     * @var string
     */
    protected static $postTypeSlug = "my-post-type";

    /**
     * The singular and plural labels for the post type, and the text domain. Should
     * be overridden in the subclass.
     *
     * @var array
     */
    protected static $labels = [
        'singular'    => 'My Post',
        'plural'      => 'My Posts',
        'text_domain' => 'my-text-domain',
    ];

    /**
     * A brief description of the post type. Should be overridden in the subclass.
     *
     * @var string
     */
    protected static $postTypeDesc = "A description of my custom post type.";

    /**
     * The post type's position in the Dashboard menu. For a complete list of
     * positions, see
     * https://developer.wordpress.org/reference/functions/register_post_type/#menu_position
     *
     * @var integer
     */
    protected static $menuPosition = 5;

    /**
     * The icon you want to use for the menu. Can be a WordPress Dashicon identifier, the
     * path to an image you want to use, or a base-64 encoded SVG string. For more information,
     * see https://developer.wordpress.org/reference/functions/register_post_type/#menu_icon
     *
     * @var string
     */
    protected static $menuIcon = null;

    /**
     * Registers the post type after generating the arguments using WPCustomPostType::getArgs()
     *
     * @return void
     */
    public static function register()
    {
        register_post_type(self::$postTypeSlug, self::getArgs());
    }

    /**
     * Generates the arguments for registering the post type. Uses static member
     * variables which can be overridden in subclasses.
     *
     * @return array
     */
    protected static function getArgs(): array
    {
        extract(self::$labels);

        $args = [
            'label'               => self::wpString($singular),
            'description'         => self::wpString(self::$postTypeDesc),
            'labels'              => self::getLabels(),
            'supports'            => self::getPostTypeSupports(),
            'taxonomies'          => self::getTaxonomies(),
            'hierarchical'        => false,
            'public'              => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'menu_position'       => self::$menuPosition,
            'menu_icon'           => self::$menuIcon,
            'show_in_admin_bar'   => true,
            'show_in_nav_menus'   => true,
            'can_export'          => true,
            'has_archive'         => true,
            'exclude_from_search' => false,
            'publicly_queryable'  => true,
            'capability_type'     => [
                str_replace(" ", "-", strtolower($singular)),
                str_replace(" ", "-", strtolower($plural)),
            ],
        ];

        // Turn the slug into a tag for registering a custom filter
        $postTag = str_replace("-", "_", self::$postTypeSlug);

        // Run it through a custom filter. Does nothing by default, but allows
        // a user to override the content of $args dynamically in other contexts.
        $args = apply_filters("${postTag}_args", $args);

        // Return the arguments.
        return $args;
    }

    /**
     * Assembles the full list of labels for populating the Dashboard menus having to
     * do with the custom post type, using the labels defined in WPCustomPostType::$labels.
     *
     * @return array
     */
    protected static function getLabels(): array
    {
        // Destructure the labels array, for readability
        extract(self::$labels);

        // Return the array of labels.
        return [
            'name'                  => self::wpString($plural, 'Post Type General Name'),
            'singular_name'         => self::wpString($singular, 'Post Type Singular Name'),
            'menu_name'             => self::wpString($plural),
            'name_admin_bar'        => self::wpString($singular),
            'archives'              => self::wpString("$singular Archives"),
            'parent_item_colon'     => self::wpString("Parent $singular:"),
            'all_items'             => self::wpString("All $plural"),
            'add_new_item'          => self::wpString("Add New $singular"),
            'add_new'               => self::wpString("Add New"),
            'new_item'              => self::wpString("New $singular"),
            'edit_item'             => self::wpString("Edit $singular"),
            'update_item'           => self::wpString("Update $singular"),
            'view_item'             => self::wpString("View $singular"),
            'search_items'          => self::wpString("Search $plural"),
            'not_found'             => self::wpString("Not found"),
            'not_found_in_trash'    => self::wpString("Not found in Trash"),
            'featured_image'        => self::wpString("Featured Image"),
            'set_featured_image'    => self::wpString("Set Featured Image"),
            'remove_featured_image' => self::wpString("Remove Featured Image"),
            'use_featured_image'    => self::wpString("Use as Featured Image"),
            'insert_into_item'      => self::wpString("Insert into $singular"),
            'uploaded_to_this_item' => self::wpString("Uploaded to this $singular"),
            'items_list'            => self::wpString("$plural List"),
            'items_list_navigation' => self::wpString("$plural List Navigation"),
            'filter_items_list'     => self::wpString("Filter $singular list"),
        ];
    }

    /**
     * Gets an array of supported features of the post type. Meant to be easily
     * overridden in a subclass, for the sake of felxibility.
     *
     * @return array
     */
    protected static function getPostTypeSupports(): array
    {
        return [
            'title',
            'editor',
            'excerpt',
            'thumbnail',
            'custom-fields',
        ];
    }

    /**
     * Gets the list of taxonomies that are relevant to the custom post type.
     * Meant to be easily overridden in a subclass.
     *
     * @return array
     */
    protected static function getTaxonomies(): array
    {
        // Derive a post tag for filters from the post type slug.
        $postTag = str_replace("-", "_", self::$postTypeSlug);

        $taxonomies = [
            'category',
            'post_tag',
        ];

        // Apply a new filter. Does nothing on its own, but allows users
        // of the post type to customize its taxonomies dynamically in a
        // variety of contexts.
        $taxonomies = apply_filters("${postTag}_taxonomies", $taxonomies);

        // Ditch taxonomies that don't exist, to avoid errors.
        foreach ($taxonomies as $taxonomy) {
            if (!taxonomy_exists($taxonomy)) {
                unset($taxonomies[$taxonomy]);
            }
        }

        // Return the list of taxonomies.
        return $taxonomies;
    }

    /**
     * Wrapper for the two main WordPress string localization functions, for the sake
     * of code brevity elsewhere.
     *
     * @param string $text     The text to be localized.
     * @param string $context  The context, if any. Default null.
     *
     * @return string
     */
    final protected static function wpString(string $text, string $context = null): string
    {
        // Grab our text domain
        $domain = self::$labels['text_domain'];

        // If $context isn't null, then we need to use the contextual localization function
        if (!is_null($context)) {
            return _x($text, $context, $domain);
        }

        // Otherwise the normal one is fine.
        return __($text, $domain);
    }
}
