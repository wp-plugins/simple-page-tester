var currentOptionsDiv;
var prevOptionsDiv;

jQuery(document).ready(function() {
	
	currentOptionsDiv = jQuery('#options_1');
	
	jQuery('#duplicate_page').click(function() {
		
		prevOptionsDiv = currentOptionsDiv;
		currentOptionsDiv = jQuery('#options_duplicate_page');
			
		prevOptionsDiv.fadeOut(200);
		currentOptionsDiv.delay(200).fadeIn(200);
		
		currentOptionsDiv.find('.spt_back').unbind();
		currentOptionsDiv.find('.spt_back').click(function() {
			currentOptionsDiv.fadeOut(200);
			prevOptionsDiv.delay(200).fadeIn(200);
			
			currentOptionsDiv = prevOptionsDiv;
			prevOptionsDiv = null;
			
		});
		
		setTimeout(duplicatePost, 400);
	});
	
	jQuery('#create_blank').click(function() {
		
		prevOptionsDiv = currentOptionsDiv;
		currentOptionsDiv = jQuery('#options_create_blank');
			
		prevOptionsDiv.fadeOut(200);
		currentOptionsDiv.delay(200).fadeIn(200);
		
		currentOptionsDiv.find('.spt_back').unbind();
		currentOptionsDiv.find('.spt_back').click(function() {
			currentOptionsDiv.fadeOut(200);
			prevOptionsDiv.delay(200).fadeIn(200);
			
			currentOptionsDiv = prevOptionsDiv;
			prevOptionsDiv = null;
			
		});
		
		setTimeout(createNewPost, 400);
	});
	
	jQuery('#choose_existing').click(function() {
		
		prevOptionsDiv = currentOptionsDiv;
		currentOptionsDiv = jQuery('#options_choose_existing');
			
		prevOptionsDiv.fadeOut(200);
		currentOptionsDiv.delay(200).fadeIn(200);
		
		currentOptionsDiv.find('.spt_back').unbind();
		currentOptionsDiv.find('.spt_back').click(function() {
			currentOptionsDiv.fadeOut(200);
			prevOptionsDiv.delay(200).fadeIn(200);
			
			currentOptionsDiv = prevOptionsDiv;
			prevOptionsDiv = null;
			
		});

		searchPosts(jQuery(this).val());
		
		jQuery('#spt_search_field').keyup(function() {
			searchPosts(jQuery(this).val());
		});
		
		jQuery('#spt_search_field').delay(500).focus();
	});
	
});

function searchPosts(searchQuery) {
	jQuery.post(
		sptAjaxUrl,
		{
			action: 'sptReplaceSearchResults',
			search_query: searchQuery,
			current_post_id: post_id
		},
		replaceSearchResults
	);
}

function replaceSearchResults(html) {
	jQuery('#spt_search_results ul').html(html);
	jQuery('.spt_select_existing').each(function() {
		jQuery(this).unbind();
	});
	
	jQuery('.spt_select_existing').click(function() {
		var existing_post_id = jQuery(this).parent().attr('id');
		
		jQuery('#spt_search_panel').hide();
		
		var exActions = jQuery('#options_choose_existing').find('p.action');
		var loaderHTML = ' <span id="existing_loader"><img src="' + sptPluginDir + 'images/spt-loader.gif" /></span>';
	
		jQuery(exActions[0]).append(loaderHTML);
		jQuery(exActions[0]).show();
		jQuery('#existing_loader').show();
		
		jQuery.post(
			sptAjaxUrl,
			{
				action: 'sptCreateSptPost',
				master_id: post_id,
				slave_id: existing_post_id
			},
			function(resultSpt) {
				jQuery('#existing_loader').remove();

				if (resultSpt == '0') {
					jQuery(exActions[0]).append('Error, split test setup failed.');
					return;
				} else {
					jQuery(exActions[0]).append('Success!');
				}
				
				jQuery(exActions[1]).append(loaderHTML);
				jQuery(exActions[1]).show();
				jQuery('#existing_loader').show();
				
				self.parent.location.href = sptAdminUrl + 'post.php?post=' + resultSpt + '&action=edit';
			}
		);
	
	});
}

function duplicatePost() {
	
	var dupActions = jQuery('#options_duplicate_page').find('p.action');
	var loaderHTML = ' <span id="duplicate_loader"><img src="' + sptPluginDir + 'images/spt-loader.gif" /></span>';
	
	jQuery(dupActions[0]).append(loaderHTML);
	jQuery(dupActions[0]).show();
	jQuery('#duplicate_loader').show();
	
	jQuery.post(
		sptAjaxUrl,
		{
			action: 'sptDuplicateCurrentPost',
			post_id: post_id
		},
		function(resultDup) {
			jQuery('#duplicate_loader').remove();
			
			if (resultDup == '0') {
				jQuery(dupActions[0]).append('Error, duplication failed.');
				return;
			} else {
				jQuery(dupActions[0]).append('Success!');
			}
			jQuery(dupActions[1]).append(loaderHTML);
			jQuery(dupActions[1]).show();
			jQuery('#duplicate_loader').show();
			
			jQuery.post(
				sptAjaxUrl,
				{				
					action: 'sptCreateSptPost',
					master_id: post_id,
					slave_id: resultDup
				},
				function(resultSpt) {
					jQuery('#duplicate_loader').remove();

					if (resultSpt == '0') {
						jQuery(dupActions[1]).append('Error, split test setup failed.');
						return;
					} else {
						jQuery(dupActions[1]).append('Success!');
					}
					
					jQuery(dupActions[2]).append(loaderHTML);
					jQuery(dupActions[2]).show();
					jQuery('#duplicate_loader').show();
					
					self.parent.location.href = sptAdminUrl + 'post.php?post=' + resultSpt + '&action=edit';
				}
			);
		}
	);
	
	
}

function createNewPost() {
	
	var cnpActions = jQuery('#options_create_blank').find('p.action');
	var loaderHTML = ' <span id="cnp_loader"><img src="' + sptPluginDir + 'images/spt-loader.gif" /></span>';
	
	jQuery(cnpActions[0]).append(loaderHTML);
	jQuery(cnpActions[0]).show();
	jQuery('#cnp_loader').show();
	
	jQuery.post(
		sptAjaxUrl,
		{
			action: 'sptCreateNewPage',
			post_id: post_id
		},
		function(resultNew) {
			jQuery('#cnp_loader').remove();
			
			if (resultNew == '0') {
				jQuery(cnpActions[0]).append('Error, new page creation failed. ' + resultNew);
				return;
			} else {
				jQuery(cnpActions[0]).append('Success! ' + resultNew);
			}
			jQuery(cnpActions[1]).append(loaderHTML);
			jQuery(cnpActions[1]).show();
			jQuery('#cnp_loader').show();
			
			jQuery.post(
				sptAjaxUrl,
				{				
					action: 'sptCreateSptPost',
					master_id: post_id,
					slave_id: resultNew
				},
				function(resultSpt) {
					jQuery('#cnp_loader').remove();

					if (resultSpt == '0') {
						jQuery(cnpActions[1]).append('Error, split test setup failed. ' + resultSpt);
						return;
					} else {
						jQuery(cnpActions[1]).append('Success! ' + resultSpt);
					}
					
					jQuery(cnpActions[2]).append(loaderHTML);
					jQuery(cnpActions[2]).show();
					jQuery('#cnp_loader').show();
					
					self.parent.location.href = sptAdminUrl + 'post.php?post=' + resultSpt + '&action=edit';
				}
			);
		}
	);
}
