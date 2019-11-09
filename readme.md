#Templates
Templates are stored in **storage/app/public/templates** and should contain
appropriate markup in order to become dynamic.

##Markup
Add a custom data attribute to a wrapper in order to make it dynamic, see examples below:

*Original Code*
#####`<span class="speaker">Name goes here</span><br>`

*Marked-up code*
#####`<span class="speaker" data-name="Speaker Name">{!! $data['speaker_name'] !!}</span><br>`

Giving this DOM parser a try, it took some time to install though...
https://packagist.org/packages/sunra/php-simple-html-dom-parser
