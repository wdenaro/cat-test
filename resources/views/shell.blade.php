<!DOCTYPE html>
<html>
<head>
    <script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
</head>
<body>


    @yield('content')


    <script id="localJS" src="{{ asset('js/local.js') }}" data-id="{{ $data['id'] }}"></script>
</body>
</html>
