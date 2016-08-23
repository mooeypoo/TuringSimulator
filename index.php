<?php
	include_once("inc.const.php");
	session_start();
?>
<html lang="en">
<head>
	<title>SmarterThanThat > Turing Machine Simulator</title>
	<meta charset="ISO-8859-1" />
	<link href="css/bootstrap.min.css" rel="stylesheet" media="screen">
	<link href="css/moo.turing.css" rel="stylesheet" media="screen">

	<script src="js/jquery.min.1.8.3.js" type="text/javascript"></script>
	
	<script src="js/bootstrap.min.js" type="text/javascript"></script>
	
	<script src="js/raphael-min.js" type="text/javascript" charset="utf-8"></script>
	<script src="js/raphael.free_transform.js" type="text/javascript" charset="utf-8"></script>

	<script src="js/moo.turing.js" type="text/javascript" charset="utf-8"></script>
	<script src="js/moo.turing.operation.js" type="text/javascript" charset="utf-8"></script>
	<script src="js/json2.js" type="text/javascript" charset="utf-8"></script>

	
	<link href='http://fonts.googleapis.com/css?family=Rambla:400,700,400italic,700italic&subset=latin,latin-ext' rel='stylesheet' type='text/css'>
<script>
/** SET UP DEFAULTS **/
	var glob = {
		workspace: {
			width: "800",
			height: "600",
		},
		states: {
			radius: 35,
			inner_radius: 30,
			bg_color: "#FFFFA3",
			stroke_width: 2,
			stroke_color: "#000000",
		},
		path: {
			color: "#666",
			stroke_width: 2,
			curve_dist: 80,
			curve_angle: 30,
		},
	};
	var userSettingsModal;
	
	var data_states = new Array();
	var states = new Array();
	var paths = new Array();
	var labels = new Array();
	var paper;


