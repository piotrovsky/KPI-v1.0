<?php

    include_once '../../includes/helpers.inc.php';
    
     $response = get_pdo();
    if ($response['result']['code'] == '0') {
       
        $pdo = $response['pdo']; 
        
        $params   = give_params();      
        $response = auth($params, $pdo);
      
        if ($response['result']['code'] == '0'){
        
            $data = json_decode($params['rows'], true);      
        
            $pdo->beginTransaction();

            $response = add_data($data['plan'], 'plan', $pdo);
        
            if ($response['result']['code'] == '0'){
            
                $id = $response['data'];
            
                try{
                    
                    $sql = 'DELETE FROM kpi_plan WHERE plan_id=?';
                    $s = $pdo->prepare($sql);
            
                    foreach ($data['plan'] as $arr){
                        $query_params = [];
                        array_push($query_params, $arr['id']);
                        //$query_params[$arr['id']];
                        $s->execute($query_params);
                    }
                    
                    if ($data['kpi_plan'] != null){
                        $response = add_data($data['kpi_plan'], 'kpi_plan', $pdo);
                    }    
                        
                } catch (PDOException $e){
        
                    $response['result']['description'] = 'Error del data: ' . $e->getMessage();
                    $response['result']['code']        = 'add_01';
                    $response['data']                  = $sql;
            
                }
            
            }
        
            if ($response['result']['code'] == '0'){
                $pdo->commit();
                $response['data'] = $id;
            } else {
                $pdo->rollBack();
            }
                    
        }    
        
    }

    response($response);

