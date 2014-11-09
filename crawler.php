<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);
class Crawler{
	
	public $urls = array(
	               'http://priceonomics.com/',//0 done
				   'http://nudges.org/',//1 done
				   'http://www.rsablogs.org.uk/tag/behavioural-economics/',//2done
				   'http://blogs.law.harvard.edu/corpgov/',//3done
				   'http://blogs.wsj.com/moneybeat/',//4 done
				   'http://corporatelawandgovernance.blogspot.com.br/',//5done
				   'http://econlog.econlib.org/',//6 done
				   'http://news.sciencemag.org/scienceinsider',// 7 done
				   'http://freakonomics.com/blog/',//8 done
				   'http://www.technologyreview.com/stream/',// http://www.technologyreview.com/9
				   'http://www.voxeu.org/',//10 done
				   'http://www.businessweek.com/global-economics',//11 done
				   'http://www.forbes.com/real-time/',//12http://www.forbes.com/fdc/welcome_mjx.shtml done
				   'http://sloanreview.mit.edu/',//13
				   'http://www.psychologytoday.com/',//14 done
				   'http://www.theguardian.com/law',//15 done
				   'http://governancejournal.net/',//16done
				   'http://www.infomoney.com.br/ultimas-noticias', //17 done
				   'http://ftalphaville.ft.com/', //done 18
				   'http://www.technologyreview.com/business/stream/', //19 done
				   'http://www.technologyreview.com/computing/stream/',//20
				   'http://www.technologyreview.com/energy/stream/',//21
				   'http://www.technologyreview.com/mobile/stream/',//22
				   'http://www.businessweek.com/companies-and-industries',//23done
				   'http://www.businessweek.com/technology',//24done
				   'http://www.businessweek.com/markets-and-finance'
	);
	private $db;
	private $crawler_table = 'blogs';
	private $rowInserted;
	
