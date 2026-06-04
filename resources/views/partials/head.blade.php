<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'Panel') — Hotel Los Cracks</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&display=swap" rel="stylesheet">

<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    hotel: {
                        dark: '#0f2922',
                        DEFAULT: '#1a3c34',
                        mid: '#2d5a4e',
                        light: '#3d8b7a',
                        muted: '#5a9a8a',
                    },
                },
                fontFamily: {
                    sans: ['"DM Sans"', 'system-ui', 'sans-serif'],
                },
                borderRadius: {
                    DEFAULT: '2px',
                    sm: '2px',
                    md: '4px',
                    lg: '4px',
                },
            },
        },
    };
</script>

<link rel="stylesheet" href="{{ asset('css/hotel.css') }}">

<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
<script defer src="https://unpkg.com/lucide@0.460.0/dist/umd/lucide.min.js"></script>
<script defer src="{{ asset('js/hotel-app.js') }}"></script>

@stack('head')
