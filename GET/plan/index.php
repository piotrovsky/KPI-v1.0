<?php

    include_once '../../includes/magicquotes.inc.php';
    include_once '../../includes/db.inc.php';
    include_once '../../includes/helpers.inc.php';
	
    $params   = give_params();
    $response = auth($params, $pdo);
    
    if ($response['result']['code'] == '0')
    {        

        try
        {
            $sql = 'SELECT * FROM plan';

            $data = $pdo->query($sql)->fetchAll(PDO::FETCH_OBJ);
            
            $response['data'] = $data;					
        }
        catch (PDOException $e)
        {
            $response['result']['description'] = 'Error check data: ' . $e->getMessage();
            $response['result']['code']        = 'get_plan_01';
        }
        
    }

    response($response);