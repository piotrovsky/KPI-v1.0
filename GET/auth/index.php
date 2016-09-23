<?php

    include_once '../../includes/helpers.inc.php';
	
    $response = get_pdo();
    if ($response['result']['code'] == '0') {
       
        $pdo = $response['pdo']; 
     
        $params   = give_params(); 
        $response = auth($params, $pdo);
        
    }

    response($response);