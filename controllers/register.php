<?php

class RegisterController extends StudipController {

    protected static $magic = 'dsdfjhgretha';
    
    public function __construct($dispatcher)
    {
        parent::__construct($dispatcher);
        $this->plugin = $dispatcher->plugin;
    }

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        
    }

    public function index_action($user_id)
    {
        $this->user = User::find($user_id);
        $this->username = $this->user->username;
        $this->Email = $this->user->email;
    }
    
    public function agree_action($user_id)
    {
        $mapping = KuferMapping::findOneBySQL('studip_id = :user_id', [':user_id' => $user_id]);
        if ($mapping){
            if ($mapping->claimed || $this->get_last_lifesign($user_id)){
                PageLayout::postMessage(MessageBox::error( "Dieser Account wurde bereits aktiviert."));
                $this->render_action('decline');
            }
        }
        
        $this->user = User::find($user_id);
        $this->username = $this->user->username;
        $this->Email = $this->user->email;
    }
    
    public function claim_account_action($user_id) //aus Seminar_Register_Auth    
    {
        $this->error_msg = "";
        $this->info_msg = "";
        //TODO check if account unclaimed!!!
        $mapping = KuferMapping::findOneBySQL('studip_id = :user_id', [':user_id' => $user_id]);
        if ($mapping){
            if ($mapping->claimed || $this->get_last_lifesign($user_id)){
                PageLayout::postMessage(MessageBox::error( "Dieser Account wurde bereits aktiviert."));
            }
        }
        //
        //$this->auth["uname"] = Request::username('username'); // This provides access for "crcregister.ihtml"
        
        $username = trim(Request::get('username')); //brauchen wir für späteren vergleich
        $Vorname = trim(Request::get('Vorname'));
        $Nachname = trim(Request::get('Nachname'));
        
        //TODO account_settings setzen in Usermanagement delete_mode
        if (Request::get('account_settings') == 'aktivitaet'){
            $status_info = new UsermanagementAccountStatus($user_id);
            $status_info->delete_mode = 'aktivitaet';
            $status_info->account_status = 0;
            $status_info->chdate = time();
            $status_info->store();
        } else {
            $status_info = new UsermanagementAccountStatus($user_id);
            $status_info->delete_mode = 'course_end';
            $status_info->account_status = 0;
            $status_info->chdate = time();
            $status_info->store();
        }
        
        $user = User::find($user_id);
        if ($user->username != $username){
            $this->error_msg = "Probleme mit dem Nutzernamen " . $user->username . ' ungleich ' .$username ;
        }

        $validator = new email_validation_class; // Klasse zum Ueberpruefen der Eingaben
        $validator->timeout = 10; // Wie lange warten wir auf eine Antwort des Mailservers?

        if (!Seminar_Session::check_ticket(Request::option('login_ticket'))) {
            $this->error_msg = "Ungültiges Login Ticket. Versuchen Sie es bitte nochmal.";
        }

        // accept only registered domains if set
        $cfg = Config::GetInstance();
        $email_restriction = $cfg->getValue('EMAIL_DOMAIN_RESTRICTION');
        if ($email_restriction) {
            $Email = trim(Request::get('Email')) . '@' . trim(Request::get('emaildomain'));
        } else {
            $Email = trim(Request::get('Email'));
        }

//        if (!$validator->ValidateUsername($username)) {
//            $this->error_msg = $this->error_msg . _("Der gewählte Benutzername ist zu kurz!") . "<br>";
//            return false;
//        } // username syntaktisch falsch oder zu kurz
        // auf doppelte Vergabe wird weiter unten getestet.

        if (!$validator->ValidatePassword(Request::quoted('password'))) {
            $this->error_msg = $this->error_msg . _("Das Passwort ist zu kurz!") . "<br>";
        }

        if (!$validator->ValidateName($Vorname)) {
            $this->error_msg = $this->error_msg . _("Der Vorname fehlt oder ist unsinnig!") . "<br>";
        } // Vorname nicht korrekt oder fehlend
        if (!$validator->ValidateName($Nachname)) {
            $this->error_msg = $this->error_msg . _("Der Nachname fehlt oder ist unsinnig!") . "<br>";
        }
        if (!$validator->ValidateEmailAddress($Email)) {
            $this->error_msg = $this->error_msg . _("Die E-Mail-Adresse fehlt oder ist falsch geschrieben!") . "<br>";
        } // E-Mail syntaktisch nicht korrekt oder fehlend

        $REMOTE_ADDR = $_SERVER["REMOTE_ADDR"];
        $Zeit = date("H:i:s, d.m.Y", time());

//        if (!$validator->ValidateEmailHost($Email)) { // Mailserver nicht erreichbar, ablehnen
//            $this->error_msg = $this->error_msg . _("Der Mailserver ist nicht erreichbar, bitte überprüfen Sie, ob Sie E-Mails mit der angegebenen Adresse verschicken und empfangen können!") . "<br>";
//        } else { // Server ereichbar
//            if (!$validator->ValidateEmailBox($Email)) { // aber user unbekannt. Mail an abuse!
//                StudipMail::sendAbuseMessage("Register", "Emailbox unbekannt\n\nUser: $username\nEmail: $Email\n\nIP: $REMOTE_ADDR\nZeit: $Zeit\n");
//                $this->info_msg = _("Die angegebene E-Mail-Adresse ist nicht erreichbar, überprüfen Sie zur Sicherheit Ihre Angaben!") . "<br>";
//            } else {
//                ; // Alles paletti, jetzt kommen die Checks gegen die Datenbank...
//            }
//        }
        
        if($this->error_msg){
            PageLayout::postMessage(MessageBox::error($this->error_msg));
            $this->redirect($this->url_for('/register/index/') . $user_id);
        } else {
        
            // alle Checks ok, Benutzer registrieren...
            $hasher = UserManagement::getPwdHasher();

            $user->password = $hasher->HashPassword(Request::get('password'));
            $user->vorname = $Vorname;
            $user->nachname = $Nachname;
            $user->email = $Email;
            $user->geschlecht = Request::int('geschlecht');

            if ($user->store()) {
                self::sendValidationMail($user);
                //$this->auth["perm"] = $user->perms;
                //return $user->user_id;
                //TODO set account claimed
                $mapping = KuferMapping::findOneBySQL('studip_id = :user_id', [':user_id' => $user->user_id]);
                if ($mapping){
                    $mapping->claimed = time();
                    $mapping->store();
                    PageLayout::postMessage(MessageBox::success(_("Ihre Registrierung wurde erfolgreich vorgenommen. ") . 
                        _("Das System wird Ihnen zur Bestätigung eine E-Mail zusenden.")));
                } else {
                    //da ist aber was schief gegangen //TODO was machen wir in diesem Fall?? Kann das überhaupt passieren?
                    PageLayout::postMessage(MessageBox::success(_("Ihre Registrierung wurde erfolgreich vorgenommen. ") . 
                        _("Das System wird Ihnen zur Bestätigung eine E-Mail zusenden.")));
                } 
            }
        }
    }

    public function decline_action(){
        
    }
    
    // customized #url_for for plugins
    public function url_for($to)
    {
        $args = func_get_args();

        # find params
        $params = array();
        if (is_array(end($args))) {
            $params = array_pop($args);
        }

        # urlencode all but the first argument
        $args = array_map('urlencode', $args);
        $args[0] = $to;

        return PluginEngine::getURL($this->dispatcher->plugin, $params, join('/', $args));
    }
    
    public static function sendValidationMail($user){
        global $_language_path;

        // if no user-object is given interpret it as a user-id
        if (is_string($user)) {
            $user = new User($user);
        }

        // template-variables for the include partial
        $Zeit     = date("H:i:s, d.m.Y", time());
        $username = $user->username;
        $Vorname  = $user->vorname;
        $Nachname = $user->nachname;
        $Email    = $user->email;

        // (re-)send the confirmation mail
        $to     = $user->email;
        $url    = $GLOBALS['ABSOLUTE_URI_STUDIP'];
        $mail   = new StudipMail();
        $abuse  = $mail->getReplyToEmail();

        $subject="Registrierung erfolgreich - " . $GLOBALS['UNI_NAME_CLEAN'];
        
        $mailbody="Willkommen im Online-Portal der " . $GLOBALS['UNI_NAME_CLEAN'] . " \n\n"
        ."Sie haben sich um $Zeit mit folgenden Angaben angemeldet:\n\n"
        ."Benutzername: $username\n"
        ."Vorname: $Vorname\n"
        ."Nachname: $Nachname\n"
        ."E-Mail-Adresse: $Email\n\n"
        ."Um sich einzuloggen öffnen Sie bitte folgenden Link\n\n"
        ."$url\n\n"
        ."in Ihrem Browser.\n"
        ."Wahrscheinlich unterstützt Ihr E-Mail-Programm ein einfaches Anklicken des Links.\n"
        ."Ansonsten müssen Sie Ihren Browser öffnen und den Link komplett in die Adress-Zeile kopieren.\n\n"  
        ."Bei Fragen wenden Sie sich an $abuse\n";

        // send the mail
        $mail->setSubject($subject)
            ->addRecipient($to)
            ->setBodyText($mailbody)
            ->send();
    }
    
    private function get_last_lifesign($user_id){
        $db = DBManager::get();
        $query = "SELECT uo.last_lifesign
                        FROM user_online uo
                        WHERE uo.user_id = :user_id";
                            
        $statement = $db->prepare($query);
        $statement->execute(array('user_id' => $user_id));
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
    
      
}