$(document).ready(function() {

	paper = Raphael("holder", 900,700);
	toggleToolBoxButtons(false);

//	$("svg").css("z-index","-999");
	$("#editorClearWorkspace").click(function() {
		var ans = false;
		//warn if there's already something loaded:
		if (data_states.length > 0) {
			var ans = confirm("This will delete your current machine and all data related to it. Are you sure?");
		} else {
			ans = true;
		}
		if (ans == true) {
			resetWorkspace();
		}

	});
	
	$("#butSampleMachine").click(function() {
		var ans = false;
		//warn if there's already something loaded:
		if (data_states.length > 0) {
			ans = confirm("This will delete your current machine and all data related to it. Are you sure?");
			if (ans==false) {
				return false;
			}
		}
		$("#progbar_main").css('width', '10');
		$("#modal_loading").modal('show');
		
			resetWorkspace();
		$("#progbar_main").css('width', '20');
			var filename = "json_sample_" + $("#selSampleMachine").val() + ".php";
			loadDataJSON(filename, false);
		$("#progbar_main").css('width', '50');
			displayStates();
		$("#progbar_main").css('width', '60');
			setupConnectors();			
		$("#progbar_main").css('width', '70');
			displayMachineInfo();
		$("#progbar_main").css('width', '80');
			$("#widget_info").slideDown();
			$("#widget_test").slideDown();

		$("#progbar_main").css('width', '90');
			toggleToolBoxButtons(true);
		$("#progbar_main").css('width', '100');
		$("#modal_loading").modal('hide');
	});
	
	$("#butloadRawJSON").click(function() {
		//warn if there's already something loaded:
		if (data_states.length > 0) {
			var ans = false;
			ans = confirm("This will delete your current machine and all data related to it. Are you sure?");
			if (ans == false) {
				return false;
			}
		}
		resetWorkspace();
		var jstr = $("#loadRawJSON").val();
//		alert(jstr);
		var data;
		try {
			var data = jQuery.parseJSON(jstr);
		} catch(e) {
			alert("Couldn't parse JSON data.");
			return false;
		}

		data_info = data.info;
		data_states = data.states;
		displayStates();
		setupConnectors();			
		displayMachineInfo();
		$("#widget_info").slideDown();
		$("#widget_test").slideDown();

			toggleToolBoxButtons(false);
		
	});
	
	$("#editorSaveMachine").click(function() {
		var jsonarr = [{'info': [], 'states': data_states}];
		var turingJsonStr = JSON.stringify(jsonarr);
		turingJsonStr = turingJsonStr.substr(1, turingJsonStr.length-2);
		$("#iframeSaveFile").attr('src','filesaver.php?d='+turingJsonStr);
//		alert($("#iframeSaveFile").html);
//		alert(turingJsonStr);
	});
	
	$("#editorAddState").click(function() {
		addDataState();
		$("#widget_info").slideDown();
		$("#widget_test").slideDown();

		if (data_states.length > 1) {
			toggleToolBoxButtons(true);
		}
	});

	
	$("#editorAddConnector").click(function() {
		var opts = '';
		for (j in data_states) {
			opts += '<option value="'+j+'">q'+j+'</option>';
		}
		$("#atrans_mystate").html(opts);
		$("#atrans_tostate").html(opts);
		$("#atrans_read").val('');
		$("#atrans_write").val('');
		$("#modal_transition_add").modal("show");
	});
	
	$("#atrans_add").click(function() {
		//get form data:
		var t_fromstate = $("#atrans_mystate").val();
		var t_tostate = $("#atrans_tostate").val();
		var t_read = $("#atrans_read").val();
		var t_write = $("#atrans_write").val();
		var t_dir = $("#atrans_dir").val();
		if ((t_read.length == 0) || (t_write.length == 0)) {
			alert("Read and replace values cannot be blank. If you want to use 'blank' value, use 'B'.");
			return false;
			$("#modal_transition_add").modal("hide");
		}
		//add to array:
		var t_transid = data_states[t_fromstate].transitions.length;
		data_states[t_fromstate].transitions.push({
			'to_state': t_tostate,
			'read': t_read,
			'replace': t_write,
			'direction': t_dir,
		});
		
		/** refresh all paths **/
		deleteConnectors();
		setupConnectors();
		displayTransitionTable();

		$("#modal_transition_add").modal("hide");
	});
		

	
	
	$("#trans_save").click(function() {
		//get form data:
		var t_mystate = $("#trans_mystate_hid").val();
		var t_transid = $("#trans_id").val();
		var t_tostate = $("#trans_tostate").val();
		var t_read = $("#trans_read").val();
		var t_write = $("#trans_write").val();
		var t_dir = $("#trans_dir").val();
		if ((t_read.length == 0) || (t_write.length == 0)) {
			alert("Read and replace values cannot be blank. If you want to use 'blank' value, use 'B'.");
			return false;
			$("#modal_transition_edit").modal("hide");
		}
		//update arrays:
		data_states[t_mystate].transitions[t_transid].to_state = t_tostate;
		data_states[t_mystate].transitions[t_transid].read = t_read;
		data_states[t_mystate].transitions[t_transid].replace = t_write;
		data_states[t_mystate].transitions[t_transid].direction = t_dir;
		//redo label for the specific path:
		var labeltext = '';
		labels[t_mystate][t_tostate].attr({text: ''});
		for (j in data_states[t_mystate].transitions) {
			//path already exists. ADD to the label:
			if (data_states[t_mystate].transitions[j].to_state == t_tostate) {
				labeltext = labels[t_mystate][t_tostate].attr('text') + "\n";
				labeltext += data_states[t_mystate].transitions[j].read + "/" + data_states[t_mystate].transitions[j].replace + "/" + data_states[t_mystate].transitions[j].direction;
				labels[t_mystate][t_tostate].attr({text: labeltext});
			}
		}
		//redo to the table:
		var tablehtml = displayTransitionTable();
		$("#transitions_list").html(tablehtml);
		$("#modal_transition_edit").modal("hide");
	});
	
	$("#turing_string_run").click(function() {
		var rawStrings = $("#turingTestStrings").val();
		var arrStrings = rawStrings.split('\n');
		var startState = $("#inf_start_state").val();
		var tabhtml = '<ul class="nav nav-tabs" id="turingResultsTabs">';
		var html = '<div class="tab-content">';
		var active = 'active';
		for (var i=0; i<arrStrings.length; i++) {
			var str = arrStrings[i];
			tabhtml += '<li><a href= "#res_'+i+'" '+active+' data-toggle="tab">'+str+'</a></li>';
			html += '<div class="tab-pane '+active+'" id="res_'+i+'">';
			html += turingRun(startState,str);
			html += "</div>";
			active='';
		}
		tabhtml += '</ul>';
		html+= '</div>';
		$("#turing_results_body").html(tabhtml + html);
		$( '#turingResultsTabs a' ).click(function (e) {
		  e.preventDefault();
		  $(this).tab('show');
		})
		$("#turingResultsTabs a:first").tab('show');
		$("#modal_turing_string_results").modal("show");
	});
	
	
	
});
</script>

