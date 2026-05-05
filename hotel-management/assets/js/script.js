// Hotel Management System - Common JS
document.addEventListener('DOMContentLoaded', () => {
  // Auto-dismiss alerts after 5s
  document.querySelectorAll('.alert').forEach(a => {
    setTimeout(() => { try { bootstrap.Alert.getOrCreateInstance(a).close(); } catch(e){} }, 5000);
  });

  // Booking form: validate dates and compute amount preview
  const ci = document.getElementById('check_in_date');
  const co = document.getElementById('check_out_date');
  const price = document.getElementById('room_price');
  const totalPreview = document.getElementById('total_preview');
  const update = () => {
    if (!ci || !co || !price || !totalPreview) return;
    if (!ci.value || !co.value) { totalPreview.textContent = '—'; return; }
    const d1 = new Date(ci.value), d2 = new Date(co.value);
    const nights = Math.round((d2 - d1) / 86400000);
    if (nights <= 0) { totalPreview.textContent = 'Invalid dates'; return; }
    const total = nights * parseFloat(price.value || 0);
    totalPreview.textContent = nights + ' nights × ৳' + parseFloat(price.value).toFixed(2)
      + ' = ৳ ' + total.toFixed(2);
  };
  [ci, co, price].forEach(el => el && el.addEventListener('change', update));
});

function confirmDelete(msg) { return confirm(msg || 'Are you sure you want to delete this?'); }
function printInvoice() { window.print(); }
