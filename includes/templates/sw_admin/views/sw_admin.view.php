<?php
$this->_out['body'][] = '
<div>SiteWorks Administration <a href="' . $this->_uri->base_url . '">Return Home</a></div>
<br>
<div>Options:</div>
<div>
	1) Manage SiteWorks Language<br>
	<form method="post">
		<select id="siteworks_language_text_options">' . $this->_p['language_text_opitons'] . '</select> 
		<select id="select_language_options">' . $this->_p['language_options'] . '</select>
		<button id="siteworks_language_options_button">Manage Text</button>
		<button id="siteworks_language_options_button2">Add Text/Option</button>
	</form>
</div>
<div id="siteworks_language_modify_div" data-key="0" data-lang="" style="background-color: #FAFAFA; border: 1px solid #000; padding: 20px;">
	<form method="post">
	You are working on the <span style="color:red;font-weight: bold;" class="siteworks_langage_selected_lang"></span> text:<br>
	( If you provide a category, the system will automatically keep your text option list item.)<br>
	<select id="siteworks_language_keep_select" style="background-color:#FFFEC7;"><option value="0">Let system decide to keep text or not</option><option value="1">Force system to keep this text</option></select>
	&nbsp;&nbsp;&nbsp;&nbsp;Category: <input type="text" style="background-color:#FFFEC7;" id="siteworks_language_category" placeholder="Option_Category"/>
	<br><br>
	<div style="display: block; clear: both; overflow: hidden;">
		<div style="float: left; width: 400px;">
			Origional Developer Text:<br>
			<div id="siteworks_language_origional" style="border: 1px solid #2A9ABF; padding: 5px; overflow-y: scroll; height: 150px;"></div>
			<br>English:<br>
			<div id="siteworks_language_english" style="border: 1px solid #2A9ABF; padding: 5px; overflow-y: scroll; height: 150px;"></div>
		</div>
		<div style="float: left; width: 20px;">&nbsp;</div>
		<div style="float: left; width: 500px;">
			Your <span style="color:red;font-weight: bold;" class="siteworks_langage_selected_lang"></span> language text goes here:<br>
			<textarea id="siteworks_language_lang"  style="padding: 5px; width: 500px; height: 250px;"></textarea>
			<br><br>
			<div>
				<button id="siteworks_language_delete_button" style="float: right; margin-right: 20px;">Delete</button>
				<button id="siteworks_language_modify_button" style="float: right; margin-right: 20px;">Modify/Add</button>
			</div>
		</div>
	</div>
	</form>
</div>
<br>
<div>
	2) Remove All Text Marked for Deletion in above pulldown.<br>
	0: The system may mark for deletion if not found in developers text.<br>
	1: means force keep<br>
	3: means marked for deletion<br>
	<form method=post><button style="background-color:#FFBDBD;" id="siteworks_delete_all_marked_button">Delete All 3:Marked for Deletion</button></form>
</div>
<br>
<div>
	<form method="post">3) <select id="siteworks_build_code_select" style="padding: 4px;">' . $this->_p['db_tables'] . '</select> &nbsp;&nbsp;<button id="siteworks_build_code_button">Build Sample Code</button></form><br>
	<div id="siteworks_build_code_reply"></div>
</div>
';
?>