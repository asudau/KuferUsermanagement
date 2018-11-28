
<?php if ($perm_autor || $perm == 'root'): ?>

<h1><?= get_title_for_status('autor', 2)?>:</h1>
<table id="keywords" class="sortable-table default">
    <thead>
		<tr>
        <th data-sort="text"><span>Name</span></th>
        <th data-sort="text"><span>Letzter Kursbesuch vor</span></th>
        <th style='display:none;'><span>Letzter Kursbesuch vor</span></th>
        <th data-sort="text"><span>Zuletzt online vor</span></th>
        <th style='display:none; '><span>Zuletzt online vor</span></th>
        <th data-sort="text" style=''><span>Anzahl Forenbeiträge</span></th>
        <? if ($badges) : ?>
        <th data-sort="text" style='width:45%'><span>Badges</span></th>
        <? endif ?>
        <!--<th>Courseware besucht?</th>-->
    </tr>
    </thead>
    
    <tbody>
    <?
    
    $badge_content;
    
    foreach ($tn_data as $tn){ 
    
        
        if($badges[$tn['user_id']]){
            foreach ($badges[$tn['user_id']] as $badge){
                $block = new \Mooc\DB\Block($badge['badge_block_id']);
                $field = current(Mooc\DB\Field::findBySQL('block_id = ? AND name = ?', array($block->id, 'file_id')));
                $file_id= $field->content;
                $field = current(\Mooc\DB\Field::findBySQL('block_id = ? AND name = ?', array($block->id, 'file_name')));
                $file_name = $field->content;
                $badge_content[$tn['user_id']] .= 
                '<img title=\'' . date('d.m.Y', $badge['mkdate']) . '\' style=\'max-width:10%\' src=\'../../sendfile.php?type=0&file_id=' . $file_id . '&file_name=' . $file_name . '\'/>';
            }
        }
        
        ?>
        <tr>
            <td><a href='<?= URLHelper::getLink('dispatch.php/profile?username=' . $tn['username']) ?>' ><?= $tn['Vorname'] . ' ' . $tn['Nachname']?></a></td>
            <td style='display:none'><?= object_get_visit($course->id, 'sem', 'last', false, $tn['user_id'])?></td>
            <td><?= $controller->lastonline_to_string(object_get_visit($course->id, 'sem', 'last', false, $tn['user_id'])) ?></td>
            <td style='display:none'><?= $tn['last_lifesign']?></td>
            <td><?= $controller->lastonline_to_string($tn['last_lifesign'])?></td>
            <td><?= $tn['Forenbeitraege']?></td>
            <? if ($badges): ?>
            <td><?= $badge_content[$tn['user_id']]?></td>
            <? endif ?>
            <!--<td>Courseware besucht?</td>-->
        </tr>
        <?
    }
    ?>
     </tbody>
</table>

<?php endif ?>

<?php if ($perm_tutor || $perm == 'root'): ?>
    
<h1><?= get_title_for_status('tutor', 2)?>:</h1>
<table id="keywordsdz" class="sortable-table default">
    <thead>
		<tr>
        <th data-sort="text"><span>Name</span></th>
        <th data-sort="text"><span>Letzter Kursbesuch vor</span></th>
        <th style='display:none;'><span>Letzter Kursbesuch vor</span></th>
        <th data-sort="text"><span>Zuletzt online vor</span></th>
        <th style='display:none; '><span>Zuletzt online vor</span></th>
        <th data-sort="text" style=''><span>Anzahl Forenbeiträge</span></th>  
    </tr>
    </thead>
    
    <tbody>
    
    <?php foreach ($tt_data as $tt): ?>
        <tr>
            <td><a href='<?= URLHelper::getLink('dispatch.php/profile?username=' . $tt['username']) ?>' ><?= $tt['Vorname'] . ' ' . $tt['Nachname']?></a></td>
            <td style='display:none'><?= object_get_visit($course->id, 'sem', 'last', false, $tt['user_id'])?></td>
            <td><?= $controller->lastonline_to_string(object_get_visit($course->id, 'sem', 'last', false, $tt['user_id'])) ?></td>
            <td style='display:none'><?= $tt['last_lifesign']?></td>
            <td><?= $controller->lastonline_to_string($tt['last_lifesign'])?></td>
            <td><?= $tt['Forenbeitraege']?></td>
            <!--<td>Courseware besucht?</td>-->
        </tr>
    <?php endforeach ?>
        
    </tbody>
</table>
<?php endif ?>

<?php if ($perm_dozent || $perm == 'root'): ?>

<h1><?= get_title_for_status('dozent', 2)?>:</h1>
<table id="keywordstt" class="sortable-table default">
    <thead>
		<tr>
        <th data-sort="text"><span>Name</span></th>
        <th data-sort="text"><span>Letzter Kursbesuch vor</span></th>
        <th style='display:none;'><span>Letzter Kursbesuch vor</span></th>
        <th data-sort="text"><span>Zuletzt online vor</span></th>
        <th style='display:none; '><span>Zuletzt online vor</span></th>
        <th data-sort="text" style=''><span>Anzahl Forenbeiträge</span></th>  
    </tr>
    </thead>
    
    <tbody>
    
    <?php foreach ($dz_data as $dz): ?>
        <tr>
            <td><a href='<?= URLHelper::getLink('dispatch.php/profile?username=' . $dz['username']) ?>' ><?= $dz['Vorname'] . ' ' . $dz['Nachname']?></a></td>
            <td style='display:none'><?= object_get_visit($course->id, 'sem', 'last', false, $dz['user_id'])?></td>
            <td><?= $controller->lastonline_to_string(object_get_visit($course->id, 'sem', 'last', false, $dz['user_id'])) ?></td>
            <td style='display:none'><?= $dz['last_lifesign']?></td>
            <td><?= $controller->lastonline_to_string($dz['last_lifesign'])?></td>
            <td><?= $dz['Forenbeitraege']?></td>
            <!--<td>Courseware besucht?</td>-->
        </tr>
    <?php endforeach ?>
        
    </tbody>
</table>
<?php endif ?>

