<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" value="{{ csrf_token() }}"/>
        <title>Payslip</title>

        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

        <!-- AdminLTE v4 CSS -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@4.0.0-beta2/dist/css/adminlte.min.css">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>

    <body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">

        <div id="app">
            <router-view></router-view>
        </div>

        <!-- jQuery (requis pour AdminLTE) -->
        <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

        <!-- Bootstrap 5 Bundle -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

        <!-- AdminLTE v4 JS -->
        <script src="https://cdn.jsdelivr.net/npm/admin-lte@4.0.0-beta2/dist/js/adminlte.min.js"></script>

    </body>
</html>
