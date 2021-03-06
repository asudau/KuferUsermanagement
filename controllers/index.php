<?php
require_once 'lib/webservices/api/studip_user.php';


class IndexController extends StudipController {

    public function __construct($dispatcher)
    {
        parent::__construct($dispatcher);
        $this->plugin = $dispatcher->plugin;
        Navigation::activateItem('course/kufer_accounts');
    }

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        $this->course = Course::findCurrent();
        
        PageLayout::setTitle($this->course->getFullname()." - " ._("Kufer Nutzerverwaltung"));
           
        $item = new Navigation(_('�bersicht'), $this->url_for('index'));
        Navigation::getItem('course/kufer_accounts')->addSubNavigation('index', $item);

        if($GLOBALS['perm']->have_studip_perm('dozent', $this->course->id)){
            $actions = new ActionsWidget();
            $actions->setTitle(_('Aktionen'));

            $actions->addLink(
            'Einladung versenden',
            $this->url_for('index/send_register_invitation'), Icon::create('mail', 'clickable')); 

            Sidebar::get()->addWidget($actions);
        } else
            throw new AccessDeniedException(_("Sie haben keine Berechtigung."));
    }

    public function index_action(){
        Navigation::activateItem('course/kufer_accounts/index');
        //get teilnehmende where exists kufer mapping and claimed = false
        $this->course = Course::findCurrent();
        
        $this->coursebegin = Studip_VHS::getCourseBegin($this->course->id);
        
        $this->members = [];
        $this->account_status = [];
        $this->invitations = [];
        $this->active = [];
        foreach($this->course->members as $member){
            $mapping = KuferMapping::findOneByStudip_id($member->user_id);
            $this->members[] = $member;
            $this->account_status[$member->user_id] = KuferMapping::getAccountStatusText($member->user_id);
            $this->invitations[$member->user_id] = KuferRegisterAccountInvitation::findOneByUser_id($member->user_id);
            $this->active[$member->user_id] = $this->get_last_lifesign($member->user_id) || $mapping->claimed;
        } 
        //action: registrierungsauffforderung versenden
        //freie registrierung??? mit username und user_id
    }
    
    public function send_register_invitation_action ($user_id = NULL){
        $this->course = Course::findCurrent();
        $invitations = KuferRegisterAccountInvitation::findBySeminar_id($this->course->id);
        $nomail = true;

        //einzelne Einladung
        if ($user_id){
            $user = User::find($user_id);
            $this->sendRegisterMail($user_id, $this->course->name);
            PageLayout::postMessage(MessageBox::success(_("Einladung versendet an " . $user->email)));
            $invitation = KuferRegisterAccountInvitation::findOneBySQL('user_id = :user_id AND seminar_id = :seminar_id', ['user_id' => $user_id, 'seminar_id' => $this->course->id]);
            if ($invitation){
                $invitation->date = time();
                $invitation->store();
            } else {
                $invitation = new KuferRegisterAccountInvitation();
                $invitation->user_id = $member->user_id;
                $invitation->seminar_id = $this->course->id;
                $invitation->invited_by = User::findCurrent()->id;
                $invitation->date = time();
                $invitation->store();
            }
            $nomail = false;
        
        //Komplett einladen
        } else {
            foreach($this->course->members as $member){
                $mapping = KuferMapping::findOneByStudip_id($member->user_id);
                $user_active = ($this->get_last_lifesign($member->user_id, $this->course->id) || $mapping->claimed);
                if(!$invitations[$member->user_id] && $mapping && !$user_active){
                    //send Invitation
                    $this->sendRegisterMail($member->user_id, $this->course->name);
                    PageLayout::postMessage(MessageBox::success(_("Einladung versendet an " . $member->email)));
                    $invitation = new KuferRegisterAccountInvitation();
                    $invitation->user_id = $member->user_id;
                    $invitation->seminar_id = $this->course->id;
                    $invitation->invited_by = User::findCurrent()->id;
                    $invitation->date = time();
                    $invitation->store();
                    $nomail = false;
                }
            }
        }
        
        if ($nomail){
            PageLayout::postMessage(MessageBox::success(_("Keine Einladungen mehr zu verschicken.")));
        }
        $this->redirect('index');
    }
   
    public function edit_startdate_action(){
        $this->coursebegin = Studip_VHS::getCourseBegin(Course::findCurrent()->id);
    }
    
    public function set_startdate_action(){
        $date = DateTime::createFromFormat('Y-m-d', Request::get('start_date'));
        if (Studip_VHS::setCourseBegin(Course::findCurrent()->id, $date->getTimestamp())){
             PageLayout::postMessage(MessageBox::success(_("Datum wurde gespeichert.")));
        }
        $this->redirect('index');
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
    
    function get_fields()
    {
        $fields = array('auth_user_md5.user_id'  => 'id',
                        'auth_user_md5.username' => 'user_name',
                        'auth_user_md5.Vorname'  => 'first_name',
                        'auth_user_md5.Nachname' => 'last_name',
                        'auth_user_md5.Email'    => 'email',
                        'auth_user_md5.perms'    => 'permission',
                        'auth_user_md5.auth_plugin' => 'auth_plugin',
                        'auth_user_md5.visible' => 'visibility');
        return $fields;
    }
    
    function new_user(){
        
        
        $row['username'] = 'unbekannter_kufernutzer_' . time();
        $row['perms'] = 'autor';
        $row['auth_plugin'] = 'Standard';
        $row['visible'] = '0';
        $user = array();
        foreach (self::get_fields() as $old => $new) {
            $user[$new] = $row[array_pop(explode('.', $old))];
        }
        $result = new Studip_User($user);

        return $result;
    }
    
    function sonderzeichen($string)
    {
        $string = str_replace("�", "ae", $string);
        $string = str_replace("�", "ue", $string);
        $string = str_replace("�", "oe", $string);
        $string = str_replace("�", "Ae", $string);
        $string = str_replace("�", "Ue", $string);
        $string = str_replace("�", "Oe", $string);
        $string = str_replace("�", "ss", $string);
        $string = str_replace("�", "", $string);
        return $string;
    }
    
    private function sendRegisterMail($user_id, $kursname){
        
        $user = New User($user_id);
        $contact_mail = $user->Email; //TODO get user Mail
        
        
        $mailtext = "Willkommen bei der              
                " . $GLOBALS['UNI_NAME_CLEAN']   . "!
                    
                Sie haben sich f�r den Kurs " . $kursname . " an der " . $GLOBALS['UNI_NAME_CLEAN'] . " angemeldet.
                    
                Die " . $GLOBALS['UNI_NAME_CLEAN'] . " stellt Ihnen eine online-Lernpattform zur Verf�gung, welche zum Beispiel Kommunikation unters�tzt,
                die zus�tzliche Bereitstellung von Kursinhalten erm�glicht und vieles mehr.
                
                Ob Sie dieses Angebot nutzen m�chten, entscheiden Sie selbst.
                
                Bei Interesse k�nnen sie sich �ber die folgende URL einen Account einrichten:
                (Nach der Registrierung haben Sie sofort Zugriff auf Ihren Kurs)
                
                " . $GLOBALS['ABSOLUTE_URI_STUDIP'] . "plugins.php/kuferusermanagement/register/agree/" . $user_id;
            

            $empfaenger = $contact_mail;
            //$absender   = "asudau@uos.de";
            $betreff    = 'Willkommen bei der ' . $GLOBALS['UNI_NAME_CLEAN'];

            $template = $GLOBALS['template_factory']->open('mail/html');
            $template->set_attribute('lang', 'de');
            $template->set_attribute('message', $mailtext);
            $mailhtml = $template->render();
            
            
            return StudipMail::sendMessage($empfaenger, $betreff, $mailtext, $mailhtml);
            /**
            return $mail->addRecipient($empfaenger)
                 ->setReplyToEmail('')
                 ->setSenderEmail('el4@elan-ev.de')
                 ->setSenderName($GLOBALS['UNI_NAME_CLEAN']) //Globals UNI_NAME
                 ->setSubject($betreff)
                 ->setBodyText($mailtext)
                 ->send();
                 **/
    }

}
