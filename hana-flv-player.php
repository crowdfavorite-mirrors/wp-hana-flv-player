<?php
/*
Plugin Name: Hana Flv Player
Plugin URI: http://wpmarketing.org/plugins/hana-flv-player/
Description: The best way to embed Flash Player and Flash movie in your Wordpress Blog. Includes GPL Flowplayer and OS FLV player. Usage: <code>[hana-flv-player video='/source_video.flv' /]</code>
Version: 2.6
Author: HanaDaddy
Author URI: http://neox.net
*/

 
class hana_flv_player
{

	

	var $plugin_folder ='hana-flv-player';
	var $version="2.6";
	var $user_attr ;
	var $update_result='';
	
	var $admin_setting_menu='&#8226;Hana Flv Player';
	var $admin_setting_title='Hana Flv Player Default Configuration';
	var $plugin_url;

	//when new player is added , add to below two arrays
	var $player_used= array('1'=> 0,'2'=>0,'3'=>0 ,'4'=>0);
	var $player_base= array('1'=> 'osflv',
				'2'=> 'flowplayer',
				'3'=> 'template_maxi_1.6.0',
				'4'=> 'flowplayer3',
				);
   
	var $default_attr=array(
						//'flowplayer'=>'',
						//'osflvplayer'=>'',
						'player'=>'4',  // 1 : OS FLV , 2: flow player, 3: MAXI, 4: flow player 3
						'width'=>'400',
						'height'=>'330',
						'description'=>'',
						'autoplay'=>'false',
						'loop'=>'false',
						'autorewind'=>'true',
						'autoload'=>'true',
						'clickurl'=>'', 	// just need to be here for div_attr checking
						'clicktarget'=>'',
						'video'=>'',		// just need to be here for div_attr checking
						'splashimage'=>'',	// just need to be here for div_attr checking
						
						'more_2'=> '', 		// just need to be here for div_attr checking
						'more_3'=> '',		// just need to be here for div_attr checking
						'more_4'=> ''
						);
	var $excerpt=false;					
 		
	function hana_flv_player() {
		$this->user_attr = get_option('hanaflv_options');
		
		//for newly added attribute , we need this check for the compatibility with old DB entry setting
		//if ( $this->user_attr && !array_key_exists ('splash_image',$this->user_attr))
		//	$this->user_attr['splashimage'] = $default_attr['splashimage'];
		
			
		if (! $this->user_attr) 
			$this->user_attr = $this->default_attr;
		
			
		$this->plugin_url=get_bloginfo("wpurl") . "/wp-content/plugins/$this->plugin_folder";
		
	}

	function bind_hooks() {
		// third arg should be large enough . If executed early in the filter chain, so we do not see <br />
		add_filter('the_content', array(&$this,'hana_flv_return') , 1);

		//remove_filter('get_the_excerpt', 'wp_trim_excerpt');
		//add_filter('get_the_excerpt', array(&$this,'hana_flv_return_exerpt'));

		add_action('admin_menu' , array(&$this,'hana_flv_admin_menu') );

		// init process for button control
		add_action('init', array(&$this,'hana_flv_addbuttons'));
		add_action('admin_print_scripts',array(&$this,'admin_javascript'));
		add_action('admin_footer',array(&$this,'admin_footer'));
	}
	
