<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Choose</title>

    </head>
    <body>

        <h1>Collect Information</h1>
        <p>Please fill in the following information:</p>

        <form method="post" action="{{ route('build') }}" enctype="multipart/form-data">
            {{ csrf_field() }}

            @foreach ($data['fields'] as $field)

                @if ($field['type'] == 'input')

                    <p><input type="text" name="{{  $field['fieldname'] }}" placeholder="{{  $field['name'] }}" size="80"></p>

                @elseif ($field['type'] == 'textarea')

                    <textarea name="{{  $field['fieldname'] }}" placeholder="{{  $field['name'] }}" rows="3" cols="76"></textarea>

                @elseif ($field['type'] == 'image')
                    <label for="{{  $field['fieldname'] }}">{{  $field['name'] }}</label><br>
                    <input type="file" name="{{  $field['fieldname'] }}">

                @endif

            @endforeach

            <input type="hidden" name="template_filename" value="{{ $data['template_filename'] }}">
            <button type="submit">Submit</button>
        </form>

    </body>
</html>
