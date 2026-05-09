        </main>
    </div>
    <script src="assets/js/main.js"></script>
    <script>
        // Update topbar title dynamically based on page
        const path = window.location.pathname;
        const titleEl = document.querySelector('.topbar-title h1');
        if(path.includes('dashboard')) titleEl.innerText = 'Dashboard';
        if(path.includes('donors')) titleEl.innerText = 'Manage Donors';
        if(path.includes('acceptors')) titleEl.innerText = 'Manage Acceptors';
        if(path.includes('inventory')) titleEl.innerText = 'Blood Inventory';
        if(path.includes('low_stock')) titleEl.innerText = 'Low Stock Alerts';
    </script>
</body>
</html>
