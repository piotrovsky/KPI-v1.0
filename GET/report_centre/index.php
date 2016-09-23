<?php

    include_once '../../includes/helpers.inc.php';  
    
    $response = get_pdo();
    if ($response['result']['code'] !== '0') {
       response($response); 
    }
    
    $pdo      = $response['pdo'];
    $params   = give_params();
    $response = auth($params, $pdo);
    $login = $params['login'];
    
    if ($response['result']['code'] == '0')
    {

        try
        {
            
            if ($login === 'egrad') {
                
                $sql = 'SELECT * FROM report_centre';           
                $data = $pdo->query($sql)->fetchAll(PDO::FETCH_OBJ);           
                $response['data'] = $data;                
                
            } else {
            
                $sql = 'SELECT rc.*, 0 lavel FROM licence lic'
                       . ' LEFT JOIN report_centre rc ON lic.report_centre_id = rc.id'
                       . ' WHERE lic.login = ?';
               
                $s = $pdo->prepare($sql);              
                $query_params = [$login];
                $s->execute($query_params);
                if ($row=$s->fetch(PDO::FETCH_ASSOC)) {
                    $id   = $row['id']; 
                    $data = [$row];
                }
                
                $sql = 'SELECT * FROM report_centre WHERE path LIKE ?';
                $s = $pdo->prepare($sql);               
                $s->execute(['%/'.$id.'/%']);
                
                while ($row=$s->fetch(PDO::FETCH_ASSOC)) {
                    array_push($data, $row);
                }
                
                $response['data'] = $data;

            }
            				
        }
        catch (PDOException $e)
        {
            $response['result']['description'] = 'Error check data: ' . $e->getMessage();
            $response['result']['code']        = 'get_rc_01';
        }
        
    }

    response($response);
    
function get_report_centres($s, $parent_id, $lavel){
   
    $new_lavel = $lavel++;
    $data      = [];
    
    $query_params = [$new_lavel, $parent_id];
    $s->execute($query_params);
    
    while ($row=$s->fetch(PDO::FETCH_ASSOC)) {
        
        $id = $row['id']; 
        array_push($data, $row);
        
        $m_data = get_report_centres($s, $id, $data, $new_lavel);
        
        foreach($m_data as $val) {
            array_push($data, $val);
        }
        
    }
    
    return $data;
    
}