	function hana_flv_return($content) {
 		return preg_replace_callback('|\[hana-flv-player(.*?)/\]|ims', array(&$this,'hana_flv_callback'), $content);
	}
 
/*	function hana_flv_return_exerpt($content) {
 		$this->excerpt=true;
 		$text=wp_trim_excerpt($text);
		$this->excerpt=false;
		return $text;
	}
*/	
	function hana_flv_admin_menu() {
		if ( function_exists('add_options_page') ) {
			add_options_page($this->admin_setting_title,$this->admin_setting_menu, 8, __FILE__,array(&$this,'hana_flv_options_page'));

		}
	}
	
 
	//Actual processing function.
	function hana_flv_callback($arg) {
		global $wp_current_filter;
		
		//print($arg[1]);
		$attr_array=$this->parse_attributes($arg[1]);
	
		
	
		$flv_attr=array();
		$div_attr=array();
		
		$key_list = array_keys($attr_array);
		
		foreach ($key_list as $key ) {
			$flv_attr[$key]=$attr_array[$key];
		}
		/*foreach ($key_list as $key ) {
			if (array_key_exists($key,$this->user_attr))
				$flv_attr[$key]=$attr_array[$key];
			else
				$div_attr[$key]=$attr_array[$key];
		}*/
		
		//print_r($attr_array);
		//print "<hr />";
	    //print_r($flv_attr);
		//print "<hr />";
	    
		reset($flv_attr);
		while(list($key,$value) = each($flv_attr)){
			if ($key != 'video' && $key != 'clickurl' && $key !='splashimage' && $key != 'more_2' && $key != 'more_3' && $key !='more_4')
				$flv_attr[$key] = strtolower($value);
		}
		
		if (! array_key_exists('player',$flv_attr)){
			$flv_attr['player']=$this->user_attr['player'];
		}
	    if (! array_key_exists('width',$flv_attr)){
			$flv_attr['width']=$this->user_attr['width'];
	    }
		if (! array_key_exists('height',$flv_attr)){
			$flv_attr['height']=$this->user_attr['height'];
	    }
		if (! array_key_exists('autoplay',$flv_attr)){
			$flv_attr['autoplay']=$this->user_attr['autoplay'];
	    }
		if (! array_key_exists('loop',$flv_attr)){
			$flv_attr['loop']=$this->user_attr['loop'];
	    }
	    if (! array_key_exists('autorewind',$flv_attr)){
			$flv_attr['autorewind']=$this->user_attr['autorewind'];
	    }
	    if (! array_key_exists('autoload',$flv_attr)){
			$flv_attr['autoload']=$this->user_attr['autoload'];
	    }

	    if (! array_key_exists('more_2',$flv_attr)) {
	    	$flv_attr['more_2']=$this->user_attr['more_2'];
	    }
	    
	    if (! array_key_exists('more_3',$flv_attr)) {
	    	$flv_attr['more_3']=$this->user_attr['more_3'];
	    }
	    
	    if (! array_key_exists('more_4',$flv_attr)) {
	    	$flv_attr['more_4']=$this->user_attr['more_4'];
	    }
	    
	    //print "<hr />";
	    //print_r($flv_attr);
	    
	   
	    //var_dump($wp_current_filter);
	    
	    if (is_array($wp_current_filter) &&  array_search('get_the_excerpt',$wp_current_filter) !== FALSE ) {	   
	    	$text=$flv_attr['description'];
		if ($text != "" ) 
			return "*Video: ".htmlspecialchars($text);	
		else
			return "";
	    }
		
	    if (! array_key_exists('video',$flv_attr)){
			
	    	return '<div style="color:#f00;font-weight:bold;">[hana-flv-player] : "video" attribute is missing for the video file\'s URL.</div>'."\n".'<!-- '. $arg[1] .'-->';	
	    }
	    
	   
	    $div_attr_string = $this->construct_attributes($div_attr);
	 
	    $player=$flv_attr['player'];
	    $description='';
		if ($flv_attr['description'] != "")
			$description="*Video:".htmlspecialchars($flv_attr['description']);
	    
	    if ($player == '3' ) {
		// flv player maxi
		
	    		
			$flv_attr['autoplay']=($flv_attr['autoplay']=='true')?'1':'';
			$flv_attr['loop']=($flv_attr['loop']=='true')?'1':'';
			$flv_attr['autoload']=($flv_attr['autoload']=='true')?'1':'';

			//by default the video is autorewind.
			//$flv_attr['autorewind']=($flv_attr['autorewind']=='true')?'on':'off';
			
			$splashImage="";
			if ($flv_attr['splashimage'] != '')
				$splashImage="&amp;startimage=".$flv_attr['splashimage'];

			$onclick='';
			if ($flv_attr['clickurl'] != '') {
				$onclick="&amp;onclick=".$flv_attr['clickurl'];				
			}
			
			$onclicktarget='';
			if ($flv_attr['clicktarget'] != '') {
				$onclicktarget="&amp;onclicktarget=".$flv_attr['clicktarget'];								
			}
			
			$default_controls="&amp;showstop=1&amp;showvolume=1&amp;showtime=1&amp;showfullscreen=1&amp;srt=1";
			
			if ($flv_attr['more_3'] != "" ){
				
			 //$flv_data['more']=str_replace('&' ,'&amp;',$flv_data['more']);
			    $flv_attr['more_3'] = trim($flv_attr['more_3']);
			    if (substr($flv_attr['more_3'],0,1) != '&' ) 
			 		$flv_attr['more_3'] = '&' . $flv_attr['more_3'];
			}
			
			 
				
			$output="<hana-ampersand>
<object id='monFlash' type='application/x-shockwave-flash' data='".$this->plugin_url."/".$this->player_base[$player]."/template_maxi/player_flv_maxi.swf' width='".$flv_attr['width']."' height='".$flv_attr['height']."'>
	<param name='movie' value='".$this->plugin_url."/".$this->player_base[$player]."/template_maxi/player_flv_maxi.swf' />
	<param name='allowFullScreen' value='true' />
	<param name='wmode' value='transparent'> 
	<param name='FlashVars' value='flv=".$flv_attr['video']."&amp;width=".$flv_attr['width']."&amp;height=".$flv_attr['height']."&amp;autoplay=".$flv_attr['autoplay']."&amp;autoload=".$flv_attr['autoload'].$splashImage."&amp;loop=".$flv_attr['loop'].$onclick.$onclicktarget.$default_controls. $flv_attr['more_3'] ."' />
				<p>$description</p>
</object></hana-ampersand>";

	    }else 
	    if ($player == '2' ) {
		// flowplayer	
			if ($this->player_used[$player] == 0 )
				$output = "<hana-ampersand><script type='text/javascript' src='".$this->plugin_url."/".$this->player_base[$player]."/html/flashembed.min.js'></script></hana-ampersand>";
		
			$this->player_used[$player] += 1;
			$splashImage='';
			 
			$flow_id = 'hana_flv_flow_' . $this->player_used[$player]; 
			if ($flv_attr['clickurl'] == '') {
				$videoFile= "videoFile: '". $flv_attr['video'] ."',";
				$playList="";
			}else{
				$videoFile="";
				$linkwindows='_self';
				//presets: '_blank', '_parent', '_self', '_top'. 
				if ($flv_attr['clicktarget'] != '') $linkwindows=$flv_attr['clicktarget'];
				
				$playList= "playList: [ { url: '".$flv_attr['video']."', linkUrl: '".$flv_attr['clickurl']."', linkWindow: '$linkwindows' } ]";
			}
			
			// if autoplay == true & splash image exists, the splashimage doesn't show and autoplay doesn't work
			if ($flv_attr['splashimage'] != '' && $flv_attr['autoplay'] == 'false')
				$splashImage = "splashImageFile: '".$flv_attr['splashimage'] ."',";

			//if ($flv_attr['more_2'] != "" && $playList != "" ){

				
		    
			if ($flv_attr['more_2'] != '' ) $flv_attr['more_2'] = ',' . trim($flv_attr['more_2']);
			if ($playList != '' ) $playList = ',' . $playList;
			    
			   // if (substr($flv_attr['more_2'],-1) != ','  ) 
			 	//	$flv_attr['more_2'] = $flv_attr['more_2'] . ',';
			//}
				
			 
				
			$output .="<hana-ampersand>
			<div $div_attr_string><div id='$flow_id'>$description</div></div>
<script type='text/javascript'>
    flashembed('$flow_id',
      { src:'".$this->plugin_url."/".$this->player_base[$player]."/FlowPlayerDark.swf', wmode: 'transparent', width: ".$flv_attr['width'].",  height: ".$flv_attr['height']." },
      { config: { $videoFile autoPlay: ".$flv_attr['autoplay']." ,loop: ".$flv_attr['loop'].", autoRewind: ".$flv_attr['autorewind'].", autoBuffering: ".$flv_attr['autoload'].",
			$splashImage initialScale: 'scale' " . $flv_attr['more_2'] . "
      		$playList      	                
	    }}
    );
</script></hana-ampersand>";

	    }else
	    if ($player == '4' ) {	
		// flowplayer3
			if ($this->player_used[$player] == 0 )
				$output = "<hana-ampersand><script type='text/javascript' src='".$this->plugin_url."/".$this->player_base[$player]."/example/flowplayer-3.2.6.min.js'></script></hana-ampersand>";
		
			$this->player_used[$player] += 1;		
			 
			$flow3_id = 'hana_flv_flow3_' . $this->player_used[$player]; 
			  
			//$output .='<a href="'.$flv_attr['video'].'" style="display:block;width:'.$flv_attr['width'].'px;height:'. $flv_attr['height'].'px" id="$flow3_id"></a>'; 
			$splashImage='';
			
		 
				
			if ($flv_attr['splashimage'] != '') {
				$alt=''; 
				if ($description != '') $alt="alt='$description'";
			
				$splashImage='<img src="'.$flv_attr['splashimage']. '" style="width:'.$flv_attr['width'].'px; height:'.$flv_attr['height'].'px;border:0;margin:0;padding:0" '.$alt.' />';
			}
				
			$output.="<hana-ampersand><div $div_attr_string><div id='$flow3_id' style='display:block;width:".$flv_attr['width']."px;height:". $flv_attr['height']."px;' title=\"$description\">$splashImage</div></div>";
//			$output.="<hana-ampersand><div $div_attr_string><a href='".$flv_attr['video']."' id='$flow3_id' style='display:block;width:".$flv_attr['width']."px;height:". $flv_attr['height']."px;'>$splashImage</a></div>";
			if ( $this->user_attr['flow3key'] != "") {
				$player=$this->plugin_url."/".$this->player_base[$player]."/flowplayer.commercial-3.2.7.swf";
				$flow3key="key: '".trim($this->user_attr['flow3key']) ."',";
			}else
				$player=$this->plugin_url."/".$this->player_base[$player]."/flowplayer-3.2.7.swf";
			
			$linkurl='';
			$linkwindows='';
			if ($flv_attr['clickurl'] != '') {
				$linkurl= ",linkUrl: '".$flv_attr['clickurl']."'";
				$linkwindows=",linkWindow: '_self'";
				if ($flv_attr['clicktarget'] != '') {
					$linkwindows=",linkWindow: '".$flv_attr['clicktarget']."'";
				}
			}
			
			$loop='';
			$autorewind='';
			
			//if loop is true, ignore autorewind.
			if ($flv_attr['loop'] == 'true' ){
				$loop=', onBeforeFinish : function() { this.play(0);  return false; } ';	
				
			}else			
			if ($flv_attr['autorewind'] =='true'){
				$autorewind=', onFinish : function () { this.seek(0); } ';
			}
			
			$plugin_text='';
			
			if ($flv_attr['more_4'] != '' ) {
				$plugin_text="
				,
				".$flv_attr['more_4']."
				
				";
				
			}
			
    		 
				
			$output .="
			<script  type='text/javascript'>
		flowplayer('$flow3_id', { src: '$player', wmode: 'transparent' }, { 
		$flow3key
    		clip:  { 
    			url: '".$prefix.$flv_attr['video']."',
        		scaling: 'scale', autoPlay: ".$flv_attr['autoplay'].", autoBuffering: ".$flv_attr['autoload']." 
				$linkurl $linkwindows $loop $autorewind
	        }
	        
	        $plugin_text
		}); 
			</script></hana-ampersand>
			 ";
			
	    }else  {
		   
	    	if ($this->player_used[$player] == 0 ) 
				$output = "<hana-ampersand><script src='".$this->plugin_url."/".$this->player_base[$player]."/AC_RunActiveContent.js' language='javascript'></script></hana-ampersand>\n";
		
			$this->player_used[$player] += 1;
			
			//In order to autoplay,the autoload must be true
			if ( $flv_attr['autoplay'] == 'true')
				$flv_attr['autoload']='true';
			
			
			$flv_attr['autoplay']=($flv_attr['autoplay']=='true')?'on':'off';
			$flv_attr['loop']=($flv_attr['loop']=='true')?'on':'off';
			$flv_attr['autorewind']=($flv_attr['autorewind']=='true')?'on':'off';
			$flv_attr['autoload']=($flv_attr['autoload']=='true')?'on':'off';
			
			
			
			
	    	$linkwindows='';
			if ($flv_attr['clicktarget'] != '') {
				$linkwindows=$flv_attr['clicktarget'];
			}
		
	 			
		$output .="<hana-ampersand>
<div $div_attr_string>
<script language='javascript'>
 AC_FL_RunContent('codebase', 'http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0', 'width', '".$flv_attr['width']."', 'height', '".$flv_attr['height']."', 'src',  '".$this->plugin_url."/".$this->player_base[$player]."/' + ((!DetectFlashVer(9, 0, 0) && DetectFlashVer(8, 0, 0)) ? 'player8' : 'player'), 'pluginspage', 'http://www.macromedia.com/go/getflashplayer', 'id', 'flvPlayer', 'allowFullScreen', 'true', 'movie', '".$this->plugin_url."/".$this->player_base[$player]."/' + ((!DetectFlashVer(9, 0, 0) && DetectFlashVer(8, 0, 0)) ? 'player8' : 'player'), 'FlashVars', 'movie=".$flv_attr['video']."&bgcolor=0x051615&fgcolor=0x13ABEC&volume=&autoload=".$flv_attr['autoload']."&autoplay=".$flv_attr['autoplay']."&autorewind=".$flv_attr['autorewind']."&loop=".$flv_attr['loop']."&clickurl=".$flv_attr['clickurl']."&clicktarget=$linkwindows','wmode','transparent');
</script>
<noscript>
 <object width='".$flv_attr['width']."' height='".$flv_attr['height']."' id='flvPlayer'>
  <param name='allowFullScreen' value='true'>
  <param name='wmode' value='transparent'> 
  <param name='movie' value='".$this->plugin_url."/".$this->player_base[$player]."/player.swf?movie=".$flv_attr['video']."&bgcolor=0x051615&fgcolor=0x13ABEC&volume=&autoload=".$flv_attr['autoload']."&autoplay=".$flv_attr['autoplay']."&autorewind=".$flv_attr['autorewind']."&loop=".$flv_attr['loop']."&clickurl=".$flv_attr['clickurl']."&clicktarget=$linkwindows'>
  <embed src='".$this->plugin_url."/".$this->player_base[$player]."/player.swf?movie=/video/babayhana.flv&bgcolor=0x051615&fgcolor=0x13ABEC&volume=&autoload=".$flv_attr['autoload']."&autoplay=".$flv_attr['autoplay']."&autorewind=".$flv_attr['autorewind']."&loop=".$flv_attr['loop']."&clickurl=".$flv_attr['clickurl']."&clicktarget=$linkwindows' width='400' height='300' allowFullScreen='true' type='application/x-shockwave-flash'>
  <p>$description</p>
 </object>
</noscript>
</div></hana-ampersand> \n";		

		}
		
    	return  $output;

    }
	

    
	function hana_flv_options_page() {

		//global  $_POST;
		if ( $this->update_result != '' ) 
			print '<div id="message" class="updated fade"><p>' . $this->update_result . '</p></div>';
			
	    //generate search keyword
$products=array('Kindle','Apple iPod','Apple iPod Touch','GPS','HD TV','Apple Magic Mouse',
'Android Tablet','Apple iPad','ebook reader','Kindle','Apple iPod nano','Canon Camera','Webcam',
'Video Camera','Digital Picture Frame','Blu-ray disc player','External Hard Drive',
'Wireless Notebook Optical Mouse','Nintendo Wii','Playstation 3','Xbox','video editing software');
 
		$total=count($products);
 		
 		$index=rand(1,$total-1);
 		
 		$product=$products[$index];
	?>
<div class="wrap">
	   <div id="icon-options-general" class="icon32"><br /></div>
	   <h2>Configuration for Hana Flv Player V<?php echo $this->version; ?></h2>
	   
	   <div style='font-size:1.2em;margin:10px;font-weight:bold;'>
	   <a href='http://wpmarketing.org/forum/forum/wp-plugin-hana-flv-player'>Forum</a>
	   <a href='http://wpmarketing.org/plugins/hana-flv-player/'>Website</a>
	   </div>
	<p>
	Now you can easily embed the FLV Flash videos in your WordPress Blog.  
	I have packaged the three FLV Flash player,<a href='http://www.osflv.com/'>OS FLV</a> (GPL) ,  <a href='http://flowplayer.org/'>FlowPlayer</a> (GPL),
	and <a href='http://flv-player.net/players/maxi/'>FLV Player Maxi</a> (<a href='http://creativecommons.org/licenses/by-sa/3.0/deed.en'>CC</a>, <a href='http://www.mozilla.org/MPL/'>MPL 1.1</a>).
	So you can use them freely without worries even for the commercial purpose unlike the JW player.
	

    </p>
    <p>
	You can place a specific tag element <code>[hana-flv-player]</code> in the article where you want to show. The 'video' attribute is mandatory. There are other optional attributes. 
	Default values for the options can be defined in this pages. See the <a href="#example">bottom of the page</a> for the example.
	
    </p>		

<ul style='list-style-type: circle;padding-left:20px;margin:10px' >
<li>Have Questions?  <a href='http://wpmarketing.org/forum/forum/wp-plugin-hana-flv-player'>Hana Flv Player Plugin Forum</a></li>
<li><a href='http://wpmarketing.org/plugins/hana-flv-player/'>Hana Flv Player Home</a></li>
</ul>
Now it's Search Engine Friendly. Use the 'description' attribute to leave details about the video. Search engines is able to read them now.
 	
<p>
 	Please support Hana Flv Player by shopping at Amazon.com from this search box. Thank you!

 	<form action='http://www.amazon.com/s/ref=nb_ss_gw?url=search-alias%3Daps' method='get'><input type='hidden' name='url' 
 	value='search-alias=aps'  /><input type='text' id='amazon_keyword' name='field-keywords' value='<?php echo $product;?>'  style='height:30px;width:300px;color:grey' onclick='document.getElementById("amazon_keyword").style.color="black";document.getElementById("amazon_keyword").value=""' /> <input type='hidden' name='tag' value='amazon-im-20' /> <input type='image' src='<?php print $this->plugin_url; ?>/button_amazon.jpg' align='ABSMIDDLE'></form>
</p>	
 	 
	
	
 	
	<hr size='1'>
	
	<h3>Default Settings</h3>
	<form action="" method="post">
	<fieldset  class="options">
	<table id="optiontable" class="editform">
		<tr>
			<th valign="top">Default Player:</th>
			<td><select name="hflv_player">
			<option value="1" <?php if ($this->user_attr['player'] == '1' ) print "selected"; ?> >1. OS FLV player (GPL)</option>
			<option value="2" <?php if ($this->user_attr['player'] == '2' ) print "selected"; ?> >2. Flow Player 2 (GPL)</option>
			<option value="3" <?php if ($this->user_attr['player'] == '3' ) print "selected"; ?> >3. FLV Player Maxi (CC, MPL1.1)</option>
			<option value="4" <?php if ($this->user_attr['player'] == '4' ) print "selected"; ?> >4. Flow Player 3 (GPL)</option>
			
			</select>
			<a href='http://www.osflv.com/'>OS FLV V2.0.5</a> , <a href='http://flowplayer.org/'>FlowPlayer V2.2.1 / V3.2.7</a>, <a href='http://flv-player.net/players/maxi/'>FLV Player Maxi</a>
			</td>
		</tr>
		<tr>
			<th valign="top">Default Width</th>
			<td><input type='text' name="hflv_width" value="<?php print $this->user_attr['width'];?>" size='4' maxlength='4'> 
			</td>
		</tr>
		<tr>
			<th valign="top">Default Height</th>
			<td><input type='text' name="hflv_height" value="<?php print $this->user_attr['height'];?>" size='4' maxlength='4'> 
			</td>
		</tr>
		<tr>
			<th valign="top">Default AutoLoad</th>
			<td><select name="hflv_autoload">
			<option value="true" <?php if ( $this->user_attr['autoload'] == 'true' ) print "selected"; ?> >true</option>
			<option value="false" <?php if ( $this->user_attr['autoload'] == 'false' ) print "selected"; ?> >false</option>
			</select>
			</td>
		</tr>
		<tr>
			<th valign="top">Default AutoPlay</th>
			<td><select name="hflv_autoplay">
			<option value="true" <?php if ( $this->user_attr['autoplay'] == 'true' ) print "selected"; ?> >true</option>
			<option value="false" <?php if ( $this->user_attr['autoplay'] == 'false' ) print "selected"; ?> >false</option>
			</select>
			</td>
		</tr>
		<tr>
			<th valign="top">Default Loop</th>
			<td><select name="hflv_loop">
			<option value="true" <?php if ( $this->user_attr['loop'] == 'true' ) print "selected"; ?> >true</option>
			<option value="false" <?php if ( $this->user_attr['loop'] == 'false' ) print "selected"; ?> >false</option>
			</select>
			</td>
		</tr>
		<tr>
			<th valign="top">Default Auto Rewind</th>
			<td><select name="hflv_autorewind">
			<option value="true" <?php if ( $this->user_attr['autorewind'] == 'true' ) print "selected"; ?> >true</option>
			<option value="false" <?php if ( $this->user_attr['autorewind'] == 'false' ) print "selected"; ?> >false</option>
			</select>			
			</td>
		</tr>
		<tr>
			<th valign="top">Default more_2</th>
			<td>Automatically used if player 2 (Flowplayer v2) is used. Generate code using below sample Generator and copy more_2 attribute value here.<br />
			<textarea rows="3" cols="70" name='hflv_more_2'><?php echo htmlspecialchars($this->user_attr['more_2']); ?></textarea>
						
			</td>
		</tr>
		<tr>
			<th valign="top">Default more_3</th>
			<td>Automatically used if player 3 (FLV Player Maxi) is used. Generate code using below sample Generator and copy more_3 attribute value here.<br />
			<textarea rows="3" cols="70" name='hflv_more_3'><?php echo htmlspecialchars($this->user_attr['more_3']); ?></textarea>
				
					
			</td>
		</tr>
		<tr>
			<th valign="top">Default more_4</th>
			<td>Automatically used if player 4 (Flowplayer v3) is used. Generate code using below sample Generator and copy more_4 attribute value here.<br />
			<textarea rows="3" cols="70" name='hflv_more_4'><?php echo htmlspecialchars($this->user_attr['more_4']); ?></textarea>
				
					
			</td>
		</tr>
		<tr>
			<th valign="top">FlowPlayer 3 License Key</th>
			<td>
			FlowPlayer 3 Commercial version will be automatically used when defined. <br />
			
			<input type='text' name="flow3key" value="<?php echo htmlspecialchars($this->user_attr['flow3key']); ?>" size='22' maxlength='22'> 
			
			<ul style='list-style-type: circle;padding-left:20px;margin:10px'>
			<li>The key property name must be in lower case and the key value cannot contain any extra spaces.</li>
			<li>The product key must match the player version. For example, a key generated for version 3.0 does not work with 3.1 version. The player included here is 3.2 version.</li>
			<li>Sample Key : <code>#$7162d2d730cf607ac6d</code></li>
			</ul>
			 	
					
			</td>
		</tr>
		
	</table>
	<p class="submit"><input name="submit" value="Update Options &raquo;" type="submit"></p>
	</fieldset>
	</form>

<hr size='1'>
<a name="example" ></a>
   <p>
	<h3>Usage Example:</h3>
	<pre style="padding: 10px; border:1px dotted black">
[hana-flv-player 
    video="<?php print $this->plugin_url; ?>/babyhana.flv"
    width="400"
    height="320"
    description="Sarah is having fun in a merry-go-round"
    clickurl="<?php bloginfo("url"); ?>"
    clicktarget="_blank"
    player="4"
    autoplay="false"
    loop="false"
    autorewind="true"
    splashimage="<?php print $this->plugin_url; ?>/splash.jpg"
/]</pre>
<p>When you defined any attribute, please make sure that you use single or double quotes around the attribute value, or my plugin may not recognize the attribute. 
</p>

	Attributes explained:
	<ul style='list-style-type: circle;padding-left:20px;margin:10px' >

		<li><strong>video</strong>: URL of the flv video file. This is mandatory.</li>
		<li><strong>width</strong>: Width of the Flash player.</li>
		<li><strong>height</strong>: Height of the Flash player.</li>
		<li><strong>description</strong>: Description of the video. This will be shown when <code>the_excerpt()</code> is used. Also it is used within the SWF objects or javascripts, so search engines can read it.</li>
		<li><strong>clickurl</strong>: If you want to open a website when a user clicks on the video, you can define the target website URL here. </li>
		<li><strong>clicktarget</strong>: The target of the URL when clicking on the video. Same window:<code>_self</code>, New window <code>_blank</code></li>
		<li><strong>player</strong>: If set to "1" , <a href='http://www.osflv.com/'>OS FLV</a> will be used. 
			If set to "2", <a href='http://flowplayer.org/'>FlowPlayer</a> will be used. 
			"3" is for <a href='http://flv-player.net/players/maxi/'>FLV Player Maxi</a>.
			"4" is for <a href='http://flowplayer.org'>FlowPlayer 3(3.2.3)</a>.</li>
		<li><strong>autoload</strong>: If true, the movie will be loaded (downloaded). If false, the starting screen will be blank since no video is downloaded.</li>
		<li><strong>autoplay</strong>: If true, the movie will play automatically when the page is loaded.</li>
		<li><strong>loop</strong>: If Loop is true, the movie will replay itself constantly.</li>
		<li><strong>autorewind</strong>: If AutoRewind is true, the cursor will be reset to the start of the movie when the movie is ended.</li>
		<li><strong>splashimage</strong>: Only works with FlowPlayer and FLV player Maxi. When autoload is off, this splash image will be shown in the player. It only supports JPEG images.</li>
		<li><strong>more_2</strong>: more options for the Flow Player v2.  
		<li><strong>more_3</strong>: more options for the Flv Player. 
		<li><strong>more_4</strong>: more options for the Flow Player v3. 
		
	</ul>
<script type="text/javascript">
function hana_flv_player_more_gen(){
	hide_control=0; //for flowplayer v3 (4)
	other_settings='';
	
    sep="";
	if (document.getElementById("im_player2").checked){
		player=2;
		sep=", ";
	}else
	if (document.getElementById("im_player3").checked){
    	player=3;
    	sep="&";
	}else
	if (document.getElementById("im_player4").checked){
		player=4;
		sep=", ";
	}else
		return;

    more="";

    if (document.getElementById("im_showstop1").checked){
    	if (player == 4) { more +="stop:true"; } //by default false
    	
    }
 
    if (document.getElementById("im_showstop2").checked){
    	if (player == 3) { more +="showstop=0"; }
    	else 
    	if (player == 2) { more +="showStopButton: false"; }
    }
    
	if (document.getElementById("im_showvol2").checked){    
    	if (player == 3) { 
    		if (more != "") {more +=sep ; } more +="showvolume=0"; 
    	} else 
    	if (player == 2) { 
    		if (more != "") {more +=sep ; } more +="showVolumeSlider: false, showMuteVolumeButton: false"; 
    	} else
        if (player == 4) {
            if (more != "") {more +=sep ; } more +="volume:false, mute:false";
        }   		
    }
	if (document.getElementById("im_showtime2").checked){    
    	if (player == 3) { 
    		if (more != "") {more +=sep ; } more +="showtime=0"; 
    	}
    	// for flowplayer v2, time information can be hidden with disabling seekbar	    		
    	else
    	if (player == 4) {
        	if (more != "") {more +=sep ; } more +="time:false";
    	}
    }
    
    //showplayer=autohide|always|never	
    //controlsOverVideo: ['locked' || 'ease' || 'no']	hideControls: true|false
    
   	if (document.getElementById("im_showctrl1").checked){    
    	if (player == 3) { 
    		if (more != "") {more +=sep ; } more +="showplayer=always"; 
    	} else 
    	if (player == 2) { 
    		if (more != "") {more +=sep ; } more +="controlsOverVideo: 'locked'";  // or 'no' ?
    	}else
    	if (player == 4) {
        	if (more != "") {more +=sep ; } more +="autoHide: 'never'"; //never,always, fullscreen
    	}
    }else		
    //autohide control
   	if (document.getElementById("im_showctrl2").checked){    
    	if (player == 3) { 
    		if (more != "") {more +=sep ; } more +="showplayer=autohide"; 
    	} else 
    	if (player == 2) { 
    		if (more != "") {more +=sep ; } more +="controlsOverVideo: 'ease'"; 
    	}else
        if (player == 4) {
        	if (more != "") {more +=sep ; } more +="autoHide: 'always'"; 
    	}    		
    }else
    //disabling control
    if (document.getElementById("im_showctrl3").checked) {
    	if (player == 3) { 
    		if (more != "") {more +=sep ; } more +="showplayer=never"; 
    	} else 
    	if (player == 2) { 
    		if (more != "") {more +=sep ; } more +="hideControls: true"; 
    	}else
        if (player == 4) {
        	hide_control=1;
    	}    		    
    }

    pcolor=document.getElementById("im_ctrlcolor_val").value

	if (document.getElementById("im_ctrlcolor_trans").checked) {
    	if (player == 3) { 
    		if (more != "") {more +=sep ; } more +="playeralpha=0&showplayer=autohide"; 
    	} else 
    	if (player == 2) { 
    		if (more != "") {more +=sep ; } more +="controlBarBackgroundColor: -1, controlBarGloss: 'none'"; 
    	}
    	if (player == 4) {
        	if (more != "") {more +=sep ; } more +="backgroundColor: 'transparent', backgroundGradient: 'none'";
    	}

    }else
    if (pcolor != "" && pcolor.length == 6) {
    	if (player == 3) { 
    		if (more != "") {more +=sep ; } more +="bgcolor1="+pcolor+"&bgcolor2="+pcolor+"&playercolor="+pcolor; 
    	} else 
    	if (player == 2) { 
    		if (more != "") {more +=sep ; } more +="controlBarBackgroundColor:'0x"+pcolor+"'"; 
    	} else 
    	if (player == 4) { 
    		if (more != "") {more +=sep ; } more +="backgroundColor:'#"+pcolor+"'"; 
    	}
    	
    }

    if (document.getElementById("im_showfull2").checked) {
    	if (player == 3) { 
    		if (more != "") {more +=sep ; } more +="showfullscreen=0"; 
    	}else 
    	if (player == 2) { 
    		if (more != "") {more +=sep ; } more +="showFullScreenButton: false"; 
    	}else
    	if (player == 4) { 
    		if (more != "") {more +=sep ; } more +="fullscreen:false"; 
    	}    		    
    }

    if (document.getElementById("im_showseek2").checked) {
    	if (player == 2) { 
    		if (more != "") {more +=sep ; } more +="showScrubber: false"; 
    	}else
    	if (player == 4) { 
    		if (more != "") {more +=sep ; } more +="scrubber: false"; 
    	}  	    
    }
    if (document.getElementById("im_showmenu2").checked) {
    	if (player == 2) { 
    		if (more != "") {more +=sep ; } more +="showMenu: false"; 
    	}    		    
    }
    


    if (player == 4){
		more = "plugins: { controls: { "+ more  + " } }"; 

		if (hide_control == 1)
			more ="plugins: { controls: null } ";
    }
    
    obj = document.getElementById("im_predef");
    if (obj.selectedIndex != 0 ){
    	val=obj[obj.selectedIndex].value;
    	
    	//<option value='0'>Do not use Predefined settings</option>
		//<option value='1'>Flow: Simple Black Control Bar</option>			    
		//<option value='2'>Flow: Controls over video</option>			    
		//<option value='3'>Flow: Controls over video with auto-hide</option>			    
		//<option value='4'>Flow: Mimimalistic Look</option>			    
		//<option value='5'>Flv: Showing Custom Logo</option>			    
    	
    	if (val == 1) {
    		player=2;
    		more="showVolumeSlider: false,showMuteVolumeButton: false, showMenu: false, controlBarBackgroundColor: 0";
    	}else
    	if (val == 2) {
    		player=2;
    		more="showVolumeSlider: false, controlsOverVideo: 'locked', controlBarBackgroundColor: -1, controlBarGloss: 'none' ";
    	}else
    	if (val == 3) {
    		player=2;
    		more="showVolumeSlider: false, controlsOverVideo: 'ease',controlBarBackgroundColor: -1, controlBarGloss: 'low' ";
    	}else
    	if (val == 4) {
    		player=2;
    		more="showStopButton: false, showScrubber: false, showVolumeSlider: false,showMuteVolumeButton: false, showFullScreenButton: false, showMenu: false, controlsOverVideo: 'locked',controlBarBackgroundColor: -1,controlBarGloss: 'none', usePlayOverlay: false ";
    	}else
    	if (val == 5) {
    		player=3;
    		more="bgcolor1=396da5&bgcolor2=396da5&playercolor=396da5";
    	}else
    	if (val == 6) {
    		player=3;
    		more="top1=<?php print $this->plugin_url; ?>/logo.png|30|20";
 		}
    	else

     	//Flow3: Minimalistic Look
     	if (val == 7) {
    		player=4;
    		more="plugins: { controls: {all:false, backgroundColor: 'transparent', backgroundGradient: 'none', play:true } },play: null ";
     	}else

     	//Flow3: Remove all controls and continuous loop. Can't stop :)
     	if (val == 8) {
     		player=4;
     	 	other_settings=" autoplay='true' loop='true' \n";
     	 	more="plugins: { controls: null } , play: null";
     	}  	
    }



    output="[hana-flv-player \n" + 
    	"video=\"<?php print $this->plugin_url; ?>/babyhana.flv\"\n"+   
    	"player=\""+player+"\"\n"+
    	other_settings+
    	"more_" + player +"=\""+ more + "\"\n" +
   		"/]";
    
    document.getElementById("im_output").value=output;
    //alert(more);
  
     
}
</script>
<hr size='1'>

	<h3>More Attributes (more_2, more_3, more_4) Sample Generator</h3>
	By using 'more_2','more_3','more_4' attributes, you can use advanced features of the each players. Especially this javascript generator is focused on the interface design option. After selecting the options you want, you can click the 'Generate' button to generate the sample usage in the output textarea.
	<form action="" method="post" onsubmit="hana_flv_player_more_gen(); return false;">
	<fieldset  class="options">
	<table id="optiontable" class="editform">
		<tr>
			<th valign="top">Predefined</th>
			<td>
				<select id='im_predef' name='m_predef'>
				<option value='0'>Do not use Predefined settings</option>
				<option value='1'>Flow2: Simple Black Control Bar</option>			    
				<option value='2'>Flow2: Controls over video</option>			    
				<option value='3'>Flow2: Controls over video with auto-hide</option>			    
				<option value='4'>Flow2: Minimalistic Look</option>	
				<option value='5'>Flv: Changing border color</option>						    
				<option value='6'>Flv: Showing Custom Logo</option>		    
				<option value='7'>Flow3: Minimalistic Look</option>		    
				<option value='8'>Flow3: Remove all controls and continuous loop.</option>		    
				</select>
				
			</td>
		</tr>
		<tr>
			<th valign="top">Player</th>
			<td><input type='radio' id='im_player2' value='2' name='m_player' checked /> Flow Player v2 (2) 
			    <input type='radio' id='im_player3' value='3' name='m_player' /> Flv Player Maxi (3) 
			    <input type='radio' id='im_player4' value='4' name='m_player' /> Flow Player v3 (4) 
			</td>
			    
 		</tr>
		<tr>
			<th valign="top">Show Stop Button</th>
			<td><input type='radio' id='im_showstop1' value='1' name='m_showstop' checked /> Yes 
			    <input type='radio' id='im_showstop2' value='0' name='m_showstop' /> No 
			</td>
			    
 		</tr>
		<tr>
			<th valign="top">Show Volume Control</th>
			<td><input type='radio' id='im_showvol1' value='1' name='m_showvol' checked /> Yes 
			    <input type='radio' id='im_showvol2' value='0' name='m_showvol' /> No 
			</td>
		</tr>
  		<tr>
			<th valign="top">Show Time Info</th>
			<td>Only for Flv Player <input type='radio' id='im_showtime1' value='1' name='m_showtime' checked /> Yes 
			    <input type='radio' id='im_showtime2' value='0' name='m_showtime' /> No  
			</td>
		</tr>
  		<tr>
			<th valign="top">Control Hide or AutoHide</th>
			<td><input type='radio' id='im_showctrl1' value='0' name='m_showctrl' checked /> Show Controls
			    <input type='radio' id='im_showctrl2' value='1' name='m_showctrl' /> AutoHide 
			    <input type='radio' id='im_showctrl3' value='2' name='m_showctrl' /> Hide
			</td>
		</tr>

  		<tr>
			<th valign="top">Control Background Color</th>
			<td>
				<input type='text' id='im_ctrlcolor_val'  name='m_ctrlcolor_val' size='6' maxlength='6'/> 'FFFFFF' format 
				<br />
				<input type='checkbox' id='im_ctrlcolor_trans' value='-1' name='m_ctrlcolor_trans' /> Transparent
			</td>
		</tr>

  		<tr>
			<th valign="top">Show FullScreen Button</th>
			<td><input type='radio' id='im_showfull1' value='1' name='m_showfull' checked  /> Yes 
			    <input type='radio' id='im_showfull2' value='0' name='m_showfull' /> No 
			</td>
		</tr>
  		<tr>
			<th valign="top">Show SeekBar</th>
			<td>Only for Flow Player
			    <input type='radio' id='im_showseek1' value='1' name='m_showseek' checked /> Yes 
			    <input type='radio' id='im_showseek2' value='0' name='m_showseek' /> No 
			</td>
		</tr>
  		<tr>
			<th valign="top">Show Menu Button</th>
			<td>Only for Flow Player v2
			    <input type='radio' id='im_showmenu1' value='1' name='m_showmenu' checked /> Yes 
			    <input type='radio' id='im_showmenu2' value='0' name='m_showmenu'/> No 
			</td>
		</tr>
		
		
  	</table>
	<p class="submit"><input name="submit" value="Generate &raquo;" type="submit"></p>
	
	<div><textarea id='im_output' rows='8' cols='80' wrap='soft' ></textarea></div>
	<a href='http://flowplayer.org/v2/player/configuration.html'>Flow Player v2 Configuration options page</a>
	<a href='http://flv-player.net/players/maxi/documentation/'>Flv Player Maxi Configuration options page</a>
	<a href='http://flowplayer.org/documentation/configuration/index.html'>Flow Player v3 Configuration options page</a>
	</fieldset>
	</form>
    
    <hr size='1'>
    
<h3>Insert flv into template theme files (such as sidebar.php)</h3>
Okay, here is the function that you can use in the theme template files to show FLV movie. Basically you need to 
use <code>hana_flv_player_template_call</code> method. The method takes a single argument.
The argument should be just the string of the attributes of usage explained the above. Just copy below code into your 
theme file and edit red colored attributes accordingly.

<pre style="padding: 10px; border:1px dotted black">
&lt;?php
if (function_exists('hana_flv_player_template_call')){
	$hana_arg="
<span style='color:#ff0000'>video='<?php print $this->plugin_url; ?>/babyhana.flv'
player='2'
width='180'
height='150'
more_2=\"showStopButton: false, showScrubber: false, showVolumeSlider: false,showMuteVolumeButton: false, 
showFullScreenButton: false, showMenu: false, controlsOverVideo: 'locked',controlBarBackgroundColor: -1,
controlBarGloss: 'none', usePlayOverlay:false \"</span>
";
	echo hana_flv_player_template_call($hana_arg);	
}
?&gt;
</pre>
    
    <div><u><strong>Note:</strong> Be careful when you use other website's video file as the video source. Since video files are usually large is size they can use up the bandwidth quickly. 
    So you should ask for the owner's permission before using that link to the file.</u></div>
	</p>
    <p>Thank you for using my plugin. - <a href='http://neox.net/'>HanaDaddy</a></p>
    <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_donations">
<input type="hidden" name="business" value="hanadaddy@gmail.com">
<input type="hidden" name="item_name" value="HanaDaddy Donation - Thank you!">
<input type="hidden" name="no_shipping" value="0">
<input type="hidden" name="no_note" value="1">
<input type="hidden" name="currency_code" value="USD">
<input type="hidden" name="tax" value="0">
<input type="hidden" name="lc" value="US">

<input type="hidden" name="bn" value="PP-DonationsBF">
<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donate_SM.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1"><br />


</form>

<p>
 	Please support Hana Flv Player by shopping at Amazon.com from this search box. Thank you!

 	<form action='http://www.amazon.com/s/ref=nb_ss_gw?url=search-alias%3Daps' method='get'><input type='hidden' name='url' 
 	value='search-alias=aps'  /><input type='text' id='amazon_keyword2' name='field-keywords' value='<?php echo $product;?>'  style='height:30px;width:300px;color:grey' onclick='document.getElementById("amazon_keyword2").style.color="black";document.getElementById("amazon_keyword2").value=""' /> <input type='hidden' name='tag' value='amazon-im-20' /> <input type='image' src='<?php print $this->plugin_url; ?>/button_amazon.jpg' align='ABSMIDDLE'></form>
 	
</p>	
 	 
<script type="text/javascript" src="http://wpmarketing.org/plugin_news.php?id=hana-flv-player"></script>
</div>

 

<?php

	}

