<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

?>
<h1>Manage Organisations</h1>
<div style="float: right">
	<ul class="page-action">
		<li><a href="<?php echo ENTRADA_URL; ?>/admin/configuration/organisations?section=add" class="strong-green">Add New Organisation</a></li>
	</ul>
</div>
<?php


$query		= "	SELECT * FROM `".AUTH_DATABASE."`.`organisations`
				ORDER BY `organisation_title` ASC";
	$results	= $db->GetAll($query);
	if ($results) {
		?>
		<div id="organisations-section">
			<form action="<?php echo ENTRADA_URL; ?>/admin/configuration/organisations?section=delete" method="post">
				<table class="tableList" cellspacing="0" cellpadding="1" border="0" summary="List of Organisations">
					<colgroup>
						<col class="modified" />
						<col class="title" />
					</colgroup>
					<thead>
						<tr>
							<td class="modified">&nbsp;</td>
							<td class="title">Organisation Title</td>
						</tr>
					</thead>
					<tbody>
						<?php
						foreach($results as $result) {
							$url = ENTRADA_URL."/admin/configuration/organisations/manage?org_id=".(int) $result["organisation_id"];

							echo "<tr>\n";
							echo "	<td><input type=\"checkbox\" name = \"remove_ids[]\" value = \"".html_encode($result["organisation_id"])."\"/></td>\n";
							//echo "	<td>".html_encode($result["organisation_id"])."</td>\n";
							echo "	<td><a href=\"".$url."\">".html_encode($result["organisation_title"])."</a></td>\n";
							echo "</tr>\n";
						}
						?>
					</tbody>
				</table><br/>
				<input type="submit" class="button" value="Delete Selected" />
				
			</form>
		</div>
		<?php
	} else {
		$NOTICE++;
		$NOTICESTR[] = "There are no apartments in the system to manage.<br /><br />Please click the &quot;Add Apartment&quot; to begin.";

		echo display_notice($NOTICESTR);
	}




?>