<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name') }}</title>
        <link rel="icon" href="/images/logo.svg" type="image/x-icon">
        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link rel="stylesheet" href="/@sweetalert2/theme-dark/dark.css" id="swal2-theme"/>
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
<style>
.dropdown-dark .vs__dropdown-toggle {
    border-color: white;
}
.dropdown-dark .vs__dropdown-menu {
  background: #1f2937;
  border-color: #FFFFFF;
}
.dropdown-dark .vs__search,
.dropdown-dark .vs__dropdown-toggle,
.dropdown-dark .vs__selected,
.dropdown-dark .vs__dropdown-menu {
  background: #1f2937;
  color: #FFFFFF;
}
.dropdown-dark .vs__clear,
.dropdown-dark .vs__open-indicator {
  fill: #FFFFFF;
}
html,body{
height:100%;
}
</style>
