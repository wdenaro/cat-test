$(function() {


    var id = $('#localJS').attr('data-id');


    $('body').prepend('<a href="pdf/' +id+ '"><button id="pdf_button">Get PDF</button></a>');


    $('body').prepend('<button id="back_button">Back</button>');
    $('#back_button').on('click', function() {
        window.history.back();
    });



});
