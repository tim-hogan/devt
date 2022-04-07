<?php
$contents = null;
function var_error_log( $object=null , $text='')
{
    global $contents;

    ob_start();
    var_dump( $object );
    $contents = ob_get_contents();
    ob_end_clean();
    error_log( "{$text} {$contents}" );
}

var_error_log($_COOKIE,"_COOKIE");
echo $contents;
$contents = str_replace($contents,"\n","<br/>");
echo "<p>{$contents}</p>";
?>