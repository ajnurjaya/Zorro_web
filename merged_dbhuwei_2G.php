<?php 
    //by Asep Jajang Nurjaya
    //merged
    $mysqlUserName      = "username";
    $mysqlPassword      = "password";
    $mysqlHostName      = "10.35.105.237";
    $DbName             = "dbname";
    $backup_name        = "join_dbhuawei_2G.sql";
    $filename           = 'mybackup_dbhuawei_2G.sql';
    $filename2          = 'join_dbhuawei_2G.sql';
    $filename3          = 'mybackup_dbeid_2G.sql';
    date_default_timezone_set('Asia/Jakarta');
    $jam = date('H', strtotime('-2 hours'));
    $hour = $jam.':00:00';
    $today = date('Y-m-d');

    //step 1 kita import dulu data query dari dbspm
    //*select database dan konek
    $con = mysqli_connect($mysqlHostName,$mysqlUserName,$mysqlPassword) or die ('error ga bisa konek: ' . mysqli_error($con));
    mysqli_select_db($con, $DbName) or die ('error memilih database: ' .mysqli_error($con));

    //*memulai import file sql
    $templine = '';
    $lines = file($filename);
    foreach($lines as $line)
        {
            $templine .= $line;
            if (substr(trim($line), -1, 1) == ';')
            {
                mysqli_query($con,$templine) or print('Error performing query \'<strong>' . $templine . '\': ' . mysqli_error($con) . '<br /><br />');
                $templine = '';
            }
        }
    echo "import table pertama berhasil";
    mysqli_close($con);

    $con1 = mysqli_connect($mysqlHostName,$mysqlUserName,$mysqlPassword) or die ('error ga bisa konek: ' . mysqli_error($con1));
    mysqli_select_db($con1, $DbName) or die ('error memilih database: ' .mysqli_error($con1));

    //*memulai import file sql
    $templine = '';
    $lines = file($filename3);
    foreach($lines as $line)
        {
            $templine .= $line;
            if (substr(trim($line), -1, 1) == ';')
            {
                mysqli_query($con1,$templine) or print('Error performing query \'<strong>' . $templine . '\': ' . mysqli_error($con1) . '<br /><br />');
                $templine = '';
            }
        }
    echo "import table kedua eid berhasil";
    mysqli_close($con1);

    //step 2 kita export query dengan join
    $con2 = mysqli_connect($mysqlHostName,$mysqlUserName,$mysqlPassword) or die ('error ga bisa konek: ' . mysqli_error($con2));
    mysqli_select_db($con2, $DbName) or die ('error memilih database: ' .mysqli_error($con2));
    $query2 = mysqli_query($con2,"SELECT * FROM huawei_zp_2g as t1 LEFT JOIN site_list as t2 ON t1.site_id=t2.site_id WHERE DATE = '$today' AND hour ='$hour'");
    $fields_amount = mysqli_field_count($con2);
    $rows_num=mysqli_affected_rows($con2);
    $content = '';

    for ($i = 0, $st_counter = 0; $i < $fields_amount;   $i++, $st_counter=0)
    {
       while($row = mysqli_fetch_row($query2))
       {
           if ($st_counter%100 == 0 || $st_counter == 0 )  
                    {
                            $content .= "\nINSERT INTO zpzt_2g_huawei VALUES";
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
    }$content .="\n\n\n";
    echo $content;
    $fp = fopen('join_dbhuawei_2G.sql', 'w');
    fwrite($fp, $content);
    fclose($fp);
    mysqli_close($con2);

    //import final data
    $con3 = mysqli_connect($mysqlHostName,$mysqlUserName,$mysqlPassword) or die ('error ga bisa konek: ' . mysqli_error($con3));
    mysqli_select_db($con3, $DbName) or die ('error memilih database: ' .mysqli_error($con3));

    //*memulai import file sql
    $templine = '';
    $lines2 = file($filename2);
    foreach($lines2 as $line2)
        {
            $templine .= $line2;
            if (substr(trim($line2), -1, 1) == ';')
            {
                mysqli_query($con3,$templine) or print('Error performing query \'<strong>' . $templine . '\': ' . mysqli_error($con3) . '<br /><br />');
                $templine = '';
            }
        }
    echo "import table final berhasil";
    mysqli_close($con3);
?>