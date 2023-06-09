<!DOCTYPE html>
<html lang="en">

<head>
    @include('admin.layout.common-head')
</head>
<style>
    .menu-img-class{
        height: 25px;
        width: 25px;
    }
    .custom-margin-auto{
        margin: 0 0 0 auto;
    }
    .custom-button-class{
        margin-right: 17px !important;
       margin-bottom: 0rem !important;
    }
</style>
<body class="g-sidenav-show  bg-gray-200">

    @include('admin.layout.sidebar')
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        @include('admin.layout.header')
        
        @yield('content')
        @include('admin.layout.footer')
    </main>
    @include('admin.layout.common-end')
    @yield('scripts')
    @stack('custom-scripts')
</body>

</html>