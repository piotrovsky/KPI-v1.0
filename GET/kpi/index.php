<?php

    include_once '../../includes/helpers.inc.php';
	
    $response = get_pdo();
    if ($response['result']['code'] !== '0') {
       response($response); 
    }
    
    $pdo      = $response['pdo'];
    $params   = give_params();
    $response = auth($params, $pdo);
    
    if ($response['result']['code'] == '0')
    {     

        try
        {
            $sql = 'SELECT * FROM kpi';

            $data = $pdo->query($sql)->fetchAll(PDO::FETCH_OBJ);
            
            $response['data'] = $data;					
        }
        catch (PDOException $e)
        {
            $response['result']['description'] = 'Error check data: ' . $e->getMessage();
            $response['result']['code']        = 'get_kpi_01';
        }
        
    }

    response($response);