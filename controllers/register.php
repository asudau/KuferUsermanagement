<?php

class RegisterController extends StudipController {

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
        $this->user = User::find($user_id);
        $this->username = $this->user->username;
        $this->Email = $this->user->email;
    }
    
    public function claim_account_action($user_id) //aus Seminar_Register_Auth    
    {
        //TODO Nutzungsbedingungen etc abfragen?
        $this->error_msg = "";
        //TODO check if account unclaimed

        $this->auth["uname"] = Request::username('username'); // This provides access for "crcregister.ihtml"

        $validator = new email_validation_class; // Klasse zum Ueberpruefen der Eingaben
        $validator->timeout = 10; // Wie lange warten wir auf eine Antwort des Mailservers?

        if (!Seminar_Session::check_ticket(Request::option('login_ticket'))) {
            return false;
        }

        $username = trim(Request::get('username'));
        $Vorname = trim(Request::get('Vorname'));
        $Nachname = trim(Request::get('Nachname'));

        // accept only registered domains if set
        $cfg = Config::GetInstance();
        $email_restriction = $cfg->getValue('EMAIL_DOMAIN_RESTRICTION');
        if ($email_restriction) {
            $Email = trim(Request::get('Email')) . '@' . trim(Request::get('emaildomain'));
        } else {
            $Email = trim(Request::get('Email'));
        }

        if (!$validator->ValidateUsername($username)) {
            $this->error_msg = $this->error_msg . _("Der gewählte Benutzername ist zu kurz!") . "<br>";
            return false;
        } // username syntaktisch falsch oder zu kurz
        // auf doppelte Vergabe wird weiter unten getestet.

        if (!$validator->ValidatePassword(Request::quoted('password'))) {
            $this->error_msg = $this->error_msg . _("Das Passwort ist zu kurz!") . "<br>";
            return false;
        }

        if (!$validator->ValidateName($Vorname)) {
            $this->error_msg = $this->error_msg . _("Der Vorname fehlt oder ist unsinnig!") . "<br>";
            return false;
        } // Vorname nicht korrekt oder fehlend
        if (!$validator->ValidateName($Nachname)) {
            $this->error_msg = $this->error_msg . _("Der Nachname fehlt oder ist unsinnig!") . "<br>";
            return false; // Nachname nicht korrekt oder fehlend
        }
        if (!$validator->ValidateEmailAddress($Email)) {
            $this->error_msg = $this->error_msg . _("Die E-Mail-Adresse fehlt oder ist falsch geschrieben!") . "<br>";
            return false;
        } // E-Mail syntaktisch nicht korrekt oder fehlend

        $REMOTE_ADDR = $_SERVER["REMOTE_ADDR"];
        $Zeit = date("H:i:s, d.m.Y", time());

        if (!$validator->ValidateEmailHost($Email)) { // Mailserver nicht erreichbar, ablehnen
            $this->error_msg = $this->error_msg . _("Der Mailserver ist nicht erreichbar, bitte überprüfen Sie, ob Sie E-Mails mit der angegebenen Adresse verschicken und empfangen können!") . "<br>";
            return false;
        } else { // Server ereichbar
            if (!$validator->ValidateEmailBox($Email)) { // aber user unbekannt. Mail an abuse!
                StudipMail::sendAbuseMessage("Register", "Emailbox unbekannt\n\nUser: $username\nEmail: $Email\n\nIP: $REMOTE_ADDR\nZeit: $Zeit\n");
                $this->error_msg = $this->error_msg . _("Die angegebene E-Mail-Adresse ist nicht erreichbar, bitte überprüfen Sie Ihre Angaben!") . "<br>";
                return false;
            } else {
                ; // Alles paletti, jetzt kommen die Checks gegen die Datenbank...
            }
        }

        // alle Checks ok, Benutzer registrieren...
        $hasher = UserManagement::getPwdHasher();
        $user = User::find($user_id);
        if ($user->username != $username)
            return false;
        
        $user->password = $hasher->HashPassword(Request::get('password'));
        $user->vorname = $Vorname;
        $user->nachname = $Nachname;
        $user->email = $Email;
        $user->geschlecht = Request::int('geschlecht');
  
        $user->store();
        if ($user->user_id) {
            Seminar_Register_Auth::sendValidationMail($user);
            $this->auth["perm"] = $user->perms;
            //return $user->user_id;
            //TODO set account claimed
            $mapping = KuferMapping::findOneBySQL('studip_id = :user_id', [':user_id' => $user->user_id]);
            $mapping->claimed = '1';
            $mapping->store();
        }
        $this->render_nothing();
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
      
}
