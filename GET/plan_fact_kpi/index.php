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
                   
                    $sql = 'SELECT D.dt date, SUM(0) plan, SUM(0) plan_correct, SUM(COALESCE(f.val, 0)) fact, SUM(0) percent, SUM(0) percent_correct  
                            FROM(
                                SELECT DATE_ADD(CAST(:BeginDate AS DATE), INTERVAL c.num*100+b.num*10+a.num DAY) dt FROM
                                    (SELECT 0 num UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) AS a,
                                    (SELECT 0 num UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) AS b,
                                    (SELECT 0 num UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) AS c
                                WHERE DATE_ADD(CAST(:BeginDate AS DATE), INTERVAL c.num*100+b.num*10+a.num DAY) <= CAST(:EndDate AS DATE)) AS D
                            LEFT JOIN( 
                                SELECT CAST(fact.date_time AS DATE) dt, fact.val val  FROM fact LEFT JOIN report_centre rc ON fact.report_centre_id = rc.id
                                WHERE (date_time BETWEEN CAST(:BeginDate AS DATE) AND CAST(:EndDate AS DATE)) AND CONCAT(rc.path, rc.id, "/") LIKE :report_centre_id AND fact.kpi_id = :kpi_id) f
                            ON f.dt = D.dt
                            GROUP BY D.dt';
                    
                } else {
                                   
                    $sql = 'SELECT  pf.dt date, 
                                    pf.avg_val plan, 
                                    CASE WHEN pf.val - SUM(COALESCE(pf1.val_fact,0)) > 0 THEN (pf.val - SUM(COALESCE(pf1.val_fact,0)))/(DATEDIFF(CAST(:EndDate AS DATE) , pf.dt) + 1) ELSE 0 END plan_correct, 
                                    pf.val_fact fact, 
                                    100*pf.val_fact/pf.avg_val percent, 
                                    CASE WHEN pf.val - SUM(COALESCE(pf1.val_fact,0)) > 0 THEN 100*pf.val_fact/((pf.val - SUM(COALESCE(pf1.val_fact,0)))/(DATEDIFF(CAST(:EndDate AS DATE) , pf.dt) + 1)) END percent_correct 
                                FROM(
                                    SELECT  kpd.dt, 
                                            kpd.val val, 
                                            SUM(kpd.val)/(DATEDIFF(CAST(:EndDate AS DATE) , CAST(:BeginDate AS DATE)) + 1) avg_val, 
                                            SUM(COALESCE(f.val,0)) val_fact 
                                        FROM(
                                            SELECT D.dt, SUM(kp.val) val FROM(  
                                                SELECT DATE_ADD(CAST(:BeginDate AS DATE), INTERVAL c.num*100+b.num*10+a.num DAY) dt
                                                    FROM
                                                        (SELECT 0 num UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) AS a,
                                                        (SELECT 0 num UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) AS b,
                                                        (SELECT 0 num UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) AS c
                                                    WHERE DATE_ADD(CAST(:BeginDate AS DATE), INTERVAL c.num*100+b.num*10+a.num DAY) <= CAST(:EndDate AS DATE)) AS D, 
                                                kpi_plan kp 
                                                LEFT JOIN report_centre rc ON kp.report_centre_id = rc.id
                                                WHERE kp.plan_id = :plan_id AND kp.kpi_id = :kpi_id AND CONCAT(rc.path, rc.id, "/") LIKE :report_centre_id
                                                GROUP BY D.dt) AS kpd 
                                        LEFT JOIN( 
                                                SELECT CAST(fact.date_time AS DATE) dt, SUM(fact.val) val  
                                                FROM fact 
                                                LEFT JOIN report_centre rc ON fact.report_centre_id = rc.id
                                                WHERE (date_time BETWEEN CAST(:BeginDate AS DATE) AND CAST(:EndDate AS DATE)) AND CONCAT(rc.path, rc.id, "/") LIKE :report_centre_id AND fact.kpi_id = :kpi_id
                                                GROUP BY dt) f
                                        ON f.dt = kpd.dt
                                    GROUP BY kpd.dt) pf
                            LEFT JOIN(
                                    SELECT  kpd.dt, 
                                            kpd.val val, 
                                            SUM(kpd.val)/(DATEDIFF(CAST(:EndDate AS DATE) , CAST(:BeginDate AS DATE)) + 1) avg_val, 
                                            SUM(COALESCE(f.val,0)) val_fact 
                                        FROM(
                                            SELECT D.dt, SUM(kp.val) val FROM(  
                                                SELECT DATE_ADD(CAST(:BeginDate AS DATE), INTERVAL c.num*100+b.num*10+a.num DAY) dt
                                                    FROM
                                                        (SELECT 0 num UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) AS a,
                                                        (SELECT 0 num UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) AS b,
                                                        (SELECT 0 num UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) AS c
                                                    WHERE DATE_ADD(CAST(:BeginDate AS DATE), INTERVAL c.num*100+b.num*10+a.num DAY) <= CAST(:EndDate AS DATE)) AS D, 
                                                kpi_plan kp 
                                                LEFT JOIN report_centre rc ON kp.report_centre_id = rc.id
                                                WHERE kp.plan_id = :plan_id AND kp.kpi_id = :kpi_id AND CONCAT(rc.path, rc.id, "/") LIKE :report_centre_id
                                                GROUP BY D.dt) AS kpd 
                                        LEFT JOIN( 
                                                SELECT CAST(fact.date_time AS DATE) dt, SUM(fact.val) val  
                                                FROM fact 
                                                LEFT JOIN report_centre rc ON fact.report_centre_id = rc.id
                                                WHERE (date_time BETWEEN CAST(:BeginDate AS DATE) AND CAST(:EndDate AS DATE)) AND CONCAT(rc.path, rc.id, "/") LIKE :report_centre_id AND fact.kpi_id = :kpi_id
                                                GROUP BY dt) f
                                        ON f.dt = kpd.dt
                                GROUP BY kpd.dt) pf1
                            ON pf.dt > pf1.dt
                            GROUP BY pf.dt';
                                    
                }
                
                $sql = str_replace(':BeginDate', $BeginDate, $sql);
                $sql = str_replace(':EndDate'  , $EndDate  , $sql);
                
                $s = $pdo->prepare($sql);
                
                $s->bindParam(':kpi_id'          , $params['kpi_id']          , PDO::PARAM_INT);
                
                $path = '%'.$params['report_centre_path'].$params['report_centre_id'].'%';
                $s->bindParam(':report_centre_id', $path, PDO::PARAM_STR);
                
                if ($params['plan_id'] !== NULL) {$s->bindParam(':plan_id', $params['plan_id'], PDO::PARAM_INT);}

                $s->execute();
                
                while ($row=$s->fetch(PDO::FETCH_ASSOC)) {
                    array_push($data, $row);
                }
            
                $response['data'] = $data;					
                 
                //$response['sql'] = $sql;
                
            } catch (PDOException $e) {
                $response['result']['description'] = 'Error check data: ' . $e->getMessage();
                $response['result']['code']        = 'get_pf_kpi_01';
                $response['sql'] = $sql;
            }
            
        }
        
    }
    
    response($response);