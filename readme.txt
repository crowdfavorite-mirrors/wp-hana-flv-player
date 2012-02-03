=== Hana Flv Player ===
Contributors: HanaDaddy
Donate link: http://wpmarketing.org/plugins/hana-flv-player/
Tags: FLV, Flash video,Free for Commercial, Flowplayer, Maxi,Flv player
Requires at least: 2.0
Tested up to: 3.2
Stable tag: 2.6

Easily embed the Flash Video in your Wordpress featuring Flowplayer(version 2 and 3), OS FLV player, FLV Player Maxi.

== Description ==

Now you can easily embed the FLV Flash videos in your WordPress Blog. I have packaged the three FLV Flash player - [OS FLV](http://www.osflv.com/) , [FlowPlayer](http://flowplayer.org/) both v2 and v3, and [FLV Player Maxi](http://flv-player.net/players/maxi/). So you can use them freely without worries even for the commercial purpose unlike the JW player.

You can place the specific tag element `[hana-flv-player]` in your wordpress article to show the video. The 'video' attribute is mandatory. There are other optional attributes. Default values for the options can be defined in this pages. See the bottom of the page for the example.

`[hana-flv-player 
    video="http://yourwebsite.com/wp-content/plugins/hana-flv-player/babyhana.flv"
    width="400"
    height="320"
    description="Sarah is having fun in a merry-go-round"
    clickurl="http://yourwebsite.com/"
    clicktarget="_blank"
    player="4"
    autoplay="false"
    loop="false"
    autorewind="true"
    splashimage="http://yourwebsite.com/wp-content/plugins/hana-flv-player/splash.jpg"
/]`


Attributes explained:

*   video: URL of the flv video file. This is mandatory.
*   width: Width of the Flash player.
*   height: Height of the Flash player.
*   description: Description of the video. This will be shown when `the_excerpt()` is used. Also it is used within the SWF objects or javascripts, so search engines can read it.
*   clickurl: If you want to open a website when a user clicks on the video, you can define the target website URL here.
*   clicktarget: The target of the URL when clicking on the video. Same window:`_self`, New window `_blank`
*   player: If set to "1" , OS FLV will be used. If set to "2", FlowPlayer v2 will be used. "3" is for FLV Player Maxi. "4" is the new FlowPlayer v3.
*   autoload: If true, the movie will be loaded (downloaded). If false, the starting screen will be blank since no video is downloaded.
*   autoplay: If true, the movie will play automatically when the page is loaded.
*   loop: If Loop is true, the movie will replay itself constantly.
*   autorewind: If AutoRewind is true, the cursor will be reset to the start of the movie when the movie is ended.
*   splashimage: Only works with FlowPlayer and Maxi. When autoload is off, this splash image will be shown in the player. It only supports JPEG images.
*   more_2: more options for Flow Player v2.
*   more_3: more options for FLV player
*   more_4: more options for FLV player v3

More Attributes (more_2, more_3) Sample Generator:

By using 'more_2', 'more_3', and 'more_4' attributes, you can use advanced features of the each players. There are tons of other options for the customization. You can use most of the options through these attributes. You can start testing by using the javascript generated provided in the settings menu. It is mainly focused on the interface design option. After selecting the options you want, you can click the 'Generate' button to generate the sample usage in the output textarea.

Insert flv into template theme files (such as sidebar.php):

Okay, here is the function that you can use in the theme template files to show FLV movie. Basically you need to use `hana_flv_player_template_call` method. The method takes a single argument. The argument should be just the string of the attributes of usage explained the above. Just copy below code into your theme file and edit red colored attributes accordingly. 

`<?php
if (function_exists('hana_flv_player_template_call')){
	$hana_arg="
video='http://localhost/wp/wp-content/plugins/hana-flv-player/babyhana.flv'
player='2'
width='180'
height='150'
more_2=\"showStopButton: false, showScrubber: false, showVolumeSlider: false,showMuteVolumeButton: false, 
showFullScreenButton: false, showMenu: false, controlsOverVideo: 'locked',controlBarBackgroundColor: -1,
controlBarGloss: 'none', usePlayOverlay:false \"
";
	echo hana_flv_player_template_call($hana_arg);	
}
?>`


Note: Be careful when you use other website's video file as the video source. Since video files are usually large is size they can use up the bandwidth quickly. So you should ask for the owner's permission before using that link to the file.

Thank you for using my plugin. -  [HanaDaddy](http://neox.net/)

Have Questions? [Hana Flv Player Plugin Forum](http://wpmarketing.org/forum/forum/wp-plugin-hana-flv-player)


== Installation ==

This section describes how to install the plugin and get it working.

1. Download and unzip the zip file. Upload `hana-flv-player` folder with all of its contents to the `/wp-content/plugins/` directory
2. Activate the plugin through the `Plugins` menu in WordPress Admin Interface.
3. Adjust the default settings in the 'Settings' menu in the Admin Interface if you want to.
4. Use `[hana-flv-player video='...'/]` in your blog article.  Attribute `video` is the only mandatory item where you define the video file. It can be full URL or absolute or relatvie path.

If you want a quick test and see if it's working fine, goto Hana Flv Player settings admin page and copy the example shown in the bottom and paste in your blog article.

== Frequently Asked Questions ==

= Can I use Hana Flv Player in my commercial website? =

Yes. All the flash players are under GPL licese and you are free to use them in a commercial website.

= I would like to save my bandwidth as best as possible. What settings would guarantee the minimum usage? =

Set autoplay and autoload to false in default setting admin page. You can override default by defining attributes inside the hana flv player tag.

= While other players are working, Flow Player 3 is not working. What's wrong? =

I found that when you use the FlowPlayer v2 (old) and v3 (new) at the same time within the same post, FlowPlayer 3 is not working properly sometimes. This must be caused by the the javascript confict between v2 and v3.  For now,  just use the same version for all the videos in a post.

== Screenshots ==

1. Plugin Settings Page.
2. Adding the hana-flv-player tag in an article.
3. Example video working.
4. New Popup Dialog for Tag creation. It can be accessed from posting tool bar button.

== Change Log ==
v2.6 (07/10/2010):
* Flowplayer v.3.2.7 upgraded
* Flowplayer Commercial version support (only for v3.2)
* Fix minor layout problem with WordPress v3.2

v2.5 (08/27/2010):

* Flowplayer v.3.2.2 upgraded
* Custom options (more_4) for Player 4 (Flowplayer v3) added

v2.4.2 (11/14/2009):

* Bug fixed with clicktarget & player 4 (Flowplayer v3)

v2.4.1 (10/26/2009):

* Bug fixed with clicktarget & player 4 (Flowplayer v3)

v2.4 (10/25/2009):

* Description attribute is created for search engine and the_exerpt function 
* clicktarget attribute is added. Use `_self` for click url to show in the same window. `_blank` for new window
* Default setting for `more_2` and `more_3` were created. If default is saved, they will be used if corresponding player is used and more_2 or more_3 attribute is not defined.

v2.3 (9/20/2009):

* In v2.2 , Player 2 (FlowPlayer 2) did not work due to missing javascript file - fixed. Sorry for the bug. 
* Large files from the original flash player distribution are removed due to installation memory failure when installing from the WordPress Installation Screen. You can still get the complete original distribution files from the older version (Hana Flv Player v2.0 and older.)

v2.2 (9/17/2009):

* z-index overlay issue (flash player is showing through the other higher z-index objects) is fixed. May not work with older browsers.
* Loop is not working for Flowplayer 2 -unresolved:I just can't find any resource on it. But fixed the loop with Flowplayer v3.

v2.0 (7/23/2009):

* For easier Hana Flv Video tag creation, new popup dialog is added. You can directly define the values using the form and click OK to create the tag. Please see the screenshot 4 for the example. The buttons in the editor will only appear for WP v2.5+

v1.8 (7/21/2009):

* Fixed Minor bug with FlowPlayer v2 . It was not properly showing under IE7.
* FlowPlayer v3.1.1 player is added. Originally intended to upgrade the old version, but decided to leave old version due to some minor differences. 

v1.7:

* Additional option can be defined for FlowPlayer and Flv Player using `more_2` and `more_3` attribues.
* Template theme file support function added.
* FlowPlayer version 2.2.1 from 2.1.3.


