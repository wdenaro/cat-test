<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Image;
use Imagick;
use App\Data;
use PDF;
use PHPHtmlParser\Dom;


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

            $arr['thumb'] = $arr['name']. '.jpg';

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

        $dom = new Dom;
        $dom->load($str);

        // Parse to extract the final width and height from the template
        $info = $dom->find('div#info');
        $final_width = $info->tag->{'data-width'}['value'];
        $final_height = $info->tag->{'data-height'}['value'];
        $pdf_width = $final_width + 70;
        $pdf_height = $final_height + 130;

        // Find elements that are marked for dynamic data
        $elements = $dom->find('div,span[data-name]');

        // This will be the array object that holds all of the dynamic field information
        $fields = array();

        foreach ($elements as $element) {

            // Create a temporary array
            $arr = array();

            // Bring in the marked data from the template into this temp array
            $arr['type'] = $element->tag->{'data-type'}['value'];
            $arr['name'] = $element->tag->{'data-name'}['value'];

            // We require the 'fieldname' used, to follow a naming convention
            $foo = preg_replace('/\s+/', '_', $arr['name']);
            $arr['fieldname'] = strtolower($foo);

            array_push($fields, $arr);
        }
        //now you have an array of all of the fields that need to be populated.

        // This will be the array object we send to the view
        $data = array();
        $data['template_filename'] = $filename;
        $data['fields'] = $fields;
        $data['pdf_width'] = $pdf_width;
        $data['pdf_height'] = $pdf_height;
        $data['final_width'] = $final_width;
        $data['final_height'] = $final_height;

        return view('collect', compact('data'));

    }






    public function build(Request $request) {

        // This will be the array object we send to the view
        $data = array();

        // A timestamp is used as a prefix for image filenames, to allow the use of readable filenames (maintaining
        // original filenames
        $data['timestamp'] = time();

        // Rip through all of the incoming data (these are all of the POST form data fields)
        foreach ($request->keys() as $key) {

            // Skip the token, we don't need it (at this point)
            if ($key != '_token') {

                // Determine if the incoming data is an image or file
                if ($request->hasFile($key)) {

                    // Prepend the filename with timestamp
                    $image_name = $data['timestamp']. '_' .$request->{$key}->getClientOriginalName();

                    // Store the file
                    Storage::putFileAs(
                        'public/templates/images/imported', $request->{$key}, $image_name
                    );

                    // Add the new filename data to the $data array
                    $data[$key] = asset('storage/templates/images/imported/' .$image_name);
                    $data[$key.'___pdf'] = public_path('storage/templates/images/imported/' .$image_name);

                } else {

                    // Transfer the incoming data to the $data array
                    $data[$key] = $request->{$key};

                }
            }
        }

        // Strip-off the unnecessary bits from the full template filename, so we can call it as a view
        $view = explode('.blade.php', $request['template_filename']);

        // Convert all the data necessary to build this template info a JSON string
        $obj = json_encode($data);

        $json_data = new Data;

        $json_data->json_data = $obj;

        $json_data->save();

        $insertedId = $json_data->id;

        $data['id'] = $insertedId;

        return view('templates.' .$view[0], compact('data'));
    }




    public function pdf($id = null) {

        if ($id) {

            $results = Data::where('id', $id)
                ->limit(1)
                ->get();

            $json = $results[0]->json_data;

            $data = json_decode($json, $assoc = TRUE);

            $data['id'] = $id;

// Uncomment if you need to re-target images to full root paths for PDF generation
//            foreach ($data as $key => $value) {
//
//                $target = $key. '___pdf';
//
//                if (array_key_exists($target, $data)) {
//                    $data[$key] = $data[$target];
//                }
//            }


//dd($data);
            // Strip-off the unnecessary bits from the full template filename, so we can call it as a view
            $view = explode('.blade.php', $data['template_filename']);

            $pdf = PDF::loadView('templates.' .$view[0], compact('data'));
            $pdf->setPaper(array(0, 0, $data['pdf_width'], $data['pdf_height']), 'portrait');
            $pdf->getDomPDF()->set_option('dpi', 72);
            $pdf->getDomPDF()->set_option('enable_html5_parser', true);
            $pdf->getDomPDF()->set_option('enable_remote', true);
            $pdf->getDomPDF()->set_option('font_height_ratio', 0.9);

            // TODO: logic to determine if chart has been created, and stored
            // $pdf->save(storage_path('app/public/charts/' .$chart_id. '.pdf'));


            // TODO: Determine naming convention for the downloaded version

            $time = time();

            $pdf->save(public_path('storage/output/pdf/' .$view[0]. '_' .$time. '.pdf'));


            $image = new Imagick();
            $image->readImage(public_path('storage/output/pdf/' .$view[0]. '_' .$time. '.pdf'));
            $image->setImageFormat('jpg');
            $image->cropImage($data['final_width'], $data['final_height'], 36, 36);
            $image->writeImage(public_path('storage/output/image/' .$view[0]. '_' .$time. '.jpg'));

            return $pdf->download($view[0]. '_' .$time. '.pdf');



        } else {
            dd('id required');
        }

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
