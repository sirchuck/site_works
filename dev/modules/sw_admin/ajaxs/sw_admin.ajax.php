<?PHP
if ( ! defined('SITEWORKS_DOCUMENT_ROOT')) exit('No direct script access allowed');

class sw_admin_ajax extends _s
{

    public function sw_admin(){
        // Notice if you did not specify a method, this would be auto-loaded.
    }

    public function get_text(){
        $j = new stdClass();
        $j->k = $this->_uri->pass_var;
        $j->l = $this->_uri->pass_vars[0];

        $r = new SiteWorks\t_site_works_lang($this->_uri->pass_var,$this->_odb);

        $j->p = $r->f['sw_lang_keep']['value'];
        $j->c = $r->f['sw_lang_category']['value'];
        $j->o = $r->f['sw_origional']['value'];
        $j->e = $r->f['english']['value'];
        $j->t = $r->f[$this->_uri->pass_vars[0]]['value'];

        echo json_encode($j);
    }

    public function set_text(){
        if($_POST['delete'] == "1"){
            if($_POST['key'] > 0){
                $r = new SiteWorks\t_site_works_lang($_POST['key'],$this->_odb);
                $r->deleteData();
            }
        }else{
            if( $_POST['key'] > 0 ){
                $r = new SiteWorks\t_site_works_lang($_POST['key'],$this->_odb);
                if($_POST['keep'] == 1){ $r->f['sw_lang_keep']['value'] = 1; }
                $r->f['sw_lang_category']['value']  = $r->clean($_POST['category']);
                $r->f[$_POST['lang']]['value']      = $r->clean($_POST['nlang']);
                $r->updateData();
            }else{
                $t = new SiteWorks\t_site_works_lang(0,$this->_odb);
                $result = $t->selectALL("sw_lang_category='" . $t->clean($_POST['category']) . "' AND sw_origional='". $t->clean($_POST['nlang']) . "'");
                if($result){
                    echo '{"v":0}';
                    return false;
                }
                $r = new SiteWorks\t_site_works_lang(0,$this->_odb);
                if($_POST['keep'] == 1){ $r->f['sw_lang_keep']['value'] = 1; }
                $r->f['sw_lang_category']['value']          = $r->clean($_POST['category']);
                $r->f[$_POST['sw_origional']]['value']      = $r->clean($_POST['nlang']);
                $r->f[$_POST['english']]['value']           = $r->clean($_POST['nlang']);
                $r->insertData();
            }
        }
        echo '{"v":1}';
    }

    public function delete_marked(){
        $r = new SiteWorks\t_site_works_lang(0,$this->_odb);
        $r->deleteData('sw_lang_keep=3');
        echo '{"v":1}';
    }

