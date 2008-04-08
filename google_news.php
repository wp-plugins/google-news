<?php
/*
Plugin Name: Google News
Description: Adds a sidebar widget to display a selectable Google News RSS feed
Version:     1.0
Author:      Olav Kolbu
Author URI:  http://www.kolbu.com/
Plugin URI:  http://wordpress.org/extend/plugins/google-news/
License:     GPL

Some WordPress-specific code from various other GPL plugins.

TODO: Possibly add sprintf-ability so people can add date, etc(?)
*/
/*
Copyright (C) 2008 kolbu.com (olav AT kolbu DOT com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

function widget_google_news_widget_init() {

	// Check for the required plugin functions. This will prevent fatal
	// errors occurring when you deactivate the dynamic-sidebar plugin.
	if ( !function_exists('register_sidebar_widget') )
		return;
	
	function widget_google_news_widget_get_feed($region, $newstype, $outputtype, $query, $numnews, $longdesc)
	{
		$result = '<ul>';
		$feedurl = 'http://news.google.com/news?output=rss';

		// This will also handle mixed mode text/image, when
		// we get the parsing under control...
		if ( $outputtype == 't' ) { 
		    $region = 't'.$region;  // Consistent API, wassat?
		} else if ( strlen($outputtype) ) {
		    $feedurl .= $outputtype;
		}
		$feedurl .= "&ned=$region";	
		if ( strlen($newstype) ) {
		    $feedurl .= "&topic=$newstype";
		}
		if ( strlen($query) ) {
		    $squery = urlencode(strtolower($query));
		    $feedurl .= "&q=$squery";
		}
		ini_set('user_agent','Mozilla;');
		if ($RemoteFile = fopen($feedurl, "r")) {
			$buffer = "";
			if ($RemoteFile) {
				while (!feof($RemoteFile)) {
				    $buffer .= fgets($RemoteFile, 1024);
				}
			}
			fclose($RemoteFile);
		} else {
			return "Google News unavailable<br>$errorstr ($errno)</ul>";
		}

		preg_match_all('/([^<]*)/', $buffer, $matches);
                $itemCount = 0; 
		foreach ($matches[0] as $line) {
			if ( !strlen($line) ) {
			    continue;
			}
			if (eregi ('^description>(.*)', $line, $out)) {
			    $description = htmlspecialchars_decode($out[1]);


			    // All this is bound to break, but Google 
			    // doesn't know usable markup from squat

			    // As per Google TOC, we need to retain related link
			    preg_match("|(<a class=p [^>]+><nobr>[^<]+</nobr></a>)|", $description, $related);

			    // Try some tricks to lose useless markup
			    $bloc = strpos($description, "<font");
			    if ( $bloc ) {
				$description = substr($description, $bloc);
			    }
			    $eloc = strpos($description, "<a href=",strpos($description, "<a href=")+1);
			    if ( $eloc ) {
				$description = substr($description,0,$eloc);
			    }

			    // No markup in tooltips
			    $tooltip = preg_replace("/<[^>]+>/","",$description);
			    $patterns = array(
					"/<(td|tr|table|div|font|ul|li)[^>]*>/",
					"/<.(td|tr|table|div|font|ul|li)[^>]*>/",
					);
			    $replacements = array(
					"",
					);
			    $description = preg_replace($patterns, $replacements, $description);
			    $description = preg_replace("|<br>|", "", $description, 1);
			    $description = preg_replace("|(<img src[^>]+>)<br>([^<]+</a>)|", "\\1\\2<br>", $description, 1);
			    $description = preg_replace("|</div><br><div|", "</div><div", $description);
			    $description .= $related[1];
			}
			if (eregi ('^title>(.*)', $line, $out)) {
				$title = $out[1];
			}
			if (eregi ('^pubDate>(.*)', $line, $out)) {
				$date = $out[1];
			}
			if (eregi ('^link>(.*)', $line, $out)) {
				$link = $out[1];
			}
			if (eregi ('^/item>', $line, $out)) {
				if ( $longdesc ) {
				    $result .= "<li>$description</li>";
				} else {
				    $result .= "<li><a href=\"$link\" target=\"_blank\" title=\"$tooltip\">$title<br>$related[1]</a></li>";
				}
				$title = 0;
				$description = 0;
				$date = 0;
				$link = 0;
				$itemCount += 1;
				if ( $itemCount >= $numnews ) {
				    return $result.'</ul>';
				}
			}
		}
		return $result.'</ul>';
	}

	function widget_google_news_widget($args) {
	    extract($args); // Gives us $before_ and $after_ I presume
			
	    // Each widget can store its own options
	    $options = get_option('widget_google_news_widget');
	    $region = $options['region'];
	    $newstype = $options['newstype'];
	    $outputtype = $options['outputtype'];
	    $query = $options['query'];
	    $numnews = $options['numnews'];
	    $longdesc = $options['longdesc'];
	    $title = $options['usefeedname'] ? $options['feedtitle'] : $options['title'];


	    echo $before_widget;
	    echo $before_title . $title . $after_title;
	    $GoogleFeed = widget_google_news_widget_get_feed($region, $newstype, $outputtype, $query, $numnews, $longdesc);
	    echo '<div style="margin-top:5px;text-align:left;">'.$GoogleFeed.'</div>';
	    echo $after_widget;
	}
	
	// This function creates the widget control, using the 
	// built in widget abilities for controlling widgets
	function widget_google_news_widget_control() {

		$options = get_option('widget_google_news_widget');

		// Initial options
		if ( !is_array($options) ) {
		    $options = array("usefeedname" => 1,
				     "numnews" => 5,
				     "region" => "U.S." );
		}
		$regions = array(
			"South Africa" => "en_za",
			"&#20013;&#22269;&#29256; (China)" => "cn",
			"&#39321;&#28207;&#29256; (Hong Kong)" => "hk",
			"भारत (Hindi)" => "hi_in",
			"India" => "in",
			"&#26085;&#26412; (Japan)" => "jp",
			"&#54620;&#44397; (Korea)" => "kr",
			"&#21488;&#28771;&#29256; (Taiwan)" => "tw",
			"&#1497;&#1513;&#1512;&#1488;&#1500; (Israel)" => "iw_il",
			"&#1575;&#1604;&#1593;&#1575;&#1604;&#1605; &#1575;&#1604;&#1593;&#1585;&#1576;&#1610; (Arabic)" => "ar_me",
			"&#1056;&#1086;&#1089;&#1089;&#1080;&#1103; (Russia)" => "ru_ru",
			"Australia" => "au",
			"New Zealand" => "nz",
			"België" => "nl_be",
			"Belgique" => "fr_be",
			"Česká republika" => "cs_cz",
			"Deutschland" => "de",
			"España" => "es",
			"France" => "fr",
			"Greece" => "el_gr",
			"Ireland" => "en_ie",
			"Italia" => "it",
			"Nederland" => "nl_nl",
			"Norge" => "no_no",
			"Österreich" => "de_at",
			"Portugal" => "pt:PT_pt",
			"Schweiz" => "de_ch",
			"Suisse" => "fr_ch",
			"Sverige" => "sv_se",
			"U.K." => "uk",
			"Canada English" => "ca",
			"Canada Français" => "fr_ca",
			"Estados Unidos" => "es_us",
			"México" => "es_mx",
			"U.S." => "us",
			"Argentina" => "es_ar",
			"Brasil" => "pt:BR_br",
			"Chile" => "es_cl",
			"Colombia" => "es_co",
			"Cuba" => "es_cu",
			"Perú" => "es_pe",
			"Venezuela" => "es_ve",
		);
		$newstypes = array(
			__("All") => "",
			__("Top News") => "h",
			__("Foreign") => "w",
			__("Domestic") => "n",
			__("Business") => "b",
			__("Sci/Tech") => "t",
			__("Health") => "m",
			__("Sports") => "s",
			__("Entertainment") => "e",
		);

		$outputtypes = array(
			__("Standard") => "",
			__("Text Only") => "t",
			__("With Images") => "&imv=1",
		);

		if ( $_POST['google_news_widget-submit'] ) {
			$newoptions['title'] = htmlspecialchars($_POST['google_news_widget-title'], ENT_QUOTES);
			$newoptions['usefeedname'] = isset($_POST['google_news_widget-usefeedname']);
			$newoptions['numnews'] = $_POST['google_news_widget-numnews'];
			$newoptions['region'] = $regions[$_POST['google_news_widget-region']];
			$newoptions['newstype'] = $newstypes[$_POST['google_news_widget-newstype']];
			$newoptions['outputtype'] = $outputtypes[$_POST['google_news_widget-outputtype']];
			$newoptions['query'] = $_POST['google_news_widget-query'];
			$newoptions['longdesc'] = isset($_POST['google_news_widget-longdesc']);
			$newoptions['feedtitle'] = "Google News<br>".$_POST['google_news_widget-region']." : ".$_POST['google_news_widget-newstype'];
			if ( $options != $newoptions ) {
			    $options = $newoptions;
			    update_option('widget_google_news_widget', $options);
			}
		}

		$title       = $options['title'];
		$usefeedname = $options['usefeedname'];
		$numnews     = $options['numnews'];
		$tmparr      = array_flip($regions);
		$region      = $tmparr[$options['region']];
		$tmparr      = array_flip($newstypes);
		$newstype    = $tmparr[$options['newstype']];
		$tmparr      = array_flip($outputtypes);
		$outputtype  = $tmparr[$options['outputtype']];
		$query       = $options['query'];
		$longdesc    = $options['longdesc'];
		
		// Here is our little form segment. Notice that we don't need a
		// complete form. This will be embedded into the existing form.
		echo '<p style="text-align:left;"><label for="google_news_widget-title">Title: <input style="width: 20em;" id="google_news_widget-title" name="google_news_widget-title" type="text" value="'.$title.'" /></label></p>';
                echo '<p style="text-align:left;"><input type="checkbox" id="google_news_widget-usefeedname" name="google_news_widget-usefeedname" value="1" '.
                     ($usefeedname?'CHECKED':'').'/> <label for="google_news_widget-usefeedname">Use the feed name as the title</label></p>';
		echo '<select name="google_news_widget-region">';
		foreach ($regions as $k => $v) {
		    echo '<option value="'.$k.'"'.($k==$region?'SELECTED':'').'>'.$k.'</option>';
		}
		echo '</select>';
		echo '<select name="google_news_widget-newstype">';
		foreach ($newstypes as $k => $v) {
		    echo '<option value="'.$k.'"'.($k==$newstype?'SELECTED':'').'>'.$k.'</option>';
		}
		echo '</select>';
		echo '<select name="google_news_widget-outputtype">';
		foreach ($outputtypes as $k => $v) {
		    echo '<option value="'.$k.'"'.($k==$outputtype?'SELECTED':'').'>'.$k.'</option>';
		}
		echo '</select>';
                echo '<p style="text-align:left;"><input type="checkbox" id="google_news_widget-longdesc" name="google_news_widget-longdesc" value="1" '.
                     ($longdesc?'CHECKED':'').'/> <label for="google_news_widget-longdesc">Use long description instead of short</label></p>';
		echo '<p style="text-align:left;"><label for="google_news_widget-numnews"><input style="width: 2em;" id="google_news_widget-numnews" name="google_news_widget-numnews" type="text" value="'.$numnews.'" /> Max number of news items to show</label></p>';
		echo '<p style="text-align:left;"><label for="google_news_widget-query">Query: <input style="width: 20em;" id="google_news_widget-query" name="google_news_widget-query" type="text" value="'.$query.'" /></label></p>';
		echo '<p style="text-align:right;"><em>Changes wont be reflected until after saving</em></p>';
		echo '<input type="hidden" id="google_news_widget-submit" name="google_news_widget-submit" value="1" />';
	}

	// This registers our optional widget control form. Because of this
	// our widget will have a button that reveals a 350x300 pixel form.
	register_widget_control('Google News', 'widget_google_news_widget_control', 350, 300);
	
	// This registers our widget so it appears with the other available
	// widgets and can be dragged and dropped into any active sidebars.
	// Description is only settable by not calling wrapper-function...?
	wp_register_sidebar_widget(sanitize_title('Google News'), 'Google News', 
                                   'widget_google_news_widget',
                              array('description' => __('Display a Google News RSS feed')));
}

// Run our code later in case this loads prior to any required plugins.
add_action('widgets_init', 'widget_google_news_widget_init');
?>
