(function($){

//	runCondition = function(statenum, chr) {
//	}
	
	
	/**********************/
	/** ANALYSIS METHODS **/
	/**********************/
	outputStringTable = function(strArray, statenum, charnum) {
		var html = ["<tr><td class='t_state' align='left'>q" + statenum + "</td>"].join("\n");
			html += "<td class='t_blank'>B</td>";
			var cl = "";
			var arrowimg = "<img src='img/up_arrow.png' height='16' border='0' />";
			for (var i=0; i<strArray.length; i++) {
				if (strArray[i] == "B") {
					cl = "class='t_blank'";
				} else {
					cl = "class='t_char'";
				}
				if (i == charnum) {
					cl = "class='t_currstate'";
				} 
				html += "<td " + cl + ">" + strArray[i] + "</td>";
			}
			html += "<td class='t_blank'>B</td>";
			html += ["</tr>"].join("\n");
			return html;
	};

	/*************************/
	/** RUN MACHINE METHODS **/
	/*************************/
	turingRun = function(startState, testStr) {
			var strArr = testStr.split("");
			var curr_char = 0,
				curr_state = startState; // 0, //start state
				prev_state = -1,
				html = "",
				stop = false;
			do {
				html += ["<table align=left style='clear:both;'>"].join("");
				html += outputStringTable(strArr, curr_state, curr_char);
					//what char are we on now?
				var c = strArr[curr_char];
				var found = false;
				if  (data_states[curr_state].transitions.length==0) { //end of line. 
					found = false;
					prev_state = curr_state;
				} else {
					for (i in data_states[curr_state].transitions) {
						if (c == data_states[curr_state].transitions[i].read) {
							found = true;
							//replace:
							strArr[curr_char] = data_states[curr_state].transitions[i].replace;
							//move to proper direction:
							var dir = data_states[curr_state].transitions[i].direction;
							if (dir == "L") {
								if (curr_char == 0) { //we're already leftmost
									strArr.unshift("B");
								} else {
									curr_char = curr_char - 1;
								}
							} else {
								if (curr_char == (strArr.length-1)) { //we're already rightmost
									strArr.push("B");
								} else {
									curr_char = curr_char + 1;
								}
							}
							//move to proper state:
							prev_state = curr_state;
							curr_state = data_states[curr_state].transitions[i].to_state;
							break;
						}
					}
				}
				
				if (found == false) {
					if (data_states[prev_state].is_end == 1) { //ended on a final state
//						alert("prev state: "+prev_state);
						html += ["<tr><td class='t_accepted' colspan='"+(strArr.length+10)+"'>ACCEPTED</td></tr>"];
					} else {
//						alert("prev state: "+prev_state);
						html += ["<tr><td class='t_rejected' colspan='"+(strArr.length+10)+"'>REJECTED</td></tr>"];
					}
					stop = true;
					html += ["</table>"].join("");
				} else {
					//found. check if it's the final state:
					if (data_states[prev_state].is_end == 1) {
						html += ["<tr><td class='t_accepted' colspan='"+(strArr.length+10)+"'>ACCEPTED</td></tr>"];
						stop = true;
						html += ["</table>"].join("");
						break;
					} //otherwise keep going.
				}

				html += ["</table>"].join("");
			} while (stop == false);

		return html;
	}
	
})(jQuery);
