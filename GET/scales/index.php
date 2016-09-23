<?php

    include_once '../../includes/magicquotes.inc.php';
    include_once '../../includes/helpers.inc.php';

    $response = response_structure();	
    $params   = give_params();
    $res_auth = auth($params, $response);
    
    if ($res_auth)
    {

        include_once '../../includes/db.inc.php';

        try
        {
            $sql = 'SELECT * FROM SCALES';

            $s = $pdo->prepare($sql);

            $s->execute();
            
            $response['data'] = $s;					
        }
        catch (PDOException $e)
        {
            $response['result']['description'] = 'Error check data: ' . $e->getMessage();
            $response['result']['code']        = 'gs02';
        }
    }

    response($response);
	