
 <?php
 require_once 'app/models/siteinfo.php';
 ?>

<table align="center" border="0" cellpadding="30" cellspacing="0">
    <tr>
        <td class="table_header_bold">
            <?= Icon::create('door-enter', 'info_alt')->asImg() ?>
            <b><?=_("Nutzungsbedingungen und Datenschutzerklärung")?></b>
        </td>
    </tr>
    <tr>
        <td class="blank">
            <div style="text-align: center">
                <div class="button-group">
                    <?= Studip\LinkButton::create(_('Ich erkenne die Nutzungsbedingungen an'), $controller->url_for('register/index/') . $user->id) ?>
                    <?= Studip\LinkButton::create(_('Registrierung abbrechen'), URLHelper::getLink('index.php')) ?>
                </div>
            </div>
        </td>   
    </tr>   
    <tr>   
        <td class="blank" width="50%">   
     
 	<? //Nutzungsbedingungen und Datenschutzerklärung aus dem Impressum darstellen   
 	$si = new Siteinfo();   
 	$nutzung = $si->get_detail_content_processed(8);    
 	$datenschutz = $si->get_detail_content_processed(9);    
 	?>   
 		                       
 	<p><?= $nutzung ?></p><br/><br/><br/>   
 	<p><?= $datenschutz ?></p> 
        </td>
    </tr>
</table>


<div style="text-align: center">
    <div class="button-group">
        <?= Studip\LinkButton::create(_('Ich erkenne die Nutzungsbedingungen an'), $controller->url_for('register/index/') . $user->id) ?>
        <?= Studip\LinkButton::create(_('Registrierung abbrechen'), URLHelper::getLink('index.php')) ?>
    </div>
</div> 