<?php
require_once("../functions/core-functions.php");
function is_admin_dashboard() { if(isset($_SESSION["admin"]) and $_SESSION["admin"]==true)return true; else header("Location:../index.php");}
is_admin_dashboard();
function is_post_id_exist($post_id)
{
    global $pdo;
    try
    {
        $stmt=$pdo->prepare("SELECT `post_id` FROM `".POSTS_TABLE."` WHERE post_id=? LIMIT 1;");
        $stmt->bindValue(1,$post_id);
        $stmt->execute();
        if($stmt->fetch()===false)return false;
        else return true;
    }
    catch(PDOException $e)
    {
        exit($e->getMessage());
    }
}


function get_post($post_id)
{
    global $pdo;
    try
    {
        $stmt=$pdo->prepare("SELECT * FROM `".POSTS_TABLE."` WHERE post_id=? LIMIT 1;");
        $stmt->bindValue(1,$post_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    catch(PDOException $e)
    {
        exit($e->getMessage());
    }
}

define('BOOTSTRAP_CSS', '
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-CuOF+2SnTUfTwSZjCXf01h7uYhfOBuxIhGKPbfEJ3+FqH/s6cIFN9bGr1HmAg4fQ" crossorigin="anonymous">');
define('BOOTSTRAP_JS', '
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-alpha3/dist/js/bootstrap.bundle.min.js" integrity="sha384-popRpmFF9JQgExhfw5tZT4I9/CI5e2QcuUZPOVXb1m7qUmeR2b50u+YFEYe1wgzy" crossorigin="anonymous"></script>');
define('JQUERY','
    <script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>');
define('TINYMCE', '
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/5.6.1/tinymce.min.js" integrity="sha512-RAKGi5Lz3BrsIKXW8sSbTM2sgNbf5m3n7zApdXDTL1IH0OvG1Xe1q2yI2R2gTDqsd2PLuQIIiPnDJeOSLikJTA==" crossorigin="anonymous"></script>');
define('STYLES', '
    <link href="../assets/style.css" rel="stylesheet">');
define('SCRIPTS', '
    <script src="../assets/script.js"></script>');
define('ICONS', '
    <link rel="stylesheet" href="../../fonts/icons.css">');
define('TINYMCE_CONFIGURATIONS', '
    <script src="../assets/tinymce-configurations.js"></script>');

?>