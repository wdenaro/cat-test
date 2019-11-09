<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Choose</title>

    </head>
    <body>

        <h1>Choose</h1>
        <p>Please choose from the following templates:</p>

        <form method="post" action="{{ route('chosen') }}">
            {{ csrf_field() }}
            <select name="template">
                <optgroup label="Choose One">
                    @foreach ($templates as $template)
                        <option value="{{ $template['filename'] }}">{{ $template['name'] }}</option>
                    @endforeach
                </optgroup>
            </select>
            <button type="submit">Submit</button>
        </form>

    </body>
</html>
