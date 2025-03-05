const $ = require('jquery');

const take_card_sound = new Audio('/sounds/take_card.mp3')
const xSlide = new Audio('/sounds/x-slide.mp3')
const BigWin = new Audio('/sounds/big_win.mp3')
const LiteWin = new Audio('/sounds/lite_win.mp3')

const combinations = {
    'header':null,
    'pair':1,
    'two_pairs':2,
    'set':4,
    'straight':9,
    'flush':10,
    'full_house':35,
    'four_of_a_kind':50,
    'straight_flush':250,
    'flush_royal':500,
};

let multiplier = 1;
let bet = 1;

$.ajax({
    url: '/game/get_bet',
    type: 'GET',
    dataType: 'json',
    success: function(data) {
        multiplier = data.multiplier
        set_multiplier_column()
        bet = data.bet
        $('#bet').val(bet)
        calculate_bets()
        if (data.message) {
            message_output(data.message);
        }
    },
    error: function(jqXHR) {
        console.log(jqXHR.responseText)
    }
});

async function win_cell(data, blink_count = 5) {
    $('#combination-output').text(data.combination);
    if (data.combination in combinations) {
        let cell = $('#'+data.combination).find('.select');
        for (let i = 0; i < blink_count; i++) {
            cell.removeClass('select');
            await sleep(100);
            cell.addClass('select');
            await sleep(100);
        }
    }
}

async function data_output(data) {
    $('#message-output').text(data.message);
    $('#money').text(data.money);
    if (data.rolls == 1) {
        $('#get-rnd-set').text("DISCARD THE SELECTED CARDS");
    } else {
        $('#get-rnd-set').text("START");
    }
}

function set_bet() {
    let csrfToken = $('meta[name="csrf-token"]').attr('content');
    $.ajax({
        url: '/game/set_bet',
        type: 'POST',
        data: {
            bet: bet,
            multiplier: multiplier
        },
        headers: {
            'X-CSRF-TOKEN': csrfToken // Добавляем CSRF токен в заголовок
        },
        success: function (data) {
            data_output(data);
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.log('Ошибка:', textStatus, errorThrown);
        }
    })
}

function playTakeCardSound() {
    take_card_sound.play()
        .catch(error => {
            console.error("Ошибка воспроизведения звука:", error);
        });
}

function sleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}

async function set_cards_img(data, instant = false) {
    let len = 0
    try {
        len = Object.keys(data.on_table).length;
    } catch (TypeError) {
        len = 0
    }
    for (let i = 1; i <= len; i++) {
        $('#card'+i+'_checkbox').prop('checked', false)
        if ($('#card'+i).children('.card').attr('src') != '/img/cards/'+data.on_table[i]+'.png') {
            $('#card'+i).html('<img class="card" src="/img/cards/'+data.on_table[i]+'.png" />');
            if (!instant) {
                playTakeCardSound()
                await sleep(500)
            }
        }
    }
    if (Object.keys(combinations).slice(-3).includes(data.combination)) {
        win_cell(data, 60);
        xSlide.play()
    } else if (Object.keys(combinations).slice(4, Object.keys(combinations).length - 3).includes(data.combination)) {
        win_cell(data, 15)
        BigWin.play()
    } else if (Object.keys(combinations).slice(1, Object.keys(combinations).length - 6).includes(data.combination)) {
        win_cell(data)
        LiteWin.play()
    } else {
        win_cell(data)
    }
}

function set_multiplier_column() {
    for (let i = 0; i < Object.keys(combinations).length; i++) {
        let row = $('#'+Object.keys(combinations)[i])
        for (let j = 1; j <= 5; j++) {
            let col = row.find('#'+j)
            if (j == multiplier) {
                col.addClass('select');
            } else {
                col.removeClass('select');
            }
        }
    }
}

function calculate_bets() {
    for (let i = 1; i < Object.keys(combinations).length; i++) {
        let row = $('#'+Object.keys(combinations)[i])
        for (let j = 1; j <= 5; j++) {
            row.find('#'+j).text(combinations[Object.keys(combinations)[i]] * j * bet);
        }
    }
}

$(document).ready(function() {
    $.ajax({
        url: '/game/get_cards',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            set_cards_img(data, true);
            data_output(data);
        },
        error: function(jqXHR) {
            console.log(jqXHR.responseText)
        }
    });

    $('#volume-control').on('input', function() {
        take_card_sound.volume = parseFloat($(this).val());
        xSlide.volume = parseFloat($(this).val());
        LiteWin.volume = parseFloat($(this).val());
        BigWin.volume = parseFloat($(this).val());
    });

    $('#bet').change(function () {
        console.log($('#bet').val());
        bet = $('#bet').val();
        calculate_bets();
    });

    $('#minus').click(function (){
        multiplier -= 1;
        console.log(multiplier)
        if (multiplier < 1) {
            multiplier = 1;
        } else {
            set_multiplier_column();
        }
    });

    $('#plus').click(function (){
        multiplier += 1;
        console.log(multiplier)
        if (multiplier > 5) {
            multiplier = 5;
        } else {
            set_multiplier_column()
        }
    });

    $('#set-bet').click(function (){
        set_bet();
    })

    $('#get-rnd-set').click(async function() {
        let cards_to_drop = [];
        for (let i = 1; i <= 5; i++) {
            if ($('#card'+i+'_checkbox').is(':checked') == true) {
                cards_to_drop.push(i);
            }
        }
        $.ajax({
            url: '/game/get_RSet?' + $.param({ items: cards_to_drop }),
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                data_output(data);
                set_cards_img(data, false);
            },
            error: function(jqXHR) {
                console.log(jqXHR.responseText);
            }
        });
    });
});
