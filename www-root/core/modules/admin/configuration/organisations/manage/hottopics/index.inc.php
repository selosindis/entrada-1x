
<h1>Manage topics</h1>
<div style="float: right">
	<ul class="page-action">
		<li><a href="<?php echo ENTRADA_URL; ?>/admin/configuration/organisations/manage/hottopics?section=add&amp;id=<?php echo $ORGANISATION_ID;?>" class="strong-green">Add New Hot Topic</a></li>
	</ul>
</div>
<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

$query = "	SELECT a.* FROM `events_lu_topics` AS a 
			LEFT JOIN `topic_organisation` AS b
			ON a.`topic_id` = b.`topic_id` 
			WHERE b.`organisation_id` = ".$db->qstr($ORGANISATION_ID)." 
			ORDER BY a.`topic_name` ASC";




$results = $db->GetAll($query);

if($results){
?>
<form action ="<?php echo ENTRADA_URL;?>/admin/configuration/organisations/manage/hottopics?section=delete&amp;id=<?php echo $ORGANISATION_ID;?>" method="post">
<table class="tableList" cellspacing="0" cellpadding="1" border="0" summary="List of Organisations">
	<colgroup>
		<col class="modified" />
		<col class="title" />
	</colgroup>
	<thead>
		<tr>
			<td class="modified">&nbsp;</td>
			<td class="title">Hot Topic</td>
		</tr>
	</thead>
	<tbody>
		<?php
			foreach($results as $result){
				echo "<tr><td><input type=\"checkbox\" name = \"remove_ids[]\" value=\"".$result["topic_id"]."\"/></td>";
				echo"<td><a href=\"".ENTRADA_URL."/admin/configuration/organisations/manage/hottopics?section=edit&amp;id=".$ORGANISATION_ID."&amp;topic_id=".$result["topic_id"]."\">".$result["topic_name"]."</a></td></tr>";
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
	$NOTICESTR[] = "There are currently no Hot Topics assigned to this Organisation";
	echo "<br/>".display_notice();
		
}

?>

