<?php require_once("core-functions.php");?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Post | Admin Panel</title>
    <?php echo BOOTSTRAP_CSS; echo STYLES; echo ICONS;?>
    <style>
        .post
        {
            box-shadow: 0 0 0.8rem #e1e1e1;
            padding: 0.5rem;
            border: 1px solid #e1e1e1;
            user-select: none;
        }
        .post-meta
        {
            padding: 0;
            margin: 0;
            list-style-type: none;
        }
        .post-meta>li
        {
            padding: 0.5rem;
            border: 1px solid #e1e1e1;
        }
    </style>
</head>
<body>

<?php
$error = [];
$posts=[];
#require("../../config.php");
function get_posts($offset,$limit)
{
    global $pdo,$error,$posts;
    try
    {
        $stmt=$pdo->prepare("SELECT * FROM `".POSTS_TABLE."` ORDER BY `post_id` DESC LIMIT :offset,:limit;");
        $stmt->bindValue(":offset",$offset,PDO::PARAM_INT);
        $stmt->bindValue(":limit",$limit,PDO::PARAM_INT);
        $stmt->execute();
        $posts=$stmt->fetchAll();
    }
    catch(PDOException $e)
    {
        array_push($error,$e->getMessage());
    }
}

if(isset($_GET['page_no']))$page_no=$_GET['page_no']; else $page_no="1";
if(ctype_digit($page_no))
{
    $limit=10;
    $offset = ($page_no-1)*$limit;

    $total_posts=$pdo->query("SELECT COUNT(`post_id`) FROM ".POSTS_TABLE.";")->fetch()[0];
    get_posts($offset,$limit);
    $total_pages=ceil($total_posts/$limit);
}
else
{
    array_push($error,"page number must be integer");
}



