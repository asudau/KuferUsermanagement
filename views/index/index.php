
<h1>
    <?= get_title_for_status('autor', 2)?> haben Zugriff auf diesen Kurs ab: 
    <?= date('d.m.Y', $coursebegin) ?>
    <a href='<?=$controller->url_for('index/edit_startdate')?>' data-dialog='size=small'  title='Datum �ndern' ><?= Icon::create('edit', 'clickable') ?></a>
</h1>
<br/>
<br/>

<table id="keywordstt" class="sortable-table default">
    <thead>
		<tr>
        <th data-sort="text"><span>Name</span></th>
        <th data-sort="text"><span>Account aktiviert</span></th>
        <th data-sort="text"><span>Einladung versendet</span></th>
        <th data-sort="text" style=''><span>Account status</span></th>  
        <th><span>Aktionen</span></th>
    </tr>
    </thead>
    
    <tbody>
    
    <?php foreach ($members as $member): ?>
        <tr>
            <td><?= $member->vorname . ' ' . $member->nachname?></a></td>
            <td><?= $active[$member->user_id] ? 'Ja' : 'Nein' ?></td>
            <td><?= $invitations[$member->user_id]->date ? date('d.m.Y', $invitations[$member->user_id]->date) : 'nein' ?></a></td>
            <td style='display:none'><?= object_get_visit($course->id, 'sem', 'last', false, $member->user_id)?></td>
            <td><?= $account_status[$member->user_id] ?></td>
            <td>
            <?php if (!$active[$member->user_id]) : ?>
                <a title='(Nochmal) Einladen' href = <?= $controller->url_for('index/send_register_invitation/' . $member->user_id )?>> <?=Icon::create('mail', 'clickable')?>
            <?php endif ?>
            </td>
        </tr>
    <?php endforeach ?>
        
    </tbody>
</table>