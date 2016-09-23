<?php
	
    try
    {
        $pdo = new PDO('mysql:host=127.0.0.1;dbname=alexmm0u_kpi', 'alexmm0u_kpi', 'alexmm0u_12345)(*?:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec('SET NAMES "utf8"');
    }
	
    catch (PDOException $e)
    {

        $response['result']['description'] = 'Unable to connect to database server!';
        $response['result']['code']        = 'db01';		

        response($response);

        exit();
    }