	function hana_flv_options_update(){
		
		if ( isset($_POST['hflv_player']) ) {
			if ( is_numeric($_POST['hflv_player']) ){
				$this->user_attr['player'] = $_POST['hflv_player'];
			}
		}

		if ( isset($_POST['hflv_width']) ) {
			if ( is_numeric($_POST['hflv_width']) )
				$this->user_attr['width'] = $_POST['hflv_width'];
		}
		
		if ( isset($_POST['hflv_height']) ) {
			if ( is_numeric($_POST['hflv_height']) )
				$this->user_attr['height'] = $_POST['hflv_height'];
		}
		
		if ( isset($_POST['hflv_autoplay']) ) {
			if ($_POST['hflv_autoplay'] =='true' || $_POST['hflv_autoplay'] =='false' )
				$this->user_attr['autoplay'] = $_POST['hflv_autoplay'];
		}
		
		if ( isset($_POST['hflv_loop']) ) {
			if ($_POST['hflv_loop'] =='true' || $_POST['hflv_loop'] =='false' )
				$this->user_attr['loop'] = $_POST['hflv_loop'];
		}

		if ( isset($_POST['hflv_autorewind']) ) {
			if ($_POST['hflv_autorewind'] =='true' || $_POST['hflv_autorewind'] =='false' )
				$this->user_attr['autorewind'] = $_POST['hflv_autorewind'];
		}

		if ( isset($_POST['hflv_autoload']) ) {
			if ($_POST['hflv_autoload'] =='true' || $_POST['hflv_autoload'] =='false' )
				$this->user_attr['autoload'] = $_POST['hflv_autoload'];
		}
		if ( isset($_POST['hflv_more_2']) ) {			
				$this->user_attr['more_2'] = str_replace("\\",'',$_POST['hflv_more_2']);
		}
		if ( isset($_POST['hflv_more_3']) ) {
				$this->user_attr['more_3'] = str_replace("\\",'',$_POST['hflv_more_3']);
		}
		if ( isset($_POST['hflv_more_4']) ) {
				$this->user_attr['more_4'] = str_replace("\\",'',$_POST['hflv_more_4']);
		}
		
		if ( isset($_POST['hflv_more_4']) ) {
				$this->user_attr['flow3key'] = str_replace("\\",'',$_POST['flow3key']);
		}
		
		//print_r ($this->user_attr);
		
		update_option('hanaflv_options',$this->user_attr);
		//$this->user_attr = get_option('hanaflv_options');
		
		$this->update_result="Settings are updated";
		
	}
    	

