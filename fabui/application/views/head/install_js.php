<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
?>
<script type="text/javascript">
	var selected_head = "<?php echo $head?>";
	heads = <?php echo json_encode($heads)?>;
	var official_heads_id_limit = 100;
	$(function () {
		
		$("#heads").on('change', set_head_img);
		$("#heads").trigger('change');
		$("#heads").on('click', function(){
		});
		$("#heads").on('select', function(){
			
		});
		$("#set-head").click(function() {
			set_head();
		});
		
		$('.settings-action').on('click', buttonAction);
		$('.capability').on('change', capability_change);
		$("#inputId").on('change', importHeadSettings);
		initFieldValidation();
		$("#advanced_settings_switch").on('click', clickShowHideSettings);
		
	});

	function initFieldValidation()
	{
		$("#head-settings").validate({
			rules:{
				name:{
					required:true
				},
				'capability[]': {
					required: true,
					minlength: 1
				},
				fw_id: {
					required: true
				}
			},
			messages: {
				name:{
					required: _("Please enter head name")
				},
				'capability[]':  _("Please select at least one capability")
			},
			  submitHandler: function(form) {
			},
			errorPlacement : function(error, element) {
				if(element[0].name == "capability[]")
				{
					error.insertAfter( $("#capabilities-container") );
				}
				else
					error.insertAfter(element.parent());
			}
		});
		$("#head-name").inputmask("Regex");
	}
	/**
	*
	**/
	function set_head_img(){

		
	 	selected_head = $(this).val();
	 	if(heads.hasOwnProperty(selected_head))
	 	{
			$("#edit-button").show();
			$("#remove-button").show();
			var head = heads[selected_head];
			if( head.fw_id < official_heads_id_limit )
				$("#remove-button").hide();
		}
		else
		{
			$("#edit-button").hide();
			$("#remove-button").hide();
		}
	 	
	 	$(".jumbotron").html('');
	 	
	 	$("#head_img").parent().attr('href', 'javascript:void(0);');
	 	$("#head_img").css('cursor', 'default');
	 	$("#set-head").prop("disabled",false);
	 	
		$("#head_img").attr('src', '/assets/img/head/' + $(this).val() + '.png');
		
		if($("#" + $(this).val() + "_description").length > 0){
			$(".jumbotron").html($("#" + $(this).val() + "_description").html());
		}
		
		if($(this).val() == 'more_heads'){
			$("#head_img").parent().attr('href', 'https://store.fabtotum.com?from=fabui&module=maintenance&section=head');
	 		$("#head_img").css('cursor', 'pointer');
	 		$("#set-head").prop("disabled",true);
		}
		if($(this).val() == 'head_shape'){
			$("#set-head").prop("disabled",true);
		}
	 }
	/**
	*
	**/
	function set_head(headToInstall){		
		
		headToInstall = headToInstall || $("#heads").val();
		
	 	if($("#heads").val() == 'head_shape'){
	 		alert( _("Please select a Head") );
	 		return false;
	 	}
	 	
	 	openWait('<i class="fa fa-gear fa-spin"></i> <?php echo _("Installing head"); ?>', '<?php echo _("Please wait"); ?>...');
	 	$.ajax({
			type: "POST",
			url: "<?php echo site_url("head/setHead") ?>/"+ headToInstall,
			dataType: 'json'
		}).done(function( data ) {
			$(".alerts-container").find('div:first-child').remove();
			$(".alerts-container").append( '<div class="alert alert-success animated  fadeIn" role="alert"><i class="fa fa-check"></i> ' + _("Well done! Now your <strong>FABtotum Personal Fabricator</strong> is set for the <strong>{0}</strong>.").format(data.name) + '</div>' );			

			setTimeout(function(){
					document.location.href =  '<?php echo site_url('head'); ?>?head_installed';
					location.reload();
				}, 2000);
		});
	}
	/**
	*
	**/
	function capability_change(update_working_mode)
	{
		update_working_mode = update_working_mode || true;
		var capabilities = [];
		var print = false;
		var mill = false;
		var laser = false;
		var scan = false;
		var feeder = false;
		var fourthaxis = false;
		
		$(".capability").each(function (index, value) {
			if($(this).is(":checked"))
			{
				capabilities.push($(this).attr('data-attr'));
			}
		});
		
		var working_mode = 3;
		
		if(capabilities.indexOf("print") > -1)
		{
			$(".print-settings").slideDown();
			working_mode = 1;
			print = true;
		}
		else
			$(".print-settings").slideUp();
			
		if(capabilities.indexOf("mill") > -1)
		{
			$(".mill-settings").slideDown();
			mill = true;
			if(working_mode == 1)
				working_mode = 0;
			else
				working_mode = 3;
		}
		else
			$(".mill-settings").slideUp();
			
		if(capabilities.indexOf("feeder") > -1 ){
			$(".feeder-settings").slideDown();
			feeder = true;
		}
		else{
			$(".feeder-settings").slideUp();
			feeder = false;
		}
		
		if(capabilities.indexOf("4thaxis") > -1){
			$(".4thaxis-settings").slideDown();
			fourthaxis = true;
		}
		else{
			$(".4thaxis-settings").slideUp();
			fourthaxis = false;
		}
		
		if(capabilities.indexOf("laser") > -1)
		{
			working_mode = 2;
			laser = true;
		}
			
		if(capabilities.indexOf("scan") > -1)
		{
			working_mode = 4;
			scan = false;
		}
		
		if(update_working_mode)
			$("#head-working_mode").val(working_mode);

		updateTool(working_mode, feeder, fourthaxis);


		if( $(this).is("input") )
		{

			var state = $(this).is(":checked");
			var tab_name =  $(this).attr('data-attr');
			
			if(state)
			{
				$("#"+tab_name+"-tab-button").trigger('click');
				if(capabilities.length == 1)
					$("#"+tab_name+"-tab").addClass("active");
			}
			else
			{
				$("#"+tab_name+"-tab").removeClass("active");
				if(capabilities.length > 0)
				{
					var last_idx = capabilities.length -1;
					$("#"+capabilities[last_idx]+"-tab-button").trigger('click');
					if(capabilities.length == 1)
					{
						$("#"+capabilities[last_idx]+"-tab").addClass("active");
					}
				}
			}
		}
		else // first time show scenario
		{
			var available_tabs = ['print', 'mill', 'feeder', '4thaxis'];
			
			for(var i=0; i<capabilities.length; i++)
			{
				var capability = capabilities[i];
				if(available_tabs.indexOf(capability) > -1)
				{
					$("#"+capability+"-tab-button").trigger('click');
					$("#"+capability+"-tab").addClass("active");
					break;
				}
			}
		}
	}
	/**
	*
	**/
	function buttonAction(){
		var action = $(this).attr('data-action');
		switch(action)
		{
			case "edit":
				if(heads.hasOwnProperty(selected_head))
				{
					populateHeadSettings(heads[selected_head]);
				}
				$("#advanced_settings_switch").prop('checked', false);
				showHideSettings(false);
				$('#settingsModal').modal('show');
				break;
			case "add":
				$("#advanced_settings_switch").prop('checked', true);
				showHideSettings(true);
				document.getElementById("head-settings").reset();
				showHideInputsForOfficialHeads('show');
				$("#head-fw_id").attr("min", official_heads_id_limit);
				$('#settingsModal').modal('show');
				break;
			case "remove":
				removeHeadSettings();
				break;
			case "save":
				if($("#head-settings").valid())
					saveHeadSettings(true);
				break;
			case "import":
				$("#inputId").trigger('click');
				break;
			case "export":
				if($("#head-settings").valid())
					exportHeadSettings();
				break;
			case "factory-reset":
				factoryReset(selected_head);
				break;
			case "save-install":
				if($("#head-settings").valid())
					saveHeadSettings(set_head);
		}
		
		return false;
	}
	/**
	*
	**/
	function getHeadSettings()
	{
		var capabilities = [];
		var plugins = [];
		
		var settings = {};
		
		$("#head-settings :input").each(function (index, value) {

			var name   = $(this).attr('name');
			var id     = $(this).attr('id');
			var type   = $(this).attr('type');
			var feeder = id.startsWith("feeder-");
			var fourthaxis = id.startsWith("4thaxis-");
			
			
			if( !settings.hasOwnProperty('feeder'))
			{
				settings['feeder'] = {};
			}
			if( !settings.hasOwnProperty('4thaxis'))
			{
				settings['4thaxis'] = {};
			}
			
			if(name)
			{
				if(type == 'checkbox')
				{
					if($(this).is(":checked"))
					{
						capabilities.push( $(this).attr('data-attr') );
					}
				}
				else
				{
					if(feeder) {
						settings['feeder'][name] = $(this).val();
					} else if(fourthaxis) {
						settings['4thaxis'][name] = $(this).val();
					} else {
						settings[name] = $(this).val();
					}
				}
				
				if(name == "custom_gcode")
				{
					if(feeder)
						settings['feeder'][name] = settings['feeder'][name].toUpperCase();
					else
						settings[name] = settings[name].toUpperCase();
				}
				if(name=="plugins")
				{
					if($(this).val() == ""){
						settings['plugins'] = new Array();
					}else{
						settings['plugins'] = $(this).val().split(",");
					}
				}
			}
		});
		
		settings['capabilities'] = capabilities;
		
		if( capabilities.indexOf("feeder") == -1 )
		{
			settings['feeder'] = {};
		}
		
		if( capabilities.indexOf("4thaxis") == -1 )
		{
			settings['4thaxis'] = {};
		}
		
		return settings;
	}
	
	
	function isObject(val) 
	{
		if (val === null) { return false;}
		return ( (typeof val === 'function') || (typeof val === 'object') );
	}
	
	function isArray(val)
	{
		return Array.isArray(val);
	}
	/**
	*
	**/
	function populateHeadSettings(head, isImport)
	{
		isImport = isImport || false ;
		
		document.getElementById("head-settings").reset();
		for (var key in head) {
			var value = head[key];
			// now you can use key as the key, value as the... you guessed right, value
			if(isArray(value))
			{
				if(key == "capabilities")
				{
					for(var i=0; i<value.length; i++)
					{
						var id = "#cap-" + value[i];
						$(id).prop('checked', true);
					}
				}
				if(key == "plugins")
				{
					$("#plugins").val(value.toString());
				}
			}
			else if(isObject(value))
			{
				if(key == "feeder")
				{
					for (var fkey in value)
					{
						var fvalue = value[fkey];
						var id = "#feeder-"+fkey;
						$(id).val(fvalue);
					}
				}
			}
			else
			{
				var id = "#head-"+key;
				$(id).val(value);
			}
		}
		capability_change(false);
		/**
		* only for fabtotums official heads
		*/
		if(!isImport){
			$("#head-fw_id").attr("min", 1);
			if(head.fw_id < official_heads_id_limit){
				showHideInputsForOfficialHeads('hide');
			}else{
				showHideInputsForOfficialHeads('show');
				$("#head-fw_id").attr("min", official_heads_id_limit);
			}
		}else{
			$("#head-fw_id").attr("min", official_heads_id_limit);
		}

	}
	/**
	*
	**/
	function saveHeadSettings(callback)
	{
		openWait('<i class="fa fa-save"></i> <?php echo _("Saving head settings"); ?>', '<?php echo _("Please wait"); ?>...');
		var settings = getHeadSettings();
		var filename = settings['name'].replace(/ /g, "_").replace(/-/g, "_").toLowerCase();
		$.ajax({
			type: 'post',
			url: '<?php echo site_url('head/saveHead'); ?>/' + filename,
			data : settings,
			dataType: 'json'
		}).done(function(response) {
			fabApp.showInfoAlert('<strong>{0}</strong> saved'.format(settings.name));

			setTimeout(function(){
				if($.isFunction(callback)){
					callback(filename);
				}else{
					location.reload();
				}				
			}, 1000);
		});
	}
	/**
	*
	**/
	function exportHeadSettings()
	{
		var settings = getHeadSettings();
		var filename = settings['name'].replace(/ /g, "_").replace(/-/g, "_").toLowerCase() + ".json";
		var content = JSON.stringify(settings, null, 2)
		var blob = new Blob([content], {type: "text/plain"});
		saveAs(blob, filename);
	}
	/**
	*
	**/
	function importHeadSettings(event)
	{
		var input = event.target;
		var reader = new FileReader();
		reader.onload = function(){
			var text = reader.result;
			
			content = jQuery.parseJSON(text);
			populateHeadSettings(content, true);
		}
		reader.readAsText(input.files[0]);
		return false;
	}
	/**
	*
	**/
	function removeHeadSettings()
	{
		$.SmartMessageBox({
			title: "<?php echo _("Attention");?>!",
			content: "<?php echo _("Remove <strong>{0}</strong> settings?");?>".format(heads[selected_head].name),
			buttons: '[<?php echo _("No")?>][<?php echo _("Yes")?>]'
		}, function(ButtonPressed) {
			if (ButtonPressed === "<?php echo _("Yes")?>")
			{
				$.ajax({
					type: 'post',
					url: '<?php echo site_url('head/removeHead'); ?>/' + selected_head,
					dataType: 'json'
				}).done(function(response) {
					fabApp.showInfoAlert('<strong>{0}</strong> removed'.format(heads[selected_head].name));
					setTimeout(function(){
						location.reload();
					}, 1000);
				});
			}
			if (ButtonPressed === "<?php echo _("No")?>")
			{
			}
		});
	}
	/**
	*
	**/
	function showHideInputsForOfficialHeads(action)
	{
		if(action == 'show'){
			$(".url-container").show();
			$(".description-container").show();
			$("#head-name").removeAttr("readonly")
			$("#head-fw_id").removeAttr("readonly");
			$(".factory-head-button").hide();
			$(".custom-head-button").show();
		}else if(action == 'hide'){
			$(".url-container").hide();
			$(".description-container").hide();
			$("#head-name").attr("readonly", "readonly");
			$("#head-fw_id").attr("readonly", "readonly");
			$(".factory-head-button").show();
			$(".custom-head-button").hide();
		}
	}
	/**
	*
	**/
	function factoryReset()
	{
		$.SmartMessageBox({
			title: "<?php echo _("Attention");?>!",
			content: "<?php echo _("Restore factory settings for <strong>{0}</strong> ?");?>".format(heads[selected_head].name),
			buttons: '[<?php echo _("No")?>][<?php echo _("Yes")?>]'
		}, function(ButtonPressed) {
			if (ButtonPressed === "<?php echo _("Yes")?>")
			{
				$.ajax({
					type: 'post',
					url: '<?php echo site_url('head/factoryReset'); ?>/' + selected_head,
					dataType: 'json'
				}).done(function(response) {
					fabApp.showInfoAlert('<?php echo _("Factory settings restored") ?>');
					setTimeout(function(){
						location.reload();
					}, 1000);
				});
			}
			if (ButtonPressed === "<?php echo _("No")?>")
			{
			}
		});
		
	}
	/**
	*
	**/
	function updateTool(working_mode, hasFeeder, hasFourthAxis)
	{
		var tool = '';
		switch(working_mode){
			case 0: //hybrid
			case 1: //FFF
				tool = 'M563 P0 D0';
				break;
			case 2: //laser
			case 3: //CNC
				tool = 'M563 P0 D-1';
				break;
			case 4: // Scan
				break;
		}
		if(hasFeeder){
			tool = 'M563 P2 D0';
		}else if(hasFourthAxis && working_mode == 4){
			tool = 'M563 P0 D3';
		}
		$("#tool").val(tool);
	}
	/**
	*
	*/
	function clickShowHideSettings()
	{
		showHideSettings($(this).prop('checked'));
	}
	/**
	*
	**/
	function showHideSettings(bool)
	{
		if (bool) {
			$(".advanced-settings").removeClass('advanced-settings').addClass("all-settings");
		} else {
			$(".all-settings").removeClass('all-settings').addClass("advanced-settings");
		}
	}
</script>
