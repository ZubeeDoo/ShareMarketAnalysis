<?php
    function ReadCsv($FileToRead)
    {
        $csv = array_map("str_getcsv", file($FileToRead, FILE_SKIP_EMPTY_LINES));
        $arUniqueCompany = [];
        $arAvailableDates = [];
        $keys = array_shift($csv);
        $StockIndex = FindColumnKeyIndex($keys, "name");
        $DateIndex = FindColumnKeyIndex($keys, "date");
    
        if($StockIndex["status"] == 200)
        {
            $iStockIndexCloumn = $StockIndex["index"];
            $iDateIndexCloumn = $DateIndex["index"];
            foreach ($csv as $i=>$row) {
                // echo json_encode($csv) . " - ". $i . " - ". json_encode($keys). " - ". json_encode($row) . "<br>";
    
                if(!in_array($row[$iStockIndexCloumn], $arUniqueCompany, true)){
                    array_push($arUniqueCompany, $row[$iStockIndexCloumn]);
                }
                
                array_push($arAvailableDates, $row[$iDateIndexCloumn]);

                $i = strtolower($i);
                $csv[$i] = array_combine($keys, $row);
            }

            return array("MinDate" => min($arAvailableDates)
                        , "MaxDate" => max($arAvailableDates)
                        , "AvailableCompany" => $arUniqueCompany
                        , "CSV" => $csv);
        }
        else
        {
            return $StockIndex;
        }
    }

    function FindColumnKeyIndex($array, $CloumnName)
    {
        foreach($array AS $i=>$indexs)
        {
            if (strpos(strtolower($indexs), $CloumnName) !== false) {
                return array("status" => "200", "index" => $i);
            }
        }

        return array("status" => "404", "msg" => "Stock name not avaliable");
    } 
    
    function FindColumnKey($array, $CloumnName)
    {
        foreach($array[0] AS $Key=>$indexs)
        {
            if (strpos(strtolower($Key), $CloumnName) !== false) {
                return array("status" => "200", "Key" => $Key);
            }
        }

        return array("status" => "404", "msg" => "Stock name not avaliable");
    }
   
?>