    //Support function-----------------------------------------------------

    
    function parse_attributes($attrib_string){

		//first str_replace \n => ' '
		// new line are already stored as <br \> , so need to convert to space
		$search_arr = array("\n","<br />","\t");
	    $replace_arr = array(" "," "," ");	
		$attrib_string = str_replace($search_arr,$replace_arr,$attrib_string);
	
		//print ($attrib_string);	
		
	    $regex='@([^\s=]+)\s*=\s*(\'[^<\']*\'|"[^<"]*"|\S*)@';
		
	    preg_match_all($regex, $attrib_string, $matches);
	
		$attr=array();
	
		//print_r($matches);
		for ($i=0; $i< count($matches[0]); $i++) {
	  		if ( ! empty($matches[0][$i]) && ! empty($matches[1][$i]))  {
				
	  			
	  			if (preg_match("/^'(.*)'$/",$matches[2][$i],$vmatch)) {
					$value=$vmatch[1];	
				}else 
				if (preg_match('/^"(.*)"$/',$matches[2][$i],$vmatch)) {
					$value=$vmatch[1];	
				}else{
					$value=$matches[2][$i];
				}
				$key=strtolower($matches[1][$i]);
				$attr[$key]= $value ;
				
			}
		}
	   
		//print "<pre>";
		//print_r($attr);
		//print "</pre>"; 
		return $attr;
		
	}
	
