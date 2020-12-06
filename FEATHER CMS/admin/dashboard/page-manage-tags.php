<?php require_once("core-functions.php");?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Tags | Admin Panel</title>
    <?php echo BOOTSTRAP_CSS; echo STYLES; echo ICONS;?>
</head>
<body>

<?php
$error = [];
$tags=[];
#require("../../config.php");
function get_tags($offset,$limit)
{
    global $pdo,$error,$tags;
    try
    {
        $stmt=$pdo->prepare("SELECT * FROM `".TAGS_TABLE."` ORDER BY `tag_id` DESC LIMIT :offset,:limit;");
        $stmt->bindValue(":offset",$offset,PDO::PARAM_INT);
        $stmt->bindValue(":limit",$limit,PDO::PARAM_INT);
        $stmt->execute();
        $tags=$stmt->fetchAll();
    }
    catch(PDOException $e)
    {
        array_push($error,$e->getMessage());
    }
}

if(isset($_GET['page_no']))$page_no=$_GET['page_no']; else $page_no="1";
if(ctype_digit($page_no))
{
    $limit=5;
    $offset = ($page_no-1)*$limit;

    $total_posts=$pdo->query("SELECT COUNT(`tag_id`) FROM `".TAGS_TABLE."`;")->fetch()[0];
    get_tags($offset,$limit);
    $total_pages=ceil($total_posts/$limit);
}
else
{
    array_push($error,"page number must be integer");
}

