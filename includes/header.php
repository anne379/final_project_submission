<!-- AI use(17/12/2025): Deepseek for beautification -->

<?php
// includes/header.php
if (!isset($pageTitle)) $pageTitle = 'BankAssist';
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($pageTitle) ?></title>
    
    <!-- Tailwind CSS (CDN for Simplicity) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="js/validation.js" defer></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        'brand-dark': '#0f172a',
                        'brand-primary': '#3b82f6',
                        'brand-accent': '#8b5cf6',
                    },
                    fontFamily: {
                        'sans': ['Inter', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* CSS Variables for Theme Colors */
        /* CSS Variables for Theme Colors */
        :root {
            --bg-color: #f8fafc; /* Light Blue-Grey */
            --text-color: #1e293b; /* Slate 800 */
            --panel-bg: #ffffff;
            --panel-border: #e2e8f0;
            --input-bg: #f1f5f9;
            --input-border: #cbd5e1;
            --heading-color: #0f172a;
        }

        .dark {
            --bg-color: #0f172a; /* Dark Blue */
            --text-color: #e2e8f0; /* Slate 200 */
            --panel-bg: rgba(255, 255, 255, 0.05); /* Glass */
            --panel-border: rgba(255, 255, 255, 0.1);
            --input-bg: rgba(255, 255, 255, 0.05);
            --input-border: rgba(255, 255, 255, 0.1);
            --heading-color: #ffffff;
        }

        /* Glass Panel Adaptation */
        .glass-panel {
            background-color: var(--panel-bg);
            border: 1px solid var(--panel-border);
            border-radius: 1rem;
            transition: background-color 0.3s, border-color 0.3s, box-shadow 0.3s;
        }
        
        /* Light Mode Shadow for depth */
        html:not(.dark) .glass-panel {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        /* Dark Mode Blur */
        .dark .glass-panel {
            backdrop-filter: blur(12px);
            box-shadow: none;
        }

        /* Inputs Adaptation */
        .input-field {
            width: 100%;
            padding: 0.75rem 1rem;
            background-color: var(--input-bg);
            border: 1px solid var(--input-border);
            border-radius: 0.5rem;
            color: var(--text-color);
            outline: none;
            transition: all 0.2s;
        }
        .input-field:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5);
            background-color: var(--panel-bg);
        }

        .btn-primary {
            width: 100%;
            padding: 0.75rem;
            background-color: #3b82f6; /* Solid Blue */
            color: white;
            font-weight: 600;
            border-radius: 0.5rem;
            transition: transform 0.2s, background-color 0.2s;
        }
        .btn-primary:hover {
            background-color: #2563eb; /* Darker Blue on Hover */
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(59, 130, 246, 0.3);
        }

        body {
            color: var(--text-color);
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
            transition: background-color 0.3s, color 0.3s;
        }

        /* Ambient Gradients */
        .ambient-bg {
            position: fixed;
            top: 0; left: 0; width: 100vw; height: 100vh;
            pointer-events: none;
            z-index: -1;
            opacity: 0; /* Hidden in light mode by default for cleaner look */
            background-image: radial-gradient(at 0% 0%, hsla(210,100%,20%,0.3) 0, transparent 50%), 
                              radial-gradient(at 50% 0%, hsla(220,100%,15%,0.3) 0, transparent 50%), 
                              radial-gradient(at 100% 0%, hsla(190,100%,15%,0.3) 0, transparent 50%);
            transition: opacity 0.5s;
        }
        
        .dark .ambient-bg {
             opacity: 1;
        }
    </style>
    
    <script>
        // Apply theme immediately
        if (localStorage.getItem('theme') === 'light') {
            document.documentElement.classList.remove('dark');
        } else {
            document.documentElement.classList.add('dark');
        }
    </script>
</head>
<body class="antialiased selection:bg-brand-primary selection:text-white bg-gray-50 dark:bg-brand-dark transition-colors duration-300">

    <div class="ambient-bg"></div>

    <!-- Theme Toggle -->
    <button id="themeToggle" class="fixed bottom-6 right-6 p-3 rounded-full bg-brand-primary text-white shadow-lg hover:shadow-brand-primary/50 transition-all z-[100] group" title="Toggle Theme">
        <!-- Sun Icon (for Dark Mode) -->
        <svg id="sunIcon" class="w-6 h-6 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
        <!-- Moon Icon (for Light Mode) -->
        <svg id="moonIcon" class="w-6 h-6 block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
    </button>

    <script>
        document.getElementById('themeToggle').addEventListener('click', () => {
            const html = document.documentElement;
            if (html.classList.contains('dark')) {
                html.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            } else {
                html.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            }
        });
    </script>
