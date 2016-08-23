(function($){

	loadDataJSON = function(dataFile, debug) {
		$.ajax({
			async: false,
			dataType: "json",
			url: dataFile,
			success: function(data){
				data_info = data.info;
				data_states = data.states;
				if (debug == true) {
					var output="<ul>";
					for (var i in data.states) {
						output+="<li>" + i;
						output+="<ul>";
						for (var j in data.states[i].transitions) {
							output+="<li>path to " + data.states[i].transitions[j].to_state + " replace "+ data.states[i].transitions[j].replace + " and move " + data.states[i].transitions[j].direction + "</li>";
						}
						output+="</ul>";
						output+="</li>";
					}
					output+="</ul>";
					$("#details").html(output);	
				}

			}
		});
	};
	
	addDataState = function() {
		var newindex = data_states.length;
		data_states[newindex] = {
			'name': newindex,
			'is_start': 0,
			'is_end': 0,
			'transitions': '',
		};

		data_states[newindex].transitions = new Array();
		var newx = 60 + 100;
		var newy = 60 + 200;
		states[newindex] = paper.set();
		states[newindex].push(paper.circle(newx, newy, glob.states.radius).attr({fill: glob.states.bg_color, "stroke-width": glob.states.stroke_width, "stroke": glob.states.stroke_color}));
		states[newindex].push(paper.circle(newx, newy, glob.states.inner_radius).attr({"stroke-width": glob.states.stroke_width, "stroke": glob.states.bg_color}));
		states[newindex].push(paper.text(newx, newy, "q"+newindex).attr({"font-size": 18}));

		paper.freeTransform(states[newindex], {rotate: false, scale: false, drag: 'self'}, callback);
		paper.freeTransform(states[newindex]).attrs.state_num = newindex;
		paper.freeTransform(states[newindex]).attrs.role = data_states[newindex].role;
		//reset machine info:
		displayMachineInfo();
	}
	
	displayStates = function() {
		for (i in data_states) {
			var newx = 60 + 100 * i;
			var newy = 60 + 50 * i;
			states[i] = paper.set();
			states[i].push(paper.circle(newx, newy, glob.states.radius).attr({fill: glob.states.bg_color, "stroke-width": glob.states.stroke_width, "stroke": glob.states.stroke_color}));
			if (data_states[i].is_end == 1) {
				states[i].push(paper.circle(newx, newy, glob.states.inner_radius).attr({"stroke-width": glob.states.stroke_width, "stroke": glob.states.stroke_color}));
			} else {
				states[i].push(paper.circle(newx, newy, glob.states.inner_radius).attr({"stroke-width": glob.states.stroke_width, "stroke": glob.states.bg_color}));
			}
			var namerole = "";
			if (data_states[i].is_start == 1) {
				namerole = ">";
			}
			states[i].push(paper.text(newx, newy, namerole+"q"+i).attr({"font-size": 18}));
		}

		for (i in states) {
			paper.freeTransform(states[i], {rotate: false, scale: false, drag: 'self'}, callback);
			paper.freeTransform(states[i]).attrs.state_num = i;
//			paper.freeTransform(states[i]).attrs.role = data_states[i].role;
		}
		
	};
	callback = function(subject, events) {
		updateConnectors(subject.attrs.state_num);
		return false;
	}; 
	
	deleteConnectors = function() {
		for (i in paths) {
			for (j in paths[i]) {
				paths[i][j].remove();
			}
		}
		for (i in labels) {
			for (j in labels[i]) {
				labels[i][j].remove();
			}
		}
		paths = [];
		labels = [];
	}
	
	setupConnectors = function() {
		for (i in data_states) {
			if (typeof paths[i] == "undefined") {
				paths[i] = new Array();
			}
			if (typeof labels[i] == "undefined") {
				labels[i] = new Array();
			}
			obj1 = states[i][0];
			for (j in data_states[i].transitions) {
				var labeltext = "";
				var toState = data_states[i].transitions[j].to_state;
				obj2 = states[toState][0];
				var pathStr = createPathString(obj1, obj2);
				//alert(pathStr);
				if (typeof paths[i][toState] != "undefined") {
					//path already exists. ADD to the label:
					labeltext = labels[i][toState].attr('text') + "\n";
					labeltext += data_states[i].transitions[j].read + "/" + data_states[i].transitions[j].replace + "/" + data_states[i].transitions[j].direction;
					labels[i][toState].attr({text: labeltext});
				} else {
					//new path:
					paths[i][toState] = paper.path(pathStr);
					paths[i][toState].attr({stroke: glob.path.color, "stroke-width": glob.path.stroke_width, 'arrow-end': 'classic-wide-long'});
					paths[i][toState].toBack();

					curvefix = getLabelCoordinates(obj1,obj2);
					labeltext += data_states[i].transitions[j].read + "/" + data_states[i].transitions[j].replace + "/" + data_states[i].transitions[j].direction;
					labels[i][toState] = paper.text(curvefix.x, curvefix.y, labeltext);
					labels[i][toState].attr({"font-size": 16, "font-family": "Rambla, Helvetica, sans-serif"});
				}
				
			}
		}
	}
	
	updateConnectors = function(snum) {
		obj1 = states[snum][0];
		//object to others
		for (i in data_states[snum].transitions) {
			var toState = data_states[snum].transitions[i].to_state;
			obj2 = states[toState][0];
			var pathStr = createPathString(obj1, obj2);
			paths[snum][toState].attr({path: pathStr});

			curvefix = getLabelCoordinates(obj1,obj2);
			labels[snum][toState].attr({x: curvefix.x, y: curvefix.y});
		}
		
		//others to object
		for (j in data_states) {
			//alert(j);
			for (t in data_states[j].transitions) {
				var toState = data_states[j].transitions[t].to_state;
				if (toState == snum) {
					obj2 = states[j][0];
					var pathStr = createPathString(obj2, obj1);
					paths[j][snum].attr({path: pathStr});
					curvefix = getLabelCoordinates(obj2,obj1);
					labels[j][snum].attr({x: curvefix.x, y: curvefix.y});
				}
			}
		}
	};
	
	
	
	createPathString = function (obj1, obj2) {
		var path;
		var theta = findAngle(obj1,obj2);
		
		var correction = {
			x: glob.states.radius * Math.cos(theta),
			y: glob.states.radius * Math.sin(theta),
		};
		
		box1 = obj1.getBBox();
		box2 = obj2.getBBox();
		var x1 = box1.x + (box1.width / 2),
			y1 = box1.y + (box1.height / 2),
			x2 = box2.x + (box2.width / 2),
			y2 = box2.y + (box2.height / 2);
		
		if (obj1==obj2) {
			var path = ["M", x1.toFixed(3), (box1.y).toFixed(3), "a", "30", "20", "130", "1", "1", (glob.states.radius+2).toFixed(3), (glob.states.radius-15).toFixed(3)].join(" ");
			//a rx ry x-axis-rotation large-arc-flag sweep-flag x y
		} else {
			curvefix = findCurvePoint(obj1,obj2, glob.path.curve_angle);
			var curvex=0;
			var curvey=0;
			
			if (x2 < x1) {
				curvex = x2 + curvefix.x;
				x1 = x1 - correction.x;
				x2 = x2 + correction.x;
			} else if (x2 > x1) {
				curvex = x2 - curvefix.x;
				x1 = x1 + correction.x;
				x2 = x2 - correction.x;
			} 
			
			if (y2 < y1) {
				curvey = y2 + curvefix.y;
				y1 = y1 - correction.y;
				y2 = y2 + correction.y;
			} else if (y2 > y1) {
				curvey = y2 - curvefix.y;
				y1 = y1 + correction.y;
				y2 = y2 - correction.y;
			}
			
			var path = ["M", x1.toFixed(3), y1.toFixed(3), "Q", curvex.toFixed(3), curvey.toFixed(3), x2.toFixed(3), y2.toFixed(3)].join(" ");
		}
		//alert(path);
		return path;
	};

	getLabelCoordinates = function(obj1,obj2) {
		box1 = obj1.getBBox();
		box2 = obj2.getBBox();
		var x1 = box1.x + (box1.width / 2),
			y1 = box1.y + (box1.height / 2),
			x2 = box2.x + (box2.width / 2),
			y2 = box2.y + (box2.height / 2);
			
		var curvex=0, curvey=0;
		if (obj1==obj2) {
			curvex = box1.x + box1.width;
			curvey = box2.y - 60;
		} else {
			curvefix = findCurvePoint(obj1,obj2, 5);
			
			if (x2 < x1) {
				curvex = x2 + curvefix.x;
			} else if (x2 > x1) {
				curvex = x2 - curvefix.x;
			} 
			
			if (y2 < y1) {
				curvey = y2 + curvefix.y;
			} else if (y2 > y1) {
				curvey = y2 - curvefix.y;
			}
		}
		var curvept = {
			x: curvex,
			y: curvey
		};
		return curvept;
	};
	
	
	
	/*************************/
	/** CALCULATION METHODS **/
	/*************************/
	findCurvePoint = function(obj1, obj2, angle) {
		var theta = findAngle(obj1,obj2);
		box1 = obj1.getBBox();
		box2 = obj2.getBBox();
		var x1 = box1.x + (box1.width / 2),
			y1 = box1.y + (box1.height / 2),
			x2 = box2.x + (box2.width / 2),
			y2 = box2.y + (box2.height / 2);
			
		/** calculate curve point **/
		var h = 80;
		var dist = Math.sqrt(Math.pow((x2-x1),2) + Math.pow((y2-y1),2));
		var alpha = angle; //Math.atan(h/dist);
		var ac = h/Math.sin(alpha);
		var curvefix = {
			x: dist/2, //ac*Math.cos(alpha+theta),
			y: ac*Math.sin(alpha+theta),
		};
//		$("#details").html("alpha:"+alpha+"<br>curvefix<br>x: "+curvefix.x+"<br>y: "+curvefix.y);
		return curvefix;
	};
	
	findAngle = function(obj1, obj2) {
		box1 = obj1.getBBox();
		box2 = obj2.getBBox();

		var cx1 = box1.x + (box1.width / 2),
			cy1 = box1.y + (box1.height / 2),
			cx2 = box2.x + (box2.width / 2),
			cy2 = box2.y + (box2.height / 2);
		
		if (cx2==cx1) {
			return 0;
		} else {
			return Math.atan(Math.abs(cy2-cy1)/Math.abs(cx2-cx1));	
		}
	};
	
	/*****************/
	/** GUI METHODS **/
	/*****************/
	toggleToolBoxButtons = function(state) {
		if (state==false) {
			$("#editorSaveMachine").attr("disabled","disabled").removeClass('btn-success');
			$("#editorClearWorkspace").attr("disabled","disabled").removeClass('btn-danger');
			$("#editorAddConnector").attr("disabled","disabled").removeClass('btn-info');
			$("#editorTestStrings").attr("disabled","disabled").removeClass('btn-inverse');
						
		} else {
			$("#editorSaveMachine").removeAttr("disabled").addClass('btn-success');
			$("#editorClearWorkspace").removeAttr("disabled").addClass('btn-danger');
			$("#editorAddConnector").removeAttr("disabled").addClass('btn-info');
			$("#editorTestStrings").removeAttr("disabled").addClass('btn-inverse');
		}
	}
	
	displayTransitionTable = function() {
		var translist = "";
			translist += "<tr>";
			translist += "<th>Transition</th>";
			translist += "<th>Read</th>";
			translist += "<th>Write</th>";
			translist += "<th>Direction</th>";
			translist += "</tr>";
		for (i in data_states) {
			if (data_states[i].transitions.length > 0) {
				for (j in data_states[i].transitions) {
					translist += "<tr class='info'>";
					translist += "<td class='transrule' trans='"+j+"' myState='"+i+"' toState='"+data_states[i].transitions[j].to_state+"' myAct='to_state'>q"+i+" <i class=\"icon-arrow-right\"></i> q" + data_states[i].transitions[j].to_state + "</td>";
					translist += "<td class='transrule' trans='"+j+"' myState='"+i+"' toState='"+data_states[i].transitions[j].to_state+"' myAct='read'>" + data_states[i].transitions[j].read + "</td>";
					translist += "<td class='transrule' trans='"+j+"' myState='"+i+"' toState='"+data_states[i].transitions[j].to_state+"' myAct='replace'>" + data_states[i].transitions[j].replace + "</td>";
					translist += "<td class='transrule' trans='"+j+"' myState='"+i+"' toState='"+data_states[i].transitions[j].to_state+"' myAct='direction'>" + data_states[i].transitions[j].direction + "</td>";
					translist += "</tr>";
				}
			}
		}

		var tablehtml = "<table id='translist' class='table table-hover table-condensed' align=center width='100%' cellspacing='0' cellpadding='0'>";
			tablehtml += translist;
			tablehtml += "</table>";

		$("#transitions_list").html(tablehtml);
		$("#translist").on('click', 'td', function(event) {
			var mystate = $(this).attr("myState");
				var tostate = $(this).attr("toState");
				var transition = $(this).attr("trans");
				var act = $(this).attr("myAct");
				
				
				var opts = '';
				for (j in data_states) {
					var sel = '';
					if (j == tostate) { sel = 'selected'; }
					opts+=	"<option value='"+j+"' "+sel+">q"+j+"</option>";
				}
				$("#trans_id").val(transition)
				$("#trans_mystate").html("q"+mystate);
				$("#trans_mystate_hid").val(mystate);
				$("#trans_tostate").html(opts);
				
				$("#trans_read").val(data_states[mystate].transitions[transition].read);
				$("#trans_write").val(data_states[mystate].transitions[transition].replace);
				$('#trans_dir option[value="'+data_states[mystate].transitions[transition].direction+'"]').prop('selected', true);
				
				$("#modal_transition_edit").modal('show');
		});
			
	}
	
	clickTransRule = function(obj) {
			var mystate = obj.attr("myState");
			var tostate = obj.attr("toState");
			var transition = obj.attr("trans");
			var act = obj.attr("myAct");
			
			var opts = '';
			for (j in data_states) {
				var sel = '';
				if (j == tostate) { sel = 'selected'; }
				opts+=	"<option value='"+j+"' "+sel+">q"+j+"</option>";
			}
			$("#trans_id").val(transition)
			$("#trans_mystate").html("q"+mystate);
			$("#trans_mystate_hid").val(mystate);
			$("#trans_tostate").html(opts);
			
			$("#trans_read").val(data_states[mystate].transitions[transition].read);
			$("#trans_write").val(data_states[mystate].transitions[transition].replace);
			$('#trans_dir option[value="'+data_states[mystate].transitions[transition].direction+'"]').prop('selected', true);
			
			$("#modal_transition_edit").modal('show');
	}
	
	displayMachineInfo = function() {
		if (data_states.length > 0) {
			//get states:
			var state_start = -1;
			var opts = [];
			var chks = [];
			var seli = -1;
			var selchk = [];
			for (i in data_states) {
				var chkChecked = '';
				var disable_start = '';
				var disable_end = '';
				if (data_states[i].is_start == 1) { 
					state_start = i; 
				}
				if (data_states[i].is_end == 1) { 
					selchk.push(i); 
				}
				opts.push("<option value='"+i+"'>q"+i+"</option>"); 
				chks.push("<button type=\"button\" class=\"btn chkAccStates\" name='chkAccStates' id='chkAccState_"+i+"' value='"+i+"' myState='"+i+"'>q"+i+"</button>"); 
				//transitions:
			}
			$("#inf_start_state").html(opts.join("\n"));
//			$("#AccStatesList").html("<div class=\"btn-group\" data-toggle=\"buttons-checkbox\">" + chks.join("") + "</div>");
			$("#AccStatesList").html("<div class=\"moo-btn-group\" data-toggle=\"buttons-checkbox\">" + chks.join("") + "</div>");
			for (i in selchk) {
				$("#chkAccState_"+selchk[i]).button('toggle');
			}

			if (state_start < 0) {
				$("#inf_start_state").append("<option value='-1' selected>  </option>");
			}
			$("#inf_start_state").val(state_start);
			//transitions:
			displayTransitionTable();
/*
			$("#transitions_list").html(tablehtml);
			$(".transrule").click(function() {
				clickTransRule($(this));
			});
*/

			/** FUNCTIONS **/
			$("#inf_start_state").change(function() {
				var newstartstate = $(this).find("option:selected").attr('value');
				for (i in states) {
					if (i == newstartstate) {
						states[i][2].attr({text: ">q"+i}); 
						data_states[i].is_start = 1;
						//disable endstate:
//						$("#chkAccState_"+i).attr("disabled","disabled");
					} else {
						if (data_states[i].is_start == 1) {
							data_states[i].is_start = 0;
							states[i][2].attr({text: "q"+i}); 
						}
//						$("#chkAccState_"+i).removeAttr("disabled");
					}
				}
			});
			
			$(".chkAccStates").click(function() {
				//false is true and true is false
				var index = $(this).attr("myState");
				if ($(this).hasClass('active')) { //
					//remove as endstate
						states[index][1].attr({"stroke": glob.states.bg_color}); 
						data_states[i].is_end = 0;
				} else {
					//make an endstate
						states[index][1].attr({"stroke": glob.states.stroke_color}); 
						data_states[i].is_end = 1;
				}
			});
			

		}
	}

	getMachineDesc = function() {
		var outputArr = new Array;
		if (typeof data_info != "undefined") {
			if (typeof data_info.name != "undefined") {
				outputArr.push("<strong>Name: </strong>" + data_info.name);
			}
			if (typeof data_info.author != "undefined") {
				outputArr.push("<strong>Author: </strong>" + data_info.author);
			}
			if (typeof data_info.description != "undefined") {
				outputArr.push("<strong>Description: </strong>" + data_info.description);
			}
			if (typeof data_info.language != "undefined") {
				outputArr.push("<strong>Language (&#8721;): </strong>{" + data_info.language + "}");
			}
			if (typeof data_info.rule != "undefined") {
				outputArr.push("<strong>Expression Rule: </strong>" + data_info.rule);
			}
			return outputArr.join("<br />");
		} else {
			return false;
		}
	}
	
	resetWorkspace = function() {
		//reset arrays:
		data_states = [];
		states = [];
		paths = [];
		labels = [];
		//clear canvas:
		paper.clear();
		$("#turingTestStrings").val("");
		toggleToolBoxButtons(false);
		$("#widget_info").slideUp();
		$("#widget_test").slideUp();

		
	}

})(jQuery);
