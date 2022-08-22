<?php
    require "functions.php";

    /*$pageWasRefreshed = isset($_SERVER['HTTP_CACHE_CONTROL']) && $_SERVER['HTTP_CACHE_CONTROL'] === 'max-age=0';

    if($pageWasRefreshed)
    {
        header("Location: login.php");
        die;
    }*/
?>

<!DOCTYPE html>
<html>
    <head>
        <?php require('head.php')?>
        <title>Thesis & Capstone Manager - Index Page</title>
    </head>
    
    <body>
        <div class="wrapper">
            <button type ="submit" id="button">Test</button>
        </div>
    </body>

    <script type="text/javascript">
        $("#button").on('click',function(){
            var closeInSeconds = 10,
                displayText = "<p>A verification code is sent to your email! You will be re-directed in #1 seconds.</p>",
                timer;

            swal({
                title: "Successfully Registered!",
                type: "success",
                html: "<p class='Alert'>A verification code is sent to your email! You will be re-directed in 10 seconds.</p><p>You may click 'Redirect Now' to be re-directed instantly.</p>",
                timer: closeInSeconds * 1000,
                allowOutsideClick: false,
                showConfirmButton: true
            }).then(function() {
                $('#reg-form').submit();
                window.location = "login.php";
            });

            timer = setInterval(function() {

                closeInSeconds--;

                if (closeInSeconds < 0) {
                    clearInterval(timer);
                }

                $('.Alert').html(displayText.replace(/#1/, closeInSeconds));
                image.png
            }, 1000);
        });
    </script>
    <?php require('footer.php'); ?>
</html>