if (count($error) > 0){
    foreach ($error as $err)
    {
        echo '<div class="alert alert-info" role="alert">' . $err . '</div>';
    }
}
else
{ ?>

    <!-- MODAL -->
    <div style="cursor: pointer" class="modal fade user-select-none"  id="modal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div id="modal_title" class="d-flex justify-content-center p-3"></div>
                <hr class="m-0 bg-dark">
                <div class="p-1">
                    <div id="modal_msg" class="p-2 text-uppercase" data-toggle="collapse" data-target="#modal_meta" aria-expanded="false"></div>
                    <div id="modal_meta" class="collapse p-2 card card-body"></div>
                </div>
                <div class="modal-footer">
                    <button id="modal_btn_close" disabled type="button" class="btn btn-secondary" data-dismiss="modal">CLOSE</button>
                    <button id="modal_btn_delete" type="button" class="btn btn-danger">DELETE PERMANENTLY</button>
                </div>
            </div>
        </div>
    </div>


    <!-- HEADER -->
    <div class="d-flex p-1 justify-content-between align-items-center shadow-sm mb-2">
        <div class="d-flex align-items-center">
            <?php include("qlinks.php"); ?>
            <div class="px-2"><b><?php echo SITE_TITLE; ?></b></div>
        </div>
    </div>


    <!--BODY-->
    <div class="">
  <div class="posts" id="posts">
    <?php
if(count($posts)>0)
{
foreach($posts as $post)
{ 
$post_id=$post["post_id"];
?>
    <div class="p-2 post m-2" data-toggle="collapse" data-target="#post_meta_<?php echo $post_id; ?>">
      <div >
        <div id="post_title_<?php echo $post_id; ?>">
          <?php echo $post["post_title"]; ?>
        </div>
      </div>
      <ul id="post_meta_<?php echo $post_id; ?>" class="collapse mt-2 post-meta">
        <li class="text-center">
          <div class="btn-group">
            <button onclick="view_post(<?php echo $post_id; ?>);" class="btn btn-primary ic ic-visible">
            </button>
            <button onclick="tag_post(<?php echo $post_id; ?>);" class="btn btn-warning ic ic-tag">
            </button>
            <button onclick="edit_post(<?php echo $post_id; ?>);" class="btn btn-info ic ic-pencil">
            </button>
            <button onclick="delete_post(<?php echo $post_id; ?>);" class="btn btn-danger ic ic-delete">
            </button>
          </div>
        </li>
        <li>STATUS : 
          <?php if($post["post_status"]=="P") echo "PUBLISHED"; else echo "DRAFT"; ?>
        </li>
        <li class="text-uppercase">PUBLISHED ON : 
          <?php echo date("M d, Y h:i a") ?>
        </li>
      </ul>
    </div>
    <?php  } 
}
else
{ 
    echo '<div class="p-3 my-4 text-center"><b class="alert alert-primary" role="alert">NO POSTS FOUND</b></div>';
}?>
  </div>
</div>



    


<!--PAGINATION-->
<form class="mt-5" action="index.php">
    <nav>
        <ul class="pagination justify-content-center">
            <li class="page-item">
                <a class="page-link btn" href="?page_no=1"><span class="badge bg-light text-dark border"> 1 </span> FIRST</a>
            </li>
            <li class="page-item <?php if ($page_no < 2) echo "disabled"; ?>">
                <a class="page-link" href="?page_no=<?php echo $page_no - 1; ?>">PREV</a>
            </li>
            <li class="page-item">
                <input min="1" max="<?php echo $total_pages ?>" type="number" name="page_no" class="form-control">
            </li>
            <li class="page-item <?php if ($page_no >= $total_pages) echo "disabled"; ?>">
                <a class="page-link" href="?page_no=<?php echo $page_no + 1; ?>">NEXT</a>
            </li>
            <li class="page-item">
                <a class="btn page-link" href="?page_no=<?php echo $total_pages; ?>"> LAST <span class="badge bg-light text-dark border"><?php echo $total_pages; ?></span></a>
            </li>
        </ul>
    </nav>
</form>


    
<?php echo BOOTSTRAP_JS; echo JQUERY; echo SCRIPTS; ?>
<script>
reload_on_navigate();
const modal_title = GEBI("modal_title");
const modal_msg = GEBI("modal_msg");
const modal_meta = GEBI("modal_meta");
const modal_btn_close = GEBI("modal_btn_close");
const modal_btn_delete = GEBI("modal_btn_delete");

const modal = new bootstrap.Modal(document.getElementById('modal'), {
    keyboard: false,
    backdrop: 'static',
});
function view_post(id) {
    location.href = "page-update-post.php?post_id=" + id;
}

function edit_post(id) {
    location.href = "page-update-post.php?post_id=" + id;
}

function tag_post(id) {
    location.href = "page-manage-relations.php?post_id=" + id;
}

function delete_post(id) {
    modal.show();
    modal_btn_close.disabled = false;
    modal_title.innerHTML = "ARE YOU SURE TO DELETE THE POST";
    modal_msg.innerHTML = GEBI("post_title_" + id).innerHTML;
    modal_meta.innerHTML = "";

    modal_btn_delete.onclick = function() {
        modal_title.innerHTML = ic_loader;
        $.ajax({
                type: "POST",
                url: "../ajax/ajax-delete-post.php",
                data: {
                    post_id: id,
                },
            })
            .done(function(response) {
                try {
                    response = JSON.parse(response);
                    if (response.type == "success") modal_title.innerHTML = ic_done;
                    else if (response.type == "error") modal_title.innerHTML = ic_error;
                    else modal_title.innerHTML = ic_catch;
                    modal_msg.innerHTML = response.msg;
                    $.each(response.meta, function(key, metavalue) {
                        modal_meta.innerHTML += metavalue + "<br>";
                    });

                } catch (err) {
                    modal_title.innerHTML = ic_catch;
                    modal_msg.innerHTML = err;
                    modal_meta.innerHTML = response;
                }
            })
            .fail(function(jqXHR, textStatus, exception) {
                modal_title.innerHTML = ic_catch;
                modal_msg.innerHTML = textStatus;
                modal_meta.innerHTML = exception;
            })
            .always(function() {
                modal_btn_close.hidden = true;
                modal_btn_delete.innerHTML = "Reload Now";
                modal_btn_delete.onclick = function() {
                    location.reload();
                }
            });
    }

}

</script>

<?php } ?>
        
</body>
</html>

            
