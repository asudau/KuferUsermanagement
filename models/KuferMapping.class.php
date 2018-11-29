<?php
/**
 * Course.class.php
 * model class for table seminare
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Annelene Sudau <asudau@uos.de>
 * @copyright   2017 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 *
 * @property integer id kufer_id database column
 * @property string index studip_id
 
 */

class KuferMapping extends SimpleORMap
{


    protected static function configure($config = array())
    {
        
        $config['db_table'] = 'kufer_id_mapping';

        parent::configure($config);
    
    }
    
    public static function getAccountStatus($user_id){
        $mapping = KuferMapping::findOneBySQL('studip_id = :user_id', [':user_id' => $user_id]);
            if($mapping){
                return $mapping->claimed ? 3 : 2;
            } else return 1;
    }
    
    public static function getAccountStatusText($user_id){
        switch(self::getAccountStatus($user_id)){
            case 1:
                return 'Account wurde manuell angelegt';
            case 2:
                return 'Account noch nicht eingerichtet';
            case 3:
                return 'Account eingerichtet';
        }
    }
}
