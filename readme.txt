=== Scrobbled Blocks ===
Contributors: jordesign
Donate link: https://jordangillman.blog
Tags: lastfm, music, scrobble, blocks, gutenberg
Requires at least: 6.0
Tested up to: 6.7
Stable tag: 1.0.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Display your Last.fm listening activity on your WordPress site with native Gutenberg blocks.

== Description ==

Scrobbled Blocks brings your Last.fm listening history to your WordPress site using native Gutenberg blocks. Whether you're a music blogger, podcaster, DJ, or just want to share your musical tastes with your audience, this plugin makes it simple.

= Two Powerful Blocks =

**Now Playing Block**
Display the track you're currently listening to, or the most recent track you've played. Perfect for sidebars, footers, or anywhere you want to show off your current musical mood.

**Recently Played Block**
Show a list or grid of your recent scrobbles. Configurable from 1-20 tracks, with flexible layout options to match your site's design.

= Key Features =

* **Live Editor Preview** - See your actual Last.fm data while editing in Gutenberg
* **Flexible Layouts** - Choose between list and grid views for the Recently Played block
* **Customisable Display** - Toggle artwork, timestamps, and Last.fm links on or off
* **Smart Caching** - Minimises API calls while keeping data fresh (1 min for Now Playing, 5 min for Recently Played)
* **Graceful Degradation** - If the API is unavailable, cached data is served; if no cache exists, blocks simply don't render
* **Theme-Friendly Styling** - Uses CSS custom properties so you can easily match your theme
* **Responsive Design** - Looks great on all screen sizes
* **Custom Placeholder** - Upload your own placeholder image for tracks without artwork


= Privacy =

This plugin connects to the Last.fm API to fetch your public listening data. No personal data from your visitors is collected or sent to Last.fm. Your API key is stored securely in your WordPress database.

== Installation ==

= Automatic Installation =

1. Go to Plugins > Add New in your WordPress admin
2. Search for "Scrobbled Blocks"
3. Click "Install Now" and then "Activate"
4. Go to Settings > Scrobbled Blocks to configure your Last.fm credentials

= Manual Installation =

1. Download the plugin zip file
2. Go to Plugins > Add New > Upload Plugin
3. Upload the zip file and click "Install Now"
4. Activate the plugin
5. Go to Settings > Scrobbled Blocks to configure your Last.fm credentials

= Configuration =

1. **Get a Last.fm API Key:**
   * Visit [Last.fm API Account Creation](https://www.last.fm/api/account/create)
   * Fill in the application name (e.g., "My WordPress Site")
   * Use your website URL for the application URL
   * Submit the form and copy the **API Key** (not the Shared Secret)

2. **Configure the Plugin:**
   * Go to Settings > Scrobbled Blocks
   * Enter your Last.fm username
   * Paste your API key
   * Optionally upload a custom placeholder image
   * Click "Save Changes"

3. **Add Blocks to Your Site:**
   * Edit any post or page with the block editor
   * Click the + button to add a block
   * Search for "Now Playing" or "Recently Played"
   * Configure the block options in the sidebar

== Frequently Asked Questions ==

= How do I get a Last.fm API key? =

Visit [https://www.last.fm/api/account/create](https://www.last.fm/api/account/create) and create an API application. You'll receive an API key immediately. The Shared Secret is not needed for this plugin.

= Why isn't my currently playing track showing as "now playing"? =

Last.fm only reports "now playing" status while the track is actively being scrobbled by your music player. If you pause or stop playback, Last.fm no longer reports it as currently playing, so the plugin shows it as your most recently played track instead.

= How often does the data refresh? =

* **Now Playing block:** Caches for 1 minute
* **Recently Played block:** Caches for 5 minutes

This balances freshness with responsible API usage. If the API is temporarily unavailable, the plugin will serve stale cached data (up to 24 hours old) rather than showing nothing.

= Why isn't album artwork showing for some tracks? =

Not all tracks on Last.fm have associated album artwork. When artwork is unavailable, the plugin displays a placeholder image. You can upload a custom placeholder in Settings > Scrobbled Blocks.

= Can I use this with the Classic Editor? =

Scrobbled Blocks requires the Gutenberg block editor. If you're using the Classic Editor plugin, you won't be able to use these blocks directly. Consider using a page builder that supports Gutenberg blocks, or enabling the block editor for specific post types.


= How do I style the blocks to match my theme? =

The plugin uses CSS custom properties that you can override. See the "CSS Customisation" section below.

= Is this plugin compatible with caching plugins? =

Yes. The plugin uses WordPress transients for its own caching, which works alongside page caching plugins. However, if you use aggressive page caching, the "Now Playing" data may be slightly delayed. Consider excluding pages with the Now Playing block from full-page caching if real-time accuracy is important.

== Screenshots ==

1. Now Playing block in the editor with live preview
2. Recently Played block in grid layout
3. Recently Played block in list layout
4. Plugin settings page
5. Blocks displayed on the frontend

== Changelog ==

= 1.0.0 =
* Initial release
* Now Playing block with artwork, timestamp, and link options
* Recently Played block with list and grid layouts
* Settings page for API configuration
* Custom placeholder image support
* Smart caching with graceful degradation
* Responsive CSS with custom properties

== Upgrade Notice ==

= 1.0.0 =
Initial release of Scrobbled Blocks. Configure your Last.fm credentials in Settings > Scrobbled Blocks to get started.

== CSS Customisation ==

The plugin uses CSS custom properties (CSS variables) for easy theming. Add these to your theme's CSS to override the defaults:

`
:root {
    /* Artwork sizes */
    --scrobble-artwork-size: 64px;
    --scrobble-artwork-size-grid: 100%;

    /* Spacing */
    --scrobble-gap: 1rem;

    /* Typography */
    --scrobble-font-size-track: inherit;
    --scrobble-font-size-artist: 0.875em;
    --scrobble-font-size-timestamp: 0.75em;

    /* Colours */
    --scrobble-color-text: inherit;
    --scrobble-color-text-secondary: inherit;
    --scrobble-color-link: inherit;
}
`

= Example: Larger Artwork =

`
:root {
    --scrobble-artwork-size: 100px;
}
`

= Example: Custom Colours =

`
:root {
    --scrobble-color-text: #333;
    --scrobble-color-text-secondary: #666;
    --scrobble-color-link: #1db954;
}
`

== Additional Notes ==

= Requirements =

* WordPress 6.0 or higher
* PHP 7.4 or higher
* A Last.fm account (free)
* A Last.fm API key (free, get one at last.fm/api)

= Support =

For bug reports and feature requests, please visit the [GitHub repository](https://github.com/jordesign/scrobbled-blocks/issues).

= Contributing =

Contributions are welcome! Please see the [GitHub repository](https://github.com/jordesign/scrobbled-blocks) for development guidelines.
