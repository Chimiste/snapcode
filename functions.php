<?php
/**
 * @package:SMS
 * @Generate username::generateUsername().
 * @Author:Techno Services
 */
 function generateUsername($original) {
	 
    $xname = explode('A/L', $original);
    $xname2 = explode('A/P', $xname[0]);
    $xname3 = explode('BIN', $xname2[0]);
    $xname4 = explode('@', $xname3[0]);
     
    $uname = str_replace(' ', '', $xname4[0]);
    $uname = str_replace(',', '', $uname);
    $uname = str_replace('.', '', $uname);
    $uname = str_replace('-', '', $uname);
    $uname = str_replace('`', '', $uname);
    $uname = str_replace('(', '', $uname);
    $uname = str_replace(')', '', $uname);
    $uname = strtolower(str_replace('\'', '', $uname));
    $uname = strtolower(str_replace('/', '', $uname));
    $uname = substr($uname,0,12);
     
    $passno_strip = substr(getToken(4), 0, 2);
  
	$ci =& get_instance();
	$ci->db->select('username');
	$ci->db->from($ci->common_model->_usersTable);
	$ci->db->where("username LIKE '$uname'");
	$check = $ci->db->count_all_results();

    if ($check > 0) {
    $uname = substr($uname,0,10);
    $uname .= $passno_strip;
    }
     
    $uname = strtolower($uname); //turn all lowercase
     
    return $uname;
 }
 
 /**
   * @Scrapper::isUrlOk()
   * @access:public
   * @Author:Bacar
   * @params:$url
   * @return
   */
	public function isUrlOk($url) {
		
	  $headers = @get_headers($url);
	  if($headers[0] == 'HTTP/1.1 200 OK') return true;
	  else return false;
	}
<?> 
 
 
