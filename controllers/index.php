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
           
        $item = new Navigation(_('Übersicht'), $this->url_for('index'));
        Navigation::getItem('course/kufer_accounts')->addSubNavigation('index', $item);

        if($GLOBALS['perm']->have_studip_perm('dozent', $this->course->id)){
            $actions = new ActionsWidget();
            $actions->setTitle(_('Aktionen'));

            $actions->addLink(
            'Einladung versenden',
            $this->url_for('index/send_register_invitation'), Icon::create('mail', 'clickable')); 

            Sidebar::get()->addWidget($actions);
        }
    }

    public function index_action(){
        Navigation::activateItem('course/kufer_accounts/index');
        //get teilnehmende where exists kufer mapping and claimed = false
        $this->course = Course::findCurrent();
        $this->members = [];
        $this->account_status = [];
        $this->invitations = [];
        foreach($this->course->members as $member){
            $mapping = KuferMapping::findOneByStudip_id($member->user_id);
            $this->members[] = $member;
            $this->account_status[$member->user_id] = KuferMapping::getAccountStatusText($member->user_id);
            $this->invitations[$member->user_id] = KuferRegisterAccountInvitation::findOneByUser_id($member->user_id);
        }
        //action: registrierungsauffforderung versenden
        //freie registrierung??? mit username und user_id
    }
    
    public function send_register_invitation_action (){
        $this->course = Course::findCurrent();
        $invitations = KuferRegisterAccountInvitation::findBySeminar_id($this->course->id);

        foreach($this->course->members as $member){
            $mapping = KuferMapping::findOneByStudip_id($member->user_id);
            if(!$invitations[$member->user_id] && $mapping){
                
                //TODO send Invitation
                $this->sendRegisterMail($member->user_id, $this->course->name);
                $invitation = new KuferRegisterAccountInvitation();
                $invitation->user_id = $member->user_id;
                $invitation->seminar_id = $this->course->id;
                $invitation->invited_by = User::findCurrent()->id;
                $invitation->date = time();
                $invitation->store();
            }
        }
        $this->render_nothing();
    }
    

//    public function create_action()
//    {
//      $user = $this->new_user();  
//
//          $user->email = 'asudau@uos.de';
//          $user->first_name = 'unbekannt';
//          $user->last_name = 'unbekannt';
//          
//          $i = 1;
//          $user_name = substr($this->sonderzeichen($user->first_name), 0, $i) . $this->sonderzeichen($user->last_name);
//          while (Studip_User::find_by_user_name($user_name) && $i<= strlen($this->sonderzeichen($user->first_name))){
//              $i++;
//              $user_name = substr($this->sonderzeichen($user->first_name), 0, $i) . $this->sonderzeichen($user->last_name);
//          }
//
//          
//          
//          $md5_user = new User();
//          $md5_user->username = $user_name;
//          $md5_user->vorname = '';
//          $md5_user->nachname = 'Vorläufiger Nutzer';
//          $md5_user->email = $user->email;
//          $md5_user->perms = $user->permission;
//          $md5_user->auth_plugin = $user->auth_plugin;
//          
//          if (!$md5_user->store())
//              return new Studip_Ws_Fault(self::parse_msg_to_clean_text($user->error));
//
//          $entry = new KuferMapping();
//          $entry->studip_id = $md5_user->user_id;
//          $entry->claimed = false;
//          $entry->store();
//          $user->id = $entry->ID;
//        
//    }
   
    
    private function get_user_data($course_id, $status){
        $db = DBManager::get();
        $query = "SELECT u.username, u.user_id, u.Vorname, u.Nachname, uo.last_lifesign, COUNT(fe.topic_id) AS Forenbeitraege
			FROM seminar_user su 
                        LEFT JOIN auth_user_md5 u ON u.user_id = su.user_id
			LEFT JOIN user_online uo ON u.user_id = uo.user_id
                        LEFT JOIN forum_entries fe ON (u.user_id = fe.user_id AND fe.seminar_id = :sem_id)
                        WHERE su.Seminar_id = :sem_id
                        AND su.status = :status
			GROUP BY u.user_id";
                            
        $statement = $db->prepare($query);
        $statement->execute(array('sem_id' => $course_id, 'status' =>$status));
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
        $string = str_replace("ä", "ae", $string);
        $string = str_replace("ü", "ue", $string);
        $string = str_replace("ö", "oe", $string);
        $string = str_replace("Ä", "Ae", $string);
        $string = str_replace("Ü", "Ue", $string);
        $string = str_replace("Ö", "Oe", $string);
        $string = str_replace("ß", "ss", $string);
        $string = str_replace("´", "", $string);
        return $string;
    }
    
    private function sendRegisterMail($user_id, $kursname){
        
        $user = New User($user_id);
        $contact_mail = $user->Email; //TODO get user Mail
        
        
        $mailtext = "Willkommen bei der              
                " . $GLOBALS['UNI_NAME_CLEAN']   . "!
                    
                Sie haben sich für den Kurs " . $kursname . " an der " . $GLOBALS['UNI_NAME_CLEAN'] . " angemeldet.
                    
                Die " . $GLOBALS['UNI_NAME_CLEAN'] . " stellt Ihnen eine online-Lernpattform zur Verfügung, welche zum Beispiel Kommunikation untersützt,
                die zusätzliche Bereitstellung von Kursinhalten ermöglicht und vieles mehr.
                
                Ob Sie dieses Angebot nutzen möchten, entscheiden Sie selbst.
                
                Bei Interesse können sie sich über die folgende URL einen Account einrichten:
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
