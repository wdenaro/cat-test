<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PDF;
use Sunra\PhpSimple\HtmlDomParser;


class MainController extends Controller {





    // In order to give the user a choice of what templates are available, we need to dig through the filesystem and
    // build an array object of filenames and (somewhat readable) names to send to the view
    public function choose() {

        // This will be the array object we send to the view
        $templates = array();

        // Grab all blade.php filenames from the storage/app/public/templates folder
        $template_files = array_filter(Storage::disk('public')->files('templates/'), function ($item) {
            return strpos($item, 'blade.php');
        });

        // Proccess this new array of filenames
        foreach ($template_files as $templates_file) {

            // Temporary array
            $arr = array();

            // Throw-away the preceeding folder name. We don't need it, we know where the templates live
            $parts = explode('templates/', $templates_file);
            $arr['filename'] = $parts[1];

            // In order to make the template names a little more readable, lose the '.blade.php' portion of the filename
            $parts = explode('.blade.php', $arr['filename']);
            $arr['name'] = $parts[0];

            // Push information for each template into the array object
            array_push($templates, $arr);

        }

        // Send the user to the view along with all the template information. The view will dynamically create a drop-
        // down list of templates, for the user to choose from
        return view('choose', compact('templates'));
    }






    // In order to give the user the availability to populate the template, we need to dig through the template to find
    // all of the marked areas that can accept dynamic data
    public function chosen(Request $request) {

        // The incoming template filename
        $filename = $request['template'];

        // Convert the template into text
        $str = Storage::disk('public')->get('templates/' .$filename);

        // TODO: Replace this with a DOM-Parser in order to pass field names and types of data to collect.

        $dom = HtmlDomParser::str_get_html($str);

        $elements = $dom->find('[data-name]');

//dd($elements[0]->attr);

        // This will be the array object we send to the view
        $fields = array();

        foreach ($elements as $element) {

            $arr = array();

            $arr['type'] = $element->attr['data-type'];
            $arr['name'] = $element->attr['data-name'];

            $foo = preg_replace('/\s+/', '_', $arr['name']);
            $arr['fieldname'] = strtolower($foo);



            array_push($fields, $arr);
        }

        //now you have an array of all of the fields that need to be populated.

        $data = array();
        $data['template_filename'] = $filename;
        $data['fields'] = $fields;

// dd($data);
//return redirect()->view('collect', compact('data'));
        return view('collect', compact('data'));

    }






    public function build(Request $request) {

        $data = array();
        $data['timestamp'] = time();

        foreach ($request->keys() as $key) {

            if ($key != '_token') {

                if ($request->hasFile($key)) {

                    $image_name = $data['timestamp']. '_' .$request->{$key}->getClientOriginalName();

                    Storage::putFileAs(
                        'public/templates/images/imported', $request->{$key}, $image_name
                    );

                    $data[$key] = $image_name;

                } else {

                    $data[$key] = $request->{$key};

                }
            }

        }

//    dd($data);
//        $data = $request;
//        $data['timestamp'] = time();
//
//        // Create an array that contains the Form names of each image (file) that is potentially coming in
//        $image_keys = $request->files->keys();
//
//        // Rip through this array
//        foreach ($image_keys as $key) {
//
//            // Check to see if a file was attached
//            if ($request->hasFile($key)) {
//
//                $image_name = $data['timestamp']. '_' .$request->{$key}->getClientOriginalName();
//
//                Storage::putFileAs(
//                    'public/templates/images/imported', $request->{$key}, $image_name
//                );
//
//                $data[$key]->pathname = $request->{$key}->getClientOriginalName();
//
//                //dd('file stored');
//
//                //print $key;
//
//                //dd($request->{$key}->getClientOriginalName());
//
//                //dd($request->{$key});
//            }
//
//        }

//dd($data->event_image);
//dd($image_keys);

//dd($request->event_image->getClientOriginalName());

    //foreach ($request->files as $file) {
        //print $file;
//print $file->getClientOriginalName();
        //$image_name = $data['timestamp']. '_' .$file->getClientOriginalName();

       // Storage::putFileAs(
            //'public/templates/images/imported', $file, $image_name
        //);

    //}

    //dd($data);

        $view = explode('.blade.php', $request['template_filename']);

    // Convert all the data necessary to build this template info a JSON string
        $obj = json_encode($data);

        return view('templates.' .$view[0], compact('data'));
    }






    public function test_a($pdf = null) {

        $text = Storage::disk('public')->get('templates/spotlight_space.blade.php');

        preg_match_all(
            '/data-name=\".+\"/', $text, $matches
        );

        $fields = array();

        foreach ($matches[0] as $match) {
            $foo = preg_replace('/data-name=\"/', '', $match);
            $bar = preg_replace('/\"/', '', $foo);
            $foobar = preg_replace('/\s+/', '_', $bar);
            $woot = strtolower($foobar);

            array_push($fields, $woot);
        }

        dd($fields);

        $data = array();
        $data['event_name'] = 'Stress solutions<br>for the digital age';
        $data['event_description'] = 'XXX<br>XXX';
        $data['speaker_name'] = 'with Delaney Rutson';
        $data['date_time'] = '11/12 at 6:30 pm<br>XXX';

        if (!$pdf) {

            return view('templates.spotlight_space.001', compact('data'));

        } else {

            $pdf = PDF::loadView('templates.spotlight_space.001', compact('data'));
            $pdf->setPaper('tabloid', 'portrait');
            $pdf->getDomPDF()->set_option('enable_html5_parser', true);

            // TODO: logic to determine if chart has been created, and stored
            // $pdf->save(storage_path('app/public/charts/' .$chart_id. '.pdf'));

            // TODO: Determine naming convention for the downloaded version
            return $pdf->download('template_001.pdf');

        }
    }






}