	function construct_attributes($arr){
	
		$output="";
		
		reset($arr);
		while (list($key, $value) = each ($arr)) {
			$envelop_char='"';
			
			if (strstr($value,'"') !== false) {
				
				$envelop_char='\'';			
			}
			$output .= " $key=".$envelop_char.$value.$envelop_char;
		}
		
		return $output;
	}
	
    	
	function hana_flv_addbuttons() {
	   	// Don't bother doing this stuff if the current user lacks permissions
	   	if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') )
	    	return;
	    //rich_editing
	    add_filter("mce_external_plugins", array(&$this,'add_tinymce_plugin'));
	    add_filter('mce_buttons', array(&$this,'register_button'));
	     
	    //for html editing 
	    add_action('edit_form_advanced', array(&$this,'print_javascript'));
		add_action('edit_page_form',array(&$this,'print_javascript'));
	    //add_action('admin_footer','print_javascript');
	}
	 
	function register_button($buttons) {
	   	array_push($buttons,  "hfplayer");
	   	return $buttons;
	}
	 
	// Load the TinyMCE plugin : editor_plugin.js (wp2.5)
	function add_tinymce_plugin($plugin_array) {
	   	$plugin_array['hanaflvplayer'] = $this->plugin_url . '/tinymce3/editor_plugin.js';
	   	return $plugin_array;
	}

