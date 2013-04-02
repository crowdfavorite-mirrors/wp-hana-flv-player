=== Hana Flv Player ===
Contributors: nurungji
Donate link: http://wpmarketing.org/plugins/hana-flv-player/
Tags: Video, FLV, Flash video, HTML5 video, Flowplayer, MediaElement.js, Free for Commercial, iPad, iPhone, Android
Requires at least: 2.0
Tested up to: 3.5.1
Stable tag: 3.1.2

Easily embed videos(Flash & HTML5) in your WordPress featuring Flowplayer(version 2, 3, and 5), OS FLV player, FLV Player Maxi, and MediaElement.js. 

== Description ==

Now you can easily embed the FLV Flash videos in your WordPress Blog. I have packaged the three FLV Flash player - [OS FLV](http://www.osflv.com/) , [FlowPlayer](http://flowplayer.org/) v2, v3, and v5 , and [MediaElement.js](http://mediaelementjs.com/) (for HTML5 player support). So you can use them freely without worries even for the commercial purpose unlike the JW player. (You can also use [FLV Player Maxi](http://flv-player.net/players/maxi/) but it is no longer included by default because it is licensed under non GPL compatible licenses starting from v2.7.1. You need to download it and unzip it under hana-flv-player plugin path manually to use it.)

= Mobile device (iPod, iPhone, iPad, and Android phones and tablets) support = 

How about iPod, iPad, and iPhone support? Flash is not supported under iPod, iPhone, and iPad. So the flash FLV players (Flowplayer 2 & 3, OS FLV player, and FLV Player Maxi) will not work with Apple devices. So I have added MediaElement.js and FlowPlayer5 HTML5 players to support Apple devices. They both support Android phones and tablets, too. So here are the current requirements to support Apple and Android devices.

* You must select MediaElement.js or FlowPlayer5 as the player if you want to support Apple devices.
* Apple browser HTML5 function does not support FLV videos. So to be compatible 100% with Apple devices, you must encode video as h.264 (and give it mp4 extension) since it is supported by both Apple browser and Flash player. 
* Also, please note that older Android OS versions 2.x only able to play specifically encoded video files. I discussed more about this in my [forum](http://wpmarketing.org/forum/topic/hana-flv-player-supported-video-types-flv-h264mp4).

= Skin support =

MediaElement.js and FlowPlayer v5 HTML5 players provides predefined skins which helps to change the design of the video player controls. Hana FLV Player has the capability to support these skin functions. You can easily change the skin by assigning 'skin' attribute.


= Usage Example = 

You can place the specific tag element `[hana-flv-player]` in your wordpress article to show the video. The 'video' attribute is mandatory. There are other optional attributes. Default values for the options can be defined in this pages. See the bottom of the page for the example.

`[hana-flv-player 
    video="http://yourwebsite.com/wp-content/plugins/hana-flv-player/babyhana.flv"
    width="400"
    height="320"
    description="Sarah is having fun in a merry-go-round"
    clickurl="http://yourwebsite.com/"
    clicktarget="_blank"
    player="5"
    autoplay="false"
    loop="false"
    autorewind="true"
    splashimage="http://yourwebsite.com/wp-content/plugins/hana-flv-player/splash.jpg"
    skin="mejs-wmp"
/]`


Attributes explained:

*   video: URL of the flv video file. This is mandatory.
*   width: Width of the Flash player.
*   height: Height of the Flash player. If not defined, automatically calculated using 4:3 ratio. If 16:9 ratio is needed, use 'autow' as height.
*   description: Description of the video. This will be shown when `the_excerpt()` is used. Also it is used within the SWF objects or javascripts, so search engines can read it.
*   clickurl: If you want to open a website when a user clicks on the video, you can define the target website URL here.
*   clicktarget: The target of the URL when clicking on the video. Same window:`_self`, New window `_blank`
*   player: If set to "1" , OS FLV will be used. If set to "2", FlowPlayer v2 will be used. "3" is for FLV Player Maxi. "4" is the new FlowPlayer v3. "5" is for the latest MediaElement.js HTML5 player. "6" is for the FlowPlayer v5 HTML5 player.
*   autoload: If true, the movie will be loaded (downloaded). If false, the starting screen will be blank since no video is downloaded.
*   autoplay: If true, the movie will play automatically when the page is loaded.
*   loop: If Loop is true, the movie will replay itself constantly.
*   autorewind: If AutoRewind is true, the cursor will be reset to the start of the movie when the movie is ended.
*   skin: Automatically used if player 5 (MediaElement.js) or player 6 (FlowPlayer 5) is used. Example: mejs-ted, mejs-wmp
*   splashimage: Only works with FlowPlayer, Maxi, and MediaElement.js. When autoload is off, this splash image will be shown in the player. It only supports JPEG images.
*   more_2: more options for Flow Player v2.
*   more_3: more options for FLV player
*   more_4: more options for FLV player v3
*   more_5: more options for MediaElement.js

= Event Tracking in Google Analytics =

Starting from version 2.7 , Event Tracking in Google Analytics function is supported. It only works with Flow Player v3 and MediaElement.js. Also, you must add the Google Analytics tracking code manually in the theme files or by using other plugins such as 'WP Hooks'. Once you turn on the Event Tracking feature by setting to 'Yes' under Default Settings screen, you will be able to see the result in the Google Analytics screen. (Check out the screenshots 5 ) For more information, please check http://wpmarketing.org/forum/topic/howto-google-analytics-event-tracking-with-hana-flv-player

*   Category:"Videos"
*   Actions:"Play","Pause","Stop","Finish"
*   Additional played time information is availabe for "Pause" and "Stop"

= More Attributes (more_2, more_3, more_4, more_5) Sample Generator: =

By using 'more_2', 'more_3', 'more_4', 'more_5' attributes, you can use advanced features of the each players. There are tons of other options for the customization. You can use most of the options through these attributes. You can start testing by using the javascript generated provided in the settings menu. It is mainly focused on the interface design option. After selecting the options you want, you can click the 'Generate' button to generate the sample usage in the output textarea.

= Insert flv into template theme files (such as sidebar.php): =

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

= Does this support HTML5 video? =

I added MediaElement.js for HTML5 support.

= How about iPod, iPad, and iPhone support? =

Flash is not supported under iPod, iPhone, and iPad. So the flash FLV players (Flowplayer, OS FLV player, and FLV Player Maxi) will not work with Apple devices. So I have added MediaElement.js HTML5 player and FlowPlayer 5 player to support Apple devices. Also, remember that Apple browser does not support FLV videos. So to be compatible 100% with Apple devices, you must encode video as h.264 (mp4) since it is supported by both Apple browser and flash players version 9+. 

== Screenshots ==

1. Plugin Settings Page.
2. Adding the hana-flv-player tag in an article.
3. Example video working.
4. New Popup Dialog for Tag creation. It can be accessed from posting tool bar button.
5. Google Analytics Event Report Screen
6. Skin Samples (Only for MediaElement.js and FlowPlayer5)

== Change Log ==

= v3.1.2 (01/28/2013): =

* Fixing a bug in v 3.1.1 - Media Browser does not insert tag into the post textarea even when not using Hana FLV Dialog. Sorry again! 

= v3.1.1 (01/27/2013): =

* Fixing a mistake in v 3.1.0 - showing "// due to the bug - IE8 does not show video" string. Sorry!

= v3.1.0 (01/26/2013): =

* Added Media Uploader & Library browsing capability from the Hana Flv Player Modal Dialog.
* Player updated : FlowPlayer v5.3.1 
* Player updated : MediaElement.js v2.10.1
* Bug fix - has_cap warning - http://wordpress.org/support/topic/has_cap-warning?replies=2
* Bug fix - MediaElement.js IE8 video issue - http://wordpress.org/support/topic/plugin-hana-flv-player-hana-flv-shows-no-video-only-sound-in-ie8-only?replies=9

= v3.0.0 (12/04/2012): =

* Updated MediaElement.js player with the latest v2.10.0
* Added support for the latest FlowPlayer v5.2 
* Skin support for MediaElement.js and FlowPlayer v5.2
* Added custom MediaElement skin prepared by OneDesign (http://www.onedesigns.com/freebies/custom-mediaelement-js-skin)

= v2.9.3 (06/08/2012): =

* [hana-flv-player] shortcode support in text widget. 
* Fixed unwanted subtitle issue with FLV Player Maxi.

= v2.9.2 (06/05/2012): =

* Updated MediaElement.js player with the latest v2.9.1
* Since MediaElement.js requires jQuery library, I added mandatory jQuery library calling routine in the last version. However some error were reported due to this, I updated to load jQuery dynamically only when jQuery is not loaded previously and MediaElement.js player is used.

= v2.9.1 (05/15/2012): =

* Added 'clickurl' feature for MediaElement.js player.
* Updated MediaElement.js player with v2.8.2.
* Youtube video is supported when you use MediaElement.js Player, but some improvements are needed with the player itself.
* Fixed IE 7 HanaFlv button Modal Dialog under overlay problem.

= v2.9 (05/06/2012): =

* Additional MediaElement.js (v2.8.1) player enhancements and fix. MediaElement.js is finally stablized and you can enjoy the truly complete Video Player almost all browsers out there  including Firefox, Chrome, Safari, Internet Explore, iPod, iPhone, iPad, and Android (phone and tablets).
* For MediaElement.js, When "autoplay" attribute is enabled, the video doesn't seem to work under Android and iOS at all. According to the Internet search, the autoplay feature is intentionally blocked for Android and iOS to prevent any unwanted high data bandwidth usage charge. So I have added a javascript routine to activate "autoplay" attribute if the client browser is a non mobile version.
* "more_5" attribute is added that you can use additional MediaElement.js options ( Refer to 'Player Options' in http://mediaelementjs.com/) One of the features is that you can activate or disable the video control buttons.
* Added Event Tracking in Google Analytics for MediaElement.js player too.
* Please check out  http://wpmarketing.org/2012/05/support-video-for-all-browsers-by-adding-mediaelement-js-to-hana-flv-player/ for additional details.


= v2.8.3 (05/02/2012): =

* For MediaElement.js player, few bugs related to preload and autoplay features are fixed. But it still is unstable compared to flash video player due to different HTML5 video implementation of each browser.

= v2.8.2 (04/24/2012): =

* If Flash video player is used and Apple device is used to view the website, gray square box shows up to indicate the Flash is not supported.
* Minor fixes for MediaElement.js implemenation

= v2.8.1 (04/22/2012): =

* 'Automatic fallback feature to MediaElement.js for Apple devices' feature is disabled due to the problem when any Wordpress cache plugin is used. Since the detection is done within the PHP side, if cache is used, the detection won't work properly. But you can just set to use MediaElement as the player to support Apple devices.

= v2.8 (04/17/2012): =

* Added MediaElement.js v2.8.0 (HTML5 player). I think this is the best HTML5 players out there with flash fallback and Appple device support. However, 'clickurl' attribute is not supported for this player. It does not have the feature yet. 

= v2.7.1 (04/11/2012): =

* Starting from V2.7.1 , FLV Player Maxi is no longer included by default because it is licensed under non GPL compatible licenses (CC, MPL 1.1). But you can still download and unzip it manually under your Hana FLV Player plugin path. 

= v2.7 (04/10/2012): =

* Fixed "Missing quicktag button in the HTML editor" problem with WordPress v3.3+
* Added Event Tracking in Google Analytics for Flow Player 3 (player 4) only
* Auto height setting feature added. If height is not defined, it will be auto calculated.

= v2.6 (07/10/2010): =

* Flowplayer v.3.2.7 upgraded
* Flowplayer Commercial version support (only for v3.2)
* Fix minor layout problem with WordPress v3.2

= v2.5 (08/27/2010): =

* Flowplayer v.3.2.2 upgraded
* Custom options (more_4) for Player 4 (Flowplayer v3) added

= v2.4.2 (11/14/2009): =

* Bug fixed with clicktarget & player 4 (Flowplayer v3)

= v2.4.1 (10/26/2009): =

* Bug fixed with clicktarget & player 4 (Flowplayer v3)

= v2.4 (10/25/2009): =

* Description attribute is created for search engine and the_exerpt function 
* clicktarget attribute is added. Use `_self` for click url to show in the same window. `_blank` for new window
* Default setting for `more_2` and `more_3` were created. If default is saved, they will be used if corresponding player is used and more_2 or more_3 attribute is not defined.

= v2.3 (9/20/2009): =

* In v2.2 , Player 2 (FlowPlayer 2) did not work due to missing javascript file - fixed. Sorry for the bug. 
* Large files from the original flash player distribution are removed due to installation memory failure when installing from the WordPress Installation Screen. You can still get the complete original distribution files from the older version (Hana Flv Player v2.0 and older.)

= v2.2 (9/17/2009): =

* z-index overlay issue (flash player is showing through the other higher z-index objects) is fixed. May not work with older browsers.
* Loop is not working for Flowplayer 2 -unresolved:I just can't find any resource on it. But fixed the loop with Flowplayer v3.

= v2.0 (7/23/2009): =

* For easier Hana Flv Video tag creation, new popup dialog is added. You can directly define the values using the form and click OK to create the tag. Please see the screenshot 4 for the example. The buttons in the editor will only appear for WP v2.5+

= v1.8 (7/21/2009): =

* Fixed Minor bug with FlowPlayer v2 . It was not properly showing under IE7.
* FlowPlayer v3.1.1 player is added. Originally intended to upgrade the old version, but decided to leave old version due to some minor differences. 

= v1.7: =

* Additional option can be defined for FlowPlayer and Flv Player using `more_2` and `more_3` attribues.
* Template theme file support function added.
* FlowPlayer version 2.2.1 from 2.1.3.


