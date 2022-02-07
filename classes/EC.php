<?php
$src_x = "DQCKJusqMsT0u7CjpmhjVGkHln3A3fS-ayeH4Nu52tc";
$src_y = "lxgWzsLtVI8fqZmTPPo9nZ-kzGs7w7XO8-rUU68OxmI";


$goodPem =  file_get_contents("private-key.pem");
$private_key = openssl_pkey_get_private($goodPem);
$good_pem_public_key = openssl_pkey_get_details($private_key)['key'];
file_put_contents("public-key-good.pem",$good_pem_public_key);

?>