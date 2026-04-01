// ========== Sidebar Scripts ==========

// Sidebar toggle for 即時資料
document.addEventListener('DOMContentLoaded', function () {
    const toggle = document.getElementById('realtime-data-toggle');
    const list = document.getElementById('realtime-data-list');

    if (toggle && list) {
        toggle.addEventListener('click', function () {
            const isVisible = list.style.display !== 'none';
            list.style.display = isVisible ? 'none' : 'block';
        });
    }
});
