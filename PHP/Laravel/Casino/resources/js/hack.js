const $ = require("jquery");

$(document).ready(function() {
    console.log('1');
    $('#try').on('input', function () {
        console.log('2');
        var inputVal = $(this).val().replace(/\s+/g, '');
        $(this).val(inputVal);
    });

    $('#font-size').on('input', function() {
        console.log($(this).val() + 'pt')
        $('#hack').css('font-size', $(this).val() + 'pt');
    });
});
