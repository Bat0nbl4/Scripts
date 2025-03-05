const $ = require("jquery");
const click_sound = new Audio('/sounds/click.mp3');
let clicks = 0;
let miniclicks = 0
let speed = 1;
let interval;

async function data_output(data) {
    $('#message-output').text(data.message);

}
function sleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));

}

function mining() {
    miniclicks += 1;
    if (Math.floor(Math.random() * 1000) + 1 <= 1) {
        click_sound.play()
        $('#mining').prop('checked', false)
        clearInterval(interval);
        interval = setInterval(function() {
            if (speed > 1) {
                speed -= 1
                $('#speed-output').text(speed);
            }
            $('#clicks-output').text(clicks+'.'+miniclicks);
        }, 50);
    }
    if (miniclicks >= 10) {
        clicks += 1
        miniclicks = 0
    }
    if (speed < 500) {
        speed += 1;
        $('#speed-output').text(speed);
        clearInterval(interval);
        interval = setInterval(mining, 550 - speed)
    }
    $('#clicks-output').text(clicks+'.'+miniclicks);
}
$(document).ready(function() {
    $('#mining').change(function() {
        if ($(this).is(':checked')) {
            clearInterval(interval);
            click_sound.play();
            interval = setInterval(mining, 550 - speed);
        } else {
            clearInterval(interval);
            click_sound.play()
            interval = setInterval(function() {
                if (speed > 1) {
                    speed -= 1;
                    $('#speed-output').text(speed);
                }
                $('#clicks-output').text(clicks+'.'+miniclicks);
            }, 50);
        }
    });



    $('#bring-out').click(function () {
        let csrfToken = $('meta[name="csrf-token"]').attr('content');
        $.ajax({
            url: '/game/bring_out',
            type: 'POST',
            data: {
                value: clicks,
            },
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            success: function (data) {
                console.log(data);
                data_output(data)
                if (clicks >= 300) {
                    clicks = 0;
                }
                $('#clicks-output').text(clicks);
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log('Ошибка:', textStatus, errorThrown);
            }
        })
    });
});
