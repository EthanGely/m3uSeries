<?php

include_once 'update_playlist.php';
// Update the playlist if needed
update_playlist();

//start session
session_start();

$attemts = isset($_SESSION['attempts']) ? $_SESSION['attempts'] : 0;

if ($attemts >= 3) {
  die('Too many attempts. Please try again later.');
}

if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin'] || $_SESSION['loggedin'] !== 'hGxKr297Ab') {
  if (!isset($_GET['psw']) || $_GET['psw'] !== 'hGxKr297Ab') {
    // Increment the attempts counter
    $_SESSION['attempts'] = $attemts + 1;
?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
      <meta charset="UTF-8">
      <title>IPTV Search - Login</title>
      <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
      <style>
        body {
          font-family: sans-serif;
          padding: 2rem;
          max-width: 400px;
          margin: auto;
        }
      </style>
    </head>

    <body class="bg-light">

      <div class="card shadow mt-5">
        <div class="card-body">
          <h1 class="h4 mb-4 text-center">IPTV Search - Login</h1>
          <form method="GET" action="">
            <div class="mb-3">
              <input type="password" class="form-control" name="psw" placeholder="Enter password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Login</button>
          </form>
          <?php if (isset($_GET['psw']) && $_GET['psw'] !== 'hGxKr297Ab') { ?>
            <div class="alert alert-danger mt-3" role="alert">
              Incorrect password. Please try again.
            </div>
          <?php } ?>
        </div>
      </div>
    </body>

    </html>
<?php
    die();
  } else {
    // Reset attempts on successful login
    $_SESSION['attempts'] = 0;
  }
}
// Set the session variable to indicate that the user is logged in
$_SESSION['loggedin'] = 'hGxKr297Ab';

