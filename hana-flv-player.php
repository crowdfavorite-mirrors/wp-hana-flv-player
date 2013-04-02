<?php
/*
Plugin Name: Hana Flv Player
Plugin URI: http://wpmarketing.org/plugins/hana-flv-player/
Description: The best way to embed Flash & HTML5 Video player in your Wordpress Blog. Includes GPL Flowplayer (2,3,5), OS FLV player, and MediaElement.js. Usage: <code>[hana-flv-player video='/source_video.flv' /]</code>
Version: 3.1.2
Author: HanaDaddy
Author URI: http://neox.net
*/

 
class hana_flv_player
{

	

	var $plugin_folder ='hana-flv-player';
	var $version="3.1.2";
	var $user_attr ;
	var $update_result='';
	
	var $admin_setting_menu='&#8226;Hana Flv Player';
	var $admin_setting_title='Hana Flv Player Default Configuration';
	var $plugin_url;

	//when new player is added , add to below two arrays
	var $player_used= array('1'=> 0,'2'=>0,'3'=>0 ,'4'=>0,'5'=>0,'6'=>0);
	var $player_base= array('1'=> 'osflv',
				'2'=> 'flowplayer',
				'3'=> 'template_maxi_1.6.0',
				'4'=> 'flowplayer3',
				'5'=> 'mediaelement',
				'6'=> 'flowplayer5',
	);
   
	var $default_attr=array(
						//'flowplayer'=>'',
						//'osflvplayer'=>'',
						'player'=>'5',  // 1 : OS FLV , 2: flow player, 3: MAXI, 4: flow player 3 , 5: MediaElement.js
						'width'=>'400',
						'height'=>'',
						'description'=>'',
						'autoplay'=>'false',
						'loop'=>'false',
						'autorewind'=>'true',
						'autoload'=>'true',
						'clickurl'=>'', 	// just need to be here for div_attr checking
						'clicktarget'=>'',
						'video'=>'',		// just need to be here for div_attr checking
						'splashimage'=>'',	// just need to be here for div_attr checking
						'event_tracking'=>'no',
						'more_2'=> '', 		// just need to be here for div_attr checking
						'more_3'=> '',		// just need to be here for div_attr checking
						'more_4'=> '',		// just need to be here for div_attr checking
						'more_5'=> '',		// just need to be here for div_attr checking
						'skin'=>'',			// 20121204 only applied to MediaElement.js
						);
	var $excerpt=false;					
 	var $skins=array();
 		
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
		if (!is_admin()) {
			//20120519 If theme loads version of jquery not as a standard wp_enqueue_script, it may break something. Let's just disable for now. 
			//20120521 We shouldn't disable it (many cases, jquery is not enabled by default) but can give as an option to disable it.
			//20120605 disabled wp_enqueue_script jquery, but added a routine to include mediaelement jquery script if jquery is not loaded
			//wp_enqueue_script("jquery"); //make sure we load jquery;
		}
		
		add_filter('wp_head',array(&$this,'print_head_javascript'));
		add_filter('the_content', array(&$this,'hana_flv_return') , 1);
		add_filter('widget_text', array(&$this,'hana_flv_return') );
		
		//remove_filter('get_the_excerpt', 'wp_trim_excerpt');
		//add_filter('get_the_excerpt', array(&$this,'hana_flv_return_exerpt'));

		add_action('admin_menu' , array(&$this,'hana_flv_admin_menu') );

