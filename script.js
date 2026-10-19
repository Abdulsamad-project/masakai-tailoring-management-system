/* Masakai Tailoring Service — Main JS */
(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {

    /* ── Auto-dismiss alerts ─────────────────────────────── */
    document.querySelectorAll('.alert').forEach(function (el) {
      setTimeout(function () {
        el.style.transition = 'opacity .5s';
        el.style.opacity    = '0';
        setTimeout(function () { el.remove(); }, 520);
      }, 5000);
    });

    /* ── Smooth scroll for anchor links ──────────────────── */
    document.querySelectorAll('a[href^="#"]').forEach(function (a) {
      a.addEventListener('click', function (e) {
        var target = document.querySelector(a.getAttribute('href'));
        if (target) {
          e.preventDefault();
          target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
      });
    });

    /* ── Garment-type conditional measurement sections ───── */
    var garmentSel = document.getElementById('garment_type');
    if (garmentSel) {
      var GROUPS = {
        babban_riga: ['grp-traditional', 'grp-trouser', 'grp-babban'],
        agbada:      ['grp-traditional', 'grp-trouser'],
        kaftan:      ['grp-kaftan',      'grp-trouser'],
        slim_fit:    ['grp-suit',        'grp-suit-trouser'],
        custom_suit: ['grp-suit',        'grp-suit-trouser'],
        bridal:      ['grp-bridal'],
        alterations: ['grp-general'],
        casual_wear: ['grp-general'],
        other:       ['grp-general'],
      };

      function applyGroups() {
        document.querySelectorAll('[data-mg]').forEach(function (el) {
          el.style.display = 'none';
        });
        var show = GROUPS[garmentSel.value] || ['grp-general'];
        show.forEach(function (id) {
          var el = document.querySelector('[data-mg="' + id + '"]');
          if (el) el.style.display = '';
        });
      }

      garmentSel.addEventListener('change', applyGroups);
      applyGroups();
    }

    /* ── Confirm before destructive actions ──────────────── */
    document.querySelectorAll('[data-confirm]').forEach(function (btn) {
      btn.addEventListener('click', function (e) {
        if (!confirm(btn.dataset.confirm)) e.preventDefault();
      });
    });

    /* ── Receipt auto-print ──────────────────────────────── */
    if (new URLSearchParams(window.location.search).get('print') === '1') {
      window.addEventListener('load', function () {
        setTimeout(function () { window.print(); }, 400);
      });
    }

  });
})();

/* ── v2: Sleeve type toggle (Round Sleeve / Long Sleeve) ─────────── */
function mskToggleSleeve() {
  var sel   = document.getElementById('sleeveType');
  var round = document.getElementById('sleeveRound');
  var long_ = document.getElementById('sleeveLong');
  if (!sel || !round || !long_) return;
  var isLong = sel.value === 'long';
  round.style.display = isLong ? 'none' : '';
  long_.style.display = isLong ? '' : 'none';
}
document.addEventListener('DOMContentLoaded', mskToggleSleeve);
