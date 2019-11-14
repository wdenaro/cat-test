<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Choose</title>

        <style>
            .template_wrapper {
                display: inline-block;
                border: 1px solid #000;
            }
            .template_wrapper:hover {
                background-color: yellow;
            }
            .template_wrapper p {
                text-align: center;
            }
            .template_wrapper img {
                cursor: pointer;
            }
        </style>

        <script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>

    </head>
    <body>

        <h1>Choose</h1>
        <p>Please choose from the following templates:</p>

        @foreach ($templates as $template)
        <form method="post" action="{{ route('chosen') }}">
            {{ csrf_field() }}
            <div class="template_wrapper">
                <p>{{ $template['name'] }}</p>
                <img src="{{ asset('storage/templates/images/thumbnails/' .$template['thumb']) }}">
                <input type="hidden" name="template" value="{{ $template['filename'] }}">
            </div>
        </form>
        @endforeach

    <script>
        $(function() {
           $('.template_wrapper img').on('click', function() {
              $(this).parent().parent().submit();
           });
        });
    </script>

    </body>
</html>
