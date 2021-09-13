# WP Helpers

A few template PHP classes to streamline WordPress plugin and custom post type development. All use the namespace `UCF\CAH\Lib\WordPress`.

- **WPPlugin**: Basic abstract class (almost an interface) that mandates a few perfunctory methods; largely exists for inheritance purposes.
- **WPShortcodePlugin**: Abstract class for a plugin meant to be activated by a shortcode. Child of `WPPlugin`.
- **WPVuePlugin**: Abstract class for a basic Vue app meant to run in WordPress (and activated by a shortcode). Child of `WPShortcodePlugin`.
- **WPVueRouterPlugin**: Abstract class for a more complex Vue app, which incorporates the Vue Router extension. Child of `WPVuePlugin`.
- **WPCustomPostType**: Abstract class providing basic hook-ups for a custom post type, with static members and methods meant to be easily overridden for customization purposes.
- **WPCustomFieldsInterface**: An interface with methods meant to provide structure for methods that add custom field metaboxes to a custom post type.
