<?php
require_once __DIR__ . '/functions.php';

$pageTitle = 'Location';

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>InkTale Location</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
  <style>
    :root{
      --cream:#f5f0e8;
      --brown:#1b1816;
      --mocha:#130e0a;
      --gold:#c9a84c;
      --warm:#e8d9c0;
      --soft:#d4c4a8;
      --white:#fffdf8
    }
    *{box-sizing:border-box}
    body{
      margin:0;
      font-family:Lato,sans-serif;
      background:var(--cream);
      color:var(--brown)
    }
    nav{
      background:var(--brown);
      padding:16px 24px;
      display:flex;
      justify-content:space-between;
      align-items:center
    }
    nav a{
      color:var(--gold);
      text-decoration:none;
      font-weight:700
    }
    .container{
      max-width:1100px;
      margin:0 auto;
      padding:28px 20px 0
    }
    .card{
      background:var(--white);
      border-radius:24px;
      padding:24px;
      border:1px solid var(--soft);
      box-shadow:0 16px 34px rgba(0,0,0,.08)
    }
    #map{
      margin-top:18px;
      min-height:420px;
      width:100%;
      border-radius:18px;
      border:2px solid var(--gold);
      overflow:hidden
    }
    .info-box{
      margin-top:18px;
      padding:16px;
      background:#f8f2e8;
      border:1px solid var(--soft);
      border-radius:14px
    }
    .map-link{
      display:inline-block;
      margin-top:12px;
      background:var(--brown);
      color:var(--gold);
      text-decoration:none;
      padding:10px 16px;
      border-radius:12px;
      font-weight:700
    }
    code{
      display:block;
      margin-top:14px;
      background:#20160f;
      color:#f5f0e8;
      padding:14px;
      border-radius:14px;
      overflow:auto
    }
  </style>
</head>
<body>
  <nav>
    <a href="index.php">← Back to Home</a>
  </nav>

  <div class="container">
    <div class="card">
      <h1 style="font-family:Playfair Display,serif;margin-top:0">Location & Map</h1>
      <p>Visit InkTale Bookstore at our location.</p>

      <div id="map">
        <iframe title="InkTale Bookstore location" width="100%" height="100%"
          style="border:0;display:block" loading="lazy"
          src="https://www.openstreetmap.org/export/embed.html?bbox=50.0688%2C26.4107%2C50.1088%2C26.4307&amp;layer=mapnik&amp;marker=26.4207%2C50.0888"></iframe>
      </div>

      <div class="info-box">
        <strong>Store Name:</strong> InkTale Bookstore<br>
        <strong>Address:</strong> Dammam, Saudi Arabia<br>
        <a class="map-link" href="https://www.google.com/maps?q=26.4207,50.0888" target="_blank">
          Open in Google Maps
        </a>
      </div>

    </div>
  </div>

  

  
</body>
</html>
    <a class="btn" href="<?= h($LOCATION_URL); ?>" target="_blank">Open in Google Maps</a>
  </div>
</section>

<?php require 'footer.php'; ?>