    public function build_sample(){
        if( isset($_POST['p']) && $_POST['p'] != ''){
            $swv = 'SiteWorks\\'.$_POST['p'];
            $r = null;
            if( class_exists($swv) ){
                $r = new $swv(0,$this->_odb);
            } else {
                $swv = $_POST['p'];
                $r = new $swv(0,$this->_odb);
            }
            $j = new stdClass();
            $o = array(); 

            // CSS
            $o[] = '----------------- C S S ---------------------';
            $o[] = '<style>';
            $o[] = '|tab|.form_element_div{ font-size: 16px; }';
            $o[] = '|tab|.form_element_label{ font-size: 16px; }';
            $o[] = '|tab|.form_element{ font-size: 16px; }';
            foreach( $r->f as $k => $v ){
                $o[] = '';
                $o[] = '|tab|#'.$k.'_div{ width: 250px; }';
                $o[] = '|tab|#'.$k.'_label{ width: 250px; }';
                $o[] = '|tab|#'.$k.'{ width: 250px; }';
            }
            $o[] = '</style>';




            // Javascript
            $o[] = '----------------- J S ---------------------';
            $o[] = '<script>';
            $oo = '';
            $o[] = '$(body).on(\'click\',\'' . $_POST['p'] . '_update_button\',function(event){';
            foreach( $r->f as $k => $v ){
                $oo .= ',"' . $k . '":$("#\''.$k.'\'").val()';
            }
            $o[] = '|tab|var s = {"action":"' . $_POST['p'] . '_update"' . $oo . '}';
            $o[] = '|tab|$.ajax({ type: "POST", url: \'//\' + base_url + \'/ajax_MODUAL/CONTROLLER/METHOD\', data: s, dataType: "json", failure: function(d){}, success: function(d){';
            $o[] = '|tab||tab|if(d.success == \'success\'){';
            $o[] = '|tab||tab||tab|// Handle Success';
            $o[] = '|tab||tab|} else {';
            $o[] = '|tab||tab||tab|// Hadle Failure';
            $o[] = '|tab||tab|}';
            $o[] = '|tab|}});';
            $o[] = '});';


            $o[] = '</script>';
            // HTML
            $o[] = '----------------- H T M L ---------------------';
            $o[] = '<form method="post">';
            foreach( $r->f as $k => $v ){
                $o[] = '|tab|<div id="'.$k.'_div" class="form_element_div">';
                $o[] = '|tab||tab|<label id="'.$k.'_label" class="form_element_label">';
                $o[] = '|tab||tab||tab|<input id="'.$k.'" class="form_element" name="'.$k.'" value="" type="text" placeholder="'.$k.'" />';
                $o[] = '|tab||tab|</label>';
                $o[] = '|tab|</div>';
            }
            $o[] = '|tab|<button id="' . $_POST['p'] . '_update_button" class="form_button">Update</button>';
            $o[] = '</form>';
            // AJAX
            $o[] = '----------------- A J A X ---------------------';
            $o[] = 'public function YOUR_METHOD(){';
            $o[] = '|tab|$j = new stdClass();';
            $o[] = '|tab|$j->success = \'failure\';';
            $o[] = '|tab|if($_POST[\'action\'] == "t_site_works_admin_update"){';
            $o[] = '|tab||tab|$r = new SiteWorks\\' . $_POST['p'] . '($_POST[\'KEY\'],$this->_odb);';
            foreach( $r->f as $k => $v ){
                $o[] = '|tab||tab|$r->f[\'' . $k . '\'][\'value\'] = $_POST[\'' . $k . '\'];';
            }
            $o[] = '|tab||tab|$r->updateData();';
            $o[] = '|tab||tab|$j->success = \'success\';';
            $o[] = '|tab|}';
            $o[] = '|tab|echo json_encode($j)}';
            $o[] = '}';

            $o[] = '';
            $o[] = '---------------- DBO C H E A T   S H E E T -------------';
            $o[] = 'Create the table object, if it is a siteworks table preface it with the SiteWorks\\ namespace.';
            $o[] = '|tab|$o = new SiteWorks\\t_site_works_lang( [0 for blank ID to auto fill fields] , [DB Connection $this->_odb or $this->_dbo[\'default\'] ] );';
            $o[] = '';
            $o[] = 'Strait Up Query the Database, accept no substitute';
            $o[] = '|tab|$result = $o->query(\'SELECT thing FROM table WHERE key = "\' . $o->clean( $MyVar ) . \'"\');';
            $o[] = '';
            $o[] = 'Clean a variable for db';
            $o[] = '|tab|$o->clean( $MyVar );';
            $o[] = '';
            $o[] = 'Clean all object field variables';
            $o[] = '|tab|$o->cleanAll();';
            $o[] = '';
            $o[] = 'Clear all object fields variables to default values.';
            $o[] = '|tab|$o->clearFields();';
            $o[] = '';
            $o[] = 'Get rows as objects or array';
            $o[] = '|tab|$o->getRows( [$result] , [false for object, true for array] );';
            $o[] = '';
            $o[] = 'Fill the table object with data for a specified key.';
            $o[] = '|tab|$o->fillData( [ true for last record OR 7 for specifc id OR \'word_key\' for alphanumeric key ] );';

            $o[] = '';
            $o[] = 'Select all records, your WHERE clause goes first, then value list, blank value pulls all fields.';
            $o[] = '|tab|$result = $o->selectAll( \'field=7 ORDER BY field\'  , \'specific_field , specific_field_2\' );';

            $o[] = '';
            $o[] = 'Insert Data from the objects field data';
            $o[] = '|tab|$o->insertData();';
            $o[] = '';
            $o[] = 'Insert Data from the objects field data, if key exists update the data';
            $o[] = '|tab|$o->insertUpdateData();';
            $o[] = '';

            $o[] = 'Update Data (where , values) if where is empty use objects keyfield value, if values is empty we pull from the object field list.';
            $o[] = '|tab|$o->updateData( empty OR \'x=y\' OR 7 OR \'word_key\' , empty OR \'field1=val1,field2=val2\' );';
            $o[] = '';

            $o[] = 'Delete data if left empty it pulls from the objects key field, if still empty we return false.';
            $o[] = '|tab|$o->deleteData( empty OR \'7\' OR \'word_key\' OR \'x=y\');';
            $o[] = '';


            $o[] = '';
            $o[] = '------------- E X T R A ---------------';
            $o[] = 'All your code should go in the Dev directory.';
            $o[] = 'ajaxs/scripts/iframs/controllers are all the same except controllers get extra output like <html> tags and console output.';
            $o[] = 'models with the same name as controller are automatically loaded when the class is loaded';
            $o[] = 'Output arrays: $this->out[\' header / title / meta / css / js / body / footer \'] and $this->console[] = "Message for Console";';

            $o[] = 'You have access to the admin and memory siteworks database tables as well with $this->admin array and $this->mem array.';

            $o[] = 'load_helper() loads from dev/helpers , load_model() loads from your moduals models folder, load_view() from your moduals view folder';
            $o[] = '$x = new MyClass will load from dev/includes folder.';
            $o[] = 'To share variables to a view page use the $this->share[\'MYVAR\'] array.';
            $o[] = 'To use debugger use $this->_tool->dmsg(\'My Words\');';
            $o[] = 'The default database is $this->_odb';
            $o[] = 'If you are using multipul databases then $this->_dbo[\'DATABASE_IDENTIFIER (default)\'] array';
            $o[] = 'You have access to the uri as well, $this->_uri-> (calltype, module, controller, method, pass_var, pass_vars, root_url, base_url, public_url, asset_url, assets array)';

            $o[] = '';
            $o[] = 'Psst, hey buddy, you wanna use color? You can at least if you use some versions of linux and the debug_server.';
            $o[] = '$this->_tool->dmsg("My words [c_red] This will be red [c_clear] This will be back to normal.")';
            $o[] = 'Your color options:';
            $o[] = '[c_black] [c_red] [c_green] [c_orange] [c_blue] [c_purple] [c_cyan] [c_gray]';
            $o[] = '[c_white] [c_light_red] [c_light_green] [c_yellow] [c_light_blue] [c_light_purple] [c_light_cyan] [c_light_gray]';
            $o[] = 'Return to normal with [c_clear]';

            $o[] = '';
            $o[] = '';

            foreach($o as $k=>$v){ $o[$k] = htmlspecialchars($v); }
            $j->v = implode("<br>",$o);
            $j->v = str_replace('|tab|','&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',$j->v);
            $j->v .= '<br><br><br>';
            echo json_encode($j);
        }
    }
    

   
}

?>