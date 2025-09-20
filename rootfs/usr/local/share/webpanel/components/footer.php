        </div>
    </div>
    
    <footer class="footer">
        <div class="footer-content">
            <p>&copy; 2025 Tor Admin Panel | 
               <a href="https://www.torproject.org/" target="_blank">Tor Project</a> | 
               <span id="last-update"></span>
            </p>
        </div>
    </footer>

    <script>
        // Update last refresh time
        document.getElementById('last-update').textContent = 'Last updated: ' + new Date().toLocaleTimeString();
        
        // Mobile menu toggle
        function toggleMobileMenu() {
            const nav = document.querySelector('.nav');
            nav.classList.toggle('mobile-open');
        }
        
        // Auto-refresh functionality
        <?php if (isset($auto_refresh)): ?>
        setTimeout(function() {
            window.location.reload();
        }, <?php echo $auto_refresh * 1000; ?>);
        <?php endif; ?>
    </script>
</body>
</html>