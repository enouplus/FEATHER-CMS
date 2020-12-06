<?php require_once("core-functions.php");?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Post | Admin Panel</title>
    <?php echo BOOTSTRAP_CSS; echo STYLES; echo ICONS;?>
</head>
<body>

<?php

$error = [];
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
    <div class="input-group justify-content-between align-items-center shadow-sm mb-2">
        <?php include("qlinks.php"); ?>
        <input type="datetime-local" id="i_post_date" class="form-control" placeholder="enter post date">
        <select id="i_post_status" class="form-select">
            <option value="P" selected>Published</option>
            <option value="D">Draft</option>
        </select>
        <button class="btn btn-primary" onclick="save_post()">SAVE</button>
    </div>


    <!--BODY-->
    <div class="p-2">
        <textarea id="i_post_title" rows="1" class="form-control r0 mb-2" placeholder="enter post title" title="Post Title"></textarea>
        <textarea id="i_post_desc" rows="1" class="form-control r0 mb-2" placeholder="enter post description" title="Post Description"></textarea>
        <input id="i_post_slug" type="text" class="form-control r0 mb-2" placeholder="enter post slug" title="Post Slug">
        <textarea id="i_post_content" class="form-control r0 mb-2" placeholder="enter post content" title="Post Content"></textarea>
    </div>


    <!--META FORM-->
    <form id="f_meta_feilds" class="p-2">
        <?php require("form-meta-feilds-add.php");?>
    </form> 
<?php
echo BOOTSTRAP_JS; 
echo JQUERY;
echo TINYMCE;
echo TINYMCE_CONFIGURATIONS;
echo SCRIPTS;
?>

<script>
reload_on_navigate();
const modal_title = GEBI("modal_title");
const modal_msg = GEBI("modal_msg");
const modal_meta = GEBI("modal_meta");
const modal_btn_close = GEBI("modal_btn_close");

tinymce.init(tinymce_configurations);

const i_post_date=GEBI("i_post_date");
const i_post_status=GEBI("i_post_status");
const i_post_title=GEBI("i_post_title");
const i_post_slug=GEBI("i_post_slug");
const i_post_desc=GEBI("i_post_desc");
const i_post_content=GEBI("i_post_content");
const f_meta_feilds=GEBI("f_meta_feilds");

const modal = new bootstrap.Modal(document.getElementById('modal'), {
    keyboard: false,
    backdrop: 'static',
});

function save_post() {
    modal.show();
    modal_btn_close.disabled = true;
    modal_title.innerHTML = ic_loader;
    modal_msg.innerHTML = "Loading...";
    modal_meta.innerHTML = "";

    $.ajax({
            type: "POST",
            url: "../ajax/ajax-add-post.php",
            data :  {
                post_date : i_post_date.value,
                post_status : i_post_status.value,
                post_title : i_post_title.value,
                post_desc : i_post_desc.value,
                post_slug : i_post_slug.value,
                post_content : tinymce.get("i_post_content").getContent(),
                post_meta : JSON.stringify($(f_meta_feilds).formToJson()),
            },
        })
        .done(function(response) {
            try {
                response = JSON.parse(response);
                if (response.type == "success") 
                {
                    modal_title.innerHTML = ic_done;
                    modal_btn_close.onclick=function()
                    {
                        location.href="page-update-post.php?post_id="+response.meta.post_id;
                    }
                }
                else if (response.type == "error") 
                {
                    modal_title.innerHTML = ic_error;
                }
                else
                { 
                    modal_title.innerHTML = ic_catch;
                }
                modal_msg.innerHTML = response.msg;
                $.each(response.meta,function(key,metavalue){
                    modal_meta.innerHTML += metavalue+"<br>";
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
            modal_btn_close.disabled = false;
        });

}

</script>

<?php } ?>
        
</body>
</html>

            
