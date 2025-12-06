=== Scrobbled Blocks ===
Contributors: yourname
Tags: lastfm, music, scrobble, blocks, gutenberg
Requires at least: 6.0
Tested up to: 6.4
Stable tag: 1.0.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Display your Last.fm listening activity on your WordPress site with Gutenberg blocks.

== Description ==

Scrobbled Blocks lets you showcase your music listening habits from Last.fm on your WordPress website. Perfect for music bloggers, podcast hosts, or anyone who wants to share what they're listening to.

**Features:**

* **Now Playing Block** - Show your currently playing or most recently played track
* **Recently Played Block** - Display a list or grid of your recent scrobbles
* Live editor preview in the block editor
* Customizable display options (artwork, timestamps, links)
* Responsive design that works on all devices
* Smart caching to minimize API calls
* CSS custom properties for easy theming

**Requirements:**

* WordPress 6.0 or higher
* PHP 7.4 or higher
* A Last.fm account and API key

== Installation ==

1. Upload the `scrobbled-blocks` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings > Scrobbled Blocks to configure your Last.fm credentials
4. Add the blocks to your posts or pages using the block editor

**Getting a Last.fm API Key:**

1. Visit https://www.last.fm/api/account/create
2. Create an API application (you can use your website URL)
3. Copy the API Key (not the Shared Secret)
4. Paste it in the plugin settings

== Frequently Asked Questions ==

= How do I get a Last.fm API key? =

Visit https://www.last.fm/api/account/create and create an API application. You'll receive an API key that you can use with this plugin.

= Why isn't my currently playing track showing? =

Last.fm only reports "now playing" status while the track is actively being scrobbled. If you pause or stop playback, it will show as "last played" instead.

= How often does the data refresh? =

The Now Playing block caches data for 1 minute, while the Recently Played block caches for 5 minutes. This helps reduce API calls while keeping data relatively fresh.

= Can I customize the appearance? =

Yes! The plugin uses CSS custom properties that you can override in your theme. See the documentation for available properties.

= Why isn't album artwork showing? =

Some tracks on Last.fm don't have associated album artwork. The plugin will display a placeholder image in these cases. You can set a custom placeholder in the plugin settings.

== Screenshots ==

1. Now Playing block in the editor
2. Recently Played block in grid layout
3. Plugin settings page
4. Blocks on the frontend

== Changelog ==

= 1.0.0 =
* Initial release
* Now Playing block
* Recently Played block with list and grid layouts
* Settings page for API configuration
* Custom placeholder image support

== Upgrade Notice ==

= 1.0.0 =
Initial release of Scrobbled Blocks.

== CSS Customization ==

You can customize the appearance using these CSS custom properties:

`
:root {
    --scrobble-artwork-size: 64px;
    --scrobble-artwork-size-grid: 100%;
    --scrobble-gap: 1rem;
    --scrobble-font-size-track: inherit;
    --scrobble-font-size-artist: 0.875em;
    --scrobble-font-size-timestamp: 0.75em;
    --scrobble-color-text: inherit;
    --scrobble-color-text-secondary: inherit;
    --scrobble-color-link: inherit;
}
`

Add your customizations to your theme's CSS to override the defaults.