if (count($error) > 0)
{
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
                    <button id="modal_btn_cancel" type="button" class="btn btn-secondary"></button>
                    <button id="modal_btn_ok" type="button" class="btn btn-primary"></button>
                </div>
            </div>
        </div>
    </div>


    <!-- HEADER -->
    <div class="d-flex p-1 justify-content-between align-items-center shadow-sm">
        <div class="d-flex align-items-center">
            <?php include("qlinks.php"); ?>
        </div>
        <div style="max-width: 500px;" class="input-group input-group-sm ml-2">
            <input type="text" id="i_add_tag_name" class="form-control" placeholder="ENTER TAG NAME">
            <input type="text" id="i_add_tag_slug" class="form-control" placeholder="ENTER TAG SLUG">
            <button onclick="add_tag()" class="btn btn-primary ic ic-add_circle"></button>
        </div>
    </div>



<div class="p-2">
    
<?php
if(count($tags)>0)
{
    echo '<div class="shadow-sm input-group mb-2"><input disabled type="text" class="form-control text-center" placeholder="TAG NAME"><input disabled type="text" class="form-control text-center" placeholder="TAG SLUG"><button class="btn btn-primary ic ic-check_circle"></button><button class="btn btn-danger ic ic-delete"></button></div>';
    foreach($tags as $tag)
    { ?>
        
    
        <div class="shadow-sm input-group mb-2">
            <input value="<?php echo $tag["tag_name"]; ?>" type="text" id="i_edit_tag_name_<?php echo $tag['tag_id']; ?>" class="form-control" placeholder="ENTER TAG NAME">
            <input value="<?php echo $tag["tag_slug"]; ?>" type="text" id="i_edit_tag_slug_<?php echo $tag['tag_id']; ?>" class="form-control" placeholder="ENTER TAG SLUG">
            <button onclick="edit_tag(<?php echo $tag['tag_id']; ?>);" class="btn btn-primary ic ic-check_circle"></button>
            <button onclick="delete_tag(<?php echo $tag['tag_id']; ?>)" class="btn btn-danger ic ic-delete"></button>
        </div>
    

    <?php } ?>


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



<?php }
else{echo '<div class="p-3 my-4 text-center"><b class="alert alert-primary" role="alert">NO TAGS FOUND</b></div>';}
?>
</div>

    <!--TAG LIST-->
   
    








    
<?php echo BOOTSTRAP_JS; echo JQUERY; echo SCRIPTS; ?>
<script>
reload_on_navigate();
const modal_title = GEBI("modal_title");
const modal_msg = GEBI("modal_msg");
const modal_meta = GEBI("modal_meta");
const modal_btn_cancel = GEBI("modal_btn_cancel");
const modal_btn_ok = GEBI("modal_btn_ok");

const modal = new bootstrap.Modal(document.getElementById('modal'), {
    keyboard: false,
    backdrop: 'static',
});

const i_add_tag_name=GEBI("i_add_tag_name");
const i_add_tag_slug=GEBI("i_add_tag_slug");
function add_tag()
{
    modal_title.innerHTML=ic_loader;
    modal_msg.innerHTML='<div class="text-center">PLEASE WAIT...</div>';
    modal_meta.innerHTML='';
    modal_btn_ok.hidden=true;
    modal_btn_cancel.hidden=true;
    modal.show();
    $.ajax
    ({
        type : "POST",
        url  : "../ajax/ajax-add-tag.php",
        data :  {tag_slug : i_add_tag_slug.value,tag_name : i_add_tag_name.value}
    })
    .done(function(response)
    {
        try
        {
            response=JSON.parse(response);
            if(response.type=="success")
            {
                modal_title.innerHTML=ic_done;
                modal_btn_cancel.onclick=function(){location.reload();}
            }
            else if(response.type=="error")
            {
                modal_title.innerHTML=ic_error;
                modal_btn_cancel.onclick=function(){modal.hide();}
            }
            else if(response.type=="catch")
            {
                modal_title.innerHTML=ic_catch;
                modal_btn_cancel.onclick=function(){location.reload();}
            }
            else
            {
                modal_title.innerHTML=ic_catch;
                modal_btn_cancel.onclick=function(){location.reload();}
            }
            modal_msg.innerHTML=response.msg;
            modal_meta.innerHTML=JSON.stringify(response.meta);
            
        }
        catch(err)
        {
            modal_title.innerHTML=ic_catch;
            modal_msg.innerHTML=err;
            modal_meta.innerHTML=JSON.stringify(response);
            modal_btn_cancel.onclick=function(){location.reload();}
        }
    })
    .fail(function(jqXHR,textStatus,exception)
    {
        modal_title.innerHTML=ic_catch;
        modal_msg.innerHTML=textStatus;
        modal_msg.innerHTML=exception;
        modal_btn_cancel.onclick=function(){modal.hide();}
    })
    .always(function()
    {
        modal_btn_cancel.innerHTML="OK";
        modal_btn_cancel.hidden=false;
    });
}

function edit_tag(id)
{
    modal_title.innerHTML=ic_loader;
    modal_msg.innerHTML='<div class="text-center">PLEASE WAIT...</div>';
    modal_meta.innerHTML='';
    modal_btn_ok.hidden=true;
    modal_btn_cancel.hidden=true;
    modal.show();
    $.ajax
    ({
        type : "POST",
        url  : "../ajax/ajax-update-tag.php",
        data :  {tag_id : id,tag_slug : $('#i_edit_tag_slug_'+id).val(),tag_name : $('#i_edit_tag_name_'+id).val()}
    })
    .done(function(response)
    {
        try
        {
            response=JSON.parse(response);
            if(response.type=="success")
            {
                modal_title.innerHTML=ic_done;
            }
            else if(response.type=="error")
            {
                modal_title.innerHTML=ic_error;
            }
            else if(response.type=="catch")
            {
                modal_title.innerHTML=ic_catch;
            }
            else
            {
                modal_title.innerHTML=ic_catch;
            }
            modal_msg.innerHTML=response.msg;
            modal_meta.innerHTML=JSON.stringify(response.meta);
            
        }
        catch(err)
        {
            modal_title.innerHTML=ic_catch;
            modal_msg.innerHTML=err;
            modal_meta.innerHTML=JSON.stringify(response);
        }
    })
    .fail(function(jqXHR,textStatus,exception)
    {
        modal_title.innerHTML=ic_catch;
        modal_msg.innerHTML=textStatus;
        modal_msg.innerHTML=exception;
    })
    .always(function()
    {
        modal_btn_cancel.innerHTML="OK";
        modal_btn_cancel.onclick=function(){location.reload();}
        modal_btn_cancel.hidden=false;
    });
}

function delete_tag(id)
{
    modal_title.innerHTML='ARE YOU SURE TO DELETE THE TAG';
    modal_msg.innerHTML=$('#i_edit_tag_name_'+id).val();
    modal_meta.innerHTML='';
    modal_btn_ok.hidden=false;
    modal_btn_cancel.hidden=false;
    modal_btn_ok.innerHTML="DELETE PERMANENTLY";
    modal_btn_cancel.innerHTML="CANCEL";
    modal.show();
    modal_btn_cancel.onclick=function(){modal.hide();}
    modal_btn_ok.onclick=function()
    {
        modal_title.innerHTML='ARE YOU SURE TO DELETE THE TAG';
        modal_btn_cancel.hidden=true;
        modal_btn_ok.disabled=true;

        $.ajax
        ({
            type : "POST",
            url  : "../ajax/ajax-delete-tag.php",
            data :  {tag_id : id}
        })
        .done(function(response)
        {
            try
            {
                response=JSON.parse(response);
                if(response.type=="success")
                {
                    modal_title.innerHTML=ic_done;
                }
                else if(response.type=="error")
                {
                    modal_title.innerHTML=ic_error;
                }
                else if(response.type=="catch")
                {
                    modal_title.innerHTML=ic_catch;
                }
                else
                {
                    modal_title.innerHTML=ic_catch;
                }
                modal_msg.innerHTML=response.msg;
                modal_meta.innerHTML=JSON.stringify(response.meta);
                
            }
            catch(err)
            {
                modal_title.innerHTML=ic_catch;
                modal_msg.innerHTML=err;
                modal_meta.innerHTML=JSON.stringify(response);
            }
        })
        .fail(function(jqXHR,textStatus,exception)
        {
            modal_title.innerHTML=ic_catch;
            modal_msg.innerHTML=textStatus;
            modal_msg.innerHTML=exception;
        })
        .always(function()
        {
            modal_btn_ok.disabled=false;
            modal_btn_ok.innerHTML="OK";
            modal_btn_ok.onclick=function(){location.reload();}
        })
    }
}


</script>

<?php } ?>
        
</body>
</html>

            
