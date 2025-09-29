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
  <title>IPTV Search</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      font-family: sans-serif;
      padding: 2rem;
      max-width: 700px;
      margin: auto;
      background: #f8f9fa;
    }

    .result img {
      max-width: 100px;
      display: block;
      margin-bottom: 0.5rem;
    }
  </style>
</head>

<body>

  <div class="container py-4">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <h1 class="mb-4 text-center">IPTV Search</h1>
        <div class="mb-4">
          <input type="text" id="search" class="form-control form-control-lg" placeholder="Start typing to search (min 3 chars)...">
        </div>
        <div id="results"></div>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // delete psw from query string
      const urlParams = new URLSearchParams(window.location.search);
      urlParams.delete('psw');
      window.history.replaceState({}, document.title, window.location.pathname + (urlParams.toString() ? '?' + urlParams.toString() : ''));
      //console.log('Page loaded, ready for search');
    });

    const input = document.getElementById('search');
    const resultsDiv = document.getElementById('results');
    let timer;

    input.addEventListener('input', () => {
      const query = input.value.trim();
      clearTimeout(timer);

      if (query.length < 3) {
        resultsDiv.innerHTML = '';
        return;
      }

      timer = setTimeout(() => {
        fetch(`fetch_m3u.php?q=${encodeURIComponent(query)}&psw=${encodeURIComponent("<?= $_SESSION['loggedin'] ?>")}`)
          .then(res => res.json())
          .then(data => {
            resultsDiv.innerHTML = '';
            if (data.length === 0) {
              resultsDiv.innerHTML = '<div class="alert alert-warning">No results found.</div>';
              return;
            }

            if (data.error) {
              resultsDiv.innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
              return;
            }

            data.forEach(item => {
              const div = document.createElement('div');
              div.className = 'result card mb-3 shadow-sm';

              const cardBody = document.createElement('div');
              cardBody.className = 'card-body d-flex align-items-center';

              // Optional image
              if (item.image) {
                const img = document.createElement('img');
                img.src = item.image;
                img.alt = item.title;
                img.className = 'me-3 rounded';
                cardBody.appendChild(img);
              } else {
                const placeholderImg = document.createElement('img');
                placeholderImg.src = 'Noimage.png';
                placeholderImg.alt = 'No Image';
                placeholderImg.className = 'me-3 rounded';
                cardBody.appendChild(placeholderImg);
              }

              const contentDiv = document.createElement('div');
              contentDiv.className = 'flex-grow-1';

              const title = document.createElement('h5');
              title.className = 'card-title mb-2';
              title.textContent = item.title;

              const link = document.createElement('a');
              link.href = `download.php?url=${encodeURIComponent(item.url)}&title=${encodeURIComponent(item.title)}&psw=${encodeURIComponent("<?= $_SESSION['loggedin'] ?>")}`;
              link.textContent = 'Download';
              link.target = '_blank';
              link.rel = 'noopener';
              link.className = 'btn btn-success btn-sm';

              <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === 'hGxKr297Ab') { ?>
                const linkPlay = document.createElement('a');
                linkPlay.href = `player.php?url=${encodeURIComponent(item.url)}&title=${encodeURIComponent(item.title)}&psw=${encodeURIComponent("<?= $_SESSION['loggedin'] ?>")}`;
                linkPlay.textContent = 'Play';
                linkPlay.target = '_blank';
                linkPlay.className = 'btn btn-success btn-sm';
              <?php } ?>

              contentDiv.appendChild(title);
              contentDiv.appendChild(link);

              <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === 'hGxKr297Ab') { ?>
                contentDiv.appendChild(linkPlay);
              <?php } ?>

              cardBody.appendChild(contentDiv);
              div.appendChild(cardBody);
              resultsDiv.appendChild(div);
            });
          });
      }, 300); // 300ms debounce
    });
  </script>
</body>

</html>