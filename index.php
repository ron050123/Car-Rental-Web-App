<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>SwiftRide Car Rental</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <header>
    <a href="index.php" class="logo">
      <img src="images/logo.png" alt="SwiftRide">
    </a>
    <a href="reservation.php" class="btn reservation-btn">
    Make a Reservation
    </a>
  </header>

  <main>
    <div class="search-filter-bar">
      <div class="search-container">
        <input type="text" id="search" placeholder="Search by type, brand, model…">
        <ul id="suggestions" class="suggestions-list" style="display:none;"></ul>
      </div>
      <div class="filters">
        <select id="filter-type"><option value="">All Types</option></select>
        <select id="filter-brand"><option value="">All Brands</option></select>
      </div>
    </div>

    <div id="car-grid" class="grid-container">
      <!-- car cards inject -->
    </div>
  </main>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="js/main.js"></script>
</body>
</html>