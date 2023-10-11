<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests"> -->

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
        <div id="modals">
        </div>
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
.dropdown-dark .vs__dropdown-toggle {
    border-color: #FFFFFF;
}

.dropdown-dark .vs__dropdown-toggle {
    background: #1f2937;
    border: solid;
    border-color: #ffffff;
    border-width: 1px;
    border-radius: 0px;
}

.vs__dropdown-toggle {
    background: white;
    border: solid;
    border-color: #6b7280;
    border-width: 1px;
    border-radius: 0px;
}
.vs__search {
    color: gray;
}
.dropdown-dark .vs__clear,
.dropdown-dark .vs__open-indicator {
  fill: #FFFFFF;
}
:root {
    --vs-disabled-bg:;
}
html,body{
height:100%;
}


@media
(min-width: 768px) {
    .scrollbar-dark ::-webkit-scrollbar {
        height: 12px;
        width: 12px;
        background: #1f2937;
    }

    .scrollbar-dark ::-webkit-scrollbar-thumb {
        background: #6b7280;
        -webkit-border-radius: 1ex;
    }

    .scrollbar-dark ::-webkit-scrollbar-corner {
        background: #6b7280;
    }
}

.darkModeImage {
    filter: invert(100%) sepia(100%) saturate(0%) hue-rotate(0deg) brightness(95%) contrast(100%);
}

.vs__selected-options {
    flex-wrap: nowrap;
    max-width: calc(100% - 40px);
}

.vs__selected {
    display: block;
    white-space: nowrap;
    text-overflow: ellipsis;
    max-width: 100%;
    overflow: hidden;
}
</style>
