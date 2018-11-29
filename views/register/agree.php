
 <?php
 require_once 'app/models/siteinfo.php';
 ?>

<table align="center" border="0" cellpadding="30" cellspacing="0">
    <tr>
        <td class="table_header_bold">
            <?= Icon::create('door-enter', 'info_alt')->asImg() ?>
            <b><?=_("Nutzungsbedingungen")?></b>
        </td>
    </tr>
    <tr>
        <td class="blank">
        <p><h3 align="center">   
	        <?= Icon::create('accept', 'clickable')->asImg()  ?>   
                <a href="<?= URLHelper::getLink(Request::url(), array('i_accept_the_terms' => 'yes')) ?>"><b><?=_("Ich erkenne die Nutzungsbedingungen und Datenschutzerkl&auml;rung an")?></b></a>   
            </h3></p>   
            <br/>   
 	</td>   
    </tr>   
    <tr>   
        <td class="blank" width="50%">   
     
 	<? //Nutzungsbedingungen und DatenschutzerklÃ?rung aus dem Impressum darstellen   
 	$si = new Siteinfo();   
 	$nutzung = $si->get_detail_content_processed(8);    
 	$datenschutz = $si->get_detail_content_processed(9);    
 	?>   
 		                       
 	<p><?= $nutzung ?></p><br/><br/><br/>   
 	<p><?= $datenschutz ?></p> 
        </td>
    </tr>
</table>


<p><h3 align="center">   
	        <?= Icon::create('accept', 'clickable')->asImg()  ?>   
                <a href="<?= URLHelper::getLink(Request::url(), array('i_accept_the_terms' => 'yes')) ?>"><b><?=_("Ich erkenne die Nutzungsbedingungen und Datenschutzerkl&auml;rung an")?></b></a>   
            </h3></p>   
            <br/>   