	function admin_javascript(){
		//show only when editing a post or page.
		if (strpos($_SERVER['REQUEST_URI'], 'post.php') || strpos($_SERVER['REQUEST_URI'], 'post-new.php') || strpos($_SERVER['REQUEST_URI'], 'page-new.php') || strpos($_SERVER['REQUEST_URI'], 'page.php')) {
		
			//wp_enqueue_script only works  in => 'init'(for all), 'template_redirect'(for only public) , 'admin_print_scripts' for admin only
			if (function_exists('wp_enqueue_script')) {
				$jspath='/'. PLUGINDIR  . '/'. $this->plugin_folder.'/jqModal/jqModal.js';
				wp_enqueue_script('jqmodal_hana', $jspath, array('jquery'));
			}

		}
		
	}
	function print_javascript () {
	 
?>
   <!--  for popup dialog -->
   <link href="<?php echo $this->plugin_url . '/jqModal/jqModal.css'; ?>" type="text/css" rel="stylesheet" />

   <script type="text/javascript">
   	jQuery(document).ready(function(){
		// Add the buttons to the HTML view
		jQuery("#ed_toolbar").append('<input type=\"button\" class=\"ed_button\" onclick=\"jQuery(\'#dialog_hanaflv\').jqmShow();\" title=\"Hana Flv Player\" value=\"Hana Flv\" />');
   	});

	jQuery(document).ready(function () {
		jQuery('#dialog_hanaflv').jqm();
	});

	function update_hanaflvplayer(){
		var f=document.hanaflvoptions;
		if (f== null) return;
		

	    /*[hana-flv-player 
		    video="http://localhost/wp271/wp-content/plugins/hana-flv-player/babyhana.flv"
		    width="400"
		    height="320"
		    clickurl="http://localhost/wp271"
		    player="4"
		    autoplay="false"
		    loop="false"
		    autorewind="true"
		    splashimage="http://localhost/wp271/wp-content/plugins/hana-flv-player/splash.jpg"
		/]*/

		text='[hana-flv-player video="'+f.video.value+'"\n';

		if (f.width.value.length >0 )
			text +='    width="'+f.width.value+'" \n';
		if (f.height.value.length > 0 )
			text +='    height="'+f.height.value+'" \n';

		text +='    description="'+f.description.value+'" \n';
		text +='    player="'+f.player[f.player.selectedIndex].value+'" \n';
		text +='    autoload="'+f.autoload[f.autoload.selectedIndex].value+'" autoplay="'+f.autoplay[f.autoplay.selectedIndex].value+'" \n';
		text +='    loop="'+f.loop[f.loop.selectedIndex].value+'" autorewind="'+f.autorewind[f.autorewind.selectedIndex].value+'" \n';
		if (f.clickurl.value.length > 0 )
			text +='    clickurl="'+f.clickurl.value +'" \n';
		if (f.clicktarget.value.length > 0 )
			text +='    clicktarget="'+f.clicktarget.value +'" \n';

		if (f.splashimage.value.length > 0)		
			text +='    splashimage="'+f.splashimage.value+'" \n';
			
		text += ' /]';
		
		if (text.length > 0){
			if ( typeof tinyMCE != 'undefined' && ( ed = tinyMCE.activeEditor ) && !ed.isHidden() ) {
				ed.focus();
				if (tinymce.isIE)
					ed.selection.moveToBookmark(tinymce.EditorManager.activeEditor.windowManager.bookmark);

				ed.execCommand('mceInsertContent', false, text);
			} else
				edInsertContent(edCanvas, text);
			 
		}	
		 
		
		jQuery('#dialog_hanaflv').jqmHide();
	}

	
   	</script>

	
	
	<?php   
	  //end of print_javascript 
	}

