<?PHP
if ( ! defined('SITEWORKS_DOCUMENT_ROOT')) exit('No direct script access allowed');

/***************************************************************************************************
**                          E X A M P L E     C O N T R O L L E R                                 **
***************************************************************************************************/

class template_controller extends _s
{
    public function template(){

        /****************************************************************************************************************
        **                                                                                                             **
        **                      E X A M P L E         D A T A B A S E       U S A G E                                  **
        **                                                                                                             **
        **   Template in /dev/dbtables/t_site_works_template_table.inc.php                                             **
        **   Nameing Convention: t_YOUR_TABLE_NAME.inc.php                                                             **
        **                                                                                                             **
        **   Insert Into Example:                                                                                      **
        **   $r = new t_YOUR_TABLE_NAME(null,$this->_odb);          // Load the table object                           **
        **   $r->f['FIELD_NAME']['value'] = 'my value'              // Set the values                                  **
        **   $r->insertData();                                      // Insert Into                                     **
        **                                                                                                             **
        **   Note: do not use site_works_template_table as a DB table name, it will not be parsed.                     **
        **                                                                                                             **
        ****************************************************************************************************************/


        /****************************************************************************************************************
        **                                                                                                             **
        **                               E X A M P L E        T H R E A D I N G                                        **
        **                                                                                                             **
        **   Put your thread scripts in: /dev/thread_scripts                                                           **
        **   Thread scripts should have a php extention: MyFile.php                                                    **
        **                                                                                                             **
        **   php_threader in your site_works root folder must have execuable permissions:                              **
        **   Ex: chmod 755 php_threader                                                                                **
        **                                                                                                             **
        **   To Call the Threader:                                                                                     **
        **   $this->_tool->thread( 'MyFile', 20, ['key1'=>'var1','key2'=>'var2'] );                                    **
        **                                                                                                             **
        **   MyFile - is your file name found in /dev/thread_scripts folder without the extention.                     **
        **   20 - is the number of seconds to wait until the script is run.                                            **
        **   Variables - Depending on how you read out the other end, its probably easiest to just send an array       **
        **        as shown above.                                                                                      **
        **                                                                                                             **
        **   Example Thread File:                                                                                      **
        **   <?php                                                                                                     **
        **        // site_works sends variables json and base64 encoded as the letter q parameter                      **
        **        // Use a line like the following to unpack your variables into an object                             **
        **        // base64_encode apparently turns an opening [ into an {                                             **
        **        $q = json_decode( base64_decode( getopt("q:")['q'] ) );                                              **
        **                                                                                                             **
        **        // Open a file your script has permissions to write to                                               **
        **        $f = fopen("/var/log/www/writeable_log.txt", "w");                                                   **
        **                                                                                                             **
        **        // Write our variables to the file                                                                   **
        **        fwrite($f, 'Key 1: '. $q->key1);                                                                     **
        **        fwrite($f, 'Key 2: '. $q->key2);                                                                     **
        **        fclose($myfile);                                                                                     **
        **   ?>                                                                                                        **
        **                                                                                                             **
        **   Note: These scripts do not go through the framework, so write vanilla php                                 **
        **   If you really need the framework, you could rig your MyFile.php with something like:                      **
        **   $x = file_get_contents('http://www.MySite.com/Modual/Controller/Method/pass_var/pass_vars');              **
        **                                                                                                             **
        ****************************************************************************************************************/

        // If you use a modual or controller lock, we will return sw_error_permission as a passvar to your default modual.
        // You can handle it with a statement like this
        if( $this->_uri->pass_var == 'sw_error_permission' ){
            $this->_out['body'][] = 'You do not have permission to view that area of the site.<br><br>';
        } 
        // If someone goes to a uri that does not exist, it will be redirected back to your default moduals controller.
        // You can test against an unexpected pass_var to give some sort of error if you wish.
        else if( $this->_uri->pass_var != '' ) {
            $this->_out['body'][] = 'You entered a bad URL. That\'s ok, we brought you back home.';
        }

        // Do you like Jquery? How about loading it from a CDN like google? You can do this in the config if you want it global
        $this->_out['js'][] = '<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>';

        // Here is an idea, let's make our model handle math
        $a = 5;
        $this->_out['body'][] = $a . ' + 5 = ' . $this->_m->addToA($a, 5) . '<br><br>';

        // Wait how do I pass a variable through the framework?
        $this->_p['a'] = 5;
        $this->_out['body'][] = $this->_p['a'] . ' + 5 = ' . $this->_m->addToSharedA(5) . '<br><br>';

        // Oooo, lets send something to the cool debug_server
        $this->_tool->dmsg('$a = '. $a);

        // But I write tons of code for fun and want to use lots of modles!
        // Ok, hold on, we just load your new model with its name then use it like normal
        // All models load from the calling Modual
        // If you want something outside load_helper helpers are found in dev/helper
        // or instantiate a class you put in the dev/includes folder.
        // Notice when calling your new model its $this->_m_model_name->method
        $this->load_model('template2');
        $this->_p['b'] = 10;
        $this->_out['body'][] = $this->_p['b'] . ' + 10 = ' . $this->_m_template2->addToSharedB(10) . '<br><br>';


        // Hey! I can't access the admin link :( 
        // Well, you have a modual lock in your config that says only admins and higher can access it.
        // You should create a login and then use these session values to allow proper users in to it.
        // For now we'll just hard code it in, but remember to remove the next two lines when ready
        // Uncomment these two lines to force a pretend good login so you can access the admin area.
        // $_SESSION['is_loggedin'] = true;
        // $_SESSION['admin_level'] = 1000;

        // Now let us add a link to the admin page.
        $this->_out['body'][] = '<a href="'. $this->_uri->base_url . '/sw_admin">Admin Page</a><br>';
        $this->_out['body'][] = '<a href="'. $this->_uri->base_url . '/template/dogs/are/our/freinds">Go to the second method in the template controller.</a><br>';

        // Ok, I guess it's pretty strait forward, but I still need to load and use muh view pages.
        // Sure, when you call a load with no filename, it automatically loads the one corresponding to your controllers name
        $this->load_view();

        // Hey, one more thing, I like to separate my controllers for clarity, it's up to you if you like it too
        $this->_out['footer'][200] = '<a href="' . $this->_uri->base_url . '/ajax_template">2) Example Ajax Controller</a><br>';
        $this->_out['footer'][300] = '<a href="' . $this->_uri->base_url . '/iframe_template">3) Example iFrame Controller</a><br>';
        $this->_out['footer'][100] = '<a href="' . $this->_uri->base_url . '/script_template">1) Example Script Controller</a><br>';
        // Er, one more one more thing - because it's an array you can arrange output as you please. :D 
        ksort($this->_out['footer']);

        // Once you get your SiteWorks database working you can use the langauge feature.
        // You encapsulate some words in a bracket with two undescores to set off the interpreter. 

    }

    public function dogs(){
        $this->_out['body'][] = 'Hey you made it! Look at your SSO, woohoo!<br>Now go back home.<br><br>';
        $this->_out['body'][] = '<a href="'. $this->_uri->base_url . '">Back Home</a><br><br>';

        $this->_out['body'][] = 'The url says go to: Modual: Template, Controller: dogs, and Method: are<br><br>Actual path:<br>';
        $this->_out['body'][] = 'Modual: ' . $this->_uri->module . '<br>';
        $this->_out['body'][] = 'Controller: ' . $this->_uri->controller . '<br>';
        $this->_out['body'][] = 'Modual: ' . $this->_uri->method . '<br>';
        $this->_out['body'][] = 'pass_var: ' . $this->_uri->pass_var . '<br>';
        $this->_out['body'][] = 'pass_vars: ' . implode('/',$this->_uri->pass_vars) . '<br><br>';

        $this->_out['body'][] = 'Notice we did not provide a controler? System found the template Modual and enterd it. <br>
            then it could not find the Controller named template so it used the Moduals name to correct.<br>
            then it tired to load the method dogs and found it. It stores the very next variable to $this->_uri->pass_var<br>
            and everythign else to the $this->_uri->pass_vars[] array.';


    }


}

?>
