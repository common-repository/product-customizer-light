jQuery( function($) {
    $( "#sortable" ).sortable({
        update: function( event, ui ) {
            var new_position = [];
            var url = $("#sortable")[0].baseURI;
            $("#sortable tr").each(function () {
                var td = $(this).find("td");
                new_position.push(td[0].innerHTML);
            });
            new_position = JSON.stringify(new_position);
            $.post(
                url,
                {new_position: new_position});
        }
    });
} );