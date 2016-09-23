<?php
function html($text)
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

function htmlout($text)
{
    echo html($text);
}

function response_structure()
{
    $result   = ['code' => '0', 'description' => ''];
    $response = ['result' => $result];

    return $response;
}

function response($response)
{
    $json_response = json_encode($response, JSON_UNESCAPED_UNICODE);

    echo $json_response;
}

function give_params()
{
    $params = $_REQUEST;
    
//    $method = filter_input(INPUT_SERVER, 'REQUEST_METHOD');
//       
//    if ($method == 'GET')
//    {        
//        $params['login']    = filter_input(INPUT_GET, 'login');
//        $params['password'] = filter_input(INPUT_GET, 'password');
//    } elseif ($method == 'POST') 
//    {
//        $params['login']    = filter_input(INPUT_POST, 'login');
//        $params['password'] = filter_input(INPUT_POST, 'password');
//    }         
    
    return $params;
    
}

function auth($params, $pdo)
{

    $response = response_structure();
    $response['result']['code'] = '0';    
    $response['result']['description'] = 'Authorization is successful!';
    
    $login = $params['login']; $pass  = '';
    $str1 = 'VKG~V!#@UYO~IU@#LNIUDBIU@#(~*@#)_E)J!@(#JD)*~(*@#)DU~_@(#UR)*!@U)$';
    $str2 = strtoupper($params['sign']);
    
    if ($login === 'egrad') {
        $pass = '12345egrad67890';        
    } else {
        
        $sql = 'SELECT caption,password,report_centre_id FROM licence where login=? LIMIT 1';        
        
        $s = $pdo->prepare($sql);
        $s->execute([$login]);

        if ($row=$s->fetch(PDO::FETCH_ASSOC)) {
            $pass                         = $row['password']; 
            $response['name']             = $row['caption']; 
            $response['report_centre_id'] = $row['report_centre_id'];          
        }  
        
    }      

    if ($pass !== '') {
       $str1 = strtoupper(md5(sult().$pass)); 
    }
    
    if ($str1 !== $str2) {
        $response['result']['description'] = 'Authorization error!';
        $response['result']['code']        = 'auth01';
    }
    
    return $response;    
}

function sult()
{
    return '72f83f45-6c73-11e3-b33e-902b34599b2a';
}

function add_data($data, $table, $pdo)
{

    $response = response_structure();
    $id = [];
    
    try{
        $sql = 'REPLACE INTO '.$table.get_columns($data[0]);
              
        $s = $pdo->prepare($sql);
               
        foreach ($data as $arr){
            
            $query_params = [];
        
            foreach ($arr as $par){
                array_push($query_params, $par);
            }
            
            $s->execute($query_params);           
            array_push($id, $pdo->lastInsertId());
        } 
            
        $response['result']['description'] = 'Data successfully added!';
        $response['data']        = $id;
                
    } catch (PDOException $e){
        
        $response['result']['description'] = 'Error check data: ' . $e->getMessage();
        $response['result']['code']        = 'add_01';
        $response['data']                  = $sql;
    }    
    
    return $response;
}

function get_columns($array){
    
    $first_iteration = TRUE;
    $columns = '';  
    $values  = '';
    
    foreach ($array as $key => $col){
                
        if ($first_iteration){
            $first_iteration = FALSE;  
        } else{
            $columns = $columns.',';
            $values  = $values.',';
        }
                
        $columns = $columns.$key;
        $values  = $values.'?';
    }
     
    $columns = '('.$columns.') VALUES ('.$values.')';
    
    return $columns;
    
}

function get_pdo(){
    
    $response = response_structure();
    
    try {
        $pdo = new PDO('mysql:host=127.0.0.1;dbname=alexmm0u_kpi', 'alexmm0u_kpi', 'alexmm0u_12345)(*?:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec('SET NAMES "utf8"');
        
        $response['pdo'] = $pdo;
        
    } catch (PDOException $e) {

        $response['result']['description'] = 'Unable to connect to database server! '. $e->getMessage();
        $response['result']['code']        = 'db01';		

    }  

    return $response;
    
}

