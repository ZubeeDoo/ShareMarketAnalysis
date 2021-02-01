<?php

include "readCsv.php";

class Analysis
{

    public $sStockName, $sStockKey, $sDateKey, $dtFromDate, $dtToDate;
    
    public function getStockFilter ($sValue)
    {
        $sStockName = $this->sStockName;
        $sStockKey = $this->sStockKey["Key"];
        $dtFromDate = $this->dtFromDate;
        $dtToDate =  $this->dtToDate;
        $sDateKey =  $this->sDateKey["Key"];

        // For debugging
        // echo $sValue[$sStockKey] . " : ". $sStockName . " : " .   $sValue[$sDateKey] . "</br>";
        // echo strtotime($sValue[$sDateKey]) . " : " . strtotime($dtFromDate) . "</br>";

        if($sValue[$sStockKey] == $sStockName && (strtotime($sValue[$sDateKey]) >=  strtotime($dtFromDate) || strtotime($sValue[$sDateKey]) <=  strtotime($dtToDate)))
            return $sValue;
    }
    
    public function getBuyAndSellDates($arFileData)
    {
        if($arFileData != null && count($arFileData) > 0)
        {
            $arResult = array("Status" => 200);
    
            $sPriceKey = FindColumnKey($arFileData, "price");
    
            $length = count($arFileData);
            $iStartingPrice = 0;
            $iStatingIndex = 0;
            $arMarkedIndexes = [];
    
            for($iIndex = 0; $iIndex > $length; $iIndex++)
            {
                if($iStartingPrice < $arFileData[$iIndex][$sPriceKey])
                {
                    $iStartingPrice = $arFileData[$iIndex][$sPriceKey];
                    $iStatingIndex = $iIndex;
                    array_push($arMarkedIndexes, $iStatingIndex);
                }
            }
    
            //Wrost
            if($iStatingIndex == 0)
            {
                $arResult["Conclusion"] = "Its Addivised not to But this Product";
            }
    
            //Best
            if($iStatingIndex == $length-1)
            {
                $arResult["Conclusion"] = "Best stock to buy at the given time frame. Please make more purcahse if possible";
                $arFileData[0]["Action"] = "Buy";
                $arFileData[$length-1]["Action"] = "Sell";
            }
    
            //Modirate
            if($iStatingIndex > 0 && $iStatingIndex < $length-1)
            {
                $arResult["Conclusion"] = "Based on the SD their might be some flaw but its all about the spices";
                foreach($arMarkedIndexes as $sValue)
                {
                    $arFileData[$sValue]["Action"] = "Buy";
                    $arFileData[$sValue-1]["Action"] = "Sell";
                }
            }
    
            $arResult["Data"] = $arFileData;
            
            $arResult["Stat"] = $this->Stand_Deviation($arFileData);
    
            return $arResult;
        }
    }
    
    public function Stand_Deviation($arData) 
    { 
        $num_of_elements = count($arData); 
          
        $variance = 0.0; 
        $sum = 0;
          
        // calculating mean using array_sum() method 
        $sPriceKey = FindColumnKey($arData, "price")["Key"];

        foreach ($arData as $item) {

            $sum += $item[$sPriceKey];
        }
        
        $average = $sum/$num_of_elements; 
          
        foreach($arData as $item) 
        { 
            // sum of squares of differences between  
            // all numbers and means. 
            $variance += pow(($item[$sPriceKey] - $average), 2); 
        } 

        $iSD = (float)sqrt($variance/$num_of_elements); 
          
        return array("SD" => $iSD, "Mean" => $average, "Variance" => $variance);
    } 

    
    public function Analytis($sFileName,  $sStockName, $dtFromDate, $dtToDate) 
    { 
        $objReadData = ReadCsv("StockPriceCSV/" . $sFileName);
    
        $arFileData = $objReadData["CSV"];
    
        $this->sStockName = $sStockName;
        $this->dtFromDate = $dtFromDate;
        $this->dtToDate = $dtToDate;
        $this->sStockKey = FindColumnKey($arFileData, "name");
        $this->sDateKey = FindColumnKey($arFileData, "date");
    
        if($this->sStockKey["status"] == 200 && $this->sDateKey["status"]  == 200)
        {    
            $arSelectedFilter = array_filter($arFileData,function($sValue)
            {    
                return $this->getStockFilter($sValue);
            });

            usort($arSelectedFilter, function($arRec1, $arRec2)
            {
                return strtotime($arRec1[$this->sDateKey["Key"]]) - strtotime($arRec2[$this->sDateKey["Key"]]);
            });
            
            $arByAndSell = $this->getBuyAndSellDates($arSelectedFilter);
            $arByAndSell["MaxDate"] = $objReadData["MaxDate"];
            $arByAndSell["MinDate"] = $objReadData["MinDate"];
            
            
            echo json_encode($arByAndSell);

        }
        else
        {
            echo json_encode(array("Status" => 500, "Msg" => "Cloumn keys are not found"));
        }
    } 
}

    $sFileName = isset($_POST['FileName']) ? $_POST['FileName'] : null;
    $dtFrom = isset($_POST['dtFrom']) ? $_POST['dtFrom'] : null;
    $dtTo = isset($_POST['dtTo']) ? $_POST['dtTo'] : null;
    $StockName = isset($_POST['StockName']) ? $_POST['StockName'] : null;

    if($sFileName != null)
    {
        $AnalysisObj = new Analysis();
        echo $AnalysisObj->Analytis($sFileName, $StockName, $dtFrom, $dtTo);
    }
    else
    {
        echo json_encode(array("Status" => 404, "Msg" => "File not found"));
    }
?>