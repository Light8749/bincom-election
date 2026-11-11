// Chained select boxes: LGA -> Ward -> Polling unit, plus live vote total.
(function () {
  function fill(select, items, placeholder, selected, label) {
    select.innerHTML = '';
    select.add(new Option(items.length ? placeholder : 'None found', ''));
    items.forEach(function (it) {
      var o = new Option(label ? label(it) : it.name, it.id);
      if (String(it.id) === String(selected)) o.selected = true;
      select.add(o);
    });
    select.disabled = !items.length;
  }
  function reset(select, text) {
    if (!select) return;
    select.innerHTML = '';
    select.add(new Option(text, ''));
    select.disabled = true;
  }
  function get(url) {
    return fetch(url, { headers: { Accept: 'application/json' } }).then(function (r) {
      if (!r.ok) throw new Error('Request failed');
      return r.json();
    });
  }

  document.querySelectorAll('[data-chain]').forEach(function (form) {
    var lga = form.querySelector('[data-lga]');
    var ward = form.querySelector('[data-ward]');
    var unit = form.querySelector('[data-unit]');
    if (!lga || !ward) return;
    var withUnits = form.hasAttribute('data-with-units');

    function loadWards(selected) {
      reset(ward, 'Loading…'); reset(unit, 'Select polling unit…');
      if (!lga.value) return reset(ward, 'Select ward…');
      return get('api.php?type=wards&lga_id=' + encodeURIComponent(lga.value) + (withUnits ? '&with_units=1' : '')).then(function (items) {
        fill(ward, items, 'Select ward…', selected, withUnits ? function (it) {
          return it.name + ' (' + it.units + ' unit' + (it.units == 1 ? '' : 's') + ')';
        } : null);
        if (ward.value && unit) return loadUnits(unit.dataset.selected);
      }).catch(function () { reset(ward, 'Could not load wards'); });
    }
    function loadUnits(selected) {
      if (!unit) return;
      reset(unit, 'Loading…');
      if (!ward.value) return reset(unit, 'Select polling unit…');
      return get('api.php?type=units&lga_id=' + encodeURIComponent(lga.value) + '&ward_id=' + encodeURIComponent(ward.value))
        .then(function (items) {
          fill(unit, items, 'Select polling unit…', selected, function (it) {
            return (it.results > 0 ? '✓ ' : '   ') + it.name + (it.number ? ' (' + it.number + ')' : '');
          });
        }).catch(function () { reset(unit, 'Could not load polling units'); });
    }

    lga.addEventListener('change', function () { loadWards(''); });
    ward.addEventListener('change', function () { loadUnits(''); });
    if (unit && unit.hasAttribute('data-autosubmit')) {
      unit.addEventListener('change', function () { if (unit.value) form.submit(); });
    }
    if (lga.value) loadWards(ward.dataset.selected);
  });

  document.querySelectorAll('[data-total]').forEach(function (form) {
    var out = form.querySelector('[data-total-out]');
    function sum() {
      var t = 0;
      form.querySelectorAll('[data-score]').forEach(function (i) { t += parseInt(i.value, 10) || 0; });
      out.textContent = t.toLocaleString();
    }
    form.addEventListener('input', sum);
    sum();
  });
})();
