
function fill(Value) {
    $('#search').val(Value);
    $('#display').hide();
 }

 $(document).ready(function() {

    $("#search").keyup(function() {

        var name = $('#search').val();

        if (name != "") 
        {
            $.ajax({

                type: "POST",
                url: "./src/archive_filter.php",
                data: {
                    search: name
                },
                success: function(html) {
                    $("#display").html(html);
                }
            });
        }
    });
 });