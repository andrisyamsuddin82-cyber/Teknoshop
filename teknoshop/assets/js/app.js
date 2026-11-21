// Teknoshop - interaksi ringan tanpa pustaka.
(function () {
  "use strict";
  var rp = function (n) { return "Rp" + Math.round(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g, "."); };

  // Menu kategori (mobile) dan sidebar admin
  document.addEventListener("click", function (e) {
    var t = e.target.closest("[data-menu]");
    if (t) { var m = document.getElementById("menu-kat"); var b = m.classList.toggle("buka"); t.setAttribute("aria-expanded", b); return; }
    t = e.target.closest("[data-menu-admin]");
    if (t) { var s = document.getElementById("admin-sisi"); var o = s.classList.toggle("buka"); t.setAttribute("aria-expanded", o); return; }
    if (e.target.closest("[data-cetak]")) { window.print(); return; }
    var st = e.target.closest("[data-step]");
    if (st) {
      var box = st.closest("[data-stepper]"), inp = box.querySelector("input");
      var min = parseInt(inp.min || "1", 10), max = parseInt(inp.max || "99", 10);
      var v = (parseInt(inp.value, 10) || min) + parseInt(st.getAttribute("data-step"), 10);
      inp.value = Math.max(min, Math.min(max, v));
      inp.dispatchEvent(new Event("change", { bubbles: true }));
    }
    // tutup menu akun saat klik di luar
    document.querySelectorAll(".akun-menu[open]").forEach(function (d) { if (!d.contains(e.target)) d.removeAttribute("open"); });
  });

  // Konfirmasi sebelum aksi berbahaya
  document.addEventListener("submit", function (e) {
    var pesan = e.target.getAttribute && e.target.getAttribute("data-konfirmasi");
    if (pesan && !window.confirm(pesan)) e.preventDefault();
  });

  // Ubah jumlah di keranjang: kirim otomatis
  document.querySelectorAll("form[data-autokirim]").forEach(function (f) {
    var tmr;
    f.addEventListener("change", function () { clearTimeout(tmr); tmr = setTimeout(function () { f.submit(); }, 450); });
  });

  // Total checkout langsung berubah saat kurir dipilih
  var co = document.querySelector("[data-checkout]");
  if (co) {
    var sub = +co.dataset.subtotal, dis = +co.dataset.diskon, gratis = +co.dataset.gratis;
    var hitung = function () {
      var r = co.querySelector("input[name=kurir]:checked");
      var tarif = r ? +r.dataset.tarif : 0;
      if (r && r.value === "reguler" && sub >= gratis) tarif = 0;
      co.querySelector("[data-ongkir]").textContent = tarif ? rp(tarif) : "Gratis";
      co.querySelector("[data-total]").textContent = rp(sub - dis + tarif);
    };
    co.querySelectorAll("input[name=kurir]").forEach(function (i) { i.addEventListener("change", hitung); });
    var reg = co.querySelector("[data-reguler]");
    if (reg && sub >= gratis) reg.innerHTML = "<s>" + reg.textContent + "</s> Gratis";
    hitung();
  }

  // Admin: kolom resi hanya saat status "dikirim"
  var sel = document.querySelector("[data-status]"), resi = document.querySelector("[data-resi]");
  if (sel && resi) {
    var atur = function () { var d = sel.value === "dikirim"; resi.style.display = d ? "" : "none"; resi.querySelector("input").required = d; };
    sel.addEventListener("change", atur); atur();
  }
})();
