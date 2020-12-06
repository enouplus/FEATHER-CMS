<?php
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR."core-functions.php");
function slugify($text) 
{
    if(is_string($text)) 
    {
        $text = preg_replace('/[^A-Za-z0-9-]+/', '-', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        if(empty($text))
        {
            exit_with_response("error","post slug becoming empty after converting it to url");
        }
        else
        {
            return $text;
        }
    }
    else 
    {
        exit_with_response("error","post slug must be string");
    }
}

function validate_post_id($post_id)
{
    if(ctype_digit($post_id))
    {
        global $pdo;
        try
        {
            $stmt=$pdo->prepare("SELECT `post_id` FROM `".POSTS_TABLE."` WHERE `post_id`=? LIMIT 1;");
            $stmt->bindValue(1,$post_id);
            $stmt->execute();
            $fetch=$stmt->fetch();
            if($fetch===false)
            {
                exit_with_response("error","post having id = ".$post_id." not exists");
            }
            else
            {
                return $post_id;
            }
        }
        catch(PDOException $e)
        {
            exit_with_response("catch",$e->getMessage());
        }
    }
    else
    {
        exit_with_response("error","post id must be numerical string");
    }
}

function validate_post_slug($post_slug,$post_id=null)
{
    $post_slug=slugify($post_slug);
    if($post_id!=null and !ctype_digit($post_id))
    {
        exit_with_response("error","post id must be numerical string");
    }
    global $pdo;
    try
    {
        $stmt=$pdo->prepare("SELECT `post_id` FROM `".POSTS_TABLE."` WHERE `post_slug`=? LIMIT 1;");
        $stmt->bindValue(1,$post_slug);
        $stmt->execute();
        $fetch=$stmt->fetch(PDO::FETCH_ASSOC);
        if($fetch===false)
        {
            return $post_slug;
        }
        else
        {
            if($post_id==null)
            {
                exit_with_response("error","post having slug = ".$post_slug." already exists");
            }
            elseif($post_id==$fetch["post_id"])
            {
                return $post_slug;
            }
            else
            {
                exit_with_response("error","post having slug = ".$post_slug." already exists");
            }
        }
    }
    catch(PDOException $e)
    {
        exit_with_response("catch",$e->getMessage());
    }
}

function validate_post_date($post_date)
{
    
    if(is_string($post_date) and ($post_date=strtotime($post_date))!==false)
    {
        return date("Y-m-d H:i:s",$post_date);
    }
    else
    {
        exit_with_response("error","post date must be valid date string"); 
    }
}

function validate_post_status($post_status)
{
    if(is_string($post_status) and $post_status=="P") return "P";
    else return "D";
}

function validate_post_title($post_title)
{
    if(is_string($post_title)) return $post_title;
    else exit_with_response("error","post title must be string");
}

function validate_post_desc($post_desc)
{
    if(is_string($post_desc)) return $post_desc;
    else exit_with_response("error","post description must be string");
}

function validate_post_content($post_content)
{
    if(is_string($post_content)) return $post_content;
    else exit_with_response("error","post content must be string");
}

function validate_post_meta($post_meta)
{
    if(is_string($post_meta))
    {
        return $post_meta;
    }
    else
    {
        exit_with_response("error","post meta must be valid string");
    }
}

function add_post($post_slug,$post_status,$post_date,$post_title,$post_desc,$post_content,$post_meta)
{
    $post_slug=validate_post_slug($post_slug);
    $post_status=validate_post_status($post_status);
    $post_date=validate_post_date($post_date);
    $post_title=validate_post_title($post_title);
    $post_desc=validate_post_desc($post_desc);
    $post_content=validate_post_content($post_content);
    $post_meta=validate_post_meta($post_meta);

    global $pdo;
    try
    {
        $stmt=$pdo->prepare
        (
            "INSERT INTO `".POSTS_TABLE."` VALUES
            (
                NULL,
                :post_slug,
                :post_status,
                :post_date,
                :post_title,
                :post_desc,
                :post_content,
                :post_meta
            );"
        );
        $stmt->bindValue(":post_slug",$post_slug);
        $stmt->bindValue(":post_status",$post_status);
        $stmt->bindValue(":post_date",$post_date);
        $stmt->bindValue(":post_title",$post_title);
        $stmt->bindValue(":post_desc",$post_desc);
        $stmt->bindValue(":post_content",$post_content);
        $stmt->bindValue(":post_meta",$post_meta);
        $stmt->execute();
        exit_with_response("success","post added successfully",array("post_id"=>$pdo->lastInsertId()));
    }
    catch(PDOException $e)
    {
        exit_with_response("error",$e->getMessage());
    }
}

function update_post($post_id,$post_slug,$post_status,$post_date,$post_title,$post_desc,$post_content,$post_meta)
{
    $post_id=validate_post_id($post_id);
    $post_slug=validate_post_slug($post_slug,$post_id);
    $post_status=validate_post_status($post_status);
    $post_date=validate_post_date($post_date);
    $post_title=validate_post_title($post_title);
    $post_desc=validate_post_desc($post_desc);
    $post_content=validate_post_content($post_content);
    $post_meta=validate_post_meta($post_meta);

    global $pdo;
    try
    {
        $stmt=$pdo->prepare
        (
            "UPDATE `".POSTS_TABLE."` SET
            `post_slug`=:post_slug,
            `post_status`=:post_status,
            `post_date`=:post_date,
            `post_title`=:post_title,
            `post_desc`=:post_desc,
            `post_content`=:post_content,
            `post_meta`=:post_meta
            WHERE post_id=:post_id LIMIT 1;"
        );
        $stmt->bindValue(":post_slug",$post_slug);
        $stmt->bindValue(":post_status",$post_status);
        $stmt->bindValue(":post_date",$post_date);
        $stmt->bindValue(":post_title",$post_title);
        $stmt->bindValue(":post_desc",$post_desc);
        $stmt->bindValue(":post_content",$post_content);
        $stmt->bindValue(":post_meta",$post_meta);
        $stmt->bindValue(":post_id",$post_id);
        $stmt->execute();
        $rows_updated=$stmt->rowCount();
        if($rows_updated==1)
        {
            exit_with_response("success","post having id = ".$post_id." updated successfully");
        }
        elseif($rows_updated==0)
        {
            exit_with_response("success","post having id = ".$post_id." updated successfully,may be no changes made");
        }
        else
        {
            exit_with_response("error",$rows_updated. " rows updated from ".POSTS_TABLE);
        }
    }
    catch(PDOException $e)
    {
        exit_with_response("error",$e->getMessage());
    }
}

function delete_post($post_id)
{
    $post_id=validate_post_id($post_id);
    global $pdo;
    try
    {
        $stmt=$pdo->prepare("DELETE FROM `".POSTS_TABLE."` WHERE `post_id`=? LIMIT 1;");
        $stmt->bindValue(1,$post_id);
        $stmt->execute();
        $rows_deleted=$stmt->rowCount();
        if($rows_deleted==1)
        {
            exit_with_response("success","post having id = ".$post_id." deleted successfully");
        }
        elseif($rows_deleted==0)
        {
            exit_with_response("error","post having id = ".$post_id." not exist");
        }
        else
        {
            exit_with_response("error",$rows_deleted. " rows deleted from ".POSTS_TABLE);
        }
    }
    catch(PDOException $e)
    {
        exit_with_response("catch",$e->getMessage());
    }
}


?>