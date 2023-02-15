<?php
    require "./libs/functions.php";
    
    is_user_valid();
    is_user_login();

    if(!isset($_GET['id']))
    {
        header("Location: ./archive.php");
        die;
    }

    if(isset($_GET['id']))
	{
		$id = $_GET['id'];

        $query = "SELECT * FROM archives WHERE id = :id LIMIT 1;";
        $selectStm = $con->prepare($query);
        $selectStm->execute(['id' => $id]);
        $row = $selectStm->fetch(PDO::FETCH_ASSOC);

        if(!$row)
        {
            header("Location: ./archives.php");
            die;
        }
	}

    $currentPage = 'archives';
    require('./libs/header.php');
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <link rel="shortcut icon" type="image/jpg" href="./favicon.ico"/>
        <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
        <link href="./bootstrap/css/bootstrap.min.css" rel="stylesheet" media="nope!" onload="this.media='all'">
        <link rel="stylesheet" href="./css/style.css">

        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.1/jquery.min.js" integrity="sha512-aVKKRRi/Q/YV+4mjoKBsE4x3H+BkegoM/em46NNlCqNTmUYADjBbeNefNxYV7giUp0VxICtqdrbqU7iVaeZNXA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script type="text/javascript" src="./js/lastseen.js"></script>
        <script type="text/javascript" src="./js/archive_filter.js"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" integrity="sha512-xh6O/CkQoPOWDdYTDqeRdPCVd1SpvCA9XXcUnZS2FmJNp1coAFzvtCN9BmamE+4aHK8yyUHUSCcJHgXloTyT2A==" crossorigin="anonymous" referrerpolicy="no-referrer" />

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ho+j7jyWK8fNQe+A12Hb8AhRq26LrZ/JpcUGGOn+Y7RsweNrtN/tE3MoK7ZeZDyx" crossorigin="anonymous"></script>
        <script src="./bootstrap/js/bootstrap.min.js"></script>

        <title>Thesis & Capstone Manager - Viewing Archive</title> 

        <style>
            #canvas_container {
                background: #333;
                text-align: center;
                border: solid 3px;
            }

            /*#pdf_renderer
            {
                height: 100vh;
                width: 100wh;
            }*/
        </style>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.0.943/pdf.min.js"></script>
    </head>

    <body>
        <div class="wrapper-follow">
            <div class="container mt-3 mb-3 bg-white border border-dark rounded-start rounded-end px-5 py-4" style="--bs-bg-opacity: .5;">
            
                <div class="col-lg-12">
                    <div class="text-center">
                        <h1 style="font-size: 35px; font-family: 'Lemon/Milk', sans-serif; color: white; -webkit-text-stroke: 1px black;"><?php echo $row['title']; ?></h1>
                        <h5 style="font-size: 14px;">Year Published: <?php echo $row['publishedyear']; ?> | Department: <?php echo categorizedDepartment($row['department']); ?> </h5>
                    
                        <div id="my_pdf_viewer">
                            <div id="navigation_controls">
                                <button id="go_previous" class="btn btn-warning">Previous</button>
                                <input id="current_page" value="1" type="number"/>
                                <button id="go_next" class="btn btn-warning">Next</button>
                            </div>

                            <div id="zoom_controls">  
                                <button id="zoom_in" class="btn btn-success">+</button>
                                <button id="zoom_out" class="btn btn-danger">-</button>
                            </div>

                            <div id="canvas_container">
                                <canvas id="pdf_renderer"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>

    <script>
        var myState = {
            pdf: null,
            currentPage: 1,
            zoom: 1
        }

        var file = './assets/archives/<?php echo $row['file']; ?>';
    
        pdfjsLib.getDocument(file).then((pdf) => {
    
            myState.pdf = pdf;
            render();
        });
        
        document.getElementById('go_previous').addEventListener('click', (e) => {
            if(myState.pdf == null || myState.currentPage == 1) 
            return;
            myState.currentPage -= 1;
            document.getElementById("current_page").value = myState.currentPage;
            render();
        });
        document.getElementById('go_next').addEventListener('click', (e) => {
            if(myState.pdf == null || myState.currentPage > myState.pdf._pdfInfo.numPages) 
            return;
            myState.currentPage += 1;
            document.getElementById("current_page").value = myState.currentPage;
            render();
        });
        document.getElementById('current_page').addEventListener('keypress', (e) => {
            if(myState.pdf == null) return;
        
            // Get key code 
            var code = (e.keyCode ? e.keyCode : e.which);
        
            // If key code matches that of the Enter key 
            if(code == 13) {
                var desiredPage = 
                document.getElementById('current_page').valueAsNumber;
                                
                if(desiredPage >= 1 && desiredPage <= myState.pdf._pdfInfo.numPages) {
                    myState.currentPage = desiredPage;
                    document.getElementById("current_page").value = desiredPage;
                    render();
                }
            }
        });
        document.getElementById('zoom_in').addEventListener('click', (e) => {
            if(myState.pdf == null || myState.zoom >= 1.5) return;

            myState.zoom += 0.5;
            render();
        });
        document.getElementById('zoom_out').addEventListener('click', (e) => {
            if(myState.pdf == null || myState.zoom <= 1) return;
            myState.zoom -= 0.5;
            render();
        });

        function render() {
            myState.pdf.getPage(myState.currentPage).then((page) => {
        
                var canvas = document.getElementById("pdf_renderer");
                var ctx = canvas.getContext('2d');
    
                var viewport = page.getViewport(myState.zoom);
                canvas.width = viewport.width;
                canvas.height = viewport.height;
        
                page.render({
                    canvasContext: ctx,
                    viewport: viewport
                });
            });
        }
    </script>
</html>