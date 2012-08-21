<script type="text/javascript">
	function updateColumns(noColumns, noCategories) {
		categories = {};
		categories["evaluation_form_responses"] = {};
		categories["evaluation_form_category_criteria"] = {};
		$$('input.response_text').each(function (i) {
			var index = i.id.replace(/[A-Za-z$_\-]/g, '');
			categories["evaluation_form_responses"][index] = {};
			categories["evaluation_form_category_criteria"][index] = {};
			categories["evaluation_form_responses"][index]["response_text"] = i.value;
			$$('textarea.criteria_'+index).each(function (j) {
				var jindex = j.id.replace(/[A-Za-z$_\-]/g, '');
				categories["evaluation_form_category_criteria"][index][jindex] = {};
				categories["evaluation_form_category_criteria"][index][jindex]["criteria"] = j.value;
			});
		});
		new Ajax.Updater({ success: 'columns_list' }, '<?php echo ENTRADA_URL; ?>/api/evaluations-rubric-column-list.api.php?columns='+noColumns, {
			method: 'post',
			parameters: {
				response_text: JSON.stringify(categories)

			},
			onCreate: function () {
				$('columns_list').innerHTML = '<tr><td colspan="3">&nbsp;</td></tr><tr><td>&nbsp;</td><td colspan="3"><span class="content-small" style="align: center;">Loading... <img src="<?php echo ENTRADA_URL; ?>/images/indicator.gif" style="vertical-align: middle;" /></span></td></tr>';
			},
			onSuccess: function () {
				loadCategories(noColumns, noCategories, 0);
			}
		});
	}
	
	var categories;
	function loadCategories (noColumns, noCategories, removeIndex) {
		categories = {};
			categories["evaluation_form_categories"] = {};
			categories["evaluation_form_category_criteria"] = {};
		$$('input.category').each(function (i) {
			var index = i.id.replace(/[A-Za-z$_\-]/g, '');
			if (removeIndex && index > removeIndex) {
				index--;
			}
			categories["evaluation_form_categories"][index] = {};
			categories["evaluation_form_category_criteria"][index] = {};
			categories["evaluation_form_categories"][index]["category"] = i.value;
			$$('textarea.criteria_'+index).each(function (j) {
				var jindex = j.id.replace(/[A-Za-z$_\-]/g, '');
				categories["evaluation_form_category_criteria"][index][jindex] = {};
				categories["evaluation_form_category_criteria"][index][jindex]["criteria"] = j.value;
			});
		});
		if (noCategories != parseInt($('categories_count').value)) {
			$('categories_count').value = noCategories;
		}
		new Ajax.Updater({ success: 'category_list' }, '<?php echo ENTRADA_URL; ?>/api/evaluations-rubric-category-list.api.php?columns='+noColumns+'&categories='+noCategories, {
			method: 'post',
			parameters: {
				response_text: JSON.stringify(categories)

			},
			onCreate: function () {
				$('category_list').innerHTML = '<tr><td colspan="3">&nbsp;</td></tr><tr><td>&nbsp;</td><td colspan="3"><span class="content-small" style="align: center;">Loading... <img src="<?php echo ENTRADA_URL; ?>/images/indicator.gif" style="vertical-align: middle;" /></span></td></tr>';
			}
		});
	}
</script>