</head>
<body>

    <div class="navbar navbar-inverse">
      <div class="navbar-inner">
        <div class="container">
          <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </a>
          <a class="brand" href="#">Turing Machine</a>
          <div class="nav-collapse collapse">
            <ul class="nav">
              <li class="active"><a href="#">Home</a></li>
              <li><a href="#about">About</a></li>
              <li><a href="#contact">Contact</a></li>
            </ul>
<?php /*
			<form class="navbar-form pull-right">
              <input class="span2" type="text" placeholder="Email">
              <input class="span2" type="password" placeholder="Password">
              <button type="submit" class="btn">Sign in</button>
            </form>
	*/ ?>
          </div><!--/.nav-collapse -->
        </div>
      </div>
    </div>
	
	
    <div class="container-fluid">
      <div class="row-fluid">
        <div class="span9"> <!-- main screen -->	
          <div class="hero-unit">
            <h1>Turing Machine Simulator <br /><small>By Moriel Schottlender</small></h1>
          </div>
		  
		  <div id="toolbox" name="toolbox" class="nav">
			<button name="editorSaveMachine" id="editorSaveMachine" class="btn btn-success btn-small" disabled='disabled'><i class="icon-share-alt icon-white"></i> Save Machine</button> 
			<button name="editorClearWorkspace" id="editorClearWorkspace" class="btn btn-danger btn-small" disabled='disabled'><i class="icon-off icon-white"></i> Clear Workspace</button> 
			<i class="icon-chevron-right icon-white"></i>
			<button name="editorAddState" id="editorAddState" class="btn btn-info btn-small"><i class="icon-plus-sign icon-white"></i> Add State</button> 
			<button name="editorAddConnector" id="editorAddConnector" class="btn btn-info btn-small" disabled='disabled'><i class="icon-random icon-white"></i> Add Connector</button>
			<i class="icon-chevron-right icon-white"></i>
<?php /*
			<button name="editorTestStrings" id="editorTestStrings" class="btn btn-inverse btn-small" disabled='disabled'><i class="icon-play icon-white"></i> Test Strings</button>
*/ ?>
		  </div>
		  
		  <div id="holder"></div>

		</div> <!-- span9 main screen -->
        <div class="span3"> <!-- sidebar -->
			<h3>Operations</h3>

			<div class="accordion moo-sidenav" id="accordion2">
			  <div class="accordion-group">
				<div class="accordion-heading">
				  <a class="accordion-toggle" data-toggle="collapse" data-parent= "#accordion2"  href= "#collapseOne" >
					Load Samples
				  </a>
				</div>
				<div id="collapseOne" class="accordion-body collapse in">
				  <div class="accordion-inner">
					<select id="selSampleMachine" name="selSampleMachine">
						<option value="1">Example #1</option>
					</select>
					<button name="butSampleMachine" id="butSampleMachine" class="btn btn-info">Load Sample</button>
				  </div>
				</div>
			  </div>
			  <div class="accordion-group">
				<div class="accordion-heading">
				  <a class="accordion-toggle" data-toggle="collapse" data-parent= "#accordion2"  href= "#collapseTwo" >
					Load Machine
				  </a>
				</div>
				<div id="collapseTwo" class="accordion-body collapse">
				  <div class="accordion-inner">
				  Paste your JSON code here:
				  <textarea name='loadRawJSON' id='loadRawJSON' placeholder="JSON Data"></textarea>
					<button name="butloadRawJSON" id="butloadRawJSON" class="btn btn-info">Load Machine</button>
<?php /*
*/ ?>
				  </div>
				</div>
			  </div>
			</div>		
		
		<div id="widget_test" style="display: none;">
			<h3>Machine Test</h3>
			<div class="well">
				<span class="help-block">Choose a string to test. Use 'B' for empty/blank character. Separate strings on separate lines.</span>
				<textarea name='turingTestStrings' id='turingTestStrings' ></textarea>
				<button class="btn btn-inverse" name="turing_string_run" id="turing_string_run"><i class="icon-play icon-white"></i>Run Strings</button>
			</div>
		</div>
		
		<div id="widget_info" style="display: none;">
		<h3>Machine Info</h3>
			<div class="well">
				<h4>States</h4>
					Starting State:
							<select id="inf_start_state">
							</select><br />
							Ending / Accepting States:
							<div id="AccStatesList">
							</div>
				<hr>
				<h4>Transitions</h4>
				<div id="transitions_list"></div>
			</div>
		</div> <!-- /widget_info -->
		
        </div><!--end sidebar-->

	  </div> <!-- row-fluid -->
	</div> <!-- container-fluid -->

