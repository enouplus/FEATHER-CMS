<?php
require_once("../functions/tag-functions.php");
is_admin_ajax();
delete_tag
(
    GVPV("tag_id")
);
?>