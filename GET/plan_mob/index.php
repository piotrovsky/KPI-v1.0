<?php

    include_once '../../includes/helpers.inc.php';
	
    $response = get_pdo();
    if ($response['result']['code'] == '0') {
       
        $pdo = $response['pdo']; 
     
        $params   = give_params(); 
        $response = auth($params, $pdo);
        $data     = [];
        
        if ($response['result']['code'] == '0') {
        
            try{
            
                $sql = 'SELECT plan.*, rc.id report_centre_id, rc.caption report_centre_caption, rc.path, pk.kpi_id FROM kpi_plan pk'
                        . ' LEFT JOIN plan ON pk.plan_id = plan.id'
                        . ' LEFT JOIN report_centre rc ON pk.report_centre_id = rc.id';
                
                if ($params['report_centre_id'] !== null) {
                   $sql = $sql.' WHERE pk.report_centre_id = ?'; 
                   $query_params = [$params['report_centre_id']];
                }
                
                $sql = $sql.' GROUP BY pk.plan_id, pk.report_centre_id, pk.kpi_id';
                
                $s = $pdo->prepare($sql);
                if ($params['report_centre_id'] !== null) {
                    $s->execute($query_params);
                } else {
                    $s->execute();
                }    
                
                $id               = '';
                $guid             = '';
                $caption          = '';
                $period_type_id   = '';
                $begin_period     = '';
                $report_centre_id = '';
                $kpi_id           = '';
                
                while ($row=$s->fetch(PDO::FETCH_ASSOC)) {
                    
                    if ($row['id'] !== $id){
                         
                        if ($id !== ''){
                            array_push($data,  ['id'               => $id, 
                                                'guid'             => $guid, 
                                                'caption'          => $caption, 
                                                'period_type_id'   => $period_type_id, 
                                                'begin_period'     => $begin_period, 
                                                'report_centre_id' => $report_centre_id.'/', 
                                                'kpi_id'           => $kpi_id.'/']);
                        }

                        $id               = $row['id'];
                        $guid             = $row['guid'];
                        $caption          = $row['caption'];
                        $period_type_id   = $row['period_type_id'];
                        $begin_period     = $row['begin_period'];
                        
                    }
                    
                    $report_centre_id = $report_centre_id.$row['path'].'/'.$row['report_centre_id'];
                    $kpi_id           = $kpi_id.'/'.$row['kpi_id'];
                    
                }
                
                array_push($data,  ['id'               => $id, 
                                    'guid'             => $guid, 
                                    'caption'          => $caption, 
                                    'period_type_id'   => $period_type_id, 
                                    'begin_period'     => $begin_period, 
                                    'report_centre_id' => $report_centre_id, 
                                    'kpi_id'           => $kpi_id]);
                
                $response['data'] = $data;					
                            
            } catch (PDOException $e) {
                $response['result']['description'] = 'Error check data: ' . $e->getMessage();
                $response['result']['code']        = 'get_plan_01';
            }
            
        }
        
    }

    response($response);