<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Ventas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/Sistema_Admin/assets/css/style.css" rel="stylesheet">
    <link href="/Sistema_Admin/assets/css/sidebar.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            overflow-x: hidden;
        }
        .app-container {
            display: flex;
            min-height: 100vh;
        }
    </style>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<div class="app-container">
    <script>
    (function() {
        const storageKey = 'theme';
        const body = document.body;

        function setButtonState(btn, icon, label, isDark) {
            if (!btn || !icon || !label) return;
            if (isDark) {
                icon.className = 'fa-solid fa-sun';
                label.textContent = 'Claro';
            } else {
                icon.className = 'fa-solid fa-moon';
                label.textContent = 'Oscuro';
            }
        }

        function applyTheme(theme) {
            const isDark = theme === 'dark';
            body.classList.toggle('dark-mode', isDark);
            const btn = document.getElementById('themeToggle');
            const icon = document.getElementById('themeIcon');
            const label = document.getElementById('themeLabel');
            setButtonState(btn, icon, label, isDark);
        }

        const saved = localStorage.getItem(storageKey);
        const initial = saved ? saved : 'light';
        applyTheme(initial);

        document.addEventListener('click', function(e) {
            const btn = document.getElementById('themeToggle');
            if (!btn) return;
            if (btn.contains(e.target)) {
                const next = body.classList.contains('dark-mode') ? 'light' : 'dark';
                localStorage.setItem(storageKey, next);
                applyTheme(next);
            }
        });
        document.addEventListener('DOMContentLoaded', function() {
            const userInfo = document.querySelector('.main-header .user-info');
            if (!userInfo || document.getElementById('themeToggle')) return;
            const userAvatar = userInfo.querySelector('.user-avatar');
            const btn = document.createElement('button');
            btn.id = 'themeToggle';
            btn.className = 'theme-toggle-btn header-inline';
            btn.title = 'Cambiar tema';
            btn.innerHTML = '<i id="themeIcon" class="fa-solid fa-moon"></i><span id="themeLabel">Oscuro</span>';
            if (userAvatar) {
                userInfo.insertBefore(btn, userAvatar);
            } else {
                userInfo.appendChild(btn);
            }
            const isDark = body.classList.contains('dark-mode');
            const icon = document.getElementById('themeIcon');
            const label = document.getElementById('themeLabel');
            if (icon && label) {
                if (isDark) { icon.className = 'fa-solid fa-sun'; label.textContent = 'Claro'; }
                else { icon.className = 'fa-solid fa-moon'; label.textContent = 'Oscuro'; }
            }
        });
    })();
    </script>