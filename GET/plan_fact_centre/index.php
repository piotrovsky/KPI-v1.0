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
                            
                $BeginDate = substr("'".$params['begin_period']."'", 0, 12);
                $EndDate   = substr("'".$params['end_period']."'"  , 0, 12);            
                
                if ($params['plan_id'] == NULL) {
                
                    $sql = 
                    'SELECT 
                        f.report_centre_id, f.report_centre_caption, 0 plan_period, f.val fact_period, 0 plan_day, 
                        f.val/CASE 
                                WHEN 
                                    CURRENT_DATE < CAST(:EndDate AS DATE) THEN DATEDIFF(CURRENT_DATE , CAST(:BeginDate AS DATE)) 
                                ELSE DATEDIFF(CAST(:EndDate AS DATE) , CAST(:BeginDate AS DATE)) 
                                END + 1 fact_day
                    FROM(
                        SELECT report_centre_id, rc.caption report_centre_caption, SUM(val) val 
                        FROM fact f
                        LEFT JOIN report_centre rc
                        ON f.report_centre_id = rc.id AND CONCAT(rc.path, rc.id, "/") LIKE :report_centre_id
                        WHERE (f.date_time BETWEEN CAST(:BeginDate AS DATE) AND CAST(:EndDate AS DATE)) AND f.kpi_id = :kpi_id  
                        GROUP BY report_centre_id) f 
                     WHERE NOT f.report_centre_caption is NULL';
                    
                } else {
                    
                    $sql =                  
                    'SELECT 
                        kp.report_centre_id, 
                        f.report_centre_caption, 
                        kp.val*D.all_days/(p.period_type_id + 1) plan_period, 
                        f.val fact_period, 
                        kp.val/(p.period_type_id + 1) plan_day, 
                        f.val/D.fact_days fact_day  
                    FROM 
                        (SELECT DATEDIFF(CAST(:EndDate AS DATE) , CAST(:BeginDate AS DATE)) + 1 all_days,
                            CASE				
                                WHEN 
                                    CURRENT_DATE < CAST(:EndDate AS DATE) THEN DATEDIFF(CURRENT_DATE , CAST(:BeginDate AS DATE)) 
                                ELSE DATEDIFF(CAST(:EndDate AS DATE) , CAST(:BeginDate AS DATE)) 
                                END + 1 fact_days) D, 
                        (SELECT report_centre_id, SUM(val) val FROM kpi_plan 
                            WHERE  kpi_id = :kpi_id AND plan_id = :plan_id
                            GROUP BY report_centre_id) kp
                        LEFT JOIN 
                        (SELECT report_centre_id, rc.caption report_centre_caption, SUM(val) val 
                            FROM fact f
                            LEFT JOIN report_centre rc
                            ON f.report_centre_id = rc.id AND CONCAT(rc.path, rc.id, "/") LIKE :report_centre_id
                            WHERE (f.date_time BETWEEN CAST(:BeginDate AS DATE) AND CAST(:EndDate AS DATE)) AND f.kpi_id = :kpi_id  
                            GROUP BY report_centre_id) f
                        ON kp.report_centre_id = f.report_centre_id    
                        LEFT JOIN plan p ON p.id = :plan_id
                        WHERE NOT f.report_centre_caption is NULL';
                
                }
                
                $sql = str_replace(':BeginDate', $BeginDate, $sql);
                $sql = str_replace(':EndDate'  , $EndDate  , $sql);
                             
                $s = $pdo->prepare($sql);
                
                $s->bindParam(':kpi_id', $params['kpi_id'], PDO::PARAM_INT);

                if ($params['plan_id'] !== NULL) {$s->bindParam(':plan_id', $params['plan_id'], PDO::PARAM_INT);}
                 
                //$path = '%/'.$response['report_centre_id'].'/%';
                $path = '%'.$params['report_centre_path'].$params['report_centre_id'].'%';
                $s->bindParam(':report_centre_id', $path, PDO::PARAM_STR);
                
                $s->execute();

                while ($row=$s->fetch(PDO::FETCH_ASSOC)) {
                    array_push($data, $row);
                }
            
                $response['data'] = $data;
                //$response['sql'] = $sql;
                
            } catch (PDOException $e) {
                $response['result']['description'] = 'Error check data: ' . $e->getMessage();
                $response['result']['code']        = 'get_pf_rc_01';
            }
            
        }
        
    }
    
     response($response);