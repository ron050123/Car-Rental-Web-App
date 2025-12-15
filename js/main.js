$(function() {
  $.getJSON('php/getCars.php', initFilters);

  function initFilters(data) {
    const types = new Set(), brands = new Set();
    data.cars.forEach(c => {
      types.add(c.carType);
      brands.add(c.brand);
    });
    types.forEach(t => $('#filter-type').append(`<option>${t}</option>`));
    brands.forEach(b => $('#filter-brand').append(`<option>${b}</option>`));
    loadCars();
  }

  function loadCars(q = $('#search').val(), type = $('#filter-type').val(), brand = $('#filter-brand').val()) {
    $.getJSON('php/searchCars.php', { q, types: type, brands: brand }, renderCars);
  }

  function renderCars(data) {
    const grid = $('#car-grid').empty();
    data.cars.forEach(car => {
      const disabled = car.available ? '' : 'disabled';
      // ensure image URLs are direct links or local paths
      const img = car.image.match(/^https?:.*\.(png|jpe?g|svg)$/i)
        ? car.image
        : car.image; // or fallback to 'images/default-car.png'
      grid.append(`
        <div class="car-card">
          <img src="${img}" alt="${car.brand} ${car.carModel}">
          <div class="info">
            <h4>${car.brand} ${car.carModel} (${car.yearOfManufacture})</h4>
            <p>${car.description}</p>
            <br>
            <p><strong>Type:</strong> ${car.carType}, ${car.fuelType}</p>
            <p><strong>Mileage:</strong> ${car.mileage}</p>
            <p><strong>Price:</strong> $${car.pricePerDay}/day</p>
            <p><strong>${car.available ? 'Available' : 'Unavailable'}</strong></p>
          </div>
          <div class="actions">
            <button ${disabled} data-vin="${car.vin}">Rent</button>
          </div>
        </div>
      `);
    });
  }

  $('#filter-type, #filter-brand').change(() => loadCars());
  $('#search').on('input', debounce(function() {
    const term = $(this).val().trim();
    if (term) {
      $.getJSON('php/searchCars.php', { q: term, suggest: 1 }, resp => {
        const list = $('#suggestions').empty();
        resp.suggestions.forEach(s => list.append(`<li>${s}</li>`));
        list.show();
      });
    } else {
      $('#suggestions').hide();
      loadCars();
    }
  }, 300));

  $(document).on('click', '#suggestions li', function() {
    $('#search').val($(this).text());
    $('#suggestions').hide();
    loadCars();
  });

  $(document).on('click', '.actions button', function() {
    const vin = $(this).data('vin');
    window.location.href = `reservation.php?vin=${encodeURIComponent(vin)}`;
  });
});

function debounce(fn, ms) {
  let t;
  return function() {
    clearTimeout(t);
    t = setTimeout(() => fn.apply(this, arguments), ms);
  };
}
