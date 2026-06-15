    </main>
    <footer class="bg-gray-900 border-t border-gray-800 p-6 text-center text-gray-500 mt-10">
        <p>&copy; <?php echo date('Y'); ?> Bracket List Tournament. Built for competitive play.</p>
    </footer>

    <script>
        // Security Protections
        document.addEventListener('contextmenu', event => event.preventDefault());
        
        document.addEventListener('keydown', function(e) {
            // Disable Ctrl+U, Ctrl+Shift+I, J, C
            if (e.ctrlKey && (e.key === 'u' || e.key === 'U')) e.preventDefault();
            if (e.ctrlKey && e.shiftKey && ['I','i','J','j','C','c'].includes(e.key)) e.preventDefault();
            // Disable Zoom (Ctrl + / - / 0)
            if (e.ctrlKey && ['+','-','0'].includes(e.key)) e.preventDefault();
        });

        // Disable trackpad/scroll zoom
        document.addEventListener('wheel', function(e) {
            if (e.ctrlKey) e.preventDefault();
        }, { passive: false });

        // Disable drag on images
        document.querySelectorAll('img').forEach(img => {
            img.addEventListener('dragstart', e => e.preventDefault());
        });
    </script>
</body>
</html>
