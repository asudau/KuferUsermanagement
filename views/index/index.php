



<h1><?= get_title_for_status('dozent', 2)?>:</h1>
<table id="keywordstt" class="sortable-table default">
    <thead>
		<tr>
        <th data-sort="text"><span>Name</span></th>
        <th data-sort="text"><span>Einladung versendet</span></th>
        <th data-sort="text" style=''><span>Account status</span></th>  
    </tr>
    </thead>
    
    <tbody>
    
    <?php foreach ($members as $member): ?>
        <tr>
            <td><?= $member->vorname . ' ' . $member->nachname?></a></td>
            <td><?= date('d.m.Y', $invitations[$member->user_id]->date) ?></a></td>
            <td style='display:none'><?= object_get_visit($course->id, 'sem', 'last', false, $member->user_id)?></td>
            <td><?= $account_status[$member->user_id] ?></td>
            <!--<td>Courseware besucht?</td>-->
        </tr>
    <?php endforeach ?>
        
    </tbody>
</table>