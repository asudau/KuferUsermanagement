<?
use Studip\Button, Studip\LinkButton;
?>

<html>


<form name="start_date" method="post" action="<?= $controller->url_for('index/set_startdate') ?>" class="default collapsable">
    
    <div>
        Neues Startdatum:
    </div>
    <div>
         <input style='width:120px; max-width:120px' type='date' name ='start_date' value='<?= date('Y-m-d', $coursebegin) ?>'>
    </div>
    
    <footer data-dialog-button>
        <?= Button::create(_('Übernehmen')) ?>
    </footer>
</form>
<?php



