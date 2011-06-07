
<h1>Manage Eventtypes</h1>
<div style="float: right">
	<ul class="page-action">
		<li><a href="<?php echo ENTRADA_URL; ?>/admin/configuration/organisations/manage/eventtypes?section=add&amp;id=<?php echo $ORGANISATION_ID;?>" class="strong-green">Add New Eventtype</a></li>
	</ul>
</div>
<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

$query = "	SELECT a.* FROM `events_lu_eventtypes` AS a 
			LEFT JOIN `eventtype_organisation` AS b
			ON a.`eventtype_id` = b.`eventtype_id` 
			WHERE b.`organisation_id` = ".$db->qstr($ORGANISATION_ID)." 
			AND a.`eventtype_active` = 1 
			ORDER BY a.`eventtype_title` ASC";




$results = $db->GetAll($query);

if($results){
?>
<form action ="<?php echo ENTRADA_URL;?>/admin/configuration/organisations/manage/eventtypes?section=delete&amp;id=<?php echo $ORGANISATION_ID;?>" method="post">
<table class="tableList" cellspacing="0" cellpadding="1" border="0" summary="List of Organisations">
	<colgroup>
		<col class="modified" />
		<col class="title" />
	</colgroup>
	<thead>
		<tr>
			<td class="modified">&nbsp;</td>
			<td class="title">Event Type</td>
		</tr>
	</thead>
	<tbody>
		<?php
			foreach($results as $result){
				echo "<tr><td><input type=\"checkbox\" name = \"remove_ids[]\" value=\"".$result["eventtype_id"]."\"/></td>";
				echo"<td><a href=\"".ENTRADA_URL."/admin/configuration/organisations/manage/eventtypes?section=edit&amp;id=".$ORGANISATION_ID."&amp;type_id=".$result["eventtype_id"]."\">".$result["eventtype_title"]."</a></td></tr>";
			}
		?>
	</tbody>
</table>
<br/>
<input type="submit" class="button" value="Delete Selected" />
</form>
<?php

}
else{
	$NOTICE++;
	$NOTICESTR[] = "There are currently no Event Types assigned to this Organisation";
	echo "<br/>".display_notice();
		
}

?>