<!-- MODAL LOADING -->
<div id="modal_loading" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h3 id="myModalLabel">Loading...</h3>
  </div>
  <div class="modal-body">
		<div class="progress progress-striped active">
		  <div id='progbar_main' name='progbar_main' class="bar" style="width: 60%;"></div>
		</div>
  </div>
</div>		
		
<!-- END MODAL LOADING -->

<!-- MODAL EDIT TRANSITION -->
<div id="modal_transition_edit" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h3 id="myModalLabel">Edit Transition</h3>
  </div>
  <div class="modal-body">
	<p>Note: For a transition that includes an 'empty' character, use 'B'</p>
		<input type="hidden" name='trans_mystate_hid' id='trans_mystate_hid' value='' />
		<input type="hidden" name='trans_id' id='trans_id' value='' />		
		<table border='0' cellspacing='0' cellpadding='4'><tr>
			<th>State</th>
			<th>To State</th>
			<th>Read</th>
			<th>Write</th>
			<th>Direction</th>
		</tr>
		<tr>
			<td id='trans_mystate'></td>
			<td><select style='width: 80px' id='trans_tostate' name='trans_tostate'></select></td>
			<td><input type='text' style='width: 50px' name='trans_read' id='trans_read' value='"+data_states[mystate].transitions[transition].read+"' maxlength='1' /></td>
			<td><input type='text' style='width: 50px' name='trans_write' id='trans_write' value='"+data_states[mystate].transitions[transition].replace+"' maxlength='1' /></td>
			<td>
				<select name='trans_dir' style='width: 80px' id='trans_dir'>
					<option value='L' selected>L</option>
					<option value='R'>R</option>
				</select>
			</td>
		</tr>
		</table>

  </div>
  <div class="modal-footer">
    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
    <button class="btn btn-primary" name="trans_save" id="trans_save">Save Transition</button>
  </div>
</div>		
		
<!-- END MODAL EDIT TRANSITION -->

<!-- MODAL ADD TRANSITION -->
<div id="modal_transition_add" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h3 id="myModalLabel">Add Transition</h3>
  </div>
  <div class="modal-body">
	<p>Note: For a transition that includes an 'empty' character, use 'B'</p>
		<table border='0' cellspacing='0' cellpadding='4'><tr>
			<th>State</th>
			<th>To State</th>
			<th>Read</th>
			<th>Write</th>
			<th>Direction</th>
		</tr>
		<tr>
			<td><select style='width: 80px' id='atrans_mystate' name='atrans_mystate'></select></td>
			<td><select style='width: 80px' id='atrans_tostate' name='atrans_tostate'></select></td>
			<td><input type='text' style='width: 50px' name='atrans_read' id='atrans_read' value='' maxlength='1' /></td>
			<td><input type='text' style='width: 50px' name='atrans_write' id='atrans_write' value='' maxlength='1' /></td>
			<td>
				<select style='width: 80px' id='atrans_dir' name='atrans_dir'>
					<option value='L' selected>L</option>
					<option value='R'>R</option>
				</select>
			</td>
		</tr>
		</table>

  </div>
  <div class="modal-footer">
    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
    <button class="btn btn-primary" name="atrans_add" id="atrans_add">Add Transition</button>
  </div>
</div>		
		
<!-- END MODAL ADD TRANSITION -->

<!-- MODAL STRING TEXT RESULTS -->
<div id="modal_turing_string_results" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h3 id="myModalLabel">Machine Test Results</h3>
  </div>
  <div class="modal-body">
	<div id='turing_results_body'></div>
  </div>
  <div class="modal-footer">
    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
  </div>
</div>		
		
<!-- END MODAL STRING TEXT RESULTS -->


<iframe id="iframeSaveFile" src="" style="display:none; visibility:hidden;"></iframe>

</body>
</html>