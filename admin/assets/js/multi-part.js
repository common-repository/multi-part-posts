(function ( $ ) {
	"use strict";

	$(function () {
		var multi_config_container = $("#multi_part_configuration")

		// The section below is a fix put in place for chosen's rendering
		// See https://github.com/harvesthq/chosen/issues/92
		// When fixed+updated, these conditionals can be removed

		if (multi_config_container.is(':hidden'))
			multi_config_container.show().addClass('chosen-render-fix');

		multi_config_container.find('#multi_part_post_select').chosen({
			placeholder_text_single: "Select a Post"
		});

		if (multi_config_container.hasClass('chosen-render-fix'))
			multi_config_container.hide().removeClass('chosen-render-fix');

		// End chosen fix

		// jQuery UI sortable with update callback to update the hidden field
		$("#multi_part_list").sortable({
			update: function(event,ui) {
				var jsonValue = [];
				$("#multi_part_list").find('li').each(function(){
					jsonValue.push(parseInt($(this).attr('data-id')));
				});
				$("#multi_part_data").val(JSON.stringify(jsonValue));
			}
		});
	});

	$(document)

		.on('click','#multi-add',function(e){
			e.preventDefault();

			var $select = $("#multi_part_post_select"),
				$selected = $select.find(":selected"),
				jsonValue = $.parseJSON($("#multi_part_data").val());

			if ($selected.attr('data-group') != "") {
				// There is already a group, let's handle
				var $confirmSection = $("#multi_part_confirm");

				$confirmSection.find('.multi_part_confirm_name').html($selected.attr('data-title'));

				$("#multi-add").fadeOut(200,function(){
					$confirmSection.fadeIn(200)
						.on('click.temp','.yes',function(e){
							e.preventDefault();

							var postArray = $.parseJSON($selected.attr('data-group'));

							for(var i=0;i<postArray.length;i++) {
								// Create a new list item
								var $selectOption = $select.find("option[value='"+postArray[i]+"']  ");

								if ($selectOption.length > 0) {
									$('<li data-id="'+$selectOption.attr('value')+'">'+$selectOption.attr('data-title')+' <a href="#" class="dashicons dashicons-no-alt multi-remove"></a></li></li>').appendTo("#multi_part_list");

									// Update the input value
									jsonValue.push(parseInt($selectOption.attr('value')));
									$selectOption.prop('disabled',true);	
								}								
							}

							// Update
							$("#multi_part_data").val(JSON.stringify(jsonValue));

							// Trigger for chosen
							$selected.prop('selected',false);
							$select.trigger('chosen:updated');

							$confirmSection.fadeOut(200,function(){
								$("#multi-add").fadeIn(200);
							}).off('.temp');
						})
						.on('click.temp','.no',function(e){
							e.preventDefault();

							$confirmSection.fadeOut(200,function(){
								$("#multi-add").fadeIn(200);
							}).off('.temp');
						});
				});
			}
			else {
				// Create a new list item
				$('<li data-id="'+$selected.attr('value')+'">'+$selected.attr('data-title')+' <a href="#" class="dashicons dashicons-no-alt multi-remove"></a></li></li>').appendTo("#multi_part_list");

				// Update the input value
				jsonValue.push(parseInt($selected.attr('value')));

				$selected.prop('disabled',true);

				// Update
				$("#multi_part_data").val(JSON.stringify(jsonValue));
				$selected.prop('selected',false);
				// Trigger for chosen
				$select.trigger('chosen:updated');
			}

			
			
		})

		.on('click','#multi_part_list .multi-remove',function(e){
			e.preventDefault();

			var $selectedPost = $(this).closest('li'),
				$list = $("#multi_part_list"),
				$select = $("#multi_part_post_select"),
				$primaryInput = $("#multi_part_data"),
				jsonValue = $.parseJSON($primaryInput.val());

			// First check if this is for the primary post 
			// as different logic will apply
			// 
			// If it's the primary post, we're going to group the remaining posts
			// together in a phantom input, remove the list items and throw them
			// back into the select with the proper group json
			// This can be found in the enable multi part change event handler below
			//
			if ($selectedPost.attr('data-id') == $("#multi_part_list").attr('data-main-post-id')) {
				$("#enable_multi_part").prop('checked',false).trigger('change');
			}
			else {
				jsonValue = _.without(jsonValue,parseInt($selectedPost.attr('data-id')));
				$("option[value='"+$selectedPost.attr('data-id')+"']",$select).prop('disabled',false).attr('data-group','');
				
				$selectedPost.remove();
				$select.trigger('chosen:updated');
				$primaryInput.val(JSON.stringify(jsonValue));
			}		

		})

		.on('change','#enable_multi_part',function(){
			$("#multi_part_configuration").toggle();

			var $primaryInput = $("#multi_part_data"),
				$list = $("#multi_part_list"),
				$select = $("#multi_part_post_select"),
				jsonValue = $.parseJSON($primaryInput.val()),
				mainPostId = parseInt($("#multi_part_list").attr('data-main-post-id'));

			if ($(this).is(':checked')) {

				jsonValue.push(mainPostId);
				$primaryInput.val(JSON.stringify(jsonValue));

			}
			else {

				if (jsonValue.length > 1) {
					var numberOfInputs = parseInt($("#multi_part_configuration").attr('data-inputs')),
						newJsonValue = _.without(jsonValue,mainPostId);

					$('<input type="hidden" name="multi_part_data_'+numberOfInputs+'" id="multi_part_data_'+numberOfInputs+'" value="'+JSON.stringify(newJsonValue)+'">').insertBefore($primaryInput);

					for (var i=0;i<newJsonValue.length;i++) {
						$("li[data-id='"+newJsonValue[i]+"']",$list).remove();

						$("option[value='"+newJsonValue[i]+"']",$select).prop('disabled',false).attr('data-group',JSON.stringify(newJsonValue));
					}

					$select.trigger('chosen:updated');
					$("#multi_part_configuration").attr('data-inputs',numberOfInputs+1);

				}
				else {

				}

				jsonValue = [];

				$primaryInput.val(JSON.stringify(jsonValue));
				
			}


		});

}(jQuery));