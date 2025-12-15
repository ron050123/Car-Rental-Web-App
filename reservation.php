<?php
session_start();
if (!empty($_GET['vin'])) {
    $_SESSION['selectedVin'] = filter_input(INPUT_GET, 'vin', FILTER_SANITIZE_STRING);
}

if (empty($_SESSION['selectedVin'])): ?>
  <!DOCTYPE html><html>
  <head>
  <meta charset="UTF-8">
  <title>Reserve <?= htmlspecialchars($car['brand'].' '.$car['carModel']) ?></title>
  <link rel="stylesheet" href="css/style.css">
  </head>
  <body>
  <header>
    <a href="index.php" class="logo">
      <img src="images/logo.png" alt="SwiftRide">
    </a>
  </header>  
  <body>
    <p class="no-car-message">Please pick a car first. <a href="index.php">Go to car list</a></p>
  </body></html>
  <?php exit;
endif;

$vin = $_SESSION['selectedVin'];

$jsonPath = __DIR__ . '/data/cars.json';
if (!file_exists($jsonPath)) { die('Error: cars.json not found'); }
$carsData = json_decode(file_get_contents($jsonPath), true)['cars'] ?? [];

$car = null;
foreach ($carsData as $c) {
  if (($c['vin'] ?? '') === $vin) { $car = $c; break; }
}
if (!$car) { die("Error: no car with VIN $vin"); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reserve <?= htmlspecialchars($car['brand'].' '.$car['carModel']) ?></title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <header>
    <a href="index.php" class="logo">
      <img src="images/logo.png" alt="SwiftRide">
    </a>
  </header>
  <main>

    <div class="reservation-container">

      <!-- Car details -->
      <aside class="car-info">
      <h2>Reservation for</h2>
        <h3><?= htmlspecialchars($car['brand'].' '.$car['carModel']) ?></h3>
        <br>
        <img src="<?= htmlspecialchars($car['image']) ?>" alt="<?= htmlspecialchars($car['carModel']) ?>">
        <ul class="car-details">
          <li><strong>Type:</strong> <?= htmlspecialchars($car['carType']) ?></li>
          <li><strong>Fuel:</strong> <?= htmlspecialchars($car['fuelType']) ?></li>
          <li><strong>Mileage:</strong> <?= htmlspecialchars($car['mileage']) ?></li>
          <li><strong>Price/day:</strong> $<?= htmlspecialchars($car['pricePerDay']) ?></li>
          <br>
          <li id="total-price"></li>
        </ul>
      </aside>

      <!-- Form or Unavailable Message -->
      <section class="form-wrapper">
        <?php if ($car['available']): ?>
          <form id="reservation-form" data-price="<?= htmlspecialchars($car['pricePerDay']) ?>">
            <input type="hidden" name="vin" value="<?= htmlspecialchars($vin) ?>">
            <label><strong>Name:</strong> <input type="text" name="customerName" required></label><br>
            <label><strong>Phone:</strong> <input type="tel" name="phoneNumber" required></label><br>
            <label><strong>Email:</strong> <input type="email" name="email" required></label><br>
            <label><strong>Driver’s License #:</strong> <input type="text" name="driversLicenseNumber" required></label><br>
            <label><strong>Start Date:</strong> <input type="date" name="startDate" required></label><br>
            <label><strong>Days:</strong> <input type="number" name="days" min="1" value="1" required></label><br>

            <button type="submit" disabled>Submit</button>
            <button type="button" id="cancel-btn">Cancel</button>
          </form>
          <div id="confirmation"></div>

        <?php else: ?>
          <p class="no-car-message">This car is no longer available. <a href="index.php">Choose another</a>.</p>
        <?php endif; ?>
      </section>

    </div>
  </main>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script>
  $(function(){
    const saved = JSON.parse(localStorage.getItem('reservationForm')||'{}');
    Object.entries(saved).forEach(([k,v])=>{
      $(`[name="${k}"]`).val(v);
    });

    function validate(){
    const $form  = $('#reservation-form');
    const price  = +$form.data('price');
    const days   = parseInt($form.find('[name="days"]').val(), 10);
    const $total = $('#total-price');

    if (!isNaN(days) && days > 0) {
      $total.text('Your Total: $' + (price * days));
    } else {
      $total.text('');
    }

    let ok = true;
    $form.find('[required]').each(function(){
      if (!$(this).val()) ok = false;
    });
    if (isNaN(days) || days < 1) ok = false;

    $form.find('button[type="submit"]').prop('disabled', !ok);

    const draft = {};
    $form.serializeArray().forEach(o => draft[o.name] = o.value);
    localStorage.setItem('reservationForm', JSON.stringify(draft));
  }

$('#reservation-form').on('input change', validate);
validate();


    $('#cancel-btn').click(()=>{
      localStorage.removeItem('reservationForm');
      window.location='index.php';
    });

    $('#reservation-form').submit(function(e){
      e.preventDefault();
      $.post('php/reserveCar.php', $(this).serialize(), resp=>{
        if(!resp.success) {
          $('#confirmation').html(`<p style="color:red;">${resp.error}</p>`);
        } else {
          $('#reservation-form').hide();
          localStorage.removeItem('reservationForm');
          $('#confirmation').html(
            `<p><strong>Vehicle Reserved!</strong> <a href="${resp.confirmationLink}" id="confirm-link">Confirm booking</a></p>`
          );
        }
      },'json');
    });

    $(document).on('click','#confirm-link',function(e){
      e.preventDefault();
      $.getJSON($(this).attr('href'), r=>{
        $('#confirmation').html(
          r.success
            ? '<p><strong>Booking confirmed! Thank you.</strong></p>'
            : `<p style="color:red;">${r.error}</p>`
        );
      });
    });
  });
  </script>
</body>
</html>