	function admin_footer(){
		
		if (strpos($_SERVER['REQUEST_URI'], 'post.php') || strpos($_SERVER['REQUEST_URI'], 'post-new.php') || strpos($_SERVER['REQUEST_URI'], 'page-new.php') || strpos($_SERVER['REQUEST_URI'], 'page.php')) {
		
		?>
		<div id="dialog_hanaflv" class='jqmWindow' style='display:none'>
	<div style='width:100%;text-align:center'>
	<h3><a href='http://wpmarketing.org/plugins/hana-flv-player/' target='_new'>Hana Flv Player</a></h3>
	 

	<form name='hanaflvoptions' onsubmit='return false;' >
	
	<table style='text-align:left;width:100%;'>
		<tr> 
			<td valign='top'>Video(required)</td>
			<td><input type='text' size='50' name='video' /></td>
		</tr>	
		<tr>
			<td valign="top">Description</td>
			<td><input type='text' name='description' size='50'/>			
			</td>
		</tr>
		<tr>
			<td valign="top">Flash Player:</td>
			<td><select name="player">
			<option value="1" <?php if ($this->user_attr['player'] == '1' ) print "selected"; ?> >1. OS FLV player (GPL)</option>
			<option value="2" <?php if ($this->user_attr['player'] == '2' ) print "selected"; ?> >2. Flow Player 2 (GPL)</option>
			<option value="3" <?php if ($this->user_attr['player'] == '3' ) print "selected"; ?> >3. FLV Player Maxi (CC, MPL1.1)</option>
			<option value="4" <?php if ($this->user_attr['player'] == '4' ) print "selected"; ?> >4. Flow Player 3 (GPL)</option>
			
			</select>
			</td>
		</tr>
		<tr>
			<td valign="top">Width</td>
			<td><input type='text' name="width" value="<?php print $this->user_attr['width'];?>" size='4' maxlength='4' /> 
			</td>
		</tr>
		<tr>
			<td valign="top">Height</td>
			<td><input type='text' name="height" value="<?php print $this->user_attr['height'];?>" size='4' maxlength='4' /> 
			</td>
		</tr>
		<tr>
			<td valign="top">AutoLoad</td>
			<td><select name="autoload">
			<option value="true" <?php if ( $this->user_attr['autoload'] == 'true' ) print "selected"; ?> >true</option>
			<option value="false" <?php if ( $this->user_attr['autoload'] == 'false' ) print "selected"; ?> >false</option>
			</select>
			</td>
		</tr>		
		
		<tr>
			<td valign="top">AutoPlay</td>
			<td><select name="autoplay">
			<option value="true" <?php if ( $this->user_attr['autoplay'] == 'true' ) print "selected"; ?> >true</option>
			<option value="false" <?php if ( $this->user_attr['autoplay'] == 'false' ) print "selected"; ?> >false</option>
			</select>
			</td>
		</tr>
		<tr>
			<td valign="top">Play Loop</td>
			<td><select name="loop">
			<option value="true" <?php if ( $this->user_attr['loop'] == 'true' ) print "selected"; ?> >true</option>
			<option value="false" <?php if ( $this->user_attr['loop'] == 'false' ) print "selected"; ?> >false</option>
			</select>
			</td>
		</tr>
		<tr>
			<td valign="top">Auto Rewind</td>
			<td><select name="autorewind">
			<option value="true" <?php if ( $this->user_attr['autorewind'] == 'true' ) print "selected"; ?> >true</option>
			<option value="false" <?php if ( $this->user_attr['autorewind'] == 'false' ) print "selected"; ?> >false</option>
			</select>			
			</td>
		</tr>
		<tr>
			<td valign="top">Click URL</td>
			<td><input type='text' name='clickurl' size='50' />			
			</td>
		</tr>
		<tr>
			<td valign="top">Click Target</td>
			<td><input type='text' name='clicktarget' size='10' />_self (same) , _blank (new window)			
			</td>
		</tr>
		<tr>
			<td valign="top">Splash Image URL</td>
			<td><input type='text' name='splashimage' size='50'/>			
			</td>
		</tr>
	</table>

	 	<p class='submit'><input type='button' value='OK' onclick='update_hanaflvplayer()'; >
	 	<input type='button' value='Cancel' onclick="jQuery('#dialog_hanaflv').jqmHide();" >
	 	</p>
	</div>	
	
	</form>	 
			
	 
	
	</div> 
	  <?php 
		}
	}
	
}


