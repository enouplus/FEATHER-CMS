<?php
require_once("../functions/post-functions.php");
is_admin_ajax();
delete_post
(
    GVPV("post_id")
);
?>