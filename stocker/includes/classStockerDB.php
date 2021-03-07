<?php
/**
 * @author Tim Hogan
 *
 */
require_once dirname(__FILE__) . "/classSQLPlus.php";
class stockerDB extends SQLPlus
{
    function __construct($params)
    {
        parent::__construct($params);
    }

    //*********************************************************************
    // Diagnostic
    //*********************************************************************
    private function var_error_log( $object=null,$text='')
    {
        ob_start();
        var_dump( $object );
        $contents = ob_get_contents();
        ob_end_clean();
        error_log( "{$text} {$contents}" );
    }


    //*********************************************************************
    // Stock
    //*********************************************************************
    public function getStockIdFromCode($stock)
    {
        if ($rec = $this->p_singlequery("select * from stock where stock_code = ?","s",$stock) )
            return $rec['idstock'];
        return null;
    }

    public function allStock()
    {
        return $this->all("select * from stock order by stock_code");
    }

    //*********************************************************************
    // Record
    //*********************************************************************
    public function createRecord($stock,$strTimstamp,$value)
    {
        if (gettype($stock) == "string")
            $idstock = $this->getStockIdFromCode($stock);
        else
            $idstock = $stock;

        return $this->p_create("insert into record (record_timestamp,record_stock,record_value) values (?,?,?)","sid",$strTimstamp,$idstock,$value);
    }

    public function AllRecordsForStock($stock)
    {
        if (gettype($stock) == "string")
            $idstock = $this->getStockIdFromCode($stock);
        else
            $idstock = $stock;
         return $this->p_all("select * from record where record_stock = ? order by record_timestamp","i",$idstock);
    }

    public function LastRecordsForStock($stock,$days)
    {
        if (gettype($stock) == "string")
            $idstock = $this->getStockIdFromCode($stock);
        else
            $idstock = $stock;
        $dt = new DateTime();
        $dt->setTimestamp($dt->getTimestamp() - (3600*24*$days));
        $strTime = $dt->format('Y-m-d H:i:s');
        return $this->p_all("select * from record where record_timestamp > ? and record_stock = ? order by record_timestamp","si",$strTime,$idstock);
    }

    public function getLastRecord($stock)
    {
        if (gettype($stock) == "string")
            $idstock = $this->getStockIdFromCode($stock);
        else
            $idstock = $stock;
        return $this->p_singlequery("select * from record where record_stock = ? order by record_timestamp desc limit 1","i",$idstock);
    }

    public function firstXDaysBach($stock,$days)
    {
        if (gettype($stock) == "string")
            $idstock = $this->getStockIdFromCode($stock);
        else
            $idstock = $stock;

        $dt = new DateTime();
        $dt->setTimestamp($dt->getTimestamp() - (3600*24*$days));
        $strTime = $dt->format('Y-m-d H:i:s');
        return $this->p_singlequery("select * from record where record_timestamp > ? and record_stock = ? order by record_timestamp limit 1","si",$strTime,$idstock);

    }

    public function firstOneDayBack($stock)
    {
        if (gettype($stock) == "string")
            $idstock = $this->getStockIdFromCode($stock);
        else
            $idstock = $stock;
        $dt = new DateTime();
        $dt->setTimestamp($dt->getTimestamp() - (3600*24));
        $strTime = $dt->format('Y-m-d H:i:s');
        return $this->p_singlequery("select * from record where record_timestamp > ? and record_stock = ? order by record_timestamp limit 1","si",$strTime,$idstock);
    }

    //*********************************************************************
    // portfolio
    //*********************************************************************
    public function allPortFolio()
    {
        return $this->all("select * from portfolio left join stock on idstock = portfolio_stock order by portfolio_timestamp");
    }



    public function portfolioSummary()
    {
        return $this->all("select stock_code, sum(portfolio_price*portfolio_qty) AS buy, sum(portfolio_qty) as qty from portfolio left join stock on idstock = portfolio_stock group by stock_code");
    }


    public function createPortfolioEntry($stock,$buysell,$price,$qty)
    {
        $dt = (new DateTime())->format('Y-m-d H:i:s');
        return $this->p_create("insert into portfolio (portfolio_timestamp,portfolio_stock,portfolio_buysell,portfolio_price,portfolio_qty,portfolio_currency) values (?,?,?,?,?,'NZD')","sisdd",$dt,$stock,$buysell,$price,$qty);
    }

    //*********************************************************************
    // Watch
    //*********************************************************************
    public function getWatch($id)
    {
        return $this->p_singlequery("select * from watch where idwatch = ?","i",$id);
    }

    public function allWatchesForStock($stock)
    {
        if (gettype($stock) == "string")
            $idstock = $this->getStockIdFromCode($stock);
        else
            $idstock = $stock;

        return $this->p_all("select * from watch where watch_stock = ? and watch_done = 0","i",$idstock);
    }

    public function watchForStock($stock)
    {
        if (gettype($stock) == "string")
            $idstock = $this->getStockIdFromCode($stock);
        else
            $idstock = $stock;

        return $this->p_singlequery("select * from watch where watch_stock = ? and watch_done = 0","i",$idstock);
    }

    public function watchDone($idwatch)
    {
        return $this->p_update("update watch set watch_done = 1 where idwatch = ?","i",$idwatch);
    }

    public function watchTriggeredAbove($idwatch)
    {
        $watch = getWatch($idwatch);
        if (! $watch['watch_once'])
        {
            return $this->p_update("update watch set watch_above_triggered = 1 where idwatch = ?","i",$idwatch);
        }
        else
            return $this->watchDone($idwatch);
    }

    public function watchTriggeredBelow($idwatch)
    {
        $watch = getWatch($idwatch);
        if (! $watch['watch_once'])
        {
            return $this->p_update("update watch set watch_below_triggered = 1 where idwatch = ?","i",$idwatch);
        }
        else
            return $this->watchDone($idwatch);
    }

    public function watchUnTriggerAbove($idwatch)
    {
        return $this->p_update("update watch set watch_above_triggered = 0 where idwatch = ?","i",$idwatch);
    }

    public function watchUnTriggerBelow($idwatch)
    {
        return $this->p_update("update watch set watch_below_triggered = 0 where idwatch = ?","i",$idwatch);
    }
}

?>