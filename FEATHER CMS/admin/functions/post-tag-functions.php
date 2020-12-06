<?php
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR."core-functions.php");
function validate_data_type($post_id,$tag_ids_array)
{
    if(ctype_digit($post_id) and is_array($tag_ids_array))
    {
        if(($total_ids=count($tag_ids_array))>0)
        {
            $valid_tag_ids=[];
            $error=[];
            for($i=0;$i<$total_ids;$i++)
            {
                $tag_id=$tag_ids_array[$i];
                if(ctype_digit($tag_id))
                {
                    if(is_tag_id_exist($tag_id))
                    {
                        array_push($valid_tag_ids,$tag_id);
                    }
                    else
                    {
                        array_push($error,"tag having id = ".$tag_id." not exists");
                    }
                }
                else
                {
                    array_push($error,$tag_id." is not a valid tag id");
                }
            }
            if(count($error)>0)
            {
                exit_with_response("error","all tag ids are not valid",$error);
            }
            elseif(count($valid_tag_ids)>0)
            {
                if(is_id_of_post_exist($post_id))
                {
                    return array("post_id"=>$post_id,"tag_ids"=>$valid_tag_ids);
                }
                else
                {
                    exit_with_response("error","post having id = ".$post_id." not exists");
                }
            }
            else
            {
                exit_with_response("error","no valid tag ids returned");
            }
        }
        else
        {
            exit_with_response("error","tags id array is empty");
        }
    }
    elseif(ctype_digit($post_id) and $tag_ids_array=="delete")
    {
        if(is_id_of_post_exist($post_id))
        {
            delete_all_relations($post_id);
        }
        else
        {
            exit_with_response("error","post having id = ".$post_id." not exists");
        }
    }
    else
    {
        exit_with_response("error","parameters sent are not in valid string");
    }
}

function is_id_of_post_exist($post_id)
{
    global $pdo;
    try
    {
        $stmt1=$pdo->prepare("SELECT `post_id` FROM `".POSTS_TABLE."` WHERE `post_id`=? LIMIT 1;");
        $stmt1->bindValue(1,$post_id);
        $stmt1->execute();
        $fetch1=$stmt1->fetch(PDO::FETCH_ASSOC);
        if($fetch1===false) return false;
        elseif($fetch1["post_id"]==$post_id) return true;
        else return true;
    }
    catch(PDOException $e)
    {
        exit_with_response("catch",$e->getMessage());
    }
}

function is_tag_id_exist($tag_id)
{
    global $pdo;
    try
    {
        $stmt1=$pdo->prepare("SELECT `tag_id` FROM `".TAGS_TABLE."` WHERE `tag_id`=? LIMIT 1;");
        $stmt1->bindValue(1,$tag_id);
        $stmt1->execute();
        $fetch1=$stmt1->fetch(PDO::FETCH_ASSOC);
        if($fetch1===false) return false;
        elseif($fetch1["tag_id"]==$tag_id) return true;
        else return true;
    }
    catch(PDOException $e)
    {
        exit_with_response("catch",$e->getMessage());
    }
}

function save_relations($post_id,$tag_ids_string)
{
    
    global $pdo;
    $data=validate_data_type($post_id,$tag_ids_string);
    $post_id=$data["post_id"];
    $tag_ids_array=array_unique($data["tag_ids"]);

    try
    {
        $stmt1=$pdo->prepare("DELETE FROM `".POST_TAG_TABLE."` WHERE `post_id`=?;");
        $stmt1->bindValue(1,$post_id);
        $stmt1->execute();

        $stmt2=$pdo->prepare("INSERT INTO `".POST_TAG_TABLE."` VALUES(:post_id,:tag_id);");
        $response=[];
        foreach($tag_ids_array as $tag_id)
        {
            try
            {
                $stmt2->bindValue(":post_id",$post_id);
                $stmt2->bindValue(":tag_id",$tag_id);
                $stmt2->execute();
                array_push($response,"post having id = ".$post_id." linked with tag having id = ".$tag_id." successfully");
            }
            catch(PDOException $e)
            {
                array_push($response,$e->getMessage());
            }
        }
        exit_with_response("success","tagged successfully",$response);
    }
    catch(PDOException $e)
    {
        exit_with_response("catch",$e->getMessage());
    }
    
}

function delete_all_relations($post_id)
{
    global $pdo;
    try
    {
        $stmt1=$pdo->prepare("DELETE FROM `".POST_TAG_TABLE."` WHERE `post_id`=?;");
        $stmt1->bindValue(1,$post_id);
        $stmt1->execute();
        exit_with_response("success","all tags unrelated successfully");
    }
    catch(PDOException $e)
    {
        exit_with_response("error",$e->getMessage());
    }
}


?>