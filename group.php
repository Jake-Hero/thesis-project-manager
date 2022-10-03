<?php
    require "includes/functions.php";
    is_user_valid();
    is_user_login();

    if($_SESSION['user']['role'] >= ROLE_ADVISOR)
    {
        header("Location: " . ROOT_FOLDER . "/admin/group.php");
        die;
    }

    $groupid = $_SESSION['user']['group_id'];
    $query = "SELECT * FROM groups WHERE groupid = :id";
    $selectStmt = $con->prepare($query);
    $selectStmt->bindValue('id', $groupid, PDO::PARAM_INT);
    $selectStmt->execute();

    $currentPage = 'group';
    require('includes/header.php');
?>

<!DOCTYPE html>
<html>
    <head>
        <?php require('head.php')?>
        <title>Thesis & Capstone Manager - Group</title>

        <style>
            table.table tr th, table.table tr td {
                border: 1px solid rgba(0, 0, 0, 0.1);
                background-color: rgba(240, 240, 240, 0.1) !important
            }

            table.table td:last-child {
                width: 130px;
            }

            table.table td a.edit {
                color: #2196F3;
            }

            table.table td a.delete {
                color: #ff0000;
            }

            table.table td i {
                font-size: 19px;
            }

            #comment-content
            { 
                height: 70vh; 
                overflow-x: scroll; 
                overflow-y: auto;
                width: 100%; 
            }

            #form_comment_2
            {
                display: none;
            }

            button,
            button:active,
            button:focus, 
            button:hover,
            .btn,
            .btn:active, 
            .btn:focus, 
            .btn:hover{   
                border:none !important;
                outline:none !important;
            }

            form.comment_reply {
                display: none;
            }
        </style>
    </head>

    <body>
        <div class="grey-wrapper">
            <div class="container-fluid header mt-4 mb-3">            

                <?php if($selectStmt->rowCount() > 0): ?>
                    <?php $group_row = $selectStmt->fetch(); ?>

                    <div class="row mx-auto d-flex justify-content-evenly mb-4">
                        <div class="col-md-12">
                            <h3 class="border-bottom border-3 border-warning" style="font-family: 'Times New Roman'; font-weight: bold;">Group Activity</h3>
                            <div class="jumbotron jumbotron-fluid border-start border-warning border-4 bg-light">
                                <div class="container-fluid">
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <dl>
                                                <dt><b class="border-bottom border-primary">Thesis Title</b></dt>
                                                <dd><p class="mt-3"><?php echo $group_row['group_title']; ?></p></dd>
                                            </dl>

                                            <dl>
                                                <dt><b class="border-bottom border-primary">Thesis Leader</b></td>
                                                <dd class="mt-2">
                                                    <?php
                                                        $query = "SELECT id, fullname, image FROM users WHERE id = :id";
                                                        $selectStm = $con->prepare($query);
                                                        $selectStm->execute(['id' => $group_row['group_leader']]);
                                                        if($selectStm->rowCount() > 0):
                                                            $rows = $selectStm->fetch();
                                                    ?>

                                                        <a href="" class="edit" title="<?php echo $rows['fullname'] ?>" data-toggle="tooltip">
                                                            <img src="<?php echo ROOT_FOLDER . '/assets/profile_pictures/' .$rows['image'] ?>" class="rounded-circle shadow-sm border-info img-sm mr-3" style="width: 40px; height: 40px;" alt="Avatar" />
                                                        </a>

                                                    <?php else: ?>

                                                        <p>No group leader</p>

                                                    <?php endif; ?>
                                                </dd>
                                            </dl>
                                        </div>

                                        <div class="col-md-6">
                                            <dl>
                                                <dt><b class="border-bottom border-primary">Members</b></dt>
                                                <dd>
                                                    <p class="mt-2">
                                                        <?php
                                                            $query = "SELECT id, fullname, image FROM users WHERE group_id = :id";
                                                            $selectStm = $con->prepare($query);
                                                            $selectStm->execute(['id' => $groupid]);
                                                            if($selectStm->rowCount() >= 2):
                                                            while($rows = $selectStm->fetch(PDO::FETCH_ASSOC)): 
                                                                if($group_row['group_leader'] == $rows['id']) continue;
                                                            ?>
                                                            
                                                            <a href="" class="edit" title="<?php echo $rows['fullname'] ?>" data-toggle="tooltip">
                                                                <img src="<?php echo ROOT_FOLDER . '/assets/profile_pictures/' .$rows['image'] ?>" class="rounded-circle shadow-sm border-info img-sm mr-3" style="width: 40px; height: 40px;" alt="Avatar" />
                                                            </a>

                                                            <?php endwhile; ?>

                                                            <?php else: ?>
                                                            
                                                            <p class="mt-3">This thesis group has no member.</p>
                                                        <?php endif; ?>
                                                    </p>
                                                </dd>
                                            </dl>

                                            <dl>
                                                <dt><b class="border-bottom border-primary">Created on</b></dt>
                                                <dd class="mt-2">
                                                    <?php echo $group_row['creation'] ?>
                                                </dd>
                                            </dl>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <dl>
                                            <dt><b class="border-bottom border-danger">Recent Group Log</b></dt>
                                        </dl>

                                        <dl>
                                            <?php 
                                                $query = "SELECT * FROM group_logs WHERE groupid = :id ORDER BY id DESC LIMIT 5;";
                                                $selectStmt = $con->prepare($query);
                                                $selectStmt->bindValue('id', $groupid, PDO::PARAM_INT);
                                                $selectStmt->execute();
                                            ?>

                                            <?php if($selectStmt->rowCount() > 0): ?>
                                                <?php while($activities_row = $selectStmt->fetch(PDO::FETCH_ASSOC)): ?>
                                                    <dd><?php echo $activities_row['log_details'] . " on " . $activities_row['log_date']; ?></dd>
                                                <?php endwhile; ?>
                                            <?php else: ?>
                                                <tr class="table-light text-center">
                                                    <dd>No recent activities.</dd>
                                                </tr>
                                            <?php endif; ?>
                                        </dl>
                                    </div>

                                </div>
                            </div>    
                        </div>
                    </div>

                    <div class="row mx-auto">
                        <div class="col-lg-6">
                            <div id="card-id" class="card">
                                <div class="card-header" style="font-family: 'Lemon/Milk', sans-serif;">Panelist Comments</div>
                                <div id="comment-content" class="card-body">
                                    <form id="form_comment">
                                        <div id="replying_to" class="alert alert-dismissible" role="alert" style="display:none;">
                                            <strong></strong>
                                            <button type="button" id="replying_hide" class="btn-close"></button>
                                        </div>

                                        <input type="hidden" name="comment_id" id="commentId" />
                                        <input type="hidden" name="author" value="<?php echo $_SESSION['user']['fullname'] ?>" />

                                        <div class="input-group">
                                            <img id="commentPic" src="<?php echo ROOT_FOLDER . '/assets/profile_pictures/' .$_SESSION['user']['image'] ?>" id="preview" class="rounded-circle mt-3 mx-2" style="width: 40px; height: 40px;" alt="Avatar" />
                                            <textarea class="form-control mx-3" name="comment" id="comment" placeholder="Your comment here"></textarea>
                                            <button id="publishBtn" class="btn" type="button">
                                                <img src="<?php echo ROOT_FOLDER . '/assets/images/send_button.svg'; ?>" style="height: 32px; width: 32px;">
                                            </button>
                                        </div>
                                    </form>
                                    <div id="view_comment"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php endif; ?>
            </div>
        </div>
    </body>
