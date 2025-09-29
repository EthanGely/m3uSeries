# m3uSeries - Netflix-Style Interface

A modern web application for browsing and streaming content from M3U playlists with a Netflix-inspired interface.

## ✨ Features

### 🎬 Content Management
- **Smart Series Grouping**: Automatically groups episodes under series names
- **Movie & Series Support**: Handles both individual movies and TV series
- **Category Browse**: Browse content by categories from M3U metadata
- **TV Channel Filtering**: Automatically excludes live TV channels

### 🎨 Modern Interface  
- **Netflix-Style UI**: Dark theme with smooth animations and hover effects
- **Responsive Design**: Works on desktop, tablet, and mobile devices
- **Sidebar Navigation**: Easy access to search, favorites, and categories
- **Card-Based Layout**: Beautiful content cards with poster images

### 🔍 Enhanced Search
- **Partial Matching**: Find series by typing partial names (min 2 characters)
- **Smart Results**: Series episodes grouped together in search results
- **Real-time Search**: Live search with 300ms debounce

### ❤️ Favorites System
- **Local Storage**: Persistent favorites without requiring database
- **Universal Support**: Add both movies and series to favorites
- **Easy Management**: One-click add/remove with heart icons

### 📺 Improved Player
- **Better Codec Support**: Enhanced video format compatibility
- **Error Handling**: Clear error messages for playback issues
- **Modern Controls**: Custom-styled video player with Netflix-like appearance
- **Download Integration**: Easy access to download functionality

### 🛡️ Security & Performance
- **Password Protection**: Maintains existing security with attempt limiting
- **Efficient Parsing**: Optimized M3U processing with streaming
- **File Size Detection**: Downloads show progress with size information

## 🎯 Series Parsing

The application automatically detects and groups series using the naming pattern:
```
"Series Name (Additional Info) S01 E01"
```

Examples:
- `Slow Horses (MULTI) FHD S01 E02` → Series: "Slow Horses", Season 1, Episode 2  
- `Alice in Borderland (VOSTFR) HD S03 E01` → Series: "Alice in Borderland", Season 3, Episode 1

## 🚫 TV Channel Filtering

Automatically excludes TV channels by detecting:
- `tvg-name` containing `|` or "TV" (e.g., `"|FR| BFM DICI ALPES SD"`)
- `group-title` containing "TV" (e.g., `"NEWS TV"`, `"SPORTS TV"`)

## 🗂️ File Structure

### Core Files
- `index.php` - Main interface with Netflix-style UI
- `fetch_m3u.php` - Enhanced M3U parser with multi-action support
- `player.php` - Improved video player with better error handling
- `series_detail.php` - Series episode listing page
- `favorites.php` - Favorites management API
- `download.php` - File download with size detection

### Legacy Files
- `serieList.php`, `serieSaison.php` - Original series browsing (deprecated)
- `tmdb.php` - TMDB integration for poster images

## 🎮 Usage

1. **Login**: Enter the application password
2. **Search**: Use the search bar to find movies and series  
3. **Browse**: Click categories in sidebar or use the Categories view
4. **Favorites**: Click heart icons to add/remove favorites
5. **Watch**: Click "Play" to stream or "Download" to save locally
6. **Series**: Click series cards to view all episodes organized by season

## 🔧 Technical Details

### API Endpoints
- `fetch_m3u.php?action=search&q=query` - Search content
- `fetch_m3u.php?action=categories` - List all categories  
- `fetch_m3u.php?action=category&category=name` - Get category content
- `favorites.php?action=list` - Get favorites
- `favorites.php?action=add` - Add favorite (POST)
- `favorites.php?action=remove&id=name` - Remove favorite

### Dependencies
- Bootstrap 5.3.3 for styling
- Font Awesome 6.0.0 for icons
- Video.js 8.10.0 for video playback
- Modern browsers with ES6+ support

## 🎨 UI/UX Improvements

The new interface provides:
- **Visual Hierarchy**: Clear content organization with consistent styling
- **Smooth Animations**: Hover effects and transitions for better user experience  
- **Accessibility**: High contrast design with proper focus states
- **Mobile-First**: Responsive design that works on all screen sizes
- **Netflix-Inspired**: Familiar interface patterns for intuitive navigation