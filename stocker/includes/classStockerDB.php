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
    // global
    //*********************************************************************
    public function getGlobal()
    {
        return $this->singlequery("select * from global limit 1");
    }

    //*********************************************************************
    // rolling
    //*********************************************************************
    public function getRollingByName($name)
    {
        return $this->p_singlequery("select * from rolling where rolling_entity = ?","s",$name);
    }

    public function createRolling($name,$modulus=10,$target=0.1)
    {
        return $this->p_create("insert into rolling (rolling_entity,rolling_modulus,rolling_target,rolling_idx,rolling_disable_seconds) values (?,?,?,0,3600)","sid",$name,$modulus,$target);
    }

    public function updateRolling($name,$count,$values)
    {
        return $this->p_update("update rolling set rolling_idx = ?, rolling_counters = ? where rolling_entity = ?","iss",$count,$values,$name);
    }

    public function resetRolling($name)
    {
        return $this->p_update("update rolling set rolling_entity_disabled = 0 where rolling_entity = ?","s",$name);
    }

    public function markRollingDisabled($name)
    {
        $dt = new DateTime('now');
        $strTime = $dt->format('Y-m-d H:i:s');
        return $this->p_update("update rolling set rolling_disabled = 1, rolling_disable_timestamp = ? where rolling_entity = ?","ss",$strTime,$name);
    }

    //*********************************************************************
    // User
    //*********************************************************************
    public function getUser($iduser)
    {
        return $this->p_singlequery("select * from user where iduser = ?","i",$iduser);
    }

    public function getUserByUserName($strUserName)
    {
        $u = $this->p_singlequery("select * from user where user_username = ?","s",$strUserName);
        if ($u && strtoupper($u['user_username']) == strtoupper($strUserName))
            return $u;
        return null;
    }


    public function disableUser($userid)
    {
        $dtNow = new DateTime('now');
        $strT = $dtNow->format('Y-m-d H:i:s');
        return $this->p_update("update user set user_disabled = 1, user_disable_timestamp = ? where iduser = ?","is",$userid,$strT);
    }

    public function deleteUser($userid)
    {
        return $this->p_update("update user set user_deleted = 1 where iduser = ?","i",$userid);
    }

    public function enableUser($userid)
    {
        return $this->p_update("update user set user_disabled = 0, user_disable_timestamp = null where iduser = ?","i",$userid);
    }

    public function updateUserLastSiginIn($userid)
    {
        $dtNow = new DateTime('now');
        $strT = $dtNow->format('Y-m-d H:i:s');
        return $this->p_update("update user set user_last_signin = ? where iduser = ?","si",$userid,$strT);
    }

    public function updateFailCounter($userid)
    {
        if ($u = $this->getUser($userid))
        {
            $cnt = intval($u['user_failed_login_count']) + 1;
            if ($this->p_update("update user set user_failed_login_count = ? where iduser = ?","ii",$cnt,$userid) )
                return $cnt;
        }
        return 0;
    }

    public function resetFailCounter($userid)
    {
        return $this->p_update("update user set user_failed_login_count = 0 where iduser = ?","i",$userid);
    }

    public function updatePassword($userid,$hash,$salt,$force=false,$renewdays=0)
    {
        $dt = new DateTime('now');
        $strNow = $dt->format('Y-m-d H:i:s');
        $strRenew = '';
        $forceflag = 0;
        //Get the user record so we can update the previous password list

        if ($user = $this->getUser($userid) )
        {
            if ($user['user_deleted'] == 0 && $user['user_disabled'] == 0)
            {
                if ($renewdays > 0)
                {
                    $dtRenew = new DateTime();
                    $dtRenew->setTimestamp($dtRenew->getTimestamp() + (3600*24*$renewdays));
                    $strRenew = $dtRenew->format('Y-m-d H:i:s');
                }

                $prevhash = '';
                $prevsalt = '';
                if ($user['user_prev_hash'] )
                    $prevhash = $user['user_prev_hash'];
                if ($user['user_prev_salt'] )
                    $prevsalt = $user['user_prev_salt'];
                $prevhash = substr($hash . $prevhash,0,640);
                $prevsalt = substr($salt . $prevsalt,0,640);
                if ($force)
                {
                    $forceflag = 1;
                    if ($renewdays > 0)
                        return $this->p_update("update user set user_pw_renew_date = ?, user_pw_change_date = null, user_hash = ?, user_salt = ?, user_forcereset = ?, user_prev_hash = ?, user_prev_salt = ? where iduser = ?","sssissi",$strRenew,$hash,$salt,$forceflag,$prevhash,$prevsalt,$userid);
                    else
                        return $this->p_update("update user set user_pw_change_date = null, user_hash = ?, user_salt = ?, user_forcereset = ?, user_prev_hash = ?, user_prev_salt = ? where iduser = ?","ssissi",$hash,$salt,$forceflag,$prevhash,$prevsalt,$userid);
                }
                else
                {
                    if ($renewdays > 0)
                        return $this->p_update("update user set user_pw_renew_date = ?, user_pw_change_date = ?, user_hash = ?, user_salt = ?, user_forcereset = ?, user_prev_hash = ?, user_prev_salt = ? where iduser = ?","ssssissi",$strRenew,$strNow,$hash,$salt,$forceflag,$prevhash,$prevsalt,$userid);
                    else
                        return $this->p_update("update user set user_pw_change_date = ?, user_hash = ?, user_salt = ?, user_forcereset = ?, user_prev_hash = ?, user_prev_salt = ? where iduser = ?","sssissi",$strNow,$hash,$salt,$forceflag,$prevhash,$prevsalt,$userid);
                }
            }
        }
        return false;
    }

    public function createUser($username,$lastname,$firstname,$hash,$salt,$securityval,$email='',$mobile='')
    {
        $suer = array();
        $user['user_username'] = $username;
        $user['user_lastname'] = $lastname;
        $user['user_firstname'] = $firstname;
        $user['user_hash'] = $hash;
        $user['user_salt'] = $salt;
        $user['user_security'] = intval($securityval);
        $user['user_email'] = $email;
        $user['user_phone1'] = $mobile;
        $user['bForceReset'] = true;
        return $this->p_create_from_array('user',$user);
    }

    //*********************************************************************
    // audit
    //*********************************************************************
    public function createAudit($type,$description,$userid=null)
    {
        $ipaddr = '';
        if ($_SERVER && isset($_SERVER['REMOTE_ADDR']))
            $ipaddr = $_SERVER['REMOTE_ADDR'];
        if ($userid)
            return $this->p_create("insert into audit (audit_type,audit_description,audit_ipaddress,audit_user) values (?,?,?,?)","sssi",$type,$description,$ipaddr,$userid);
        else
            return $this->p_create("insert into audit (audit_type,audit_description,audit_ipaddress) values (?,?,?)","sss",$type,$description,$ipaddr);
    }

    public function allAudits()
    {
        return $this-allFromTable("audit","","order by audit_timestamp");
    }

    public function allAuditsofType($type)
    {
        return $this->p_all("select * from audits where audit_type = ? order by audit_timestamp","s",$type);
    }

    //*********************************************************************
    // Stock
    //*********************************************************************
    public function getStockById($id)
    {
        return $this->p_singlequery("select * from stock where idstock = ?","i",$id);
    }

    public function getStock($code)
    {
        return $this->p_singlequery("select * from stock where stock_code = ?","s",$code);
    }

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
    public function createRecord($stock,$strTimstamp,$value,$currency='USD')
    {
        if (gettype($stock) == "string")
            $idstock = $this->getStockIdFromCode($stock);
        else
            $idstock = $stock;

        return $this->p_create("insert into record (record_timestamp,record_stock,record_value,record_currency) values (?,?,?,?)","sids",$strTimstamp,$idstock,$value,$currency);
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

    public function firstXHoursBach($stock,$hours)
    {
        if (gettype($stock) == "string")
            $idstock = $this->getStockIdFromCode($stock);
        else
            $idstock = $stock;

        $dt = new DateTime();
        $dt->setTimestamp($dt->getTimestamp() - (3600*$hours));
        $strTime = $dt->format('Y-m-d H:i:s');
        return $this->p_singlequery("select * from record where record_timestamp > ? and record_stock = ? order by record_timestamp limit 1","si",$strTime,$idstock);
    }

    //*********************************************************************
    // portfolio
    //*********************************************************************
    public function getPortfolio($id)
    {
        return $this->p_singlequery("select * from portfolio where idportfolio = ?","i",$id);
    }

    public function allPortFolio($userid)
    {
        return $this->all("select * from portfolio left join stock on idstock = portfolio_stock where portfolio_user = {$userid} order by portfolio_timestamp");
    }

    public function allPortfolioBuyForUser($userid)
    {
        return $this->all("select * from portfolio left join stock on idstock = portfolio_stock where portfolio_user = {$userid} and portfolio_buysell = 'buy' order by portfolio_stock,portfolio_timestamp");
    }

    public function allPortfolioBuyForUserStock($userid,$stockid)
    {
        return $this->all("select * from portfolio left join stock on idstock = portfolio_stock where portfolio_stock = {$stockid}  and portfolio_user = {$userid} and portfolio_buysell = 'buy' and portfolio_archive != 1 order by portfolio_stock,portfolio_timestamp");
    }

    public function allPortfolioSellForUser($userid)
    {
        return $this->all("select * from portfolio left join stock on idstock = portfolio_stock where portfolio_user = {$userid} and portfolio_buysell = 'sell' order by portfolio_stock,portfolio_timestamp");
    }

    public function allPortfolioDividendsForUser($userid)
    {
        return $this->all("select * from portfolio left join stock on idstock = portfolio_stock where portfolio_user = {$userid} and portfolio_buysell = 'div' order by portfolio_stock,portfolio_timestamp");
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

    public function setPortfolioArchive($id,$what)
    {
        if ($what)
            return $this->p_update("update portfolio set portfolio_archive = 1 where idportfolio = ?","i",$id);
        else
            return $this->p_update("update portfolio set portfolio_archive = 0 where idportfolio = ?","i",$id);
    }

    //*********************************************************************
    // Watch
    //*********************************************************************
    public function getWatch($id)
    {
        return $this->p_singlequery("select * from watch where idwatch = ?","i",$id);
    }

    public function createWatch($userid,$stock,$below,$above,$rti)
    {
        return $this->p_create("insert into watch (watch_user,watch_stock,watch_below,watch_above,watch_rti) values (?,?,?,?,?)","iiddd",$userid,$stock,$below,$above,$rti);
    }

    public function allActiveWatches()
    {
        return $this->all("select * from watch left join stock on idstock = watch_stock where watch_done = 0");
    }

    public function allActiveWatchesWithUser()
    {
        return $this->all("select * from watch left join user on iduser = watch_user left join stock on idstock = watch_stock watch_done = 0");
    }

    public function allWatchesForStock($stock)
    {
        if (gettype($stock) == "string")
            $idstock = $this->getStockIdFromCode($stock);
        else
            $idstock = $stock;

        return $this->p_all("select * from watch left join stock on idstock = watch_stock where watch_stock = ? and watch_done = 0","i",$idstock);
    }

    public function allWatchesForUser($userid)
    {
        return $this->p_all("select * from watch left join stock on idstock = watch_stock where watch_user = ? order by stock_code, watch_above,watch_below","i",$userid);
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

    public function setWatch($watchid,$value)
    {
        if ($value)
            return $this->p_update("update watch set watch_done = 0 where idwatch = ?","i",$watchid);
        else
            return $this->p_update("update watch set watch_done = 1 where idwatch = ?","i",$watchid);
    }

    public function setWatchBelow($watchid,$value)
    {
        return $this->p_update("update watch set watch_below = ? where idwatch = ?","di",$value,$watchid);
    }

    public function setWatchAbove($watchid,$value)
    {
        return $this->p_update("update watch set watch_above = ? where idwatch = ?","di",$value,$watchid);
    }

    public function watchTriggeredAbove($idwatch)
    {
        echo "Called triggered below id = {$idwatch}\n";
        $watch = $this->getWatch($idwatch);
        echo " got watch id = {$watch['idwatch']} watch_once = {$watch['watch_once']} \n";
        if (! $watch['watch_once'])
        {
            echo " about to set watch_above_triggered\n";
            return $this->p_update("update watch set watch_above_triggered = 1 where idwatch = ?","i",$idwatch);
        }
        else
        {
            echo " about to set watch done\n";
            return $this->watchDone($idwatch);
        }
    }

    public function watchTriggeredBelow($idwatch)
    {
        echo "Called triggered below id = {$idwatch}\n";
        $watch = $this->getWatch($idwatch);
        echo " got watch id = {$watch['idwatch']} watch_once = {$watch['watch_once']} \n";
        if (! $watch['watch_once'])
        {
            echo " about to set watch_below_triggered\n";
            return $this->p_update("update watch set watch_below_triggered = 1 where idwatch = ?","i",$idwatch);
        }
        else
        {
            echo " about to set watch done\n";
            return $this->watchDone($idwatch);
        }
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