var globals = {
    // Путь от корня сайта к ajax.php
    ajaxPath: 'ajax.php',

    // Метод для обработки очереди
    process: function(action){
        $.post(this.ajaxPath,{
            action: action
        })
            .success(function(xhr) {
                var response = $.parseJSON(xhr);

                $('#console')
                    .append("<span class='"+response.status+"'>"+response.message+"</span>");

                // Если статус не complete, то рекурсивно повторить
                if(response.status !== 'complete'){
                    globals.process(action);
                }
            });
    }
}

$(function(){
    // При клике на любую ссылку с css классом "db"
    $(".db").click(function(e){
        e.preventDefault();
        // Берем значение data-action, чтобы вызвать одноименный метод через ajax запрос
        var action = $(e.target).data('action');
        // отправляем ajax запрос на урл
        $.post(globals.ajaxPath,{
            action: action
        })
            .success(function(xhr) {
                var response = $.parseJSON(xhr);
                if(response.type == 'message'){
                    $('#console')
                        .empty()
                        .html("<span class='"+response.status+"'>"+response.message+"</span>");
                }
            });

    });

    // Ссылки с классом .screen используются не для ajax, а для каких-то действий на странице
    $(".screen").click(function(e){
        e.preventDefault();
        // Берем значение ее data-action, чтобы понять что будем делать
        var action = $(e.target).data('action');
        switch(action){
            case 'ScreenClear':
                $('#console').empty();
                break;
        }
    });

    // Ссылки с классом .process используются для обработки очереди заданий
    $(".process").click(function(e){
        e.preventDefault();
        // Берем значение data-action, чтобы вызвать одноименный метод через ajax запрос
        var action = $(e.target).data('action');
        $('#console').empty();
        globals.process(action);
    });
});