	function __construct($config){
		 set_time_limit(0);
		 $this->db = new Mysqlidb(HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
	}
  /**
   * @Crawler::getInsertedRow()
   * @access:public
   * @Author:Bacar
   * @return
   */
	public function getInsertedRow(){
	   return $this->rowInserted;	
	}
  /**
   * @Crawler::trimData()
   * @access:public
   * @Author:Bacar
   * @return
   */
   public function trimData($data){
	   return trim($data);  
   }
  /**
   * @Crawler::extractBlogData()
   * @access:public
   * @Author:Bacar
   * @return
   */
   public function extractBlogData($url , $loc,$max_page = ''){
	    
	   $blogs = array();
	   
	   switch ($loc){
		  
		  case 0:
		  
		    for ($i=1; $i<=$max_page;$i++){
				
				$html = file_get_html($url.'?page='.$i.'&s=latest');
				
				if(!empty($html)){
				   
				   $n=0;
				   foreach ($html->find('#blogPosts-main li') as $row){
					   
					 if(!empty($row->find('div a',0)->href)) {
						 $blogs[$n]['url'] = $row->find('div a',0)->href;
					 }
					 else $blogs[$n]['url'] = '';
					 
					 if(!empty($row->find('div a img',0)->src)) {
						 $blogs[$n]['news_logo'] = $row->find('div a img',0)->src;
					 }
					 else $blogs[$n]['news_logo'] = '';
					 
					 if(!empty($row->find('div .blogPreview a h3',0)->plaintext)){
					    $blogs[$n]['headline'] = $row->find('div .blogPreview a h3',0)->plaintext;
					 }
					 else $blogs[$n]['headline'] = '';
					 
					 if(!empty($row->find('div .blogPreview p',1)->plaintext)){
					   $blogs[$n]['subheadline'] = $row->find('div .blogPreview p',1)->plaintext;
					 }
					 else $blogs[$n]['subheadline']  = '';
					 if(!empty($row->find('div .blogPreview p span',0)->plaintext)){
						 $blogs[$n]['datetime'] = $row->find('div .blogPreview p span',0)->plaintext;
					 }
					 else $blogs[$n]['datetime'] = '';
					 
					 
					 if(!empty($blogs[$n]['url'])) $html_body = $html = file_get_html($blogs[$n]['url']);
					 else $html_body = '';
					 
					 if(!empty($html_body)){
						 
						 if(!empty($html_body->find('#cms-main .blogContent',0)->plaintext)){
							 $blogs[$n]['articlebody'] = $html_body->find('#cms-main .blogContent',0)->plaintext;
							 $blogs[$n]['articlebody'] = preg_replace('/[^\p{L}\p{N}\s]/u', '', $blogs[$n]['articlebody']);
						 }
						 else $blogs[$n]['articlebody'] = '';
						 
						if(!empty($html_body->find('.blogContent em em em a',0)->plaintext)) {
							$blogs[$n]['author'] = $html_body->find('.blogContent em em em a',0)->plaintext;
						}
						else $blogs[$n]['author'] = '';
					 }
					 else {
						 
						 $blogs[$n]['author'] = '';
						 $blogs[$n]['articlebody'] = '';	 
					 }
					
					 $records = array(
					            'category' => $url,
								'headline' => $this->trimData($blogs[$n]['headline']),
								'subheadline' => $blogs[$n]['subheadline'],
								'author' => $blogs[$n]['author'],
								'datetime' => $blogs[$n]['datetime'],
								'articlebody' => $blogs[$n]['articlebody'],
								'tags' => '',
								'news_logo' => $blogs[$n]['news_logo'],
								'url' => $blogs[$n]['url']
					 );
					//store data
					$this->db->insert($this->crawler_table, $records);

					 $n++;
					 break;    
				   }
				   
				   $html->clear;
				   
				   $this->rowInserted = $i;
				
				}
			}
			
		  break; 
		  
		  case 1:
		    for ($i=1; $i<=$max_page;$i++){
				
				$html = file_get_html($url.'page/'.$i.'/');
				
				if(!empty($html)){
				   
				   $n=0;
				   foreach ($html->find('.posts div') as $row){
					   
					 if(!empty($row->find('.meta h2 a',0)->href)) {
						 $blogs[$n]['url'] = $row->find('.meta h2 a',0)->href;
					 }
					 else $blogs[$n]['url'] = '';
					 
					 if(!empty($row->find('.tagdata a',0)->plaintext)) {
						 $blogs[$n]['tag'] = $row->find('.tagdata a',0)->plaintext;
					 }
					 else $blogs[$n]['tag'] = '';
					 
					 $blogs[$n]['news_logo'] = '';
					 
					 if(!empty($row->find('.meta h2 a',0)->plaintext)){
					    $blogs[$n]['headline'] = $row->find('.meta h2 a',0)->plaintext;
					 }
					 else $blogs[$n]['headline'] = '';
					 
					 $blogs[$n]['subheadline']  = '';
					 
					 if(!empty($row->find('.meta .metadata .date',0)->plaintext)){
						 $blogs[$n]['datetime'] = $row->find('.meta .metadata .date',0)->plaintext;
					 }
					 else $blogs[$n]['datetime'] = '';


						 
					 if(!empty($row->find('.content ',0)->innertext)){
						 $blogs[$n]['articlebody'] = $row->find('.content',0)->innertext;
					 }
					 else $blogs[$n]['articlebody'] = '';
						 
					 $blogs[$n]['author'] = '';

					
					 $records = array(
					            'category' => $url,
								'headline' => $this->trimData($blogs[$n]['headline']),
								'subheadline' => $blogs[$n]['subheadline'],
								'author' => $blogs[$n]['author'],
								'datetime' => $blogs[$n]['datetime'],
								'articlebody' => $blogs[$n]['articlebody'],
								'tags' => $blogs[$n]['tag'],
								'news_logo' => $blogs[$n]['news_logo'],
								'url' => $blogs[$n]['url']
					 );
					//store data
					$this->db->insert($this->crawler_table, $records);

					 $n++;
					 break;    
				   }
				   
				   $html->clear;
				   
				   $this->rowInserted = $i;
				
				}
			}
		  break;
		  
		  case 2://http://www.rsablogs.org.uk/tag/behavioural-economics/
		    $html = file_get_html($url);
			$headline = array();
			$author = array();
			$url = array();
			$subheadline = array();
			$date = array();
			$tags = array();
	
			$n = 0;
			foreach ($html->find('#contentleft h1') as $row){
				
				 if(!empty($row->plaintext)){
					    $headline[] = $row->plaintext;
				 }
			     else $headline[] = '';
				 
				 $n++;
			}
			
			foreach ($html->find('#contentleft h1') as $row){
				
				 if(!empty($row->find('a',0)->href)){
					    $url[] = $row->find('a',0)->href;
				 }
			     else $url[] = '';
				 
				 $n++;
			}
			
			foreach ($html->find('#contentleft .date') as $row){
				
				 if(!empty($row->find('a',0)->plaintext)){
					    $author[] = $row->find('a',0)->plaintext;
				 }
			     else $author[] = '';
				 
				 $n++;
			}
			
			foreach ($html->find('#contentleft .date') as $row){
				
				 if(!empty($row->plaintext)){
					    $date[] = preg_replace('~<a(.*?)</a>~Usi', "", $row->plaintext);;
				 }
			     else $date[] = '';
				 
				 $n++;
			}
			
			
			foreach ($html->find('#contentleft p') as $row){
				
				 if(!empty($row->plaintext)){
					    $subheadline[] = $row->plaintext;
				 }
			     else $subheadline[] = '';
				 
				 $n++;
			}
			/*$n = 0;
			foreach ($html->find('.postmeta') as $row){
				
				foreach ($row->find('p a')  as $aLists){
				   if(!empty($aLists->plaintext)){
						  $tags[$n]['tag'] = $aLists->plaintext;
				   }
				   else $tags[$n]['tag'] = '';
				   
				  
				}
				 
				 $n++;
			}
			
			print '<pre>';
			print_r($tags);
			exit;*/
			
			
			$j = 0;
			foreach ($headline as $key=>$value){
				
			      $blogs[$j]['url']	 = $url[$key];
				  $blogs[$j]['headline'] = $value;
				  $blogs[$j]['subheadline'] = $subheadline[$key];
				  $blogs[$j]['author'] = $author[$key];
				  $blogs[$j]['datetime'] = $date[$key];
				  
				  if(!empty($blogs[$j]['url'])) $html_body = $html = file_get_html($blogs[$j]['url']);
				  else $html_body = '';
					   
				   if(!empty($html_body)){
					   
					   if(!empty($html_body->find('#contentleft',0)->innertext)){
						   $blogs[$j]['articlebody'] = $html_body->find('#contentleft',0)->innertext;
					   }
					   else $blogs[$j]['articlebody'] = '';

					  $html_body->clear;
					   
				   }
				   else {

					   $blogs[$j]['articlebody'] = ''; 
				   }
				   
				    $records = array(
					            'category' => 'http://www.rsablogs.org.uk/tag/behavioural-economics/',
								'headline' => $this->trimData($blogs[$j]['headline']),
								'subheadline' => $blogs[$j]['subheadline'],
								'author' => $blogs[$j]['author'],
								'datetime' => $blogs[$j]['datetime'],
								'articlebody' => $blogs[$j]['articlebody'],
								'tags' => '',
								'news_logo' => '',
								'url' => $blogs[$j]['url']
					 );
					//store data
					$this->db->insert($this->crawler_table, $records);
				  
				  $j++;
			}
			
			/*print '<pre>';
			print_r($blogs);*/
			
		  break;
		  
		  case 3://http://blogs.law.harvard.edu/corpgov/
		  
			for ($i=1; $i<=$max_page;$i++){
				
				  $html = file_get_html($url.'page/'.$i.'/');
				  
				  if(!empty($html)){
					 
					 $n=0;
					 
					 foreach ($html->find('#content .post') as $row){
						 
					   if(!empty($row->find('.meta a',0)->href)) {
						   $blogs[$n]['url'] = $row->find('.meta a',0)->href;
					   }
					   else $blogs[$n]['url'] = '';
					   
					   if(!empty($row->find('.content .teaser-image',0)->src)) {
						   $blogs[$n]['news_logo'] = $row->find('.content .teaser-image',0)->src;
					   }
					   else $blogs[$n]['news_logo'] = '';
  
					   if(!empty($row->find('h2 a',0)->plaintext)){
						  $blogs[$n]['headline'] = $row->find('h2 a',0)->plaintext;
					   }
					   else $blogs[$n]['headline'] = '';
					   
					   if(!empty($row->find('.storycontent',0)->innertext)){
						 $blogs[$n]['subheadline'] = $row->find('.storycontent',0)->plaintext;
					   }
					   else $blogs[$n]['subheadline']  = '';
					   
					   if(!empty($row->find('.meta',0)->plaintext)){
						   
						   $blogs[$n]['datetime'] = $row->find('.meta',0)->innertext;
						   $blogs[$n]['datetime'] = preg_replace('~<div(.*?)</div>~Usi', "", $blogs[$n]['author']);
						   $blogs[$n]['datetime'] = preg_replace('~<a(.*?)</div>~Usi', "", $blogs[$n]['a']);
					   }
					   else $blogs[$n]['datetime'] = '';
					   
					   if(!empty( $row->find('.meta',0)->innertext)) {
							  $blogs[$n]['author'] = $row->find('.meta',0)->innertext;
							  $blogs[$n]['author'] = preg_replace('~<div(.*?)</div>~Usi', "", $blogs[$n]['author']);
							  $blogs[$n]['author'] = preg_replace('~<a(.*?)</div>~Usi', "", $blogs[$n]['author']);
					   }
					   else $blogs[$n]['author'] = '';
					   
					   $tags = array();

						 foreach ($row->find('.meta .tags a') as $tagLists) {
							 
							 $tags[] = $tagLists->plaintext; 
						 }

					   if(isset($tags[0]) && $tags[0]){
						  $blogs[$n]['tag'] = implode(',', $tags);
					   }
					   else $blogs[$n]['tag'] = '';
					   
					   if(!empty($blogs[$n]['url'])) $html_body = $html = file_get_html($url.$blogs[$n]['url']);
					   else $html_body = '';
					   
					   if(!empty($html_body)){
						   
						   if(!empty($html_body->find('.post .storycontent',0)->innertext)){
							   $blogs[$n]['articlebody'] = $html_body->find('.post .storycontent',0)->innertext;
						   }
						   else $blogs[$n]['articlebody'] = '';
						  
						  if(!empty($row->find('.content h2',0)->plaintext)){
						  $blogs[$n]['headline'] = $row->find('.content h2',0)->plaintext;
					   }
					   else $blogs[$n]['headline'] = '';
						  
						  $html_body->clear;
						   
					   }
					   else {
  
						   $blogs[$n]['articlebody'] = '';
							$blogs[$n]['headline'] = '';	 
					   }
					  
					   $records = array(
								  'category' => $url,
								  'headline' => $this->trimData($blogs[$n]['headline']),
								  'subheadline' => $blogs[$n]['subheadline'],
								  'author' => $blogs[$n]['author'],
								  'datetime' => $blogs[$n]['datetime'],
								  'articlebody' => $blogs[$n]['articlebody'],
								  'tags' => $blogs[$n]['tag'],
								  'news_logo' => $blogs[$n]['news_logo'],
								  'url' => $blogs[$n]['url']
					   );
					  //store data
					  $this->db->insert($this->crawler_table, $records);
  
					   $n++;
					   
					  // break;
						 
					 }
					 
					 $html->clear;
					 
					 $this->rowInserted = $i;  
			}
			}
		  break; 
		  
		  
		  
		  case 4://http://blogs.wsj.com/moneybeat/
		  
		  for ($i=1; $i<=$max_page;$i++){
				
				$html = file_get_html($url.'page/'.$i.'/');
				
				if(!empty($html)){
				   
				   $n=0;
				   foreach ($html->find('#postList li') as $row){
					   
					 if(!empty($row->find('article .post-main h2 a',0)->href)) {
						 $blogs[$n]['url'] = $row->find('article .post-main h2 a',0)->href;
					 }
					 else $blogs[$n]['url'] = '';
					 
					 if(!empty($row->find('article .post-main h2 .post-thumb',0)->href)) {
						 $blogs[$n]['news_logo'] = $row->find('article .post-main h2 .post-thumb',0)->href;
					 }
					 else $blogs[$n]['news_logo'] = '';
					 
					 if(!empty($row->find('article .post-main h2 a',0)->plaintext)){
					    $blogs[$n]['headline'] = $row->find('article .post-main h2 a',0)->plaintext;
					 }
					 else $blogs[$n]['headline'] = '';
					 
					 if(!empty($row->find('.post-main .post-content',0)->innertext)){
					   $blogs[$n]['subheadline'] = $row->find('.post-main .post-content',0)->plaintext;
					 }
					 else $blogs[$n]['subheadline']  = '';
					 
					 if(!empty($row->find('article .post-data .post-time',0)->plaintext)){
						 $blogs[$n]['datetime'] = $row->find('article .post-data .post-time',0)->plaintext;
					 }
					 else $blogs[$n]['datetime'] = '';
					 
					 if(!empty($row->find('.post-main .post-author',0)->plaintext)) {
							$blogs[$n]['author'] = $row->find('.post-main .post-author',0)->plaintext;
					 }
					 else $blogs[$n]['author'] = '';
					 
					 $tags = array();
					 foreach ($row->find('article .post-data .post-tags li') as $tagLists) {
						 
						 $tags[] = $tagLists->plaintext; 
					 }
					
					 if(isset($tags[0])){
					    $blogs[$n]['tag'] = implode(',', $tags);
					 }
					 else $blogs[$n]['tag'] = '';
					 
					 if(!empty($blogs[$n]['url'])) $html_body = $html = file_get_html($blogs[$n]['url']);
					 else $html_body = '';
					 
					 if(!empty($html_body)){
						 
						 if(!empty($html_body->find('.post-content',0)->innertext)){
							 $blogs[$n]['articlebody'] = $html_body->find('.post-content',0)->innertext;
						 }
						 else $blogs[$n]['articlebody'] = '';
						 
					 }
					 else {

						 $blogs[$n]['articlebody'] = '';	 
					 }
					
					 $records = array(
					            'category' => $url,
								'headline' => $this->trimData($blogs[$n]['headline']),
								'subheadline' => $blogs[$n]['subheadline'],
								'author' => $blogs[$n]['author'],
								'datetime' => $blogs[$n]['datetime'],
								'articlebody' => $blogs[$n]['articlebody'],
								'tags' => '',
								'news_logo' => $blogs[$n]['news_logo'],
								'url' => $blogs[$n]['url']
					 );
					//store data
					$this->db->insert($this->crawler_table, $records);

					 $n++;
					 
					// break;
					   
				   }
				   
				   $html->clear;
				   
				   $this->rowInserted = $i;
				
				}
		  }
		  break; 
		  
		  case 5://http://corporatelawandgovernance.blogspot.com.br/
		        $html = file_get_html($url);
				
				if(!empty($html)){
				   
				   $n=0;
				   foreach ($html->find('#Blog1 .blog-posts .date-outer') as $row){
					   
					 if(!empty($row->find('.date-posts .post-outer .post .post-title a',0)->href)) {
						 $blogs[$n]['url'] = $row->find('.date-posts .post-outer .post .post-title a',0)->href;
					 }
					 else $blogs[$n]['url'] = '';
					 
					  $tags = array();
			
					 foreach ($row->find('.post-footer . post-footer-line post-footer-line-2 a') as $tagLists) {
						 
						 $tags[] = $tagLists->plaintext; 
					 }

					 if(isset($tags[0]) && $tags[0]){
					    $blogs[$n]['tag'] = implode(',', $tags);
					 }
					 else $blogs[$n]['tag'] = '';
					 
			
					 if(!empty($row->find('.date-posts .post-outer .post .post-body img',0)->src)){
					    $blogs[$n]['news_logo'] = $row->find('.date-posts .post-outer .post .post-body img',0)->src;
					 }
					 else $blogs[$n]['news_logo'] = '';
					 
					 if(!empty($row->find('.date-posts .post-outer .post .post-title',0)->plaintext)){
					    $blogs[$n]['headline'] = $row->find('.date-posts .post-outer .post .post-title',0)->plaintext;
					 }
					 else $blogs[$n]['headline'] = '';
					 
					  if(!empty($row->find('.date-posts .post-outer .post .post-body',0)->plaintext)){
					    $blogs[$n]['subheadline'] = $row->find('.date-posts .post-outer .post .post-body',0)->plaintext;
						$blogs[$n]['subheadline'] = preg_replace('~<img(.*?)</img>~Usi', "", $blogs[$n]['subheadline']);
					 }
					 else $blogs[$n]['subheadline']  = '';
					 
					 if(!empty($row->find('h2 span',0)->plaintext)){
						 $blogs[$n]['datetime'] = $row->find('h2 span',0)->plaintext;
					 }
					 else $blogs[$n]['datetime'] = '';


						 
					 if(!empty($row->find('.date-posts .post-outer .post .post-body ',0)->innertext)){
						 $blogs[$n]['articlebody'] = $row->find('.date-posts .post-outer .post .post-body',0)->innertext;
					 }
					 else $blogs[$n]['articlebody'] = '';
						 
					 if(!empty($row->find('.post-footer .post-footer-line-1 .post-author span',0)->plaintext)) {
							$blogs[$n]['author'] = $row->find('.post-footer .post-footer-line-1 .post-author span',0)->plaintext;
					 }
					 else $blogs[$n]['author'] = '';

					
					 $records = array(
					            'category' => $url,
								'headline' => $this->trimData($blogs[$n]['headline']),
								'subheadline' => $blogs[$n]['subheadline'],
								'author' => $blogs[$n]['author'],
								'datetime' => $blogs[$n]['datetime'],
								'articlebody' => $blogs[$n]['articlebody'],
								'tags' => $blogs[$n]['tag'],
								'news_logo' => $blogs[$n]['news_logo'],
								'url' => $blogs[$n]['url']
					 );
					//store data
					$this->db->insert($this->crawler_table, $records);

					 $n++;
					// break;    
				   }
				   
				   $html->clear;
				   
				   //$this->rowInserted = $n;
				   
				   return $blogs;
				
				}
		  break; 
		  
		  
		  case 6://http://econlog.econlib.org/
		    $html = file_get_html($url);
			$headline = array();
			$author = array();
			$urlArr = array();
			$articlebody = array();
			$date = array();
			$tags = array();
	
			$n = 0;
			foreach ($html->find('.s3c1 .padding .section .p h3 a') as $row){
				
				 if(!empty($row->plaintext)){
					    $headline[] = $row->plaintext;
				 }
			     else $headline[] = '';
				 
				 $n++;
			}
			
			foreach ($html->find('.s3c1 .padding .section .p h3') as $row){
				
				 if(!empty($row->find('a',0)->href)){
					    $urlArr[] = $row->find('a',0)->href;
				 }
			     else $urlArr[] = '';
				 
				 $n++;
			}
			
			
			foreach ($html->find('.s3c1 .padding .section .p .hosted') as $row){
				
				 if(!empty($row->plaintext)){
					    $author[] = $row->plaintext;
				 }
			     else $author[] = '';
				 
				 $n++;
			}
			
			foreach ($html->find('.dateline') as $row){
				
				 if(!empty($row->plaintext)){
					    $date[] = $row->plaintext;
				 }
			     else $date[] = '';

			}
			
			foreach ($html->find('.s3c1 .padding .section .blog') as $row){
				
				 if(!empty($row->plaintext)){
					    $articlebody[] = $row->plaintext;
				 }
			     else $articlebody[] = '';

			}
			
			$n = 0;
			foreach ($html->find('.s3c1 .padding .section .pl .pi') as $row){
				
				foreach ($row->find('a') as $ltags){
				   if(!empty($ltags->plaintext)){
						  $tags[$n]['tag'] = $row->plaintext;
				   }
				   else $tags[$n]['tag'] = '';
				}
				 
				 $n++;
			}
			
			unset($tags[1]);unset($tags[22]);unset($tags[43]);
			unset($tags[2]);unset($tags[23]);unset($tags[44]);
			unset($tags[4]);unset($tags[25]);unset($tags[46]);
			unset($tags[5]);unset($tags[26]);unset($tags[47]);
			unset($tags[7]);unset($tags[28]);unset($tags[49]);
			unset($tags[8]);unset($tags[29]);unset($tags[50]);
			unset($tags[10]);unset($tags[31]);unset($tags[52]);
			unset($tags[11]);unset($tags[32]);unset($tags[53]);
			unset($tags[13]);unset($tags[34]);unset($tags[55]);
			unset($tags[14]);unset($tags[35]);unset($tags[56]);
			unset($tags[16]);unset($tags[37]);unset($tags[58]);
			unset($tags[17]);unset($tags[38]);unset($tags[59]);
			unset($tags[19]);unset($tags[40]);
			unset($tags[20]);unset($tags[41]);
			
			$tags  = array_values($tags);

			$j = 0;
			foreach ($headline as $key=>$value){
				
			      $blogs[$j]['url']	 = $urlArr[$key];
				  $blogs[$j]['headline'] = $value;
				  $blogs[$j]['subheadline'] = '';
				  $blogs[$j]['author'] = $author[$key];
				  $blogs[$j]['datetime'] = $date[$key];
				  $blogs[$j]['articlebody'] = $articlebody[$key];
				  $blogs[$j]['tag'] = $tags[$key]['tag'];

				   
				    $records = array(
					            'category' => $url,
								'headline' => $this->trimData($blogs[$j]['headline']),
								'subheadline' => $blogs[$j]['subheadline'],
								'author' => $blogs[$j]['author'],
								'datetime' => $blogs[$j]['datetime'],
								'articlebody' => $blogs[$j]['articlebody'],
								'tags' => $blogs[$j]['tag'],
								'news_logo' => '',
								'url' => $blogs[$j]['url']
					 );
					//store data
					$this->db->insert($this->crawler_table, $records);
				  
				  $j++;
			}
			
			/*print '<pre>';
			print_r($blogs);*/
			
		  break;
		  
		  case 7:
				
			  $html = file_get_html($url);
			 
			  if(!empty($html)){
				 
				 $n=0;
				 foreach ($html->find('.content-primary div article') as $row){
					 
				   if(!empty($row->find('.content h1 .field-items .field-item span a',0)->href)) {
					   $blogs[$n]['url'] = $row->find('.content h1 .field-items .field-item span a',0)->href;
				   }
				   else $blogs[$n]['url'] = '';
				   
				   if(!empty($row->find('div div .field-items .field-item figure img',0)->src)) {
					   $blogs[$n]['news_logo'] = $row->find('div div .field-items .field-item figure img',0)->src;
				   }
				   else $blogs[$n]['news_logo'] = '';
				   
				   if(!empty($row->find('.content h1',0)->plaintext)){
					  $blogs[$n]['headline'] = $row->find('.content h1',0)->plaintext;
				   }
				   else $blogs[$n]['headline'] = '';
				   
				   if(!empty($row->find('div div div .field-items .field-item',0)->innertext)){
					 $blogs[$n]['subheadline'] = $row->find('div div div .field-items .field-item',0)->plaintext;
				   }
				   else $blogs[$n]['subheadline']  = '';
				   
				   if(!empty($row->find('.article-byline .article-author-list',1)->plaintext)){
					   $blogs[$n]['datetime'] = $row->find('.article-byline .article-author-list',1)->plaintext;
				   }
				   else $blogs[$n]['datetime'] = '';
				   
				   if(!empty( $row->find('.article-byline .article-author-list',0)->plaintext)) {
						  $blogs[$n]['author'] = $row->find('.article-byline .article-author-list',0)->plaintext;
				   }
				   else $blogs[$n]['author'] = '';
				  			   
				   if(!empty($row->find('.content .snews-article__post-content-meta 
					  .category-list-inline',0)->plaintext)){
					  $blogs[$n]['tag'] = $row->find('.content .snews-article__post-content-meta 
					  .category-list-inline',0)->plaintext;
				   }
				   else $blogs[$n]['tag'] = '';
				   
				   if(!empty($blogs[$n]['url'])) $html_body = $html = file_get_html('http://news.sciencemag.org'.
				   $blogs[$n]['url']);
				   else $html_body = '';
				   
				   if(!empty($html_body)){
					   
					   if(!empty($html_body->find('.snews-article__article-body--full-text .field-items',0)->innertext)){
						   $blogs[$n]['articlebody'] = $html_body->find('.snews-article__article-body--full-text 
						   .field-items',0)->innertext;
					   }
					   else $blogs[$n]['articlebody'] = '';
					   
				   }
				   else {

					   $blogs[$n]['articlebody'] = '';	 
				   }
				  
				   $records = array(
							  'category' => $url,
							  'headline' => $this->trimData($blogs[$n]['headline']),
							  'subheadline' => $blogs[$n]['subheadline'],
							  'author' => $blogs[$n]['author'],
							  'datetime' => $blogs[$n]['datetime'],
							  'articlebody' => $blogs[$n]['articlebody'],
							  'tags' => $blogs[$n]['tag'],
							  'news_logo' => $blogs[$n]['news_logo'],
							  'url' => 'http://news.sciencemag.org'.$blogs[$n]['url']
				   );
				  //store data
				  $this->db->insert($this->crawler_table, $records);

				   $n++;
				   
				  // break;
					 
				 }
				 
				 $html->clear;
				 
				 $this->rowInserted = $i;
			  
			  }
		  break;
		  
		  case 8:
		  
		  for ($i=1; $i<=$max_page;$i++){
				
				$html = file_get_html($url.'page/'.$i.'/');
				
				if(!empty($html)){
				   
				   $n=0;
				   
				   foreach ($html->find('#wrapper #content .blog-posts .post') as $row){
					   
					 if(!empty($row->find('h3 a',0)->href)) {
						 $blogs[$n]['url'] = $row->find('h3 a',0)->href;
					 }
					 else $blogs[$n]['url'] = '';
					 
					
					 
					 if(!empty($row->find('h3 a',0)->plaintext)){
					    $blogs[$n]['headline'] = $row->find('h3 a',0)->plaintext;
					 }
					 else $blogs[$n]['headline'] = '';
					 
					 if(!empty($row->find('p',0)->innertext)){
					   $blogs[$n]['subheadline'] = $row->find('p',0)->innertext;
					 }
					 else $blogs[$n]['subheadline']  = '';
					 
					 if(!empty($row->find('.postmeta .postmeta-date',0)->plaintext)){
						 $blogs[$n]['datetime'] = $row->find('.postmeta .postmeta-date',0)->plaintext;
					 }
					 else $blogs[$n]['datetime'] = '';
					 
					 if(!empty($row->find('.postmeta li',0)->plaintext)) {
							$blogs[$n]['author'] = $row->find('.postmeta li',0)->plaintext;
					 }
					 else $blogs[$n]['author'] = '';
					 
					 $tags = array();
					 foreach ($row->find('.postnav em a') as $tagLists) {
						 
						 $tags[] = $tagLists->plaintext; 
					 }
					   
					 if(isset($tags[0])){
					    $blogs[$n]['tag'] = implode(',', $tags);
					 }
					 else $blogs[$n]['tag'] = '';
					 
					 if(!empty($blogs[$n]['url'])) $html_body = $html = file_get_html($blogs[$n]['url']);
					 else $html_body = '';
					 
					 if(!empty($html_body)){
						 
						 if(!empty($html_body->find('.entry',0)->innertext)){
							 $blogs[$n]['articlebody'] = $html_body->find('.entry',0)->innertext;
						 }
						 else $blogs[$n]['articlebody'] = '';
						 
						/*if($html_body->find('.entry .wp-caption img',0)->src) {
						 $blogs[$n]['news_logo'] = $html_body->find('.entry .wp-caption img',0)->src;
					    }
					    else*/ $blogs[$n]['news_logo'] = '';
						
						$html_body->clear;
						 
					 }
					 else {

						 $blogs[$n]['articlebody'] = '';	 
					 }
					
					 $records = array(
					            'category' => $url,
								'headline' => $this->trimData($blogs[$n]['headline']),
								'subheadline' => $blogs[$n]['subheadline'],
								'author' => $blogs[$n]['author'],
								'datetime' => $blogs[$n]['datetime'],
								'articlebody' => $blogs[$n]['articlebody'],
								'tags' => '',
								'news_logo' => $blogs[$n]['news_logo'],
								'url' => $blogs[$n]['url']
					 );
					//store data
					$this->db->insert($this->crawler_table, $records);

					 $n++;
					 
					// break;
					   
				   }
				   
				   $html->clear;
				   
				   $this->rowInserted = $i;
				
				}
		  }
		  break; 
		  
		  case 9://http://www.technologyreview.com/stream/
		  
		  for ($i=1; $i<=$max_page;$i++){
				
				$html = file_get_html($url.'magazine/news/518796/view/519831/news/519791/featuredstory/403319/contributor/contact/magazine/archive/stream/view/519866/stream /stream/contact/submit/lists/innovators-under-35/page/'.$i.'/?sort=recent');
				
				if(!empty($html)){
				   
				   $n=0;
				   
				   foreach ($html->find('.stream-container .stream .stream li') as $row){
					   
					 if(!empty($row->find('article .meta h1 a',0)->href)) {
						 $blogs[$n]['url'] = $url.$row->find('article .meta h1 a',0)->href;
					 }
					 else $blogs[$n]['url'] = '';
					 
					 if($html->find('article .offix .image img',0)->src) {
						 $blogs[$n]['news_logo'] = $html->find('article .offix .image img',0)->src;
					 }
					 else $blogs[$n]['news_logo'] = '';

					 if(!empty($row->find('article .meta h1 a',0)->plaintext)){
					    $blogs[$n]['headline'] = $row->find('article .meta h1 a',0)->plaintext;
					 }
					 else $blogs[$n]['headline'] = '';
					 
					 if(!empty($row->find('p',0)->innertext)){
					   $blogs[$n]['subheadline'] = $row->find('p',0)->innertext;
					 }
					 else $blogs[$n]['subheadline']  = '';
					 
					 if(!empty($row->find('article .offix .byline time',0)->plaintext)){
						 $blogs[$n]['datetime'] = $row->find('article .offix .byline time',0)->plaintext;
					 }
					 else $blogs[$n]['datetime'] = '';
					 
					 if(!empty($row->find('article .offix .byline .name',0)->plaintext)) {
							$blogs[$n]['author'] = $row->find('article .offix .byline .name',0)->plaintext;
					 }
					 else $blogs[$n]['author'] = '';
					 
					 $tags = array();
					 foreach ($row->find('.postnav em a') as $tagLists) {
						 
						 $tags[] = $tagLists->plaintext; 
					 }
					   
					 if(isset($tags[0])){
					    $blogs[$n]['tag'] = implode(',', $tags);
					 }
					 else $blogs[$n]['tag'] = '';
					 
					 if(!empty($blogs[$n]['url'])) $html_body = $html = file_get_html($blogs[$n]['url']);
					 else $html_body = '';
					 
					 if(!empty($html_body)){
						 
						 if(!empty($html_body->find('#main-article .article-meta',0)->innertext)){
							 $blogs[$n]['articlebodyheader'] = $html_body->find('#main-article .article-meta',0)->innertext;
						 }
						 else $blogs[$n]['articlebodyheader'] = '';
						 
						 if(!empty($html_body->find('#main-article .body',0)->innertext)){
							 $blogs[$n]['articlebody'] = $blogs[$n]['articlebodyheader'].' 
							 '.$html_body->find('#main-article .body',0)->innertext;
						 }
						 else $blogs[$n]['articlebody'] = '';
						 
						  if(!empty($html_body->find('#authors .credits',0)->innertext)){
							 $blogs[$n]['tag'] = $html_body->find('#authors .credits',0)->innertext;
						 }
						 else $blogs[$n]['tag'] = '';
						
						$html_body->clear;
						 
					 }
					 else {

						 $blogs[$n]['articlebody'] = '';
						 $blogs[$n]['tag'] = '';
						 $blogs[$n]['articlebodyheader'] = '';	 
					 }
					
					 $records = array(
					            'category' => $url,
								'headline' => $this->trimData($blogs[$n]['headline']),
								'subheadline' => $blogs[$n]['subheadline'],
								'author' => $blogs[$n]['author'],
								'datetime' => $blogs[$n]['datetime'],
								'articlebody' => $blogs[$n]['articlebody'],
								'tags' => '',
								'news_logo' => $blogs[$n]['news_logo'],
								'url' => $blogs[$n]['url']
					 );
					//store data
					$this->db->insert($this->crawler_table, $records);

					 $n++;
					 
					// break;
					   
				   }
				   
				   $html->clear;
				   
				   $this->rowInserted = $i;
				
				}
		  }
		  break;
		  
		  case 10;
		  
		        $html = file_get_html($url);
				if(!empty($html)){
				   
				   $n=0;
				   
				   foreach ($html->find('.view-content .views-row') as $row){
					   
					 if(!empty($row->find('.views-field-title h2 a',0)->href)) {
						 $blogs[$n]['url'] = $row->find('.views-field-title h2 a',0)->href;
					 }
					 else $blogs[$n]['url'] = '';
					 
					 if(!empty($row->find('.views-field-title h2',0)->plaintext)){
					    $blogs[$n]['headline'] = $row->find('.views-field-title h2',0)->plaintext;
					 }
					 else $blogs[$n]['headline'] = '';
					 
					 if(!empty($row->find('.views-field-field-body',0)->innertext)){
					   $blogs[$n]['subheadline'] = $row->find('.views-field-field-body',0)->plaintext;
					 }
					 else $blogs[$n]['subheadline']  = '';
					 
					 if(!empty($row->find('.views-field-nothing .author .date-display-single',0)->plaintext)){
						 $blogs[$n]['datetime'] = $row->find('.views-field-nothing .author .date-display-single',0)->plaintext;
					 }
					 else $blogs[$n]['datetime'] = '';
					 
					 if(!empty( $row->find('.views-field-nothing .author strong',0)->plaintext)) {
							$blogs[$n]['author'] = $row->find('.views-field-nothing .author strong',0)->plaintext;
					 }
					 else $blogs[$n]['author'] = '';
					 
					 $tags = array();
					 $j = 0;
					 foreach ($row->find('.content .node-article p') as $tagLists) {
						 
						 if($j==2){
							 foreach ($row->find('a') as $tagLists1) {
							   $tags[] = $tagLists1->plaintext;
							 }
						 }
						 $j++;
					 }
					   
					 if(isset($tags[0]) && $tags[0]){
					    $blogs[$n]['tag'] = implode(',', $tags);
					 }
					 else $blogs[$n]['tag'] = '';
					 
					 if(!empty($blogs[$n]['url'])) $html_body = $html = file_get_html($url.$blogs[$n]['url']);
					 else $html_body = '';
					 
					 if(!empty($html_body)){
						 
						 if(!empty($html_body->find('.content .article-content',0)->innertext)){
							 $blogs[$n]['articlebody'] = $html_body->find('.content .article-content',0)->innertext;
						 }
						 else $blogs[$n]['articlebody'] = '';
						 
						 if(!empty($html_body->find('.content .article-teaser',0)->innertext)){
							 $blogs[$n]['articlebodyheader'] = $html_body->find('.content .article-teaser',0)->innertext;
						 }
						 else $blogs[$n]['articlebodyheader'] = '';
						 
						/*if($html_body->find('.entry .wp-caption img',0)->src) {
						 $blogs[$n]['news_logo'] = $html_body->find('.entry .wp-caption img',0)->src;
					    }
					    else*/ $blogs[$n]['news_logo'] = '';
						
						$html_body->clear;
						 
					 }
					 else {

						 $blogs[$n]['articlebody'] = '';
						 $blogs[$n]['articlebodyheader'] = '';	 
					 }
					
					 $records = array(
					            'category' => $url,
								'headline' => $this->trimData($blogs[$n]['headline']),
								'subheadline' => $blogs[$n]['subheadline'],
								'author' => $blogs[$n]['author'],
								'datetime' => $blogs[$n]['datetime'],
								'articlebody' => $blogs[$n]['articlebodyheader'].' <br>'.$blogs[$n]['articlebody'],
								'tags' => '',
								'news_logo' => $blogs[$n]['news_logo'],
								'url' => $blogs[$n]['url']
					 );
					//store data
					$this->db->insert($this->crawler_table, $records);

					 $n++;
					 
					//break;
					   
				   }
				   
				   $html->clear;
				   
				   $this->rowInserted = $n;
				
				}
				
		  break;
		  
		  case 11://http://www.businessweek.com/global-economics
		  
				
			  $html = file_get_html($url);
			  
			  if(!empty($html)){
				 
				 $n=0;
				
				 foreach ($html->find('.tab_panel ul li') as $row){
					 
				   if(!empty($row->find('h5 a',0)->href)) {
					   $blogs[$n]['url'] = $row->find('h5 a',0)->href;
				   }
				   else $blogs[$n]['url'] = '';
				   
				   if(!empty($row->find('a img',0)->src)) {
					   $blogs[$n]['news_logo'] = $row->find('a img',0)->src;
				   }
				   else $blogs[$n]['news_logo'] = '';

				   if(!empty($row->find('h5 a',0)->plaintext)){
					  $blogs[$n]['headline'] = $row->find('h5 a',0)->plaintext;
				   }
				   else $blogs[$n]['headline'] = '';
				   
				   if(!empty($row->plaintext)){
					 $blogs[$n]['subheadline'] = $row->plaintext;
				   }
				   else $blogs[$n]['subheadline']  = '';
				   
                    if(!empty( $row->find('h6 a',0)->plaintext)) {
						  $blogs[$n]['tag'] = $row->find('h6 a',0)->plaintext;
				   }
				   else $blogs[$n]['tag'] = '';
				   
				   if(!empty($blogs[$n]['url'])) $html_body = $html = file_get_html($blogs[$n]['url']);
				   else $html_body = '';
				   
				   if(!empty($html_body)){
					   
					   if(!empty($html_body->find('.article_body',0)->plaintext)){
						   $blogs[$n]['articlebody'] = $html_body->find('.article_body',0)->plaintext;
					   }
					   else $blogs[$n]['articlebody'] = '';
					   
					   if(!empty( $html_body->find('#authorial',0)->plaintext)) {
							  $blogs[$n]['author'] = $html_body->find('#authorial',0)->plaintext;
					   }
					   else $blogs[$n]['author'] = '';
					   
					  if(!empty($html_body->find('#authorial time',0)->plaintext)){
						   
						   $blogs[$n]['datetime'] = $html_body->find('#authorial time',0)->innertext;
					   }
					   else $blogs[$n]['datetime'] = '';
					  
					  $html_body->clear;
					   
				   }
				   else {

					   $blogs[$n]['articlebody'] = '';
					   $blogs[$n]['author'] = '';
					   $blogs[$n]['datetime'] = '';
					 
				   }
				  
				   $records = array(
							  'category' => $url,
							  'headline' => $this->trimData($blogs[$n]['headline']),
							  'subheadline' => $blogs[$n]['subheadline'],
							  'author' => $blogs[$n]['author'],
							  'datetime' => $blogs[$n]['datetime'],
							  'articlebody' => $blogs[$n]['articlebody'],
							  'tags' => $blogs[$n]['tag'],
							  'news_logo' => $blogs[$n]['news_logo'],
							  'url' => $blogs[$n]['url']
				   );
				  //store data
				  $this->db->insert($this->crawler_table, $records);

				   $n++;
				   
				  // break;
					 
				 }
				 
				 $html->clear;
				 
				 $this->rowInserted = $n;  
			  }
		   break; 
		  
		  
		  case 12://http://www.forbes.com/real-time/
		  
				
			  $html = file_get_html($url);
			  
			  if(!empty($html)){
				 
				 $n=0;
				
				 foreach ($html->find('.stream_holder .stream_contents .stream_content li') as $row){
					 
				   if(!empty($row->find('.item_wrapper article h2 a',0)->href)) {
					   $blogs[$n]['url'] = $row->find('.item_wrapper article h2 a',0)->href;
				   }
				   else $blogs[$n]['url'] = '';
				   
				   if(!empty($row->find('.item_wrapper article .img_pull a img',0)->src)) {
					   $blogs[$n]['news_logo'] = $row->find('.item_wrapper article .img_pull a img',0)->src;
				   }
				   else $blogs[$n]['news_logo'] = '';

				   if(!empty($row->find('.item_wrapper article h2 a',0)->plaintext)){
					  $blogs[$n]['headline'] = $row->find('.item_wrapper article h2 a',0)->plaintext;
				   }
				   else $blogs[$n]['headline'] = '';
				   
				   if(!empty($row->find('.item_wrapper article p',0)->innertext)){
					 $blogs[$n]['subheadline'] = $row->find('.item_wrapper article p',0)->plaintext;
				   }
				   else $blogs[$n]['subheadline']  = '';
				   
				   if(!empty($row->find('.item_wrapper h6 i',0)->plaintext)){
					   
					   $blogs[$n]['datetime'] = $row->find('.item_wrapper h6 i',0)->innertext;
				   }
				   else $blogs[$n]['datetime'] = '';
				   
				   if(!empty( $row->find('.item_wrapper article cite span a',0)->plaintext)) {
						  $blogs[$n]['author'] = $row->find('.item_wrapper article cite span a',0)->plaintext;
				   }
				   else $blogs[$n]['author'] = '';
				   
                   $blogs[$n]['tag'] = '';
				   
				   if(!empty($blogs[$n]['url'])) $html_body = $html = file_get_html($blogs[$n]['url']);
				   else $html_body = '';
				   
				   if(!empty($html_body)){
					   
					   if(!empty($html_body->find('.article_body .body .body_inner',0)->innertext)){
						   $blogs[$n]['articlebody'] = $html_body->find('.article_body .body .body_inner',0)->innertext;
					   }
					   else $blogs[$n]['articlebody'] = '';
					  
					  $html_body->clear;
					   
				   }
				   else {

					   $blogs[$n]['articlebody'] = '';
					 
				   }
				  
				   $records = array(
							  'category' => $url,
							  'headline' => $this->trimData($blogs[$n]['headline']),
							  'subheadline' => $blogs[$n]['subheadline'],
							  'author' => $blogs[$n]['author'],
							  'datetime' => $blogs[$n]['datetime'],
							  'articlebody' => $blogs[$n]['articlebody'],
							  'tags' => $blogs[$n]['tag'],
							  'news_logo' => $blogs[$n]['news_logo'],
							  'url' => $blogs[$n]['url']
				   );
				  //store data
				  $this->db->insert($this->crawler_table, $records);

				   $n++;
				   
				  // break;
					 
				 }
				 
				 $html->clear;
				 
				 $this->rowInserted = $n;  
		}
		  break; 
		  
		  case 14:
		  
			for ($i=1; $i<=$max_page;$i++){
				
				  $html = file_get_html($url.'?page='.$i.'/');
				  
				  if(!empty($html)){
					 
					 $n=0;
					 
					 foreach ($html->find('#contentColumn #content-below-right .block .node-article') as $row){
						 
					   if(!empty($row->find('h2 a',0)->href)) {
						   $blogs[$n]['url'] = $row->find('h2 a',0)->href;
					   }
					   else $blogs[$n]['url'] = '';
					   
					   if(!empty($row->find('.content .teaser-image',0)->src)) {
						   $blogs[$n]['news_logo'] = $row->find('.content .teaser-image',0)->src;
					   }
					   else $blogs[$n]['news_logo'] = '';
  
					   if(!empty($row->find('h2 a',0)->plaintext)){
						  $blogs[$n]['headline'] = $row->find('h2 a',0)->plaintext;
					   }
					   else $blogs[$n]['headline'] = '';
					   
					   if(!empty($row->find('.content p',0)->innertext)){
						 $blogs[$n]['subheadline'] = $row->find('.content p',0)->plaintext;
					   }
					   else $blogs[$n]['subheadline']  = '';
					   
					   if(!empty($row->find('.meta',0)->plaintext)){
						   
						   $blogs[$n]['datetime'] = $row->find('.meta',0)->innertext;
						   $blogs[$n]['datetime'] = preg_replace('~<a(.*?)</a>~Usi', "", $blogs[$n]['datetime']);
						   $blogs[$n]['datetime'] = str_replace('By' , '', $blogs[$n]['datetime']);
						   $blogs[$n]['datetime'] = str_replace('in' , '', $blogs[$n]['datetime']);
					   }
					   else $blogs[$n]['datetime'] = '';
					   
					   if(!empty( $row->find('.meta a',0)->plaintext)) {
							  $blogs[$n]['author'] = $row->find('.meta a',0)->plaintext;
					   }
					   else $blogs[$n]['author'] = '';
					   
					   $tags = array();

					   foreach ($row->find('.postnav em a') as $tagLists) {
						   
						   $tags[] = $tagLists->plaintext; 
					   }
	
					   if(isset($tags[0]) && $tags[0]){
						  $blogs[$n]['tag'] = implode(',', $tags);
					   }
					   else $blogs[$n]['tag'] = '';
					   
					   if(!empty($blogs[$n]['url'])) $html_body = $html = file_get_html($url.$blogs[$n]['url']);
					   else $html_body = '';
					   
					   if(!empty($html_body)){
						   
						   if(!empty($html_body->find('.content .article-content-top',0)->innertext)){
							   $blogs[$n]['articlebody'] = $html_body->find('.content .article-content-top',0)->innertext;
						   }
						   else $blogs[$n]['articlebody'] = '';
						   
						  if(!empty($html_body->find('.content .inline-content-bottom-right',0)->innertext)) {
						   $blogs[$n]['articlebodybottom'] = $html_body->find('.content 
						   .inline-content-bottom-right',0)->innertext;
						  }
						  else $blogs[$n]['articlebodybottom'] = '';
						  
						  $html_body->clear;
						   
					   }
					   else {
  
						   $blogs[$n]['articlebody'] = '';
							$blogs[$n]['articlebodybottom'] = '';	 
					   }
					  
					   $records = array(
								  'category' => $url,
								  'headline' => $this->trimData($blogs[$n]['headline']),
								  'subheadline' => $blogs[$n]['subheadline'],
								  'author' => $blogs[$n]['author'],
								  'datetime' => $blogs[$n]['datetime'],
								  'articlebody' => $blogs[$n]['articlebodybottom'].$blogs[$n]['articlebody'],
								  'tags' => $blogs[$n]['tag'],
								  'news_logo' => $blogs[$n]['news_logo'],
								  'url' => $blogs[$n]['url']
					   );
					  //store data
					  $this->db->insert($this->crawler_table, $records);
  
					   $n++;
					   
					  // break;
						 
					 }
					 
					 $html->clear;
					 
					 $this->rowInserted = $i;  
			}
			}
		  break; 
		  
		  case 15://http://www.theguardian.com/law
				
			$html = file_get_html($url);
			
			if(!empty($html)){
			   
			   $n=0;
			   
			   foreach ($html->find('.auto-trail-list ul li') as $row){
				   
				 if(!empty($row->find('h3 a',0)->href)) {
					 $blogs[$n]['url'] = $row->find('h3 a',0)->href;
				 }
				 else $blogs[$n]['url'] = '';
				 
				 if(!empty($row->find('#content .article-wrapper figure img',0)->src)) {
					 $blogs[$n]['news_logo'] = $row->find('#content .article-wrapper figure img',0)->src;
				 }
				 else $blogs[$n]['news_logo'] = '';

				 if(!empty($row->find('h3 a',0)->plaintext)){
					$blogs[$n]['headline'] = $row->find('h3 a',0)->plaintext;
				 }
				 else $blogs[$n]['headline'] = '';
				 
				 if(!empty($row->find('.trail-text',0)->plaintext)){
				   $blogs[$n]['subheadline'] = $row->find('.trail-text',0)->plaintext;
				 }
				 else $blogs[$n]['subheadline']  = '';
				 
				 if(!empty($row->find('.strap',0)->plaintext)){
					 
					 $blogs[$n]['datetime'] = $row->find('.strap',0)->innertext;
					 $blogs[$n]['datetime'] = preg_replace('~<a(.*?)</a>~Usi', "", $blogs[$n]['datetime']);
				 }
				 else $blogs[$n]['datetime'] = '';

                 $blogs[$n]['tag'] = '';
				 
				 if(!empty($blogs[$n]['url'])) $html_body = $html = file_get_html($blogs[$n]['url']);
				 else $html_body = '';
				 
				 if(!empty($html_body)){
					 
				 if(!empty($html_body->find('#main-article-info',0)->innertext)) {
					 $blogs[$n]['articlebodytop'] = $html_body->find('#main-article-info',0)->innertext;
					}
					else $blogs[$n]['articlebodytop'] = '';
					 
					 if(!empty($html_body->find('#content',0)->innertext)){
						 $blogs[$n]['articlebody'] = $blogs[$n]['articlebodytop'].' '.$html_body->find('#content',0)->innertext;
					 }
					 else $blogs[$n]['articlebody'] = '';
					 
					 if(!empty( $row->find('.contributor a',0)->plaintext)) {
						$blogs[$n]['author'] = $row->find('.contributor a',0)->plaintext;
					 }
					 else $blogs[$n]['author'] = '';

					$html_body->clear;
					 
				 }
				 else {

					 $blogs[$n]['articlebody'] = '';
					  $blogs[$n]['articlebodybottom'] = '';	 
				 }
				
				 $records = array(
							'category' => $url,
							'headline' => $this->trimData($blogs[$n]['headline']),
							'subheadline' => $blogs[$n]['subheadline'],
							'author' => $blogs[$n]['author'],
							'datetime' => $blogs[$n]['datetime'],
							'articlebody' => $blogs[$n]['articlebodybottom'].$blogs[$n]['articlebody'],
							'tags' => $blogs[$n]['tag'],
							'news_logo' => $blogs[$n]['news_logo'],
							'url' => $blogs[$n]['url']
				 );
				//store data
				$this->db->insert($this->crawler_table, $records);

				 $n++;
				 
				// break;
				   
			   }
			   
			   $html->clear;
			   
			   $this->rowInserted = $i;  
	  }
		  break; 
		  
		  case 16://'http://governancejournal.net/'
		  
			for ($i=1; $i<=$max_page;$i++){
				
				  if($i==1) $url = $url;
				  else $url.'?page='.$i.'/';
				  
				  $html = file_get_html($url);
				  
				  if(!empty($html)){
					 
					 $n=0;
					 
					 foreach ($html->find('#content .post') as $row){
						 
					   if(!empty($row->find('h2 a',0)->href)) {
						   $blogs[$n]['url'] = $row->find('h2 a',0)->href;
					   }
					   else $blogs[$n]['url'] = '';
					   
					   if(!empty($row->find('.main img',0)->src)) {
						   $blogs[$n]['news_logo'] = $row->find('.main img',0)->src;
					   }
					   else $blogs[$n]['news_logo'] = '';
  
					   if(!empty($row->find('h2 a',0)->plaintext)){
						  $blogs[$n]['headline'] = $row->find('h2 a',0)->plaintext;
					   }
					   else $blogs[$n]['headline'] = '';
					   
					   if(!empty($row->find('.main',0)->innertext)){
						 $blogs[$n]['subheadline'] = $row->find('.main',0)->innertext;
					   }
					   else $blogs[$n]['subheadline']  = '';
					   
					   if(!empty($row->find('.meta .signature p',1)->plaintext)){
						   
						   $blogs[$n]['datetime'] = $row->find('.meta .signature p',1)->plaintext;
					   }
					   else $blogs[$n]['datetime'] = '';
					   
					   if(!empty( $row->find('.meta .signature p',0)->plaintext)) {
							  $blogs[$n]['author'] = $row->find('.meta .signature p',0)->plaintext;
					   }
					   else $blogs[$n]['author'] = '';
					   
					   if(!empty($row->find('.meta .tags a',0)->plaintext)){
						  $blogs[$n]['tag'] = $row->find('.meta .tags a',0)->plaintext;
					   }
					   else $blogs[$n]['tag'] = '';
  
					   if(!empty($row->find('.main',0)->innertext)){
						   $blogs[$n]['articlebody'] = $row->find('.main',0)->innertext;
					   }
					   else $blogs[$n]['articlebody'] = '';
  
					  
					   $records = array(
								  'category' => $url,
								  'headline' => $this->trimData($blogs[$n]['headline']),
								  'subheadline' => $blogs[$n]['subheadline'],
								  'author' => $blogs[$n]['author'],
								  'datetime' => $blogs[$n]['datetime'],
								  'articlebody' => $blogs[$n]['articlebody'],
								  'tags' => $blogs[$n]['tag'],
								  'news_logo' => $blogs[$n]['news_logo'],
								  'url' => $blogs[$n]['url']
					   );
					  //store data
					  $this->db->insert($this->crawler_table, $records);
  
					   $n++;
					   
					  // break;
						 
					 }
					 
					 $html->clear;
					 
					 $this->rowInserted = $i;  
			}
			}
		  break;
		  
		   case 17://http://www.infomoney.com.br/ultimas-noticias
		  
			for ($i=1; $i<=$max_page;$i++){
				
				  if($i==1) $url = $url;
				  else $url.'pagina/'.$i.'/';
				  
				  $html = file_get_html($url);
				  
				  if(!empty($html)){
					 
					 $n=0;
					 
					 foreach ($html->find('#pnlContent .infomoney .id-607 .id-737 .last-news .artigo-ilustrado') as $row){
						
						
					   if(!empty($row->find('.correcaoie7 a',0)->href)) {
						   $blogs[$n]['url'] = 'http://www.infomoney.com.br/'.$row->find('.correcaoie7 a',0)->href;
					   }
					   else $blogs[$n]['url'] = '';

					   if(!empty($row->find('figure a img',0)->src)) {
						   $blogs[$n]['news_logo'] = $row->find('figure a img',0)->src;
					   }
					   else $blogs[$n]['news_logo'] = '';
  
					   if(!empty($row->find('.correcaoie7 a',0)->plaintext)){
						  $blogs[$n]['headline'] = $row->find('.correcaoie7 a',0)->plaintext;
					   }
					   else $blogs[$n]['headline'] = '';
					   
					   if(!empty($row->find('p',1)->innertext)){
						 $blogs[$n]['subheadline'] = $row->find('p',1)->innertext;
					   }
					   else $blogs[$n]['subheadline']  = '';
					   
					   if(!empty($row->find('.headline-new time',1)->plaintext)){
						   
						   $blogs[$n]['datetime'] = $row->find('.headline-new time',1)->plaintext;
						   $blogs[$n]['datetime'] = preg_replace('~<time(.*?)</time>~Usi', "", $blogs[$n]['datetime']);
					   }
					   else $blogs[$n]['datetime'] = '';
					   
					   if(!empty( $row->find('.headline-new time',0)->plaintext)) {
							  $blogs[$n]['author'] = $row->find('.headline-new time',0)->plaintext;
					   }
					   else $blogs[$n]['author'] = '';
					   
					   /*if(!empty($row->find('.meta .tags a',0)->plaintext)){
						  $blogs[$n]['tag'] = $row->find('.meta .tags a',0)->plaintext;
					   }
					   else*/ 
					   $blogs[$n]['tag'] = '';
  
					   if(!empty($row->find('.main',0)->innertext)){
						   $blogs[$n]['articlebody'] = $row->find('.main',0)->innertext;
					   }
					   else $blogs[$n]['articlebody'] = '';
					   
					   if(!empty($blogs[$n]['url'])) $html_body = $html = file_get_html($blogs[$n]['url']);
					   else $html_body = '';
					   
					   if(!empty($html_body)){
						   
						   if(!empty($html_body->find('#divNews #conteudo-artigo #contentNews',0)->innertext)){
							   $blogs[$n]['articlebody'] = $html_body->find('#divNews #conteudo-artigo
							    #contentNews',0)->innertext;
						   }
						   else $blogs[$n]['articlebody'] = '';

						  $html_body->clear;
						   
					   }
					   else {
  
						   $blogs[$n]['articlebody'] = ''; 
					   }
  
					  
					   $records = array(
								  'category' => $url,
								  'headline' => $this->trimData($blogs[$n]['headline']),
								  'subheadline' => $blogs[$n]['subheadline'],
								  'author' => $blogs[$n]['author'],
								  'datetime' => $blogs[$n]['datetime'],
								  'articlebody' => $blogs[$n]['articlebody'],
								  'tags' => $blogs[$n]['tag'],
								  'news_logo' => $blogs[$n]['news_logo'],
								  'url' => $blogs[$n]['url']
					   );
					  //store data
					  $this->db->insert($this->crawler_table, $records);
  
					   $n++;
					   
					  // break;
						 
					 }
					 
					 $html->clear;
					 
					 $this->rowInserted = $i;  
			}
			}
		  break; 
		  
		  case 18://http://ftalphaville.ft.com/
		  
			for ($i=1; $i<=$max_page;$i++){
				
				  if($i==1) $url = $url;
				  else $url.'page/'.$i.'/';
				  
				  $html = file_get_html($url);
				  
				  if(!empty($html)){
					 
					 $n=0;
					 
					 foreach ($html->find('.inner .post') as $row){
						
						
					   if(!empty($row->find('.entry-header h1 a',0)->href)) {
						   $blogs[$n]['url'] = $row->find('.entry-header h1 a',0)->href;
					   }
					   else $blogs[$n]['url'] = '';

					   $blogs[$n]['news_logo'] = '';
  
					   if(!empty($row->find('.entry-header h1 a',0)->plaintext)){
						  $blogs[$n]['headline'] = $row->find('.entry-header h1 a',0)->plaintext;
					   }
					   else $blogs[$n]['headline'] = '';
					   
					   if(!empty($row->find('.entry-content',0)->plaintext)){
						 $blogs[$n]['subheadline'] = $row->find('.entry-content',0)->plaintext;
					   }
					   else $blogs[$n]['subheadline']  = '';
					   
					   if(!empty($row->find('.entry-header .entry-meta .entry-date',0)->plaintext)){
						   
						   $blogs[$n]['datetime'] = $row->find('.entry-header .entry-meta .entry-date',0)->plaintext;
					   }
					   else $blogs[$n]['datetime'] = '';
					   
					   if(!empty( $row->find('.entry-header .entry-meta strong',0)->plaintext)) {
							  $blogs[$n]['author'] = $row->find('.entry-header .entry-meta strong',0)->plaintext;
					   }
					   else $blogs[$n]['author'] = '';
					   
					    $base = $blogs[$n]['url'];
						$cookie = 'cookies.txt';
						$curl = curl_init();
						curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
						curl_setopt($curl, CURLOPT_HEADER, false);
						curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
						curl_setopt($curl, CURLOPT_URL, $base);
						curl_setopt($curl, CURLOPT_REFERER, $base);
						curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
						curl_setopt ($curl, CURLOPT_FOLLOWLOCATION, 1);
						curl_setopt ($curl, CURLOPT_POST, 1); 
						curl_setopt ($curl,CURLOPT_POSTFIELDS,"username=chamssoudinebacar@yahoo.fr&password=12345678");
						curl_setopt($curl, CURLOPT_COOKIEJAR,       realpath($cookie));
						curl_setopt($curl, CURLOPT_COOKIEFILE,      realpath($cookie));
						$str = curl_exec($curl);
						
						
						//curl_setopt ($curl, CURLOPT_POST, 0); 
						curl_setopt($curl, CURLOPT_URL, $blogs[$n]['url']);
						$str = curl_exec($curl);
					    $info = curl_getinfo($curl);
						
						curl_close($curl);
			/*			print '<pre>';
		print_r($str);*/
					   $blogs[$n]['tag'] = '';
					   
					   if(!empty($blogs[$n]['url'])) $html_body = str_get_html($str);
					   else $html_body = '';
					   
					   if(!empty($html_body)){ echo 100000;
						   
						   if(!empty($html_body->find('.entry-content',0)->plaintext)){
							   $blogs[$n]['articlebody'] = $html_body->find('.entry-content',0)->plaintext;
						   }
						   else $blogs[$n]['articlebody'] = '';
						  

						  $html_body->clear;
						   
					   }
					   else {
  
						   $blogs[$n]['articlebody'] = ''; 
						  
					   }
  
					  
					   $records = array(
								  'category' => $url,
								  'headline' => $this->trimData($blogs[$n]['headline']),
								  'subheadline' => $blogs[$n]['subheadline'],
								  'author' => $blogs[$n]['author'],
								  'datetime' => $blogs[$n]['datetime'],
								  'articlebody' => $blogs[$n]['articlebody'],
								  'tags' => $blogs[$n]['tag'],
								  'news_logo' => $blogs[$n]['news_logo'],
								  'url' => $blogs[$n]['url']
					   );
					  //store data
					  $this->db->insert($this->crawler_table, $records);
  
					   $n++;
					   
					   //break;
						 
					 }
					 
					 $html->clear;
					 
					 $this->rowInserted = $i;  
			}
			}
			
		  break; 
		  
		  case 19://http://www.technologyreview.com/business/stream/
		  
			for ($i=1; $i<=$max_page;$i++){
				
				  if($i==1) $url = $url;
				  else $url.'page/'.$i.'/?sort=recent';
				  
				  $html = file_get_html($url);
				  
				  if(!empty($html)){
					 
					 $n=0;
					 
					 foreach ($html->find('.stream-container .stream .stream li') as $row){
						
						
					   if(!empty($row->find('article .meta h1 a',0)->href)) {
						   $blogs[$n]['url'] = "http://www.technologyreview.com/".$row->find('article 
						   .meta h1 a',0)->href;
					   }
					   else $blogs[$n]['url'] = '';

					   if(!empty($row->find('article .image a img',0)->src)) {
						   $blogs[$n]['news_logo'] = $row->find('article .image a img',0)->src;
					   }
					   else $blogs[$n]['news_logo'] = '';
  
					   if(!empty($row->find('article .meta h1 a',0)->plaintext)){
						  $blogs[$n]['headline'] = $row->find('article .meta h1 a',0)->plaintext;
					   }
					   else $blogs[$n]['headline'] = '';
					   
					   if(!empty($row->find('article .offix .byline p',0)->innertext)){
						 $blogs[$n]['subheadline'] = $row->find('article .offix .byline p',0)->innertext;
					   }
					   else $blogs[$n]['subheadline']  = '';
					   
					   if(!empty($row->find('article .offix .byline time',0)->plaintext)){
						   
						   $blogs[$n]['datetime'] = $row->find('article .offix .byline time',0)->plaintext;
					   }
					   else $blogs[$n]['datetime'] = '';
					   
					   if(!empty( $row->find('article .offix .byline .name',0)->plaintext)) {
							  $blogs[$n]['author'] = $row->find('article .offix .byline .name',0)->plaintext;
					   }
					   else $blogs[$n]['author'] = '';
					   
					   
					   if(!empty($blogs[$n]['url'])) $html_body = $html = file_get_html($blogs[$n]['url']);
					   else $html_body = '';
					   
					   if(!empty($html_body)){
						   
						   if(!empty($html_body->find('#main-article .wrapper .body',0)->innertext)){
							   $blogs[$n]['articlebody'] = $html_body->find('#main-article .wrapper .body',0)->innertext;
						   }
						   else $blogs[$n]['articlebody'] = '';
						   
					   if(!empty($row->find('#authors .credits',1)->plaintext)){
						  $blogs[$n]['tag'] = $row->find('#authors .credits',0)->plaintext;
					   }
					   else  $blogs[$n]['tag'] = '';

						  $html_body->clear;
						   
					   }
					   else {
  
						   $blogs[$n]['articlebody'] = ''; 
						   $blogs[$n]['tag'] = '';
					   }
  
					  
					   $records = array(
								  'category' => $url,
								  'headline' => $this->trimData($blogs[$n]['headline']),
								  'subheadline' => $blogs[$n]['subheadline'],
								  'author' => $blogs[$n]['author'],
								  'datetime' => $blogs[$n]['datetime'],
								  'articlebody' => $blogs[$n]['articlebody'],
								  'tags' => $blogs[$n]['tag'],
								  'news_logo' => $blogs[$n]['news_logo'],
								  'url' => $blogs[$n]['url']
					   );
					  //store data
					  $this->db->insert($this->crawler_table, $records);
  
					   $n++;
					   
					  // break;
						 
					 }
					 
					 $html->clear;
					 
					 $this->rowInserted = $i;  
			}
			}
			
		  break;
		  
		  case 20://http://www.technologyreview.com/computing/stream/
		  
			for ($i=1; $i<=$max_page;$i++){
				
				  if($i==1) $url = $url;
				  else $url.'page/'.$i.'/?sort=recent';
				  
				  $html = file_get_html($url);
				  
				  if(!empty($html)){
					 
					 $n=0;
					 
					 foreach ($html->find('.stream-container .stream .stream li') as $row){
						
						
					   if(!empty($row->find('article .meta h1 a',0)->href)) {
						   $blogs[$n]['url'] = "http://www.technologyreview.com/".$row->find('article 
						   .meta h1 a',0)->href;
					   }
					   else $blogs[$n]['url'] = '';

					   if(!empty($row->find('article .image a img',0)->src)) {
						   $blogs[$n]['news_logo'] = $row->find('article .image a img',0)->src;
					   }
					   else $blogs[$n]['news_logo'] = '';
  
					   if(!empty($row->find('article .meta h1 a',0)->plaintext)){
						  $blogs[$n]['headline'] = $row->find('article .meta h1 a',0)->plaintext;
					   }
					   else $blogs[$n]['headline'] = '';
					   
					   if(!empty($row->find('article .offix .byline p',0)->innertext)){
						 $blogs[$n]['subheadline'] = $row->find('article .offix .byline p',0)->innertext;
					   }
					   else $blogs[$n]['subheadline']  = '';
					   
					   if(!empty($row->find('article .offix .byline time',0)->plaintext)){
						   
						   $blogs[$n]['datetime'] = $row->find('article .offix .byline time',0)->plaintext;
					   }
					   else $blogs[$n]['datetime'] = '';
					   
					   if(!empty( $row->find('article .offix .byline .name',0)->plaintext)) {
							  $blogs[$n]['author'] = $row->find('article .offix .byline .name',0)->plaintext;
					   }
					   else $blogs[$n]['author'] = '';
					   
					   
					   if(!empty($blogs[$n]['url'])) $html_body = $html = file_get_html($blogs[$n]['url']);
					   else $html_body = '';
					   
					   if(!empty($html_body)){
						   
						   if(!empty($html_body->find('#main-article .wrapper .body',0)->innertext)){
							   $blogs[$n]['articlebody'] = $html_body->find('#main-article .wrapper .body',0)->innertext;
						   }
						   else $blogs[$n]['articlebody'] = '';
						   
					   if(!empty($row->find('#authors .credits',1)->plaintext)){
						  $blogs[$n]['tag'] = $row->find('#authors .credits',0)->plaintext;
					   }
					   else  $blogs[$n]['tag'] = '';

						  $html_body->clear;
						   
					   }
					   else {
  
						   $blogs[$n]['articlebody'] = ''; 
						   $blogs[$n]['tag'] = '';
					   }
  
					  
					   $records = array(
								  'category' => $url,
								  'headline' => $this->trimData($blogs[$n]['headline']),
								  'subheadline' => $blogs[$n]['subheadline'],
								  'author' => $blogs[$n]['author'],
								  'datetime' => $blogs[$n]['datetime'],
								  'articlebody' => $blogs[$n]['articlebody'],
								  'tags' => $blogs[$n]['tag'],
								  'news_logo' => $blogs[$n]['news_logo'],
								  'url' => $blogs[$n]['url']
					   );
					  //store data
					  $this->db->insert($this->crawler_table, $records);
  
					   $n++;
					   
					  // break;
						 
					 }
					 
					 $html->clear;
					 
					 $this->rowInserted = $i;  
			}
			}
			
		  break; 
		  
		  case 21://http://www.technologyreview.com/energy/stream/
		  
			for ($i=1; $i<=$max_page;$i++){
				
				  if($i==1) $url = $url;
				  else $url.'page/'.$i.'/?sort=recent';
				  
				  $html = file_get_html($url);
				  
				  if(!empty($html)){
					 
					 $n=0;
					 
					 foreach ($html->find('.stream-container .stream .stream li') as $row){
						
						
					   if(!empty($row->find('article .meta h1 a',0)->href)) {
						   $blogs[$n]['url'] = "http://www.technologyreview.com/".$row->find('article 
						   .meta h1 a',0)->href;
					   }
					   else $blogs[$n]['url'] = '';

					   if(!empty($row->find('article .image a img',0)->src)) {
						   $blogs[$n]['news_logo'] = $row->find('article .image a img',0)->src;
					   }
					   else $blogs[$n]['news_logo'] = '';
  
					   if(!empty($row->find('article .meta h1 a',0)->plaintext)){
						  $blogs[$n]['headline'] = $row->find('article .meta h1 a',0)->plaintext;
					   }
					   else $blogs[$n]['headline'] = '';
					   
					   if(!empty($row->find('article .offix .byline p',0)->innertext)){
						 $blogs[$n]['subheadline'] = $row->find('article .offix .byline p',0)->innertext;
					   }
					   else $blogs[$n]['subheadline']  = '';
					   
					   if(!empty($row->find('article .offix .byline time',0)->plaintext)){
						   
						   $blogs[$n]['datetime'] = $row->find('article .offix .byline time',0)->plaintext;
					   }
					   else $blogs[$n]['datetime'] = '';
					   
					   if(!empty( $row->find('article .offix .byline .name',0)->plaintext)) {
							  $blogs[$n]['author'] = $row->find('article .offix .byline .name',0)->plaintext;
					   }
					   else $blogs[$n]['author'] = '';
					   
					   
					   if(!empty($blogs[$n]['url'])) $html_body = $html = file_get_html($blogs[$n]['url']);
					   else $html_body = '';
					   
					   if(!empty($html_body)){
						   
						   if(!empty($html_body->find('#main-article .wrapper .body',0)->innertext)){
							   $blogs[$n]['articlebody'] = $html_body->find('#main-article .wrapper .body',0)->innertext;
						   }
						   else $blogs[$n]['articlebody'] = '';
						   
					   if(!empty($row->find('#authors .credits',1)->plaintext)){
						  $blogs[$n]['tag'] = $row->find('#authors .credits',0)->plaintext;
					   }
					   else  $blogs[$n]['tag'] = '';

						  $html_body->clear;
						   
					   }
					   else {
  
						   $blogs[$n]['articlebody'] = ''; 
						   $blogs[$n]['tag'] = '';
					   }
  
					  
					   $records = array(
								  'category' => $url,
								  'headline' => $this->trimData($blogs[$n]['headline']),
								  'subheadline' => $blogs[$n]['subheadline'],
								  'author' => $blogs[$n]['author'],
								  'datetime' => $blogs[$n]['datetime'],
								  'articlebody' => $blogs[$n]['articlebody'],
								  'tags' => $blogs[$n]['tag'],
								  'news_logo' => $blogs[$n]['news_logo'],
								  'url' => $blogs[$n]['url']
					   );
					  //store data
					  $this->db->insert($this->crawler_table, $records);
  
					   $n++;
					   
					  // break;
						 
					 }
					 
					 $html->clear;
					 
					 $this->rowInserted = $i;  
			}
			}
			
		  break; 
		  
		  case 22://http://www.technologyreview.com/mobile/stream/
		  
			for ($i=1; $i<=$max_page;$i++){
				
				  if($i==1) $url = $url;
				  else $url.'page/'.$i.'/?sort=recent';
				  
				  $html = file_get_html($url);
				  
				  if(!empty($html)){
					 
					 $n=0;
					 
					 foreach ($html->find('.stream-container .stream .stream li') as $row){
						
						
					   if(!empty($row->find('article .meta h1 a',0)->href)) {
						   $blogs[$n]['url'] = "http://www.technologyreview.com/".$row->find('article 
						   .meta h1 a',0)->href;
					   }
					   else $blogs[$n]['url'] = '';

					   if(!empty($row->find('article .image a img',0)->src)) {
						   $blogs[$n]['news_logo'] = $row->find('article .image a img',0)->src;
					   }
					   else $blogs[$n]['news_logo'] = '';
  
					   if(!empty($row->find('article .meta h1 a',0)->plaintext)){
						  $blogs[$n]['headline'] = $row->find('article .meta h1 a',0)->plaintext;
					   }
					   else $blogs[$n]['headline'] = '';
					   
					   if(!empty($row->find('article .offix .byline p',0)->innertext)){
						 $blogs[$n]['subheadline'] = $row->find('article .offix .byline p',0)->innertext;
					   }
					   else $blogs[$n]['subheadline']  = '';
					   
					   if(!empty($row->find('article .offix .byline time',0)->plaintext)){
						   
						   $blogs[$n]['datetime'] = $row->find('article .offix .byline time',0)->plaintext;
					   }
					   else $blogs[$n]['datetime'] = '';
					   
					   if(!empty( $row->find('article .offix .byline .name',0)->plaintext)) {
							  $blogs[$n]['author'] = $row->find('article .offix .byline .name',0)->plaintext;
					   }
					   else $blogs[$n]['author'] = '';
					   
					   
					   if(!empty($blogs[$n]['url'])) $html_body = $html = file_get_html($blogs[$n]['url']);
					   else $html_body = '';
					   
					   if(!empty($html_body)){
						   
						   if(!empty($html_body->find('#main-article .wrapper .body',0)->innertext)){
							   $blogs[$n]['articlebody'] = $html_body->find('#main-article .wrapper .body',0)->innertext;
						   }
						   else $blogs[$n]['articlebody'] = '';
						   
					   if(!empty($row->find('#authors .credits',1)->plaintext)){
						  $blogs[$n]['tag'] = $row->find('#authors .credits',0)->plaintext;
					   }
					   else  $blogs[$n]['tag'] = '';

						  $html_body->clear;
						   
					   }
					   else {
  
						   $blogs[$n]['articlebody'] = ''; 
						   $blogs[$n]['tag'] = '';
					   }
  
					  
					   $records = array(
								  'category' => $url,
								  'headline' => $this->trimData($blogs[$n]['headline']),
								  'subheadline' => $blogs[$n]['subheadline'],
								  'author' => $blogs[$n]['author'],
								  'datetime' => $blogs[$n]['datetime'],
								  'articlebody' => $blogs[$n]['articlebody'],
								  'tags' => $blogs[$n]['tag'],
								  'news_logo' => $blogs[$n]['news_logo'],
								  'url' => $blogs[$n]['url']
					   );
					  //store data
					  $this->db->insert($this->crawler_table, $records);
  
					   $n++;
					   
					  // break;
						 
					 }
					 
					 $html->clear;
					 
					 $this->rowInserted = $i;  
			}
			}
			
		  break; 
		  
		  case 23://http://www.businessweek.com/companies-and-industries
		  
				
			  $html = file_get_html($url);
			  
			  if(!empty($html)){
				 
				 $n=0;
				
				 foreach ($html->find('.tab_panel ul li') as $row){
					 
				   if(!empty($row->find('h5 a',0)->href)) {
					   $blogs[$n]['url'] = $row->find('h5 a',0)->href;
				   }
				   else $blogs[$n]['url'] = '';
				   
				   if(!empty($row->find('a img',0)->src)) {
					   $blogs[$n]['news_logo'] = $row->find('a img',0)->src;
				   }
				   else $blogs[$n]['news_logo'] = '';

				   if(!empty($row->find('h5 a',0)->plaintext)){
					  $blogs[$n]['headline'] = $row->find('h5 a',0)->plaintext;
				   }
				   else $blogs[$n]['headline'] = '';
				   
				   if(!empty($row->plaintext)){
					 $blogs[$n]['subheadline'] = $row->plaintext;
				   }
				   else $blogs[$n]['subheadline']  = '';
				   
                    if(!empty( $row->find('h6 a',0)->plaintext)) {
						  $blogs[$n]['tag'] = $row->find('h6 a',0)->plaintext;
				   }
				   else $blogs[$n]['tag'] = '';
				   
				   if(!empty($blogs[$n]['url'])) $html_body = $html = file_get_html($blogs[$n]['url']);
				   else $html_body = '';
				   
				   if(!empty($html_body)){
					   
					   if(!empty($html_body->find('.article_body',0)->plaintext)){
						   $blogs[$n]['articlebody'] = $html_body->find('.article_body',0)->plaintext;
					   }
					   else $blogs[$n]['articlebody'] = '';
					   
					   if(!empty( $html_body->find('#authorial',0)->plaintext)) {
							  $blogs[$n]['author'] = $html_body->find('#authorial',0)->plaintext;
					   }
					   else $blogs[$n]['author'] = '';
					   
					  if(!empty($html_body->find('#authorial time',0)->plaintext)){
						   
						   $blogs[$n]['datetime'] = $html_body->find('#authorial time',0)->innertext;
					   }
					   else $blogs[$n]['datetime'] = '';
					  
					  $html_body->clear;
					   
				   }
				   else {

					   $blogs[$n]['articlebody'] = '';
					   $blogs[$n]['author'] = '';
					   $blogs[$n]['datetime'] = '';
					 
				   }
				  
				   $records = array(
							  'category' => $url,
							  'headline' => $this->trimData($blogs[$n]['headline']),
							  'subheadline' => $blogs[$n]['subheadline'],
							  'author' => $blogs[$n]['author'],
							  'datetime' => $blogs[$n]['datetime'],
							  'articlebody' => $blogs[$n]['articlebody'],
							  'tags' => $blogs[$n]['tag'],
							  'news_logo' => $blogs[$n]['news_logo'],
							  'url' => $blogs[$n]['url']
				   );
				  //store data
				  $this->db->insert($this->crawler_table, $records);

				   $n++;
				   
				  // break;
					 
				 }
				 
				 $html->clear;
				 
				 $this->rowInserted = $n;  
			  }
		   break; 
		   
		   case 24://http://www.businessweek.com/technology
		  
				
			  $html = file_get_html($url);
			  
			  if(!empty($html)){
				 
				 $n=0;
				
				 foreach ($html->find('.tab_panel ul li') as $row){
					 
				   if(!empty($row->find('h5 a',0)->href)) {
					   $blogs[$n]['url'] = $row->find('h5 a',0)->href;
				   }
				   else $blogs[$n]['url'] = '';
				   
				   if(!empty($row->find('a img',0)->src)) {
					   $blogs[$n]['news_logo'] = $row->find('a img',0)->src;
				   }
				   else $blogs[$n]['news_logo'] = '';

				   if(!empty($row->find('h5 a',0)->plaintext)){
					  $blogs[$n]['headline'] = $row->find('h5 a',0)->plaintext;
				   }
				   else $blogs[$n]['headline'] = '';
				   
				   if(!empty($row->plaintext)){
					 $blogs[$n]['subheadline'] = $row->plaintext;
				   }
				   else $blogs[$n]['subheadline']  = '';
				   
                    if(!empty( $row->find('h6 a',0)->plaintext)) {
						  $blogs[$n]['tag'] = $row->find('h6 a',0)->plaintext;
				   }
				   else $blogs[$n]['tag'] = '';
				   
				   if(!empty($blogs[$n]['url'])) $html_body = $html = file_get_html($blogs[$n]['url']);
				   else $html_body = '';
				   
				   if(!empty($html_body)){
					   
					   if(!empty($html_body->find('.article_body',0)->plaintext)){
						   $blogs[$n]['articlebody'] = $html_body->find('.article_body',0)->plaintext;
					   }
					   else $blogs[$n]['articlebody'] = '';
					   
					   if(!empty( $html_body->find('#authorial',0)->plaintext)) {
							  $blogs[$n]['author'] = $html_body->find('#authorial',0)->plaintext;
					   }
					   else $blogs[$n]['author'] = '';
					   
					  if(!empty($html_body->find('#authorial time',0)->plaintext)){
						   
						   $blogs[$n]['datetime'] = $html_body->find('#authorial time',0)->innertext;
					   }
					   else $blogs[$n]['datetime'] = '';
					  
					  $html_body->clear;
					   
				   }
				   else {

					   $blogs[$n]['articlebody'] = '';
					   $blogs[$n]['author'] = '';
					   $blogs[$n]['datetime'] = '';
					 
				   }
				  
				   $records = array(
							  'category' => $url,
							  'headline' => $this->trimData($blogs[$n]['headline']),
							  'subheadline' => $blogs[$n]['subheadline'],
							  'author' => $blogs[$n]['author'],
							  'datetime' => $blogs[$n]['datetime'],
							  'articlebody' => $blogs[$n]['articlebody'],
							  'tags' => $blogs[$n]['tag'],
							  'news_logo' => $blogs[$n]['news_logo'],
							  'url' => $blogs[$n]['url']
				   );
				  //store data
				  $this->db->insert($this->crawler_table, $records);

				   $n++;
				   
				  // break;
					 
				 }
				 
				 $html->clear;
				 
				 $this->rowInserted = $n;  
			  }
		   break; 
		   
		   case 25://http://www.businessweek.com/markets-and-finance
		  
				
			  $html = file_get_html($url);
			  
			  if(!empty($html)){
				 
				 $n=0;
				
				 foreach ($html->find('.tab_panel ul li') as $row){
					 
				   if(!empty($row->find('h5 a',0)->href)) {
					   $blogs[$n]['url'] = $row->find('h5 a',0)->href;
				   }
				   else $blogs[$n]['url'] = '';
				   
				   if(!empty($row->find('a img',0)->src)) {
					   $blogs[$n]['news_logo'] = $row->find('a img',0)->src;
				   }
				   else $blogs[$n]['news_logo'] = '';

				   if(!empty($row->find('h5 a',0)->plaintext)){
					  $blogs[$n]['headline'] = $row->find('h5 a',0)->plaintext;
				   }
				   else $blogs[$n]['headline'] = '';
				   
				   if(!empty($row->plaintext)){
					 $blogs[$n]['subheadline'] = $row->plaintext;
				   }
				   else $blogs[$n]['subheadline']  = '';
				   
                    if(!empty( $row->find('h6 a',0)->plaintext)) {
						  $blogs[$n]['tag'] = $row->find('h6 a',0)->plaintext;
				   }
				   else $blogs[$n]['tag'] = '';
				   
				   if(!empty($blogs[$n]['url'])) $html_body = $html = file_get_html($blogs[$n]['url']);
				   else $html_body = '';
				   
				   if(!empty($html_body)){
					   
					   if(!empty($html_body->find('.article_body',0)->plaintext)){
						   $blogs[$n]['articlebody'] = $html_body->find('.article_body',0)->plaintext;
					   }
					   else $blogs[$n]['articlebody'] = '';
					   
					   if(!empty( $html_body->find('#authorial',0)->plaintext)) {
							  $blogs[$n]['author'] = $html_body->find('#authorial',0)->plaintext;
					   }
					   else $blogs[$n]['author'] = '';
					   
					  if(!empty($html_body->find('#authorial time',0)->plaintext)){
						   
						   $blogs[$n]['datetime'] = $html_body->find('#authorial time',0)->innertext;
					   }
					   else $blogs[$n]['datetime'] = '';
					  
					  $html_body->clear;
					   
				   }
				   else {

					   $blogs[$n]['articlebody'] = '';
					   $blogs[$n]['author'] = '';
					   $blogs[$n]['datetime'] = '';
					 
				   }
				  
				   $records = array(
							  'category' => $url,
							  'headline' => $this->trimData($blogs[$n]['headline']),
							  'subheadline' => $blogs[$n]['subheadline'],
							  'author' => $blogs[$n]['author'],
							  'datetime' => $blogs[$n]['datetime'],
							  'articlebody' => $blogs[$n]['articlebody'],
							  'tags' => $blogs[$n]['tag'],
							  'news_logo' => $blogs[$n]['news_logo'],
							  'url' => $blogs[$n]['url']
				   );
				  //store data
				  $this->db->insert($this->crawler_table, $records);

				   $n++;
				   
				  // break;
					 
				 }
				 
				 $html->clear;
				 
				 $this->rowInserted = $n;  
			  }
		   break; 
		   
	   }
   }
}
?>