</html>

<script>
    $(function(){
        $("#replying_hide").on("click", function(){
            $('#replying_to').hide();
            $("#commentId").val(0);
        });
    });

    function postReply(commentId, posted_by) {
        $('#commentId').val(commentId);
        $('#comment-content').scrollTop(0);

        ShowAlert('You are replying to ' + posted_by);
    }

    function ShowAlert(text) {
        var AlertMsg = $('div[role="alert"]');
        $(AlertMsg).find('strong').html(text);
        $(AlertMsg).removeAttr('class');
        $(AlertMsg).addClass('alert alert-primary');
        $(AlertMsg).show();
    }

    function deleteReply(commentId) {
        swal({
                title: 'Are you sure?',
                text: "You won't be able to undo this action.",
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Delete'
            }).then((result) => {
                if (result.value) {
                    $.ajax({
                        type: 'GET', 
                        url: 'src/comment_delete.php',
                        data: {'comment_id' : commentId},
                        success: function(response) {
                            if(response=="success") {
                                listComment();
                                Swal.fire(
                                    'Deleted',
                                    'Comment Deleted.',
                                    'success'
                                )
                            } else {
                                Swal.fire(
                                    'Error',
                                    'Something went wrong.',
                                    'error'
                                )
                            }
                        }
                    });
                }
            });
    }

    $("#publishBtn").click(function () {
        var str = $("#form_comment").serialize();
        if($("#comment").val()) {
            $.ajax({
                url: "src/comment_add.php",
                data: str,
                type: 'get',
                success: function (response)
                {
                    var result = eval('(' + response + ')');
                    if (response)
                    {
                        if($("#comment").val()) {
                            $("#comment").val("");
                            $("#commentId").val("");
                            
                            listComment();
                            $('html, body').animate({ scrollTop: $("#comment").offset().top }, 0);
                        }
                    } 
                }
            });
        }
    });

    $(document).ready(function () {
        listComment();
    });

    function listComment() {
        $('#replying_to').show();
        $('#replying_to').hide();

        $.ajax({
            url:"src/comment_list.php",
            method:"POST",
            success:function(response)
            {
                $('#view_comment').html(response);
            }
        })
    }
</script>