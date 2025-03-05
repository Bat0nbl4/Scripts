const $ = require('jquery');

$(document).ready(function() {
    $('#btn').click(function() {
        // Получаем значение из текстового поля с id "myInput"
        var inputValue = $('#inp').val();

        // Отображаем значение в элементе с id "output"
        $('#output').text(inputValue);
    });

    $('#load-post').click(function() {
        var postId = $('#post-id').val();
        var session = null

        $.ajax({
            url: 'posts/' + postId,
            type: 'GET',
            dataType: 'json',
            success: function(data) {

                $.ajax({
                    url: 'user/get_session',
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        session = data;
                        $('#delete-button').html('<p>SESSION GOT</p>');
                    },
                    error: function() {
                        $('#delete-button').html('<p>SOMETHING WRONG!</p>');
                    }
                });

                $('#post').html('<h2>' + data.title + '</h2><p>' + data.text + '</p>');

                console.log(session);
            },
            error: function(xhr) {
                if (xhr.status == 404) {
                    $('#post').html('<p>Пост [' + postId + '] не найден.</p>')
                } else {
                    $('#post').html('<p>Произошла ошибка.</p>');
                }
            }
        });
    });

    $('#delete-post').click(function() {
        var postId = $('#post-id').val();

        $.ajax({
            url: 'posts/' + postId,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                console.log(data)
                $('#post').html('<h2>' + data.title + '</h2><p>' + data.text + '</p>');
            },
            error: function(xhr) {
                if (xhr.status == 404) {
                    $('#post').html('<p>Пост [' + postId + '] не найден.</p>');
                } else {
                    $('#post').html('<p>Произошла ошибка.</p>');
                }
            }
        });
    });
});
