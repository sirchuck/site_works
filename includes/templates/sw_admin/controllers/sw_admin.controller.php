<?PHP
if ( ! defined('SITEWORKS_DOCUMENT_ROOT')) exit('No direct script access allowed');

class sw_admin_controller extends _s
{
    public function sw_admin(){

// Normally you'd put this in the _js area of your dev folder. 
// But the entire sw_admin modual is completely optional and
// I did not want to make you hunt down files to remove.
// Just remove the self-contained sw_admin modual to get rid of this.
$this->_out['css'][] = '<style>
	#siteworks_language_modify_div{display: none;}
	button{cursor:pointer;}
</style>';
$this->_out['js'][] = '<script>
function postAjax(file,params,callback) {
	file = \'' . $this->_uri->base_url . '\' + file;
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() { if (this.readyState == 4 && this.status == 200) { callback( JSON.parse(this.responseText) ); } };
    xhttp.open("POST", file, true);
	xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.send(params);
}
function attach_click(id,f){
    var x = document.getElementById(id);
    if (x.addEventListener) { x.addEventListener("click", function(event){event.preventDefault();f();}); } else if (x.attachEvent) { x.attachEvent("onclick", function(event){event.preventDefault();f();}); }
}
function siteworks_language_fill(d){
	document.getElementById("siteworks_language_modify_div").style.display = "block";
	document.getElementById("siteworks_language_modify_div").setAttribute("data-key", d.k);
	document.getElementById("siteworks_language_modify_div").setAttribute("data-lang", d.l);
	document.getElementsByClassName("siteworks_langage_selected_lang")[0].innerHTML = d.l.toUpperCase();
	document.getElementsByClassName("siteworks_langage_selected_lang")[1].innerHTML = d.l.toUpperCase();

	if(d.p == 1){
		document.getElementById("siteworks_language_keep_select").value = 1;
	} else {
		document.getElementById("siteworks_language_keep_select").value = 0;
	}
	document.getElementById("siteworks_language_origional").innerHTML = d.o;
	document.getElementById("siteworks_language_english").innerHTML = d.e;
	document.getElementById("siteworks_language_category").value = d.c;
	document.getElementById("siteworks_language_lang").value = d.t;
}
function siteworks_language_options_button_function(){
	document.getElementById("siteworks_language_modify_button").innerHTML = "Modify Text";
	var d = postAjax(\'/ajax_sw_admin/get_text/\' + document.getElementById("siteworks_language_text_options").options[document.getElementById("siteworks_language_text_options").selectedIndex].value + \'/\' + document.getElementById("select_language_options").options[document.getElementById("select_language_options").selectedIndex].value,"",siteworks_language_fill);
}
function siteworks_language_options_button_function2(){
	document.getElementById("siteworks_language_modify_div").style.display = "block";
	document.getElementById("siteworks_language_modify_div").setAttribute("data-key", 0);
	document.getElementById("siteworks_language_modify_div").setAttribute("data-lang", \'english\');
	document.getElementsByClassName("siteworks_langage_selected_lang")[0].innerHTML = \'English\';
	document.getElementsByClassName("siteworks_langage_selected_lang")[1].innerHTML = \'English\';
	document.getElementById("siteworks_language_keep_select").value = 0;
	document.getElementById("siteworks_language_origional").innerHTML = "";
	document.getElementById("siteworks_language_english").innerHTML = "";

	document.getElementById("siteworks_language_category").value = "";
	document.getElementById("siteworks_language_lang").value = "";
	document.getElementById("siteworks_language_modify_button").innerHTML = "Add Text";
}
function siteworks_language_delete_button_function(){ siteworks_language_modify_text(1); document.getElementById("siteworks_language_modify_div").style.display = "none";
	var sel=document.getElementById("siteworks_language_text_options");
	for (var i=0; i<sel.length; i++){if (sel.options[i].value == document.getElementById("siteworks_language_modify_div").getAttribute("data-key") ){sel.remove(i);}}
}
function siteworks_language_modify_button_function(){ siteworks_language_modify_text(0); document.getElementById("siteworks_language_modify_div").style.display = "none"; }
function siteworks_language_modify_text(tmp){
	var p = "delete=" + tmp +
		"&key=" + document.getElementById("siteworks_language_modify_div").getAttribute("data-key") + 
		"&lang=" + document.getElementById("siteworks_language_modify_div").getAttribute("data-lang") + 
		"&keep=" + document.getElementById("siteworks_language_keep_select").options[document.getElementById("siteworks_language_keep_select").selectedIndex].value + 
		"&category=" + document.getElementById("siteworks_language_category").value + 
		"&nlang=" + document.getElementById("siteworks_language_lang").value;
	var d = postAjax(\'/ajax_sw_admin/set_text/\',p,siteworks_language_modify_return);
}
function siteworks_language_modify_return(d){ if(d.v == 1){ alert("Item Modified"); }else{ alert("Error trying to modify language database."); } }

function siteworks_delete_all_marked_button_function(){
	if (confirm("Are you sure you want to remove all text marked for deletion?")) {
		var d = postAjax(\'/ajax_sw_admin/delete_marked/\',"",siteworks_delete_all_marked_complete); 
	}
}
function siteworks_delete_all_marked_complete(d){ alert("All Marked Text Removed");location.reload(); }

function siteworks_build_code_button_function(){
	var d = postAjax(\'/ajax_sw_admin/build_sample/\',"&p=" + document.getElementById("siteworks_build_code_select").value,siteworks_build_code_button_function_reply);
}
function siteworks_build_code_button_function_reply(d){ document.getElementById("siteworks_build_code_reply").innerHTML = d.v; }

window.addEventListener(\'load\', function(){
    attach_click(\'siteworks_language_options_button\',siteworks_language_options_button_function);
    attach_click(\'siteworks_language_options_button2\',siteworks_language_options_button_function2);
    attach_click(\'siteworks_language_delete_button\',siteworks_language_delete_button_function);
    attach_click(\'siteworks_language_modify_button\',siteworks_language_modify_button_function);
    attach_click(\'siteworks_delete_all_marked_button\',siteworks_delete_all_marked_button_function);
    attach_click(\'siteworks_build_code_button\',siteworks_build_code_button_function);

});
</script>
';
		
		$this->_p['language_options'] = '';
		$this->_p['language_text_opitons'] = '';
		$r = new SiteWorks\t_site_works_lang(null,$this->_odb);
		foreach( $r->f as $k=>$v ){	if(substr($k, 0,3) != 'sw_'){ $this->_p['language_options'] .= '<option value="' . $k . '">' . $k . '</option>'; } }

		$result = $r->selectAll('sw_lang_key>0 ORDER BY sw_lang_category, sw_lang_keep DESC','sw_lang_key, sw_lang_keep, sw_lang_category, SUBSTRING(sw_origional,1,50) as sw_lang');
		while($row=$r->getRows( $result )){ $this->_p['language_text_opitons'] .= '<option value="' . $row->sw_lang_key . '">' . $row->sw_lang_keep . ': ' . (($row->sw_lang_category)? '(' . $row->sw_lang_category . ') ':'') . $row->sw_lang . '</option>'; }


		$this->_p['db_tables']='';
		foreach($this->_dbo as $k => $c){
			$result = $c->q('SHOW TABLES;');
			while ($row = $result->fetch_row()) { $this->_p['db_tables'] .= '<option value="t_' . $row[0] . '">t_' . $row[0] . '</option>'; }
			$c->freeResult($result);
		}

		$this->load_view();
    }

}
?>