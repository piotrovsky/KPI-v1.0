<?php

    include_once '../../includes/helpers.inc.php';
	
    $response = get_pdo();
    if ($response['result']['code'] == '0') {
       
        $pdo = $response['pdo']; 
     
        $params   = give_params(); 
        $response = auth($params, $pdo);
    
        if ($response['result']['code'] == '0') {
        
            $data = json_decode($params['rows'], true);      
       
            $response = add_data($data, 'licence', $pdo);     
        
        }
    }

    response($response);