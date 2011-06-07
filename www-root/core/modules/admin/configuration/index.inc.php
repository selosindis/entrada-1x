
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
	<h2>Organisations</h2>
		<div id="organisations-section">
			<form action="<?php echo ENTRADA_URL; ?>/admin/regionaled/apartments?section=delete" method="post">
				<table class="tableList" cellspacing="0" cellpadding="1" border="0" summary="List of Organisations">
					<colgroup>
						<col class="title" />
					</colgroup>
					<thead>
						<tr>
							<td class="title">Organisation Title</td>
						</tr>
					</thead>
					<tbody>
						<?php
						foreach($results as $result) {
							$url = ENTRADA_URL."/admin/configuration/organisations/manage?id=".(int) $result["organisation_id"];

							echo "<tr>\n";
							echo "	<td><a href=\"".$url."\">".html_encode($result["organisation_title"])."</a></td>\n";
							echo "</tr>\n";
						}
						?>
					</tbody>
				</table>
			</form>
		</div>
		<?php
	} else {
		$NOTICE++;
		$NOTICESTR[] = "There are no apartments in the system to manage.<br /><br />Please click the &quot;Add Apartment&quot; to begin.";

		echo display_notice($NOTICESTR);
	}


?>

