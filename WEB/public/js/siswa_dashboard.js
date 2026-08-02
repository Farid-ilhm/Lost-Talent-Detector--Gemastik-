function switchTab(tabName) {
    // Sembunyikan semua section halaman
    var sections = document.querySelectorAll('.page-section');
    sections.forEach(function(sec) {
        sec.style.display = 'none';
    });

    // Reset class active pada semua tombol category pill
    var buttons = document.querySelectorAll('.cat-pill');
    buttons.forEach(function(btn) {
        btn.classList.remove('active');
    });

    // Tampilkan section yang dipilih
    var activeSec = document.getElementById('page-' + tabName);
    if (activeSec) {
        activeSec.style.display = 'block';
    }

    // Tambahkan class active pada tombol pill yang dipilih
    var activeBtn = document.getElementById('btn-' + tabName);
    if (activeBtn) {
        activeBtn.classList.add('active');
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