//this is to return back the ampersand .
if (!class_exists('hana_ampersand')){

class hana_ampersand 


{
	var $target = array("&#8217;","&#8220;","&#8221;","&#038;","\'","&#8242;", "&#8216;");
	var $replace= array("'",'"','"',"&","'","'","'");
	

	function hana_return_ampersand (){
		
	}

	function bind_hooks(){
		// must be executed at the end to guarantee that it is not modified by other plugin	
		add_filter('the_content', array(&$this,'filter_callback'), '100');
	}
	

	function filter_callback($content) {
		return preg_replace_callback('|<hana-ampersand>(.*?)</hana-ampersand>|ims', array(&$this,'replace_callback'), $content);
	}
		
	function replace_callback($arg) {
		return str_replace($this->target,$this->replace,clean_pre($arg[1]));
	}
}

}

$hana_flv = new hana_flv_player();

$hana_flv->bind_hooks();


// admin option page update
if ( isset($_POST['hflv_player']) ) {
	$hana_flv->hana_flv_options_update();
}


// Below is to convert &#038 back to '&'
$hana_amp = new hana_ampersand();
$hana_amp->bind_hooks();

function hana_flv_player_template_call($arg){
	global $hana_flv;
	
	if(!$hana_flv){
		$hana_flv = new hana_flv_player();
	}
	
	return $hana_flv->hana_flv_callback(array('',$arg));
}

require_once("wpmarketing_feed.php");