unset($_GET['psw']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>m3u Series - Netflix Style</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      background: linear-gradient(135deg, #141414 0%, #1a1a1a 100%);
      color: #ffffff;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      overflow-x: hidden;
    }
    
    .sidebar {
      position: fixed;
      left: 0;
      top: 0;
      width: 280px;
      height: 100vh;
      background: linear-gradient(180deg, #0f0f0f 0%, #1a1a1a 100%);
      border-right: 2px solid #333;
      z-index: 1000;
      transition: transform 0.3s ease;
      overflow-y: auto;
    }
    
    .sidebar.collapsed {
      transform: translateX(-280px);
    }
    
    .sidebar-header {
      padding: 1.5rem;
      border-bottom: 1px solid #333;
      background: linear-gradient(135deg, #e50914 0%, #f40612 100%);
      color: white;
    }
    
    .sidebar-header h3 {
      margin: 0;
      font-size: 1.5rem;
      font-weight: bold;
    }
    
    .nav-section {
      padding: 1rem 0;
      border-bottom: 1px solid #333;
    }
    
    .nav-section-title {
      padding: 0.5rem 1.5rem;
      font-size: 0.9rem;
      color: #888;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 1px;
    }
    
    .nav-item {
      padding: 0.75rem 1.5rem;
      cursor: pointer;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }
    
    .nav-item:hover {
      background: rgba(229, 9, 20, 0.1);
      color: #e50914;
    }
    
    .nav-item.active {
      background: linear-gradient(90deg, rgba(229, 9, 20, 0.2) 0%, transparent 100%);
      color: #e50914;
      border-right: 3px solid #e50914;
    }
    
    .nav-item i {
      width: 20px;
      text-align: center;
    }
    
    .main-content {
      margin-left: 280px;
      min-height: 100vh;
      transition: margin-left 0.3s ease;
    }
    
    .main-content.expanded {
      margin-left: 0;
    }
    
    .top-bar {
      background: rgba(0, 0, 0, 0.8);
      backdrop-filter: blur(10px);
      padding: 1rem 2rem;
      border-bottom: 1px solid #333;
      display: flex;
      justify-content: space-between;
      align-items: center;
      position: sticky;
      top: 0;
      z-index: 100;
    }
    
    .search-container {
      flex-grow: 1;
      max-width: 600px;
      margin: 0 2rem;
      position: relative;
    }
    
    .search-input {
      width: 100%;
      padding: 0.75rem 1rem 0.75rem 3rem;
      background: rgba(255, 255, 255, 0.1);
      border: 2px solid transparent;
      border-radius: 25px;
      color: white;
      font-size: 1rem;
      transition: all 0.3s ease;
    }
    
    .search-input:focus {
      outline: none;
      border-color: #e50914;
      background: rgba(255, 255, 255, 0.15);
      box-shadow: 0 0 20px rgba(229, 9, 20, 0.3);
    }
    
    .search-input::placeholder {
      color: #999;
    }
    
    .search-icon {
      position: absolute;
      left: 1rem;
      top: 50%;
      transform: translateY(-50%);
      color: #999;
    }
    
    .toggle-sidebar {
      background: rgba(255, 255, 255, 0.1);
      border: none;
      color: white;
      padding: 0.5rem;
      border-radius: 8px;
      cursor: pointer;
      transition: all 0.3s ease;
    }
    
    .toggle-sidebar:hover {
      background: rgba(255, 255, 255, 0.2);
    }
    
    .content-area {
      padding: 2rem;
      min-height: calc(100vh - 80px);
    }
    
    .section-title {
      font-size: 1.8rem;
      font-weight: bold;
      margin-bottom: 1.5rem;
      color: #e50914;
    }
    
    .results-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 1.5rem;
      margin-top: 2rem;
    }
    
    .item-card {
      background: rgba(255, 255, 255, 0.05);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: 12px;
      overflow: hidden;
      transition: all 0.3s ease;
      cursor: pointer;
      position: relative;
    }
    
    .item-card:hover {
      transform: translateY(-10px) scale(1.03);
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
      border-color: #e50914;
    }
    
    .item-image {
      width: 100%;
      height: 200px;
      object-fit: cover;
      background: linear-gradient(45deg, #333, #555);
      display: flex;
      align-items: center;
      justify-content: center;
    }
    
    .item-image img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    
    .item-image .placeholder {
      font-size: 3rem;
      color: #666;
    }
    
    .item-content {
      padding: 1.5rem;
    }
    
    .item-type {
      display: inline-block;
      background: linear-gradient(45deg, #e50914, #f40612);
      color: white;
      padding: 0.3rem 0.8rem;
      border-radius: 15px;
      font-size: 0.75rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 0.75rem;
    }
    
    .item-type.movie {
      background: linear-gradient(45deg, #1e88e5, #42a5f5);
    }
    
    .item-title {
      font-size: 1.1rem;
      font-weight: 600;
      margin-bottom: 0.5rem;
      color: #ffffff;
      line-height: 1.3;
    }
    
    .item-category {
      color: #999;
      font-size: 0.9rem;
      margin-bottom: 1rem;
    }
    
    .item-actions {
      display: flex;
      gap: 0.5rem;
      flex-wrap: wrap;
    }
    
    .btn-action {
      padding: 0.5rem 1rem;
      border-radius: 20px;
      border: none;
      font-weight: 500;
      font-size: 0.85rem;
      transition: all 0.3s ease;
      cursor: pointer;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
    }
    
    .btn-play {
      background: linear-gradient(45deg, #28a745, #20c997);
      color: white;
    }
    
    .btn-download {
      background: linear-gradient(45deg, #007bff, #0056b3);
      color: white;
    }
    
    .btn-favorite {
      background: linear-gradient(45deg, #ffc107, #ffeb3b);
      color: #333;
    }
    
    .btn-favorite.is-favorite {
      background: linear-gradient(45deg, #e50914, #f40612);
      color: white;
    }
    
    .btn-action:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
      text-decoration: none;
      color: white;
    }
    
    .categories-list {
      max-height: 300px;
      overflow-y: auto;
    }
    
    .category-item {
      display: flex;
      justify-content: between;
      align-items: center;
      padding: 0.75rem 1.5rem;
      cursor: pointer;
      transition: all 0.3s ease;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    .category-item:hover {
      background: rgba(229, 9, 20, 0.1);
      color: #e50914;
    }
    
    .category-item.active {
      background: linear-gradient(90deg, rgba(229, 9, 20, 0.2) 0%, transparent 100%);
      color: #e50914;
    }
    
    .category-count {
      background: rgba(255, 255, 255, 0.1);
      padding: 0.2rem 0.6rem;
      border-radius: 12px;
      font-size: 0.8rem;
      margin-left: auto;
    }
    
    .loading {
      text-align: center;
      padding: 2rem;
      color: #999;
    }
    
    .no-results {
      text-align: center;
      padding: 3rem;
      color: #666;
    }
    
    .no-results i {
      font-size: 4rem;
      margin-bottom: 1rem;
      color: #444;
    }
    
    @media (max-width: 768px) {
      .sidebar {
        transform: translateX(-280px);
      }
      
      .sidebar.open {
        transform: translateX(0);
      }
      
      .main-content {
        margin-left: 0;
      }
      
      .search-container {
        margin: 0 1rem;
      }
      
      .content-area {
        padding: 1rem;
      }
      
      .results-grid {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 1rem;
      }
    }
  </style>
</head>

<body>
  <!-- Sidebar -->
  <div class="sidebar" id="sidebar">
    <div class="sidebar-header">
      <h3><i class="fas fa-tv me-2"></i>m3u Series</h3>
    </div>
    
    <div class="nav-section">
      <div class="nav-section-title">Navigation</div>
      <div class="nav-item active" data-view="search">
        <i class="fas fa-search"></i>
        Search
      </div>
      <div class="nav-item" data-view="favorites">
        <i class="fas fa-heart"></i>
        Favorites
      </div>
      <div class="nav-item" data-view="categories">
        <i class="fas fa-list"></i>
        Categories
      </div>
    </div>
    
    <div class="nav-section" id="categories-section">
      <div class="nav-section-title">Categories</div>
      <div class="categories-list" id="categories-list">
        <div class="loading">Loading categories...</div>
      </div>
    </div>
  </div>

  <!-- Main Content -->
  <div class="main-content" id="mainContent">
    <div class="top-bar">
      <button class="toggle-sidebar" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
      </button>
      
      <div class="search-container">
        <i class="fas fa-search search-icon"></i>
        <input type="text" id="searchInput" class="search-input" placeholder="Search for movies and series...">
      </div>
      
      <div class="d-flex align-items-center gap-3">
        <span class="text-muted">Welcome!</span>
      </div>
    </div>
    
    <div class="content-area">
      <div id="contentTitle" class="section-title">Search Results</div>
      <div id="contentArea">
        <div class="no-results">
          <i class="fas fa-search"></i>
          <h3>Start searching</h3>
          <p>Type at least 2 characters to search for movies and series</p>
        </div>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Clean URL
      const urlParams = new URLSearchParams(window.location.search);
      urlParams.delete('psw');
      window.history.replaceState({}, document.title, window.location.pathname + (urlParams.toString() ? '?' + urlParams.toString() : ''));
      
      loadCategories();
      initializeEventListeners();
    });

    let currentView = 'search';
    let searchTimer;
    let categories = {};

    function initializeEventListeners() {
      // Search input
      document.getElementById('searchInput').addEventListener('input', debounceSearch);
      
      // Navigation items
      document.querySelectorAll('.nav-item[data-view]').forEach(item => {
        item.addEventListener('click', function() {
          switchView(this.dataset.view);
        });
      });
    }

    function toggleSidebar() {
      document.getElementById('sidebar').classList.toggle('collapsed');
      document.getElementById('mainContent').classList.toggle('expanded');
    }

    function switchView(view) {
      currentView = view;
      
      // Update active nav item
      document.querySelectorAll('.nav-item').forEach(item => item.classList.remove('active'));
      document.querySelector(`[data-view="${view}"]`).classList.add('active');
      
      // Update content
      const contentTitle = document.getElementById('contentTitle');
      const contentArea = document.getElementById('contentArea');
      
      switch(view) {
        case 'search':
          contentTitle.textContent = 'Search Results';
          contentArea.innerHTML = '<div class="no-results"><i class="fas fa-search"></i><h3>Start searching</h3><p>Type at least 2 characters to search for movies and series</p></div>';
          break;
        case 'favorites':
          contentTitle.textContent = 'My Favorites';
          loadFavorites();
          break;
        case 'categories':
          contentTitle.textContent = 'All Categories';
          showCategoriesView();
          break;
      }
    }

    function loadCategories() {
      fetch('fetch_m3u.php?action=categories')
        .then(res => res.json())
        .then(data => {
          categories = data;
          const categoriesList = document.getElementById('categories-list');
          categoriesList.innerHTML = '';
          
          Object.entries(data).forEach(([category, count]) => {
            const item = document.createElement('div');
            item.className = 'category-item';
            item.innerHTML = `
              <span>${category}</span>
              <span class="category-count">${count}</span>
            `;
            item.addEventListener('click', () => loadCategory(category));
            categoriesList.appendChild(item);
          });
        })
        .catch(err => {
          console.error('Error loading categories:', err);
          document.getElementById('categories-list').innerHTML = '<div class="text-danger p-3">Error loading categories</div>';
        });
    }

    function loadCategory(category) {
      switchView('category');
      document.getElementById('contentTitle').textContent = category;
      document.getElementById('contentArea').innerHTML = '<div class="loading">Loading...</div>';
      
      // Update active category
      document.querySelectorAll('.category-item').forEach(item => item.classList.remove('active'));
      event.target.closest('.category-item').classList.add('active');
      
      fetch(`fetch_m3u.php?action=category&category=${encodeURIComponent(category)}`)
        .then(res => res.json())
        .then(data => {
          displayResults(data);
        })
        .catch(err => {
          console.error('Error loading category:', err);
          document.getElementById('contentArea').innerHTML = '<div class="text-danger text-center p-4">Error loading category content</div>';
        });
    }

    function debounceSearch() {
      clearTimeout(searchTimer);
      const query = document.getElementById('searchInput').value.trim();
      
      if (currentView !== 'search') {
        switchView('search');
      }
      
      if (query.length < 2) {
        document.getElementById('contentArea').innerHTML = '<div class="no-results"><i class="fas fa-search"></i><h3>Start searching</h3><p>Type at least 2 characters to search for movies and series</p></div>';
        return;
      }
      
      document.getElementById('contentArea').innerHTML = '<div class="loading">Searching...</div>';
      
      searchTimer = setTimeout(() => {
        fetch(`fetch_m3u.php?action=search&q=${encodeURIComponent(query)}`)
          .then(res => res.json())
          .then(data => {
            if (data.error) {
              document.getElementById('contentArea').innerHTML = `<div class="text-danger text-center p-4">${data.error}</div>`;
              return;
            }
            displayResults(data);
          })
          .catch(err => {
            console.error('Search error:', err);
            document.getElementById('contentArea').innerHTML = '<div class="text-danger text-center p-4">Search error occurred</div>';
          });
      }, 300);
    }

    function loadFavorites() {
      document.getElementById('contentArea').innerHTML = '<div class="loading">Loading favorites...</div>';
      
      fetch('favorites.php?action=list')
        .then(res => res.json())
        .then(data => {
          const favoritesArray = Object.values(data);
          if (favoritesArray.length === 0) {
            document.getElementById('contentArea').innerHTML = '<div class="no-results"><i class="fas fa-heart"></i><h3>No favorites yet</h3><p>Add some movies or series to your favorites</p></div>';
            return;
          }
          displayResults(favoritesArray.map(fav => fav.data));
        })
        .catch(err => {
          console.error('Error loading favorites:', err);
          document.getElementById('contentArea').innerHTML = '<div class="text-danger text-center p-4">Error loading favorites</div>';
        });
    }

    function showCategoriesView() {
      const content = Object.entries(categories).map(([category, count]) => `
        <div class="item-card" onclick="loadCategory('${category}')">
          <div class="item-image">
            <div class="placeholder">
              <i class="fas fa-folder-open"></i>
            </div>
          </div>
          <div class="item-content">
            <div class="item-type">Category</div>
            <div class="item-title">${category}</div>
            <div class="item-category">${count} items</div>
          </div>
        </div>
      `).join('');
      
      document.getElementById('contentArea').innerHTML = content ? `<div class="results-grid">${content}</div>` : '<div class="no-results"><i class="fas fa-folder-open"></i><h3>No categories found</h3></div>';
    }

    function displayResults(results) {
      const contentArea = document.getElementById('contentArea');
      
      if (!results || results.length === 0) {
        contentArea.innerHTML = '<div class="no-results"><i class="fas fa-film"></i><h3>No results found</h3><p>Try adjusting your search terms</p></div>';
        return;
      }
      
      const resultsHTML = results.map(item => createItemCard(item)).join('');
      contentArea.innerHTML = `<div class="results-grid">${resultsHTML}</div>`;
    }

    function createItemCard(item) {
      const isMovie = item.type !== 'series';
      const title = isMovie ? item.title : item.series_name;
      const image = item.image || 'Noimage.png';
      
      let actionsHTML = '';
      if (isMovie) {
        actionsHTML = `
          <a href="player.php?url=${encodeURIComponent(item.url)}&title=${encodeURIComponent(item.title)}&psw=${encodeURIComponent("<?= $_SESSION['loggedin'] ?>")}" 
             target="_blank" class="btn-action btn-play">
            <i class="fas fa-play"></i>Play
          </a>
          <a href="download.php?url=${encodeURIComponent(item.url)}&title=${encodeURIComponent(item.title)}&psw=${encodeURIComponent("<?= $_SESSION['loggedin'] ?>")}" 
             target="_blank" class="btn-action btn-download">
            <i class="fas fa-download"></i>Download
          </a>
          <button class="btn-action btn-favorite" onclick="toggleFavorite(${JSON.stringify(item).replace(/"/g, '&quot;')}, this, event)">
            <i class="fas fa-heart"></i>
          </button>
        `;
      } else {
        actionsHTML = `
          <a href="series_detail.php?series=${encodeURIComponent(item.series_name)}" 
             class="btn-action btn-play">
            <i class="fas fa-tv"></i>View Episodes
          </a>
          <button class="btn-action btn-favorite" onclick="toggleFavorite(${JSON.stringify(item).replace(/"/g, '&quot;')}, this, event)">
            <i class="fas fa-heart"></i>
          </button>
        `;
      }
      
      return `
        <div class="item-card">
          <div class="item-image">
            ${image !== 'Noimage.png' ? 
              `<img src="${image}" alt="${title}" onerror="this.parentNode.innerHTML='<div class=\\'placeholder\\'><i class=\\'fas fa-${isMovie ? 'film' : 'tv'}\\'></i></div>';">` :
              `<div class="placeholder"><i class="fas fa-${isMovie ? 'film' : 'tv'}"></i></div>`
            }
          </div>
          <div class="item-content">
            <div class="item-type ${isMovie ? 'movie' : ''}">${isMovie ? 'Movie' : 'Series'}</div>
            <div class="item-title">${title}</div>
            <div class="item-category">${item.category || 'Unknown'}</div>
            <div class="item-actions">
              ${actionsHTML}
            </div>
          </div>
        </div>
      `;
    }

    function toggleFavorite(item, button, event) {
      event.stopPropagation();
      const id = item.type === 'series' ? item.series_name : item.title;
      const isFavorite = button.classList.contains('is-favorite');
      
      if (isFavorite) {
        fetch(`favorites.php?action=remove&id=${encodeURIComponent(id)}`)
          .then(res => res.json())
          .then(data => {
            if (data.success) {
              button.classList.remove('is-favorite');
              button.innerHTML = '<i class="fas fa-heart"></i>';
            }
          });
      } else {
        fetch('favorites.php?action=add', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(item)
        })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            button.classList.add('is-favorite');
            button.innerHTML = '<i class="fas fa-heart-broken"></i>';
          }
        });
      }
    }
  </script>
</body>

</html>