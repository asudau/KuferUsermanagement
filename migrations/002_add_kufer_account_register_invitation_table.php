<?php
class AddKuferAccountRegisterInvitationTable extends DBMigration {

    public function description () {
        return 'create table for registration invitations that have been send to users';
    }

    public function up () {
        $db = DBManager::get();
        $db->exec("CREATE  TABLE IF NOT EXISTS `kufer_register_account_invitation` (
            `user_id` VARCHAR(32) NULL ,
            `seminar_id` VARCHAR(32) NULL ,
            `invited_by` VARCHAR(32) NULL ,
            `date` int(11) NULL ,
            PRIMARY KEY (`user_id`, `seminar_id`)
        )");
		
        SimpleORMap::expireTableScheme();
    }

    public function down () {
		DBManager::get()->exec("DROP TABLE kufer_register_account_invitation");
        SimpleORMap::expireTableScheme();
    }
}