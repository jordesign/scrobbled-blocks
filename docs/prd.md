# Scrobbled Blocks - Product Requirements Document

## Overview

**Plugin Name:** Scrobbled Blocks  
**Slug:** `scrobbled-blocks`  
**Block Namespace:** `scrobble-blocks`  
**Version:** 1.0.0  
**Description:** Display your Last.fm listening activity on your WordPress site with Gutenberg blocks.

### Minimum Requirements
- WordPress 6.0+
- PHP 7.4+

---

## Features

### 1. Settings Page

A standard WordPress admin settings page (`Settings > Scrobbled Blocks`) with the following fields:

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| Last.fm Username | Text input | Yes | The Last.fm username to fetch scrobbles for |
| Last.fm API Key | Text input | Yes | API key obtained from https://www.last.fm/api/account/create |
| Default Artwork Placeholder | Media upload | No | Custom placeholder image when album art is unavailable. Plugin ships with a default. |

**Validation:**
- On save, validate that both username and API key are provided
- Optionally test the API connection and display success/error feedback

**Security:**
- Use WordPress Settings API
- Sanitise all inputs
- Store API key using `update_option()` (consider encryption for sensitive data)
- Capability check: `manage_options`

---

### 2. API Integration

**Endpoint:** `https://ws.audioscrobbler.com/2.0/`  
**Method:** `user.getRecentTracks`  
**Authentication:** API key (no user authentication required)

**API Parameters:**
```
method=user.getrecenttracks
user={username}
api_key={api_key}
format=json
limit={number_of_tracks}
```

**Response Data Used:**
- `track.name` - Track title
- `track.artist['#text']` - Artist name
- `track.album['#text']` - Album name
- `track.image` - Array of artwork URLs (use 'extralarge' size: 300x300)
- `track.url` - Last.fm track URL
- `track.date.uts` - Unix timestamp of scrobble
- `track['@attr'].nowplaying` - Boolean string indicating currently playing

**Caching Strategy:**
- Use WordPress Transients API
- Cache key format: `scrobbled_blocks_recent_{username}_{limit}`
- Cache duration:
  - "Now Playing" block: 1 minute (`MINUTE_IN_SECONDS`)
  - "Recently Played" block: 5 minutes (`5 * MINUTE_IN_SECONDS`)
- Invalidate/refresh cache on API call when transient expires

---

### 3. Blocks

#### 3.1 Now Playing Block

**Block Name:** `scrobble-blocks/now-playing`  
**Description:** Displays the currently playing or most recently played track.

**Block Attributes:**
| Attribute | Type | Default | Description |
|-----------|------|---------|-------------|
| showArtwork | boolean | true | Display album artwork |
| showTimestamp | boolean | true | Display relative timestamp |
| linkToLastFm | boolean | true | Link track/artist to Last.fm |

**Display Logic:**
- Fetch 1 track from API
- If `nowplaying` attribute is present → display as currently playing
- If most recent track timestamp is older than 5 minutes → implicitly "last played" (no label change - user handles their own headings)

**Render Output:**
```html
<div class="wp-block-scrobble-blocks-now-playing">
  <div class="scrobble-artwork">
    <img src="{artwork_url}" alt="{album} by {artist}" />
  </div>
  <div class="scrobble-info">
    <span class="scrobble-track">
      <a href="{lastfm_url}" target="_blank" rel="noopener noreferrer">{track_name}</a>
    </span>
    <span class="scrobble-artist">{artist_name}</span>
    <time class="scrobble-timestamp" datetime="{iso_timestamp}" title="{absolute_time}">
      {relative_time}
    </time>
  </div>
</div>
```

---

#### 3.2 Recently Played Block

**Block Name:** `scrobble-blocks/recently-played`  
**Description:** Displays a list or grid of recently played tracks.

**Block Attributes:**
| Attribute | Type | Default | Description |
|-----------|------|---------|-------------|
| numberOfItems | number | 5 | Number of tracks to display (1-20) |
| layout | string | 'list' | Display layout: 'list' or 'grid' |
| gridColumns | number | 3 | Number of columns when layout is 'grid' (2-6) |
| showArtwork | boolean | true | Display album artwork |
| showTimestamp | boolean | true | Display relative timestamp |
| linkToLastFm | boolean | true | Link track/artist to Last.fm |

**Block Controls (Inspector/Sidebar):**
- Number of items: `RangeControl` (min: 1, max: 20)
- Layout: `ButtonGroup` toggle (List / Grid)
- Grid columns: `RangeControl` (min: 2, max: 6) - only visible when layout is 'grid'
- Show artwork: `ToggleControl`
- Show timestamp: `ToggleControl`
- Link to Last.fm: `ToggleControl`

**Render Output (List):**
```html
<ul class="wp-block-scrobble-blocks-recently-played is-layout-list">
  <li class="scrobble-item">
    <div class="scrobble-artwork">
      <img src="{artwork_url}" alt="{album} by {artist}" />
    </div>
    <div class="scrobble-info">
      <span class="scrobble-track">
        <a href="{lastfm_url}" target="_blank" rel="noopener noreferrer">{track_name}</a>
      </span>
      <span class="scrobble-artist">{artist_name}</span>
      <time class="scrobble-timestamp" datetime="{iso_timestamp}" title="{absolute_time}">
        {relative_time}
      </time>
    </div>
  </li>
  <!-- repeat for each track -->
</ul>
```

**Render Output (Grid):**
```html
<div class="wp-block-scrobble-blocks-recently-played is-layout-grid" 
     style="--grid-columns: {gridColumns};">
  <div class="scrobble-item">
    <!-- same inner structure as list item -->
  </div>
  <!-- repeat for each track -->
</div>
```

