(function () {
    const sidebarOpen = document.getElementById('sidebar-open');
    const sidebarClose = document.getElementById('sidebar-close');
    const sidebar = document.getElementById('sidebar');

    sidebarOpen.addEventListener('click', function () {
        sidebar.classList.remove('-translate-x-full');
    });

    sidebarClose.addEventListener('click', function () {
        sidebar.classList.add('-translate-x-full');
    });
})();
