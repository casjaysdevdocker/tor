        <nav class="nav">
            <a href="index.php" <?php echo ($current_page === 'dashboard') ? 'class="active"' : ''; ?>>
                📊 Dashboard
            </a>
            <a href="config.php" <?php echo ($current_page === 'config') ? 'class="active"' : ''; ?>>
                ⚙️ Configuration
            </a>
            <a href="hidden.php" <?php echo ($current_page === 'hidden') ? 'class="active"' : ''; ?>>
                🧅 Hidden Services
            </a>
            <a href="logs.php" <?php echo ($current_page === 'logs') ? 'class="active"' : ''; ?>>
                📝 Logs
            </a>
            <a href="tokens.php" <?php echo ($current_page === 'tokens') ? 'class="active"' : ''; ?>>
                🔑 API Tokens
            </a>
        </nav>