		// init process for button control
		//show only when editing a post or page.		
		if (strpos($_SERVER['REQUEST_URI'], 'post.php') || strpos($_SERVER['REQUEST_URI'], 'post-new.php') || strpos($_SERVER['REQUEST_URI'], 'page-new.php') || strpos($_SERVER['REQUEST_URI'], 'page.php')) {
			add_action('init', array(&$this,'hana_flv_addbuttons'));
			add_action('admin_print_scripts',array(&$this,'admin_javascript'));
			add_action('admin_print_styles',array(&$this,'admin_style'));
			add_action('admin_footer',array(&$this,'admin_footer'));
		}
	}

	function print_head_javascript(){


		echo "
<script type='text/javascript'>
var g_hanaFlash = false;
try {
  var fo = new ActiveXObject('ShockwaveFlash.ShockwaveFlash');
  if(fo) g_hanaFlash = true;
}catch(e){
  if(navigator.mimeTypes ['application/x-shockwave-flash'] != undefined) g_hanaFlash = true;
}
function hanaTrackEvents(arg1,arg2,arg3,arg4) { if ( typeof( pageTracker ) !=='undefined') { pageTracker._trackEvent(arg1, arg2, arg3, arg4);} else if ( typeof(_gaq) !=='undefined'){  _gaq.push(['_trackEvent', arg1, arg2, arg3, arg4]);}}
function hana_check_mobile_device(){ if(navigator.userAgent.match(/iPhone/i) || navigator.userAgent.match(/iPod/i) || navigator.userAgent.match(/iPad/i)  || navigator.userAgent.match(/Android/i)) { return true; }else return false; }
</script>
";
		
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
	//check if maxi is installed manually
	function check_if_maxi_exist(){
		return file_exists( dirname(__FILE__) . '/template_maxi_1.6.0/template_maxi/player_flv_maxi.swf');
	}
	
	function check_if_apple(){
		//20120422 There is a problem if any Wordpress cache plugin is used. Should be disabled for now. 		
		/*if (strpos($_SERVER['HTTP_USER_AGENT'], 'iPad') ||
			strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone') ||
			strpos($_SERVER['HTTP_USER_AGENT'], 'iPod')) {
			return true;
		}*/
		return false;
	}


	function get_extension($url){
		return substr($url,-3);
	}
    
	function hana_flv_admin_menu() {
		if ( function_exists('add_options_page') ) {
			global $wp_version;
			if ( $wp_version < 2 ) $capability=8; else $capability='manage_options';
			add_options_page($this->admin_setting_title,$this->admin_setting_menu, $capability, __FILE__,array(&$this,'hana_flv_options_page'));
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
			if ($key != 'video' && $key != 'clickurl' && $key !='splashimage' && $key != 'more_2' && $key != 'more_3' && $key !='more_4' && $key != 'more_5' )
				$flv_attr[$key] = strtolower($value);
		}
		
		if (! array_key_exists('player',$flv_attr)){
			$flv_attr['player']=$this->user_attr['player'];
		}
		
		
		//20120411 
		if ($flv_attr['player'] ==3 && !$this->check_if_maxi_exist() )
			$flv_attr['player']=4;

		//20120417 HTML5 for Apple device 
		if ($this->check_if_apple() ) { // && strtolower($this->get_extension($flv_attr['video'])) =='mp4' ){
			$flv_attr['player']=5;	//mediaelement is HTML5 supports ipad, iphone. But what happens if the file is not mp4?
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
  
	    $flv_attr['event_tracking']=$this->user_attr['event_tracking'];
	    
	    if (! array_key_exists('more_2',$flv_attr)) {
	    	$flv_attr['more_2']=$this->user_attr['more_2'];
	    }
	    
	    if (! array_key_exists('more_3',$flv_attr)) {
	    	$flv_attr['more_3']=$this->user_attr['more_3'];
	    }
	    
	    if (! array_key_exists('more_4',$flv_attr)) {
	    	$flv_attr['more_4']=$this->user_attr['more_4'];
	    }
	    if (! array_key_exists('more_5',$flv_attr)) {
	    	$flv_attr['more_5']=$this->user_attr['more_5'];
	    }
	    
		if (! array_key_exists('skin',$flv_attr)) {
	    	$flv_attr['skin']=$this->user_attr['skin'];
	    }
	     
	    
 	    $autoheight=false;
	    //Auto height : auto - 4:3 height , autow - 16:9
	    // widht must exist
		if ($flv_attr['width'] != '') {
			if ($flv_attr['height']== '' || $flv_attr['height'] == 'auto'){
	    		$flv_attr['height'] = round((3/4) * $flv_attr['width']) ;	    		
	    		$autoheight=true;
			}else
			if ($flv_attr['height'] == 'autow'){
	    		$flv_attr['height'] = round((9/16) * $flv_attr['width']) ;
	    		$autoheight=true;
			} 
			
		}
			    
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
	    
		
		$inactive_message="Sorry, your browser does not support Flash Video Player";
		$inactive_style="display:block;width:".$flv_attr['width']."px;height:".$flv_attr['height']."px;background-color:#555555;color:#ffffff;padding:0";
		
		
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

			//20120606 disable subtitles SRT (srt=1) causing some issues
			$default_controls="&amp;showstop=1&amp;showvolume=1&amp;showtime=1&amp;showfullscreen=1";
			
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
	<param name='wmode' value='transparent' /> 
	<param name='FlashVars' value='flv=".$flv_attr['video']."&amp;width=".$flv_attr['width']."&amp;height=".$flv_attr['height']."&amp;autoplay=".$flv_attr['autoplay']."&amp;autoload=".$flv_attr['autoload'].$splashImage."&amp;loop=".$flv_attr['loop'].$onclick.$onclicktarget.$default_controls. $flv_attr['more_3'] ."' />
    <span style='$inactive_style;padding:5px;'><span style='display:block'>$inactive_message</span> $description</span>
</object></hana-ampersand>";

	    }else 
	    if ($player == '2' ) {
		// flowplayer	
			if ($this->player_used[$player] == 0 )
				$output = "<hana-ampersand><script type='text/javascript' src='".$this->plugin_url."/".$this->player_base[$player]."/html/flashembed2.min.js'></script></hana-ampersand>";
		
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
			
			 
			//initialScale : Scale or fit
			//For Flowplayer 2, let's leave initialScale as Scale due to a problem showing incorrect scale of first image 
			if ($autoheight) $scale='fit'; else $scale='scale';
			$output .="<hana-ampersand>
			<div $div_attr_string><div id='$flow_id' style='$inactive_style'><div class='inactive_message'></div>$description</div></div>
<script type='text/javascript'>
if (typeof g_hanaFlash !== 'undefined' && !g_hanaFlash){
    jQuery('#$flow_id').css( 'padding', '5px' );
	jQuery('#$flow_id .inactive_message').html('$inactive_message');
}else{
    flashembed2('$flow_id',
      { src:'".$this->plugin_url."/".$this->player_base[$player]."/FlowPlayerDark.swf', wmode: 'transparent', width: ".$flv_attr['width'].",  height: ".$flv_attr['height']." },
      { config: { $videoFile autoPlay: ".$flv_attr['autoplay']." ,loop: ".$flv_attr['loop'].", autoRewind: ".$flv_attr['autorewind'].", autoBuffering: ".$flv_attr['autoload'].",
			$splashImage initialScale: '$scale' " . $flv_attr['more_2'] . "
      		$playList      	                
	    }}
    );
}
</script></hana-ampersand>";

	    }else
	    if ($player == '4' ) {	
		// flowplayer3
			if ($this->player_used[$player] == 0 ) {
				$output = "<hana-ampersand><script type='text/javascript' src='".$this->plugin_url."/".$this->player_base[$player]."/example/flowplayer-3.2.6.min.js'></script></hana-ampersand>";
			}
			
			$this->player_used[$player] += 1;		
			 
			$flow3_id = 'hana_flv_flow3_' . $this->player_used[$player]; 
			  
			//$output .='<a href="'.$flv_attr['video'].'" style="display:block;width:'.$flv_attr['width'].'px;height:'. $flv_attr['height'].'px" id="$flow3_id"></a>'; 
			$splashImage='';
			
		 
				
			if ($flv_attr['splashimage'] != '') {
				$alt=''; 
				if ($description != '') $alt="alt='$description'";
			
				$splashImage='<img src="'.$flv_attr['splashimage']. '" style="width:'.$flv_attr['width'].'px; height:'.$flv_attr['height'].'px;border:0;margin:0;padding:0" '.$alt.' />';
			}
				
			$output.="<hana-ampersand><div $div_attr_string><div id='$flow3_id' style='$inactive_style' title=\"$description\">$splashImage</div></div>";
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
			
			//For Event Tracking in Google Analytics
			$event_tracking='';
			if ($flv_attr['event_tracking'] =='yes') {
            			$event_tracking="    ,
				onStart: function(clip) {
					hanaTrackEvents('Videos', 'Play', clip.url,0); 
				},
				onPause: function(clip) {
					hanaTrackEvents('Videos', 'Pause', clip.url, parseInt(this.getTime()) ); 
				},
				onStop: function(clip) {
					hanaTrackEvents('Videos', 'Stop', clip.url , parseInt(this.getTime()) ); 
				},
				onFinish: function(clip) {
					hanaTrackEvents('Videos', 'Finish', clip.url,0); 
				} 
				";    
				 
			}
			
			//scaling: scale, fit
			if ($autoheight) $scale='fit'; else $scale='scale';
			// adding background color  => canvas: { backgroundColor: '#000000', backgroundGradient: 'none',},
			$output .="
<script  type='text/javascript'>
if (typeof g_hanaFlash !== 'undefined' && !g_hanaFlash){
    jQuery('#$flow3_id').css( 'padding', '5px' );
	jQuery('#$flow3_id').html(\"<span class='inactive_message' style='display:block'>$inactive_message</span> ".str_replace('"','\"',$description). "\");
}else{			
		flowplayer('$flow3_id', { src: '$player', wmode: 'transparent' }, { 
		$flow3key
			canvas: { backgroundColor: '#000000', backgroundGradient: 'none',},
    		clip:  { 
    			url: '".$prefix.$flv_attr['video']."',
        		scaling: '".$scale."', autoPlay: ".$flv_attr['autoplay'].", autoBuffering: ".$flv_attr['autoload']." 
				$linkurl $linkwindows $loop $autorewind 

				
				$event_tracking
			
	        }
	        
	        $plugin_text
		});
}
</script></hana-ampersand>";
	        
	    }else
	    if ($player == '5' ) {	
		// mediaelement 
			 
	    		//<script type='text/javascript' src='".$this->plugin_url."/".$this->player_base[$player]."/build/jquery.js'></script>
			if ($this->player_used[$player] == 0 ) {
				//wp_enqueue_script("mediaelementjs-scripts", $this->plugin_url."/".$this->player_base[$player]."/build/mediaelement-and-player.min.js", array('jquery'), "2.7.0", false);
				//wp_enqueue_style("mediaelementjs-styles", $this->plugin_url."/".$this->player_base[$player]."/build/mediaelementplayer.css");

			
				//20120605 jquery dynamic loading routine added -> needed for MediaElementJS
				$output = "<hana-ampersand>
				<script type='text/javascript'>
				if (typeof jQuery == 'undefined') { document.write('<script type=\"text/javascript\" src=\"".$this->plugin_url."/".$this->player_base[$player]."/build/jquery.js\"><\/script>'); }	
				</script>
				<style>.mejs-inner img { max-width:100%; max-height:100%; margin:0 ; padding:0 } 
				.mejs-overlay-button, .mejs-overlay-loading  {display:none;}</style>
				<script type='text/javascript' src='".$this->plugin_url."/".$this->player_base[$player]."/build/mediaelement-and-player.min.js'></script>
				<link rel='stylesheet' href='".$this->plugin_url."/".$this->player_base[$player]."/build/mediaelementplayer.mod.css' />
				<!-- due to the bug - IE8 does not show video --> 
				<!--[if IE 8]><style> .me-plugin { position: static; } </style><![endif]-->
				</hana-ampersand>";
				
				 
			}
	    	

	    	if (($flv_attr['skin'] == 'mejs-ted' ||  $flv_attr['skin'] =='mejs-wmp' ) && !in_array('mejs-skins',$this->skins)){
				$this->skins[]='mejs-skins';
				$output .= "<link rel='stylesheet' href='".$this->plugin_url."/".$this->player_base[$player]."/build/mejs-skins.css' />";
	    	}
	    				
	    	if ( $flv_attr['skin'] == 'onedesign' && !in_array('onedesign',$this->skins)){
				$this->skins[]='onedesign';
				$output .= "<link rel='stylesheet' href='".$this->plugin_url."/".$this->player_base[$player]."/build/skin/onedesign/onedesign.css' />";		
			}
			
			$this->player_used[$player] += 1;		
			 
			$media_id = 'hana_flv_media_' . $this->player_used[$player]; 
			
		 
			$splashImage='';
			$preload='none'; //preload='none' doesn't work with IE9
			$autoplay='';
			
			if ($flv_attr['splashimage'] != '') {
				$splashImage="poster='".$flv_attr['splashimage']."'";
			}
			
			if ($flv_attr['autoload'] == 'true') {				
				$preload='true';				
			}
				
			
			//iOS and Android does not allow auto start play and have issues -> implement as javascript below
			if ($flv_attr['autoplay'] =='true' ){
				//$autoplay='autoplay="true"'; //when "autoplay" attribute name is used, it is autoplayed
			 	$preload='none'; //In firefox, preload should be 'none' to execute autoplay . that's strange
				//$preload='auto'; // it should be auto for chrome to play video with play() javascript funtion; but with firefox it fails to play
			}
			//check youtube http://www.youtube.com
			$youtube='';
			if ( substr($flv_attr['video'],0,22) == 'http://www.youtube.com'){ 
				$youtube="type='video/youtube' ";
			}

			$class="";
			if ($flv_attr['skin'] != "")
				$class="class='".$flv_attr['skin']."'";
				
			//preload='.$preload.'&amp;
			$output.="<hana-ampersand><div style='padding:0;margin:0; border:0;'><video $class id='$media_id' $youtube src='".$flv_attr['video']."' width='".$flv_attr['width']."' height='".$flv_attr['height']."' $splashImage 
	preload='$preload'  $autoplay   controls='controls' >";

			if ($youtube == '')
				$output.='	<object width="'.$flv_attr['width'].'" height="'.$flv_attr['height'].'" type="application/x-shockwave-flash" data="'.$this->plugin_url."/".$this->player_base[$player].'/build/flashmediaelement.swf"> 		
		<param name="movie" value="'.$this->plugin_url."/".$this->player_base[$player].'/build/flashmediaelement.swf" /> 
		<param name="flashvars" value="controls=true&amp;file='.$flv_attr['video'].'&amp;poster='.$flv_attr['splashimage'].'" />		
		'.$description.'
	</object>';
			else
				$output.=$description;
				
			$output.='</video></div></hana-ampersand>';
			
 			
			$options=""; 
			if ($flv_attr['loop'] == 'true') {
				if ($options != '')  $options.=',';
				$options .= "loop: true";
			}
			
			//pauseOtherPlayers : let user to play other player at the same time
			//AndroidUseNativeControls : true => this way it's more stable under Android
			if ($options != '')  $options.=',';
			$options .= "pauseOtherPlayers: false "; //, AndroidUseNativeControls: true,  iPadUseNativeControls: true , iPhoneUseNativeControls: true";
			
			//unfotunately, with HTML5, changing the scale is not possible.  Each browser has its own implementation
			//if (!$autoheight){
			//if ($options != '')  $options.=',';
			//$options .= "enableAutosize: true"; //only for flash player
			//}
			
			$autoplay_js='';
			if ($flv_attr['autoplay'] =='true' ){
				//iOS and Android does not allow auto start play
				//using javascript play function doesn't guarantee play automatically for all three browsers.
				//but adding autoplay attribute dynamically works for three majotr browsers (IE9, Firefox, Chrome) 
				$autoplay_js="if (! hana_check_mobile_device()) { jQuery('#".$media_id."').attr('autoplay','true'); }";
			}
			
			
			
			//if ($options != '')  $options.=',';
			//$options.=" features: []";
			//$options.=" features: ['playpause','progress','current','duration','tracks','volume','fullscreen'],";
			 
			if ($flv_attr['more_5'] != '' ) {
				if ($options != '')  $options.=',';
				$options.=$flv_attr['more_5'];
			}
		
			$event_tracking="";
			$event_tracking_body='';
			if ($flv_attr['event_tracking'] =='yes') {
				$event_tracking_body="			
					me.addEventListener('play', function() { hanaTrackEvents('Videos', 'Play', me.src,0); }, false);
					me.addEventListener('pause', function() { hanaTrackEvents('Videos', 'Pause', me.src, parseInt(me.currentTime) ); }, false);
					me.addEventListener('ended', function() { hanaTrackEvents('Videos', 'Finish', me.src,0) }, false);
					";
			}

			if ($flv_attr['clickurl'] != ''){
				
				$target=strtolower($flv_attr['clicktarget']);
				if ($target == '' || $target=='_self') {
					$event_tracking_body.="
					me.addEventListener('click', function() { me.pause(); window.location.href='".$flv_attr['clickurl']."'; },false); ";
				} else{
					$event_tracking_body.="
					me.addEventListener('click', function() {  me.pause(); window.open('".$flv_attr['clickurl']."'); },false); ";	
				}					
			}			 
										
			if ( $event_tracking_body != ''){
				$event_tracking=", success: function(me) {\n" . $event_tracking_body . ' }';
			}			
	 
						
			$output.="<hana-ampersand><script type='text/javascript'>
				$autoplay_js 
				jQuery('#".$media_id."').mediaelementplayer({ $options, pluginType:'youtube'  $event_tracking });
				</script></hana-ampersand>";
					
			 
	    }
	    else if ($player == '6' ) {	
	    //flowplayer 5
	    	/*
	    	 * TODO: FlowPlayer5 : implement analytics  //flowplayer.conf.analytics = "UA-27182341-1";
	    	 * 	//Events can be found : Google Analytics > Content > Events > Top Events > Video / Seconds played.
	    	 * TODO: FlowPlayer5 : custom logo - commercial version key & logo // flowplayer.conf.key="";flowplayer.conf.logo = 'http://mydomain.com/logo.png";  
	    	 * TODO: FlowPlayer5 : embeding on off //flowplayer.conf.embed = false;
	    	 * 
	    	 * or can be done within the div tag.
	    	 * <div class="flowplayer"
					data-key="$437712314481272"
					data-analytics="UA-27182341-1"
					data-logo="http://flowplayer.org/media/img/mylogo.png">
 				   <video preload='none'>
	    	 * 
	    	 */
			if ($this->player_used[$player] == 0 ) {
				$output="<hana-ampersand>
				<script type='text/javascript'>
				if (typeof jQuery == 'undefined') { document.write('<script type=\"text/javascript\" src=\"http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js\"><\/script>'); }	
				</script>
				<script src='".$this->plugin_url."/".$this->player_base[$player]."/flowplayer.min.js'></script>
				<script>flowplayer.conf.embed = false; //disable embeding</script>
	 			</hana-ampersand>";
			}
			
						
			$available_skins=array('functional','minimalist','playful');
			if ($flv_attr['skin'] == '' || !in_array($flv_attr['skin'] ,$available_skins)) {
				$flv_attr['skin'] = 'minimalist';
			} 
			
			
			if ( ($flv_attr['skin'] =='functional' || $flv_attr['skin'] == 'minimalist' || $flv_attr['skin'] =='playful' ) 
				&& !in_array('all-skins',$this->skins)){
				$this->skins[]='all-skins';				
				$output .= "<link rel='stylesheet' type='text/css' href='".$this->plugin_url."/".$this->player_base[$player]."/skin/all-skins.css' />";
			}
	    	
	    	
	    	$this->player_used[$player] += 1;		
			 
			$media_id = 'hana_flv_flow5_' . $this->player_used[$player]; 
			
	    		 
			 
			
			if ($flv_attr['splashimage'] != '') {
			
				 $output.="<style>#$media_id {  background: #000 url(".$flv_attr['splashimage'] .") 0 0 no-repeat; background-size: 100%; }</style>";
			}
	    
			$preload='none'; //preload='none' doesn't work with IE9
			$autoplay='';
			$loop='';
			
			if ($flv_attr['splashimage'] != '') {
				$splashImage="poster='".$flv_attr['splashimage']."'";
			}
			
			if ($flv_attr['autoload'] == 'true') {				
				$preload='true';				
			}
				
			
			//iOS and Android does not allow auto start play and have issues -> implement as javascript below
			if ($flv_attr['autoplay'] =='true' ){
				//$autoplay='autoplay="true"'; //when "autoplay" attribute name is used, it is autoplayed
			 	$preload='none'; //In firefox, preload should be 'none' to execute autoplay . that's strange
				//$preload='auto'; // it should be auto for chrome to play video with play() javascript funtion; but with firefox it fails to play
				$autoplay='autoplay';
			}
			
			if ($flv_attr['loop'] == 'true') {
				$loop='loop';
			}
			
		
	    	$output.="
	    	<div class='flowplayer ".$flv_attr['skin']." ' id='$media_id' style='width:".$flv_attr['width']."px; height:".$flv_attr['height']."px; ' title='".htmlspecialchars($description)."' >
	   			<video src='".$flv_attr['video']."'  style='background-color:black' $autoplay $loop preload='$preload'></video>
			</div>";
	    	
	    	$output.="<hana-ampersand><script type='text/javascript'>
			jQuery('#".$media_id."').flowplayer({ });
			</script></hana-ampersand>";

	    } else  {
	    
		   
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
if (typeof g_hanaFlash !== 'undefined' && !g_hanaFlash){
	document.write(\"<span style='$inactive_style;padding:5px;'><span style='display:block'>$inactive_message</span> $description</span>\");
}else {   
	AC_FL_RunContent('codebase', 'http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0', 'width', '".$flv_attr['width']."', 'height', '".$flv_attr['height']."', 'src',  '".$this->plugin_url."/".$this->player_base[$player]."/' + ((!DetectFlashVer(9, 0, 0) && DetectFlashVer(8, 0, 0)) ? 'player8' : 'player'), 'pluginspage', 'http://www.macromedia.com/go/getflashplayer', 'id', 'flvPlayer', 'allowFullScreen', 'true', 'movie', '".$this->plugin_url."/".$this->player_base[$player]."/' + ((!DetectFlashVer(9, 0, 0) && DetectFlashVer(8, 0, 0)) ? 'player8' : 'player'), 'FlashVars', 'movie=".$flv_attr['video']."&bgcolor=0x333333&fgcolor=0x999999&volume=&autoload=".$flv_attr['autoload']."&autoplay=".$flv_attr['autoplay']."&autorewind=".$flv_attr['autorewind']."&loop=".$flv_attr['loop']."&clickurl=".$flv_attr['clickurl']."&clicktarget=$linkwindows','wmode','transparent');
}
</script>
<noscript>
 <object width='".$flv_attr['width']."' height='".$flv_attr['height']."' id='flvPlayer'>
  <param name='allowFullScreen' value='true'>
  <param name='wmode' value='transparent'> 
  <param name='movie' value='".$this->plugin_url."/".$this->player_base[$player]."/player.swf?movie=".$flv_attr['video']."&bgcolor=0x333333&fgcolor=0x999999&volume=&autoload=".$flv_attr['autoload']."&autoplay=".$flv_attr['autoplay']."&autorewind=".$flv_attr['autorewind']."&loop=".$flv_attr['loop']."&clickurl=".$flv_attr['clickurl']."&clicktarget=$linkwindows'>
  <embed src='".$this->plugin_url."/".$this->player_base[$player]."/player.swf?movie=".$flv_attr['video']."&bgcolor=0x333333&fgcolor=0x999999&volume=&autoload=".$flv_attr['autoload']."&autoplay=".$flv_attr['autoplay']."&autorewind=".$flv_attr['autorewind']."&loop=".$flv_attr['loop']."&clickurl=".$flv_attr['clickurl']."&clicktarget=$linkwindows' width='".$flv_attr['width']."' height='".$flv_attr['height']."' allowFullScreen='true' type='application/x-shockwave-flash'>
  <span style='$inactive_style;padding:5px;'><span style='display:block'>$inactive_message</span> $description</span>
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
$products=array('Kindle','Apple iPod','Apple iPod Touch','iPad','HD TV','Apple MAC',
'Android Tablet','Apple iPad','ebook reader','Canon Camera','Video Camera','Kindle Fire','Blu-ray disc player','External Hard Drive',
'Tablet','Playstation','Xbox','Ultrabook','Laptop Computer');
 
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
<?php if (!$this->check_if_maxi_exist()) : ?>
	<div  style='margin-bottom:10px; padding:5px; background-color:#fff8ae; font-weight:bold'>NOTE: Starting from V2.7.1 , <a href='http://flv-player.net/players/maxi/'>FLV Player Maxi</a> is no longer included by default because it is licensed under non GPL compatible licenses 
	(<a href='http://creativecommons.org/licenses/by-sa/3.0/deed.en'>CC</a>, <a href='http://www.mozilla.org/MPL/'>MPL 1.1</a>). 
	But you can still <a href='http://flvplayer.googlecode.com/files/template_maxi_1.6.0.zip'>download</a> (or <a href='http://wpmarketing.org/support/template_maxi_1.6.0.zip'>here</a>) 
	and unzip it manually under your Hana FLV Player plugin path (<code><?php echo dirname(__FILE__); ?></code>). 
	So <code><?php echo dirname(__FILE__) . '/template_maxi_1.6.0/template_maxi/player_flv_maxi.swf'; ?></code> must exist.
	If not, Flow Player 3 will be used instead.
	</div>
<?php endif; ?>
	Now you can easily embed the FLV Flash videos in your WordPress Blog.  
	I have packaged the two FLV Flash players - <a href='http://www.osflv.com/'>OS FLV</a> (GPL), <a href='http://flowplayer.org/'>FlowPlayer</a> (GPL), and <a href='http://mediaelementjs.com/'>MediaElement.js</a> HTML5 player to support HTML5 players including Apple devices. So you can use them freely without worries even for the commercial purpose unlike the JW player.
	( You can also use <a href='http://flv-player.net/players/maxi/'>FLV Player Maxi</a> , but it is no longer included by default because it is licensed under non GPL compatible licenses. ) 	

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
Use either FLV or H.264 encoded MP4 video files. <a href='http://wpmarketing.org/forum/topic/hana-flv-player-supported-video-types-flv-h264mp4'>More information about how to convert video encodings</a>
</p>

<!-- 
<p>
 	Please support Hana Flv Player by shopping at Amazon.com from this search box. Thank you!

 	<form action='http://www.amazon.com/s/ref=nb_ss_gw?url=search-alias%3Daps' method='get'><input type='hidden' name='url' 
 	value='search-alias=aps'  /><input type='text' id='amazon_keyword' name='field-keywords' value='<?php echo $product;?>'  style='height:30px;width:300px;color:grey' onclick='document.getElementById("amazon_keyword").style.color="black";document.getElementById("amazon_keyword").value=""' /> <input type='hidden' name='tag' value='amazon-im-20' /> <input type='image' src='<?php print $this->plugin_url; ?>/button_amazon.jpg' align='ABSMIDDLE'></form>
</p>	
 	 
 --> 	
	
	
<style>
h3 {
	font-size:1.5em;
}

div.division {
	margin-top:10px;
	padding:5px 10px 5px 10px;
	border: 2px solid black;
	-moz-border-radius: 10px;
	border-radius: 10px;
}

table#optiontable {
	border-collapse:collapse;
}

table#optiontable tr{
	border-top:1px dotted gray;		
}

table#optiontable td{
	padding:5px;
}
</style>
 	
<script>
function check_hflv_player(){
	select=document.getElementById("hflv_player");

	<?php if (!$this->check_if_maxi_exist()) : ?>
	if (select.selectedIndex == 2 ){
		alert("Please note that FLV Player Maxi is not include by default.\nYou need to download it from their website.\nPlease refer to the explanation in the top section.\nIf it does not exist, Flow Player 3 will be used.");
	}
	<?php endif;?>
	
}
</script>
<div class='division'>

	<h3>Default Settings</h3>
	<form action="" method="post" name='default_setting'>
	<fieldset  class="options">
	<table id="optiontable" class="editform">
		<tr>
			<th valign="top">Default Player:</th>
			<td><select name="hflv_player" id='hflv_player' onchange='check_hflv_player()'>
			<option value="1" <?php if ($this->user_attr['player'] == '1' ) print "selected"; ?> >1. OS FLV player (GPL) - Flash only</option>
			<option value="2" <?php if ($this->user_attr['player'] == '2' ) print "selected"; ?> >2. Flow Player 2 (GPL) - Flash only</option>
			<option value="3" <?php if ($this->user_attr['player'] == '3' ) print "selected"; ?> >3. FLV Player Maxi (CC, MPL1.1) - Flash only</option>
			<option value="4" <?php if ($this->user_attr['player'] == '4' ) print "selected"; ?> >4. Flow Player 3 (GPL) - Flash only</option>
			<option value="5" <?php if ($this->user_attr['player'] == '5' ) print "selected"; ?> >5. Mediaelement.js (GPL) - HTML5 & Flash video player</option>
			<option value="6" <?php if ($this->user_attr['player'] == '6' ) print "selected"; ?> >6. Flow Player 5 (GPL) - HTML5 & Flash video player</option>
			
			</select>
			<a href='http://www.osflv.com/'>OS FLV V2.0.5</a> , <a href='http://flash.flowplayer.org/'>FlowPlayer V2/V3/V5</a>, <a href='http://flowplayer.org/'>FLowPlayer v5.2</a>, <a href='http://flv-player.net/players/maxi/'>FLV Player Maxi</a>
			<a href='http://mediaelementjs.com/'>MediaElement.js</a>
			<div style='font-size:0.8em; padding:5px;'>
			NOTE:To support Apple devices, you must select either Mediaelement.js or FlowPlayer 5. Also remember to encode video using h.264 mp4 ecoding since Apple browsers do not support FLV encodings.  
			<a href='http://wpmarketing.org/forum/topic/hana-flv-player-supported-video-types-flv-h264mp4'>More information about video encoding</a>
			</div>
			</td>
		</tr>
		<tr>
			<th valign="top">Default Width</th>
			<td><input type='text' name="hflv_width" value="<?php print $this->user_attr['width'];?>" size='5' maxlength='5'> 
			Width of the Flash player. Cannot be blank.
			</td>
		</tr>
		<tr>
			<th valign="top">Default Height</th>
			<td><input type='text' name="hflv_height" value="<?php print $this->user_attr['height'];?>" size='5' maxlength='5'> 
			If empty, height is adjusted automatically based on 4:3 ratio. If you want to use 16:9 ratio, use 'autow' as height value.
			</td>
		</tr>
		<tr>
			<th valign="top">Default AutoLoad</th>
			<td><select name="hflv_autoload">
			<option value="true" <?php if ( $this->user_attr['autoload'] == 'true' ) print "selected"; ?> >true</option>
			<option value="false" <?php if ( $this->user_attr['autoload'] == 'false' ) print "selected"; ?> >false</option>
			</select>
			If true, the movie will be loaded (downloaded). If false, the starting screen will be blank since no video is downloaded.
			</td>
		</tr>
		<tr>
			<th valign="top">Default AutoPlay</th>
			<td><select name="hflv_autoplay">
			<option value="true" <?php if ( $this->user_attr['autoplay'] == 'true' ) print "selected"; ?> >true</option>
			<option value="false" <?php if ( $this->user_attr['autoplay'] == 'false' ) print "selected"; ?> >false</option>
			</select>
			If true, the movie will play automatically when the page is loaded.
			</td>
		</tr>
		<tr>
			<th valign="top">Default Loop</th>
			<td><select name="hflv_loop">
			<option value="true" <?php if ( $this->user_attr['loop'] == 'true' ) print "selected"; ?> >true</option>
			<option value="false" <?php if ( $this->user_attr['loop'] == 'false' ) print "selected"; ?> >false</option>
			</select>
			 If Loop is true, the movie will replay itself constantly.
			</td>
		</tr>
		<tr>
			<th valign="top">Default Auto Rewind</th>
			<td><select name="hflv_autorewind">
			<option value="true" <?php if ( $this->user_attr['autorewind'] == 'true' ) print "selected"; ?> >true</option>
			<option value="false" <?php if ( $this->user_attr['autorewind'] == 'false' ) print "selected"; ?> >false</option>
			</select>
			 If AutoRewind is true, the cursor will be reset to the start of the movie when the movie is ended.
			</td>
		</tr>
		<tr>
			<th valign="top">Default Skin</th>
			<td><input type='text' name="hflv_skin" value="<?php print $this->user_attr['skin'];?>" size='20' maxlength='50'> 			 
 		 	Automatically used if player 5 (MediaElement.js) or player 6 (Flowplayer 5) is used.<br />
 		 	MediaElement.js: 	
				<a href='javascript:void(0)' onclick='document.default_setting.hflv_skin.value="mejs-ted";'>mejs-ted</a>,
				<a href='javascript:void(0)' onclick='document.default_setting.hflv_skin.value="mejs-wmp";'>mejs-wmp</a>,
				<a href='javascript:void(0)' onclick='document.default_setting.hflv_skin.value="onedesign";'>onedesign</a> <span style='font-size:0.8em'>( Thanks to <a href='http://www.onedesigns.com/freebies/custom-mediaelement-js-skin'>OneDesign</a> )</span>
			FlowPlayer 5: 	
				<a href='javascript:void(0)' onclick='document.default_setting.hflv_skin.value="minimalist";'>minimalist</a>,
				<a href='javascript:void(0)' onclick='document.default_setting.hflv_skin.value="functional";'>functional</a>,
				<a href='javascript:void(0)' onclick='document.default_setting.hflv_skin.value="playful";'>playful</a>
					
			</td>
		</tr>
		<tr>
			<th valign="top">Event Tracking</th>
			<td>
			 <select name="hflv_event_tracking">
			<option value="yes" <?php if ( $this->user_attr['event_tracking'] == 'yes' ) print "selected"; ?> >Yes</option>
			<option value="no" <?php if ( $this->user_attr['event_tracking'] == 'no' ) print "selected"; ?> >No</option>
			</select>
			
			<div style='margin:5px 0 5px 0'>
			Enable Event Tracking in Google Analytics. Only works with Flow Player 3 and MediaElement.js. You must add Google Analytics code separately by hard coding into theme files or by using other plugins.
			<ul style='list-style-type: circle;padding-left:20px;margin:10px'><li>Category:"Videos"</li><li>Actions:"Play","Pause","Stop","Finish"</li><li>Additional played time information is availabe for "Pause" and "Stop"</li></ul>
			Please check this <a href='http://wpmarketing.org/forum/topic/howto-google-analytics-event-tracking-with-hana-flv-player'>forum post</a> for more information about how to use Google Analytics Event Tracking.
			
			</div> 		
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
			<th valign="top">Default more_5</th>
			<td>Automatically used if player 5 (MediaElement.js) is used. You can define the options for the player here.<br />
			<textarea rows="3" cols="70" name='hflv_more_5'><?php echo htmlspecialchars($this->user_attr['more_5']); ?></textarea>
			<div> Check out <a href='http://mediaelementjs.com/'>MediaElement.js homepage</a> => player option section for more details. For example, you can remove the controls completly by setting <code>features: []</code> or to see all control buttons <code> features: ['playpause','progress','current','duration','tracks','volume','fullscreen']</code>
		 			
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

</div>
<div class='division'>
<a name="example" ></a>
   <p>
	<h3>Usage Example:</h3>
	There is a tinymce or quicktag button that you can directly use , but here is the full usage example.
	
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
    skin=""
/]

Another sample of auto height
[hana-flv-player 
    video="<?php print $this->plugin_url; ?>/sarah.flv"
    width="350"
    height=""
    description="Sarah is having fun at the beach"
    player="4"
/]

This is a sample of HTML5 player with mp4 video file playing
[hana-flv-player 
    video="<?php print $this->plugin_url; ?>/hana_sleding.mp4"
    description="Hana is having fun while sleding"
    player="5"
    autoplay="false"
    autoload="true"
    loop="true"
 /]
 
</pre>
<p>When you defined any attribute, please make sure that you use single or double quotes around the attribute value, or my plugin may not recognize the attribute. 
</p>

	Attributes explained:
	<ul style='list-style-type: circle;padding-left:20px;margin:10px' >

		<li><strong>video</strong>: URL of the flv video file. This is mandatory.</li>
		<li><strong>width</strong>: Width of the Flash player.</li>
		<li><strong>height</strong>: Height of the Flash player. If not defined, automatically calculated using 4:3 ratio. If 16:9 ratio is needed, use 'autow' as height.</li>
		<li><strong>description</strong>: Description of the video. This will be shown when <code>the_excerpt()</code> is used. Also it is used within the SWF objects or javascripts, so search engines can read it.</li>
		<li><strong>clickurl</strong>: If you want to open a website when a user clicks on the video, you can define the target website URL here. </li>
		<li><strong>clicktarget</strong>: The target of the URL when clicking on the video. Same window:<code>_self</code>, New window <code>_blank</code></li>
		<li><strong>player</strong>: If set to "1" , <a href='http://www.osflv.com/'>OS FLV</a> will be used. 
			If set to "2", <a href='http://flowplayer.org/'>FlowPlayer</a> will be used. 
			"3" is for <a href='http://flv-player.net/players/maxi/'>FLV Player Maxi</a>.
			"4" is for <a href='http://flash.flowplayer.org'>FlowPlayer 3(3.2.3)</a>.
			"5" is for <a href='http://mediaelementjs.com/'>MediaElement.js HTML5 player</a>.
			"6" is for <a href='http://flowplayer.org/'>FlowPlayer 5 HTML5 player</a>.
			</li>
		<li><strong>autoload</strong>: If true, the movie will be loaded (downloaded). If false, the starting screen will be blank since no video is downloaded.</li>
		<li><strong>autoplay</strong>: If true, the movie will play automatically when the page is loaded.</li>
		<li><strong>loop</strong>: If Loop is true, the movie will replay itself constantly.</li>
		<li><strong>autorewind</strong>: If AutoRewind is true, the cursor will be reset to the start of the movie when the movie is ended.</li>
		<li><strong>skin</strong>: Automatically used if player 5 (MediaElement.js) or player 6 (FlowPlayer 5) is used. Example: mejs-ted, mejs-wmp</li>
		
		<li><strong>splashimage</strong>: Only works with FlowPlayer and FLV player Maxi. When autoload is off, this splash image will be shown in the player. It only supports JPEG images.</li>
		<li><strong>more_2</strong>: more options for the Flow Player v2.  
		<li><strong>more_3</strong>: more options for the Flv Player. 
		<li><strong>more_4</strong>: more options for the Flow Player v3. 
		<li><strong>more_5</strong>: more options for MediaElement.js. 
 
		
	</ul>

</div>

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
 
 
<div class='division'>

	<h3><a href='javascript:void(0);' onclick="jQuery('#pane3').toggle('fast');">More Attributes (more_2, more_3, more_4) Sample Generator</a></h3>
	By using 'more_2','more_3','more_4' attributes, you can use advanced features of the each players. Especially this javascript generator is focused on the interface design option. After selecting the options you want, you can click the 'Generate' button to generate the sample usage in the output textarea.
	<a href='javascript:void(0);' onclick="jQuery('#pane3').toggle('fast');">Click here to see the form</a>
<div id='pane3' style='display:none'>
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
</div>
</div>

<div class='division'>
    
<h3><a href='javascript:void(0);' onclick="jQuery('#pane4').toggle('fast');">Insert flv into template theme files (such as sidebar.php)</a></h3>
You can use [hana-flv-player] shorttag within the text widget, but if you want to implement in the template files, you can follow <a href='javascript:void(0);' onclick="jQuery('#pane4').toggle('fast');">this procedure</a>.
<div id='pane4' style='display:none'>
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
</div>

</div>    

    <div style='margin-top:10px'><u><strong>Note:</strong> Be careful when you use other website's video file as rthe video source. Since video files are usually large is size they can use up the bandwidth quickly. 
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
			if ( $_POST['hflv_height']=='' || is_numeric($_POST['hflv_height']) || $_POST['hflv_height']=='auto' || $_POST['hflv_height']=='autow' )
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
	    if ( isset($_POST['hflv_event_tracking']) ) {
			if ($_POST['hflv_event_tracking'] =='yes' || $_POST[hflv_event_tracking] =='no' )
				$this->user_attr['event_tracking'] = $_POST['hflv_event_tracking'];
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
		if ( isset($_POST['hflv_more_5']) ) {
				$this->user_attr['more_5'] = str_replace("\\",'',$_POST['hflv_more_5']);
		}
		if ( isset($_POST['hflv_skin']) ) {
				$this->user_attr['skin'] = str_replace("\\",'',$_POST['hflv_skin']);
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
		
		//wp_enqueue_script only works  in => 'init'(for all), 'template_redirect'(for only public) , 'admin_print_scripts' for admin only
		if (function_exists('wp_enqueue_script')) {
			$jspath='/'. PLUGINDIR  . '/'. $this->plugin_folder.'/jqModal/jqModal.js';
			wp_enqueue_script('jqmodal_hana', $jspath, array('jquery'));
			
			wp_enqueue_script('media-upload');
			wp_enqueue_script('thickbox');
			//wp_register_script('hana-script', $this->plugin_url.'/hana-script.js', array('jquery','media-upload','thickbox'));
		    //wp_enqueue_script('hana-script');
		}
		
	}

	function admin_style() {
		wp_enqueue_style('thickbox');
	}
	
	function print_javascript () {
	 
?>
   <!--  for popup dialog -->
   <link href="<?php echo $this->plugin_url . '/jqModal/jqModal.css'; ?>" type="text/css" rel="stylesheet" />

   <script type="text/javascript">
    function show_hana_flv_btn(){
    	jQuery('#dialog_hanaflv').jqmShow();
    	jQuery.ajax({ 
    		url: '<?php echo $this->plugin_url ;?>/plugin_feed.php',
    		type: 'GET',
    		data: 'id=hana-flv-player',
    		dataType: 'html',
    		beforeSend: function() {
    			jQuery('#hana_flv_notice').html("");
    		},
    		success: function(data, textStatus, xhr) {
    			jQuery('#hana_flv_notice').html(data);
    		},
    		error: function(xhr, textStatus, errorThrown) {
    			jQuery('#hana_flv_notice').html("<center>Thank you for using my plugin! Visit my website <a href='http://wpmarketing.org'>http://wpmarketing.org</a></center>");
    		}
    	});
    }

    //This is for quicktag HTML mode (refering to wp-includes/js/quicktags.dev.js)
    function click_hana_flv_btn(){
    	show_hana_flv_btn();
    }

	
   	jQuery(document).ready(function(){
		// Add the buttons to the HTML view
	    if (QTags && typeof QTags.addButton == 'function' ) { // WP 3.3+
			QTags.addButton('hana_flv_btn','Hana FLV',click_hana_flv_btn);
			
	    }else{ // Previous WP versions
			jQuery("#ed_toolbar").append('<input type=\"button\" class=\"ed_button\" onclick=\"show_hana_flv_btn();\" title=\"Hana Flv Player\" value=\"Hana Flv\" />');
		}
   	});
	 
	jQuery(document).ready(function () {
		
		jQuery('#dialog_hanaflv').jqm({modal:false});	


		jQuery('#video_upload_button').click(function() {
			formfield = jQuery('#video_file').attr('name');
			tb_show('', 'media-upload.php?type=video&amp;TB_iframe=true');
			jQuery('#TB_window').css('z-index',4000);
			return false;
		});

		var original_send_to_editor=window.send_to_editor;		
		window.send_to_editor = function(html_text) {
			if ( jQuery("#dialog_hanaflv").is(":visible") ) {
				html_text='<div>'+html_text+'</div>'; // not sure, but need to wrap with extra tag to make this work.
				vurl =jQuery('a',html_text).attr('href');
				jQuery('#video_file').val(vurl);
				tb_remove();
			}else {
				original_send_to_editor(html_text);
			}			  
		}
		
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

		if (f.skin.value.length > 0)		
			text +='    skin="'+f.skin.value+'" \n';
		
			
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
	<div style='font-size:1.2em;font-weight:bold;margin:10px;'><a href='http://wpmarketing.org/plugins/hana-flv-player/' target='_new'>Hana Flv Player</a></div>
	 

	 
	<form name='hanaflvoptions' onsubmit='return false;' >
	
	<table style='text-align:left;width:100%;'>
		<tr> 
			<td valign='top' width='90'>Video URL (required)</td>
			<td><input type='text' size='90' name='video' id="video_file" />
			<span class='submit'><input id="video_upload_button" type="button" value="Upload or Browse Video" 
				title='Click on the "Insert into Post" button to select file' /></span>
		</tr>	
		<tr>
			<td valign="top">Description</td>
			<td><input type='text' name='description' size='90'/>			
			</td>
		</tr>
		<tr>
			<td valign="top">Video Player</td>
			<td><select name="player">
			<option value="1" <?php if ($this->user_attr['player'] == '1' ) print "selected"; ?> >1. OS FLV player (GPL) - Flash player only</option>
			<option value="2" <?php if ($this->user_attr['player'] == '2' ) print "selected"; ?> >2. Flow Player 2 (GPL) - Flash player only</option>
			<option value="3" <?php if ($this->user_attr['player'] == '3' ) print "selected"; ?> >3. FLV Player Maxi (CC, MPL1.1) - Flash player only</option>
			<option value="4" <?php if ($this->user_attr['player'] == '4' ) print "selected"; ?> >4. Flow Player 3 (GPL) - Flash player only</option>
			<option value="5" <?php if ($this->user_attr['player'] == '5' ) print "selected"; ?> >5. Mediaelement.js (GPL) - HTML5 & Flash video player</option>
			<option value="6" <?php if ($this->user_attr['player'] == '6' ) print "selected"; ?> >6. Flow Player 5 (GPL) - HTML5 & Flash video player</option>
			
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
			<span style='font-size:0.8em'>'<a href='javascript:void(0)' onclick='document.hanaflvoptions.height.value="auto";'>auto</a>' for 4:3 auto height, 
			'<a href='javascript:void(0)' onclick='document.hanaflvoptions.height.value="autow";'>autow</a>'  for 16:9 auto height</span>
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
			<td valign="top">Player Skin</td>
			<td><input type='text' name='skin' size='20'  value="<?php print $this->user_attr['skin'];?>" />
			<div style='font-size:0.8em'>For MediaElement.js Player : 
				<a href='javascript:void(0)' onclick='document.hanaflvoptions.skin.value="mejs-ted";'>mejs-ted</a>,
				<a href='javascript:void(0)' onclick='document.hanaflvoptions.skin.value="mejs-wmp";'>mejs-wmp</a>
				<a href='javascript:void(0)' onclick='document.hanaflvoptions.skin.value="onedesign";'>onedesign</a>
			</div>
			<div style='font-size:0.8em'>For FlowPlayer 5 Player : 
				<a href='javascript:void(0)' onclick='document.hanaflvoptions.skin.value="minimalist";'>minimalist</a>,
				<a href='javascript:void(0)' onclick='document.hanaflvoptions.skin.value="functional";'>functional</a>
				<a href='javascript:void(0)' onclick='document.hanaflvoptions.skin.value="playful";'>playful</a>
			</div>
			</td>
		</tr>
		<tr>
			<td valign="top">Click URL</td>
			<td><input type='text' name='clickurl' size='90' />			
			</td>
		</tr>
		<tr>
			<td valign="top">Click Target</td>
			<td><input type='text' name='clicktarget' size='10' />
			<span style='font-size:0.8em'><a href='javascript:void(0)' onclick='document.hanaflvoptions.clicktarget.value="_self";'>_self</a> (same window) , <a href='javascript:void(0)' onclick='document.hanaflvoptions.clicktarget.value="_blank";'>_blank</a> (new window)			
			</td>
		</tr>
		<tr>
			<td valign="top">Splash Image URL</td>
			<td><input type='text' name='splashimage' size='90'/>			
			</td>
		</tr>
		
	</table>

	<p>
	 	<span class='submit'><input type='button' value='OK' style='width:60px' onclick='update_hanaflvplayer()'; >
	 	<input type='button' value='Cancel' onclick="jQuery('#dialog_hanaflv').jqmHide();" style='width:60px'>
	 	</span>
	</p>
	</div>	
	
	</form>	 
    <hr style='width:300px' />
    <div id='hana_flv_notice' style='margin:auto; width:100%;height:50px; padding:0;overflow:hidden;'></div>
	
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

