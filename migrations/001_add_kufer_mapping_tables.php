<?php
class AddKuferMappingTables extends DBMigration {

    public function description () {
        return 'create tables for mapping of kufer and studip IDs';
    }

    public function up () {
        $db = DBManager::get();
        $db->exec("CREATE  TABLE IF NOT EXISTS `kufer_id_mapping` (
            `kufer_id` int(12) NULL ,
            `studip_id` VARCHAR(32) NULL ,
            `claimed` int(11) NULL ,
            `mkdate` int(11) NULL ,
            PRIMARY KEY (`kufer_id`)
        )");
		
        $db->exec("CREATE  TABLE IF NOT EXISTS `kufer_date_id_mapping` (
            `kufer_id` int(12) NULL ,
            `studip_id` VARCHAR(32) NULL ,
            PRIMARY KEY (`kufer_id`)
        )");
		
		
        SimpleORMap::expireTableScheme();
    }

    public function down () {
		DBManager::get()->exec("DROP TABLE kufer_id_mapping");
        DBManager::get()->exec("DROP TABLE kufer_date_id_mapping");
        SimpleORMap::expireTableScheme();
    }
}
