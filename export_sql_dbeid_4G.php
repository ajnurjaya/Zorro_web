<?php 
    //by Asep Jajang Nurjaya
    //ver 1 07/19/2020
    //thanks to stackoverflow :)
    //ENTER THE RELEVANT INFO BELOW
    $mysqlUserName      = "user";
    $mysqlPassword      = "password";
    $mysqlHostName      = "iphost";
    $DbName             = "dbname";
    $backup_name        = "mybackup_dbeid_4G.sql";
    $tables             = array ("lte_performance_tglass_week");
    

   //or add 5th parameter(array) of specific tables:    array("mytable1","mytable2","mytable3") for multiple tables

    Export_Database($mysqlHostName,$mysqlUserName,$mysqlPassword,$DbName,  $tables, $backup_name );
    

    function Export_Database($host,$user,$pass,$name,  $tables, $backup_name )
    {
        $mysqli = new mysqli($host,$user,$pass,$name); 
        $mysqli->select_db($name); 
        $mysqli->query("SET NAMES 'utf8'");

        $queryTables    = $mysqli->query('SHOW TABLES'); 
        while($row = $queryTables->fetch_row()) 
        { 
            $target_tables[] = $row[0]; 
        }   
        if($tables !== false) 
        { 
            $target_tables = array_intersect( $target_tables, $tables); 
        }
        foreach($target_tables as $table)
        {
           date_default_timezone_set('Asia/Jakarta');
            $jam = date('H') - 2; // dikurangi 2 jam karena query di server minus 2 jam
            $today = date ('m/d/Y');
            $latency = '#DIV/0';
            $result         =   $mysqli->query("SELECT eutrancellid, LEFT(eutrancellid,6), STR_TO_DATE(DATE,'%m/%d/%Y') AS date, maketime(HOUR,00,00) AS HOUR FROM  ".$table." WHERE dl_latency = '#DIV/0' AND DATE = '$today' AND hour = '$jam'"); 
            $fields_amount  =   $result->field_count;  
            $rows_num=$mysqli->affected_rows;     
            //$res            =   $mysqli->query('SHOW CREATE TABLE '.$table); 
            //$TableMLine     =   $res->fetch_row();
            //$content        = (!isset($content) ?  '' : $content) . "\n\n".$TableMLine[1].";\n\n";

            for ($i = 0, $st_counter = 0; $i < $fields_amount;   $i++, $st_counter=0) 
            {
                while($row = $result->fetch_row())  
                { //when started (and every after 100 command cycle):
                    if ($st_counter%100 == 0 || $st_counter == 0 )  
                    {
                            $content .= "\nINSERT INTO huawei_zp_4g VALUES";
                    }
                    $content .= "\n(";
                    for($j=0; $j<$fields_amount; $j++)  
                    { 
                        $row[$j] = str_replace("\n","\\n", addslashes($row[$j]) ); 
                        if (isset($row[$j]))
                        {
                            $content .= '"'.$row[$j].'"' ; 
                        }
                        else 
                        {   
                            $content .= '""';
                        }     
                        if ($j<($fields_amount-1))
                        {
                                $content.= ',';
                        }      
                    }
                    $content .=")";
                    //every after 100 command cycle [or at last line] ....p.s. but should be inserted 1 cycle eariler
                    if ( (($st_counter+1)%100==0 && $st_counter!=0) || $st_counter+1==$rows_num) 
                    {   
                        $content .= ";";
                    } 
                    else 
                    {
                        $content .= ",";
                    } 
                    $st_counter=$st_counter+1;
                }
            } $content .="\n\n\n";
        }
        //$backup_name = $backup_name ? $backup_name : $name."___(".date('H-i-s')."_".date('d-m-Y').")__rand".rand(1,11111111).".sql";
        //$backup_name = $backup_name ? $backup_name : $name.".sql";
        //header("Content-type: application/vnd-ms-excel");
        //header('Content-Type: application/octet-stream');   
        //header("Content-Transfer-Encoding: Binary"); 
        //header("Content-disposition: attachment; filename=\"".$backup_name."\""); 
        echo $content;
        $fp = fopen('mybackup_dbeid_4G.sql', 'w');
        fwrite($fp, $content);
        fclose($fp);
        //var_dump($result);
        exit;
        
    }
?>