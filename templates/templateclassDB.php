<?php
/**
 * @author Tim Hogan
 * search on Edit and replace
 */
require_once dirname(__FILE__) . "/classSQLPlus.php";
class editDBName extends SQLPlus
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
    // Global
    //*********************************************************************
    public function getGlobal()
    {
        return $this->singlequery("select * from global");
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
}
?>