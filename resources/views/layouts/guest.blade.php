<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'MyFit - AI 다이어트 플래너')</title>

    <!-- Google Fonts - Outfit & Work Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Work+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Custom Tailwind Config -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        heading: ['Outfit', 'sans-serif'],
                        body: ['Work Sans', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#ecfeff',
                            100: '#cffafe',
                            200: '#a5f3fc',
                            300: '#67e8f9',
                            400: '#22d3ee',
                            500: '#06b6d4',
                            600: '#0891b2',
                            700: '#0e7490',
                            800: '#155e75',
                            900: '#164e63',
                        },
                        accent: {
                            50: '#f0fdfa',
                            100: '#ccfbf1',
                            200: '#99f6e4',
                            300: '#5eead4',
                            400: '#2dd4bf',
                            500: '#14b8a6',
                            600: '#0d9488',
                            700: '#0f766e',
                            800: '#115e59',
                            900: '#134e4a',
                        }
                    }
                }
            }
        }
    </script>

    <style>
        body {
            font-family: 'Work Sans', sans-serif;
        }
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Outfit', sans-serif;
        }
        .font-heading {
            font-family: 'Outfit', sans-serif;
        }
        .bg-gradient-mesh {
            background:
                radial-gradient(at 40% 20%, rgba(6, 182, 212, 0.15) 0px, transparent 50%),
                radial-gradient(at 80% 0%, rgba(20, 184, 166, 0.1) 0px, transparent 50%),
                radial-gradient(at 0% 50%, rgba(6, 182, 212, 0.1) 0px, transparent 50%),
                radial-gradient(at 80% 50%, rgba(20, 184, 166, 0.08) 0px, transparent 50%),
                radial-gradient(at 0% 100%, rgba(6, 182, 212, 0.12) 0px, transparent 50%),
                linear-gradient(135deg, #f0fdfa 0%, #ecfeff 50%, #f8fafc 100%);
        }
    </style>

    @stack('styles')
</head>
<body class="bg-gradient-mesh min-h-screen">
    <main>
        @yield('content')
    </main>

    <!-- Vue.js 3 CDN -->
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>

    <!-- Axios for API calls -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <!-- Axios setup -->
    <script>
        axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
        axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').content;
        axios.defaults.baseURL = '/api';
    </script>

    @stack('scripts')
</body>
</html>
