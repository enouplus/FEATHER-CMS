<?php require_once("core-functions.php");?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Relations | Admin Panel</title>
    <?php echo BOOTSTRAP_CSS; echo STYLES; echo ICONS;?>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/slim-select/1.26.2/slimselect.min.css" rel="stylesheet"></link>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/slim-select/1.26.2/slimselect.min.js"></script>
</head>
<body>

<?php
function get_post_title($post_id){
    global $pdo;
    try
    {
        $stmt = $pdo->prepare("SELECT `post_title` FROM `" . POSTS_TABLE . "` WHERE post_id=? LIMIT 1;");
        $stmt->bindValue(1, $post_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    catch(PDOException $e)
    {
        exit($e->getMessage());
    }
}
$error = [];
$post_title;
if (isset($_GET["post_id"])){
    $post_id = $_GET["post_id"];
    $_GLOBALS["post_id"] = $post_id;
    if (ctype_digit($post_id))
    {
        $post_title = get_post_title($post_id);
        $related_tags = get_related_tags($post_id);
        $all_tags = get_all_tags();
    }
    else
    {
        array_push($error, "post id must be numerical string");
    }
}
else{
    array_push($error, "post id not selected");
}
function get_all_tags(){
    global $error;
    global $pdo;
    try
    {
        $tags = $pdo->query("SELECT `tag_id` AS `value`,tag_name AS `text` FROM `" . TAGS_TABLE . "`;")
            ->fetchAll(PDO::FETCH_ASSOC);
        if (count($tags) < 1)
        {
            array_push($error, "no tags found.");
        }
        return $tags;
    }
    catch(PDOException $e)
    {
        exit($e->getMessage());
    }
}
function get_related_tags($post_id){
    global $error, $post_title;

    if ($post_title !== false)
    {
        global $pdo;
        try
        {
            $stmt = $pdo->prepare("SELECT `tag_id` FROM `" . POST_TAG_TABLE . "` WHERE post_id=?;");
            $stmt->bindValue(1, $post_id);
            $stmt->execute();
            $tag_ids_array = [];
            $tags_ids_ass_array = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($tags_ids_ass_array as $tag)
            {
                array_push($tag_ids_array, $tag["tag_id"]);
            }
            return $tag_ids_array;

        }
        catch(PDOException $e)
        {
            exit($e->getMessage());
        }
    }
    else
    {
        array_push($error, "post not exists");
    }
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
                    <div id="modal_msg" class="p-2 text-uppercase" data-toggle="collapse" data-target="#modal_meta" aria-expanded="false">some message</div>
                    <div id="modal_meta" class="collapse p-2 card card-body"></div>
                </div>
                <div class="modal-footer">
                    <button id="modal_btn_close" type="button" class="btn btn-secondary" data-dismiss="modal">CLOSE</button>
                </div>
            </div>
        </div>
    </div>

    <!-- HEADER -->
    
    <div class="d-flex justify-content-between align-items-center shadow-sm mb-2 py-1 px-2">
        <?php include("qlinks.php"); ?>
        <div><b>MANAGE RELATIONS</b></div>
        <button class="btn btn-sm btn-primary" onclick="save()">SAVE</button>
    </div>
   

     <!-- SELECTOR -->
    <div class="p-2"><select id="tags" multiple></select></div>

    <div class="p-2">
        <div class="alert alert-info shadow-sm" role="alert">
           <?php echo($post_title["post_title"]); ?>
        </div>
    </div>

    
    <?php

        echo BOOTSTRAP_JS; 
        echo JQUERY;
        echo SCRIPTS;

    ?>

<script>
reload_on_navigate();
const modal_title = GEBI("modal_title");
const modal_msg = GEBI("modal_msg");
const modal_meta = GEBI("modal_meta");
const modal_btn_close = GEBI("modal_btn_close");
const modal = new bootstrap.Modal(document.getElementById('modal'), {
    keyboard: false,
    backdrop: 'static',
});

var select = new SlimSelect({
    select: '#tags',
    closeOnSelect: false,
    hideSelectedOption: true,
});

select.setData(<?php echo json_encode($all_tags);?>);
select.set(<?php echo json_encode($related_tags);?>);

function save() {
    modal.show();
    modal_btn_close.disabled = true;
    modal_title.innerHTML = ic_loader;
    modal_msg.innerHTML = "Loading...";
    modal_meta.innerHTML = "";
    var selection = select.selected();
    if (selection.length < 1) {
        selection = "delete";
    }

    $.ajax({
            type: "POST",
            url: "../ajax/ajax-save-relations.php",
            data: {
                post_id: <?php echo $post_id; ?> ,
                tag_ids : selection
            }
        })
        .done(function(response) {
            try {
                response = JSON.parse(response);
                if (response.type == "success") modal_title.innerHTML = ic_done;
                else if (response.type == "error") modal_title.innerHTML = ic_error;
                else modal_title.innerHTML = ic_catch;
                modal_msg.innerHTML = response.msg;
                $.each(response.meta,function(key,metavalue){
                    modal_meta.innerHTML += metavalue+"<br>";
                });
                
            } catch (err) {
                modal_title.innerHTML = ic_catch;
                modal_msg.innerHTML = error;
                modal_meta.innerHTML = response;
            }
        })
        .fail(function(jqXHR, textStatus, exception) {
            modal_title.innerHTML = ic_catch;
            modal_msg.innerHTML = textStatus;
            modal_meta.innerHTML = exception;
        })
        .always(function() {
            modal_btn_close.disabled = false;
        });

}

</script>

<?php } ?>
        
</body>
</html>

            
