function switchTab(tabName) {
    // Sembunyikan semua section halaman
    var sections = document.querySelectorAll('.page-section');
    sections.forEach(function(sec) {
        sec.style.display = 'none';
    });

    // Reset font weight tombol navigasi
    var buttons = document.querySelectorAll('[id^="btn-"]');
    buttons.forEach(function(btn) {
        btn.style.fontWeight = 'normal';
    });

    // Tampilkan section yang dipilih
    var activeSec = document.getElementById('page-' + tabName);
    if (activeSec) {
        activeSec.style.display = 'block';
    }

    // Tebalkan font tombol aktif
    var activeBtn = document.getElementById('btn-' + tabName);
    if (activeBtn) {
        activeBtn.style.fontWeight = 'bold';
    }

    window.location.hash = tabName;
}

// Auto load halaman dari URL hash saat refresh
document.addEventListener('DOMContentLoaded', function() {
    var hash = window.location.hash.replace('#', '');
    if (hash && document.getElementById('page-' + hash)) {
        switchTab(hash);
    } else {
        switchTab('profil');
    }
});
