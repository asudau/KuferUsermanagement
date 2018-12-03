<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 * 
 * user_id VARCHAR(32) 
 * seminar_id VARCHAR(32) 
 * invited_by VARCHAR(32) 
 * date int(11) 
 */


class KuferRegisterAccountInvitation extends SimpleORMap
{


    protected static function configure($config = array())
    {
        
        $config['db_table'] = 'kufer_register_account_invitation';
        
        parent::configure($config);
    
    }
}