---

### 4. REST API Endpoint (for Editor Preview)

**Route:** `/wp-json/scrobble-blocks/v1/recent-tracks`  
**Method:** GET  
**Permission:** `edit_posts` (editors and above)

**Parameters:**
| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| limit | integer | 5 | Number of tracks to return (1-20) |

**Response:**
```json
{
  "success": true,
  "tracks": [
    {
      "name": "Track Name",
      "artist": "Artist Name",
      "album": "Album Name",
      "artwork": "https://...",
      "url": "https://last.fm/...",
      "timestamp": 1701849600,
      "nowplaying": false
    }
  ]
}
```

**Error Response:**
```json
{
  "success": false,
  "error": "API key not configured"
}
```

---

### 5. Error Handling

| Scenario | Frontend Behaviour | Editor Behaviour |
|----------|-------------------|------------------|
| API key not configured | Render nothing (graceful degradation) | Display notice: "Please configure your Last.fm API key in Settings > Scrobbled Blocks" |
| API request fails | Render nothing | Display notice: "Unable to fetch Last.fm data. Please check your settings." |
| Username invalid | Render nothing | Display notice: "Last.fm user not found. Please check your username." |
| No recent tracks | Render nothing | Display notice: "No recent tracks found for this user." |
| Rate limited | Serve stale cache if available, else render nothing | Display notice if no cached data available |

---

### 6. Styling

**Approach:** 
- Minimal default styling that respects theme defaults
- Use CSS custom properties for easy theme overrides
- No external CSS frameworks

**CSS Custom Properties:**
```css
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
```

**Key Styling Rules:**
- Artwork: square aspect ratio, `object-fit: cover`
- Grid layout: CSS Grid with `--grid-columns` custom property
- List layout: flexbox for each item (artwork left, info right)
- Responsive: grid collapses to fewer columns on smaller screens
- Timestamps: subtle/secondary text colour
- Links: inherit theme link styles, `target="_blank"` with appropriate `rel` attributes

**Files:**
- `assets/css/blocks.css` - Frontend styles
- `assets/css/editor.css` - Editor-specific styles (if needed)

---

### 7. File Structure

```
scrobbled-blocks/
├── scrobbled-blocks.php          # Main plugin file
├── readme.txt                     # WordPress.org readme
├── assets/
│   ├── css/
│   │   ├── blocks.css            # Frontend block styles
│   │   └── editor.css            # Editor styles
│   ├── js/
│   │   └── editor.js             # Block editor JavaScript (built)
│   └── images/
│       └── placeholder.svg       # Default artwork placeholder
├── build/                         # Compiled block assets (generated)
├── includes/
│   ├── class-api.php             # Last.fm API wrapper class
│   ├── class-settings.php        # Settings page
│   └── class-rest-api.php        # REST API endpoints
├── src/
│   ├── now-playing/
│   │   ├── block.json
│   │   ├── edit.js
│   │   ├── index.js
│   │   └── render.php
│   └── recently-played/
│       ├── block.json
│       ├── edit.js
│       ├── index.js
│       └── render.php
├── package.json
└── webpack.config.js             # Or use @wordpress/scripts defaults
```

---

### 8. Build Process

**Tools:**
- `@wordpress/scripts` for block building
- `@wordpress/env` for local development (optional)

**Commands:**
```bash
npm install        # Install dependencies
npm run build      # Production build
npm run start      # Development with watch
npm run plugin-zip # Create distributable zip
```

---

### 9. Internationalisation

- Text domain: `scrobbled-blocks`
- All user-facing strings wrapped in translation functions (`__()`, `_e()`, `esc_html__()`, etc.)
- Include `languages/` directory for translation files
- Relative time strings: "just now", "X minutes ago", "X hours ago", "X days ago"

---

### 10. Security Considerations

- Escape all output (`esc_html()`, `esc_url()`, `esc_attr()`)
- Sanitise all input (`sanitize_text_field()`, `absint()`)
- Verify nonces on settings save
- Capability checks on all admin pages and REST endpoints
- API key stored in options table (consider `wp_options` with autoload 'no' for sensitive data)
- External links use `rel="noopener noreferrer"`

---

### 11. Future Enhancements (Out of Scope for v1.0)

- Additional blocks: Top Artists, Top Albums, Loved Tracks
- Shortcode alternatives for classic editor users
- Block patterns/variations for common layouts
- WebSocket/polling for real-time "Now Playing" updates
- Multiple user support (different users per block)
- Customisable "Now Playing" vs "Last Played" threshold
- Widget for legacy widget areas

---

## Success Criteria

1. Plugin installs and activates without errors on WordPress 6.0+ / PHP 7.4+
2. Settings page correctly saves and retrieves API credentials
3. Both blocks render correctly in editor with live preview
4. Frontend displays track data with proper caching
5. Graceful degradation when API unavailable or misconfigured
6. Passes WordPress Plugin Check (basic standards)
7. No JavaScript console errors in editor
8. Responsive layout works on mobile devices

---

## References

- [Last.fm API Documentation](https://www.last.fm/api)
- [Last.fm user.getRecentTracks](https://www.last.fm/api/show/user.getRecentTracks)
- [WordPress Block Editor Handbook](https://developer.wordpress.org/block-editor/)
- [WordPress Settings API](https://developer.wordpress.org/plugins/settings/settings-api/)
- [WordPress Transients API](https://developer.wordpress.org/plugins/transients/)
- [WordPress REST API Handbook](https://developer.wordpress.org/rest-api/)
