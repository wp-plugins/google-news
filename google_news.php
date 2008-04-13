<?php
/*
Plugin Name: Google News
Description: Displays a selectable Google News RSS feed, inline or widget
Version:     2.0 
Author:      Olav Kolbu
Author URI:  http://www.kolbu.com/
Plugin URI:  http://wordpress.org/extend/plugins/google-news/
License:     GPL

Some WordPress-specific code from various other GPL plugins.

Fix short / long
Fix numitems? Only shows 1
Test old plugin then new
Make sure all options work
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

if ( ! class_exists("google_news_plugin")) {
    class google_news_plugin {

        private $regions = array(
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

        private $newstypes = array(
            "All" => "",
            "Top News" => "h",
            "Foreign" => "w",
            "Domestic" => "n",
            "Business" => "b",
            "Sci/Tech" => "t",
            "Health" => "m",
            "Sports" => "s",
            "Entertainment" => "e",
        );

        private $outputtypes = array(
            "Standard" => "",
            "Text Only" => "t",
            "With Images" => "&imv=1",
        );

        private $desctypes = array(
            "Short" => "",
            "Long" => "l",
        );

        private $just_saved = 0;

        // Constructor
        function google_news_plugin() {

            // Form POSTs
            if ( $_POST ) {
                $this->update_options($_POST);
                $this->just_saved = 1;
            }

	    add_filter('the_content', array(&$this, 'insert_news')); 
            add_action('admin_menu', array(&$this, 'admin_menu'));
            add_action('plugins_loaded', array(&$this, 'widget_init'));

        }

        // *************** Admin interface ******************

        // Callback for admin menu
        function admin_menu() {
            add_options_page('Google News Options', 'Google News',
                             'administrator', __FILE__, 
                              array(&$this, 'plugin_options'));
        }

        // The actual admin page, content from another fn as we double as
        // widget
        function plugin_options() {
            $html = '';
            $html .= '<div class="wrap">';
            $html .= '<h2>Google News</h2>';

            $html .= '<form method="post">';
            $html .= $this->admin_form();
            $html .= '<p><input type="submit" value="Save  &raquo;"></p>';
            $html .= '</form>'; 
            $html .= '</div>';

            print($html);     
        }

        function admin_form() {
            $html        = '';
            $options     = get_option("google_news");

            $flipregions     = array_flip($this->regions);
            $flipnewstypes   = array_flip($this->newstypes);
            $flipoutputtypes = array_flip($this->outputtypes);
            $flipdesctypes   = array_flip($this->desctypes);

            // First time ever? Prep some values
            if ( !is_array($options) ) {
                // Clean up from earlier versions
                $oldoptions = get_option('widget_google_news_widget');
                if ( is_array($oldoptions) ) {
                    $options['title']      = $oldoptions['title'];
                    $options['numnews']    = $oldoptions['numnews'];
                    $options['region']     = $oldoptions['region'];
                    $options['newstype']   = $oldoptions['newstype'];
                    $options['outputtype'] = $oldoptions['outputtype'];
                    $options['query']      = $oldoptions['query'];
                    $options['feedtype']   = $flipregions[$options['newstype']].
                                             ' : '.
                                             $flipnewstypes[$options['outputtype']];
                    
                    delete_option('widget_google_news_widget');
                    update_option('google_news', $options);
                } else {
                    $options = array( 'numnews' => 5,
                                     'region' => 'us',
				     'feedtype' => 'U.S. : All' );
                    update_option('google_news', $options);
                }
            }

            if ( $this->just_saved ) {
                $html .= '<div class="updated"><p><strong>' . __('Options saved.', 'google-news') . '</strong></p></div>';
                $this->just_saved = 0;
            }

            $title       = $options['title'];
            $numnews     = $options['numnews'];
            $region      = $flipregions[$options['region']];
            $newstype    = $flipnewstypes[$options['newstype']];
            $outputtype  = $flipoutputtypes[$options['outputtype']];
            $desctype    = $flipdesctypes[$options['desctype']];
            $query       = $options['query'];

            $html .= '<table class="form-table" style="width: 100%;">';
            $html .= ' <tr>';
            $html .= '  <th scrope="row">';
            $html .= '   <label for="google_news-title">Admin-defined title:</label>';
            $html .= '  </th>';
            $html .= '  <td>';
            $html .= '   <input style="width: 20em;" id="google_news-title" name="google_news-title" type="text" value="'.$title.'" />';
            $html .= '   (Optional, if empty then feed name is used)';
            $html .= '  </td>';
            $html .= ' </tr>';
            $html .= ' <tr>';
            $html .= '  <th scrope="row">';
            $html .= '   <label for="google_news-region">News region:</label>';
            $html .= '  </th>';
            $html .= '  <td>';
            $html .= '<select name="google_news-region">';
            foreach ($this->regions as $k => $v) {
                $html .= '<option '.(strcmp($k,$region)?'':'selected').' value="'.$k.'" >'.$k.'</option>
';
            }
            $html .= '</select>';
            $html .= '  </td>';
            $html .= ' </tr>';
            $html .= ' <tr>';
            $html .= '  <th scrope="row">';
            $html .= '   <label for="google_news-newstype">News type:</label>';
            $html .= '  </th>';
            $html .= '  <td>';
            $html .= '<select name="google_news-newstype">';
            foreach ($this->newstypes as $k => $v) {
                $html .= '<option '.(strcmp($k,$newstype)?'':'selected').' value="'.$k.'" >'.$k.'</option>';
            }
            $html .= '</select>';
            $html .= '  </td>';
            $html .= ' </tr>';
            $html .= ' <tr>';
            $html .= '  <th scrope="row">';
            $html .= '   <label for="google_news-outputtype">Output type:</label>';
            $html .= '  </th>';
            $html .= '  <td>';
            $html .= '<select name="google_news-outputtype">';
            foreach ($this->outputtypes as $k => $v) {
                $html .= '<option '.(strcmp($k,$outputtype)?'':'selected').' value="'.$k.'" >'.$k.'</option>';
            }
            $html .= '</select>';
            $html .= '  </td>';
            $html .= ' </tr>';
            $html .= ' <tr>';
            $html .= '  <th scrope="row">';
            $html .= '   <label for="google_news-desctype">News item length:</label>';
            $html .= '  </th>';
            $html .= '  <td>';
            $html .= '<select name="google_news-desctype">';
            foreach ($this->desctypes as $k => $v) {
                $html .= '<option '.(strcmp($k,$desctype)?'':'selected').' value="'.$k.'" >'.$k.'</option>';
            }
            $html .= '</select>';
            $html .= '  </td>';
            $html .= ' </tr>';
            $html .= ' <tr>';
            $html .= '  <th scrope="row">';
            $html .= '   <label for="google_news-numnews">Max items to show:</label>';
            $html .= '  </th>';
            $html .= '  <td>';
            $html .= '   <input style="width: 2em;" id="google_news-numnews" name="google_news-numnews" type="text" value="'.$numnews.'" />';
            $html .= '  </td>';
            $html .= ' </tr>';
            $html .= ' <tr>';
            $html .= '  <th scrope="row">';
            $html .= '   <label for="google_news-query">Optional query filter:</label>';
            $html .= '  </th>';
            $html .= '  <td>';
            $html .= '   <input style="width: 20em;" id="google_news-query" name="google_news-query" type="text" value="'.$query.'" />';
            $html .= '  </td>';
            $html .= ' </tr>';
            $html .= '</table>';
            $html .= '<input type="hidden" id="google_news-submit" name="google_news-submit" value="1" />';

            return $html;
        }

        function update_options($data) {
            $newoptions = array();
            $options = get_option("google_news");

            $newoptions['title']       = $data['google_news-title'];
            $newoptions['numnews']     = $data['google_news-numnews'];
            $newoptions['region']      = $this->regions[$data['google_news-region']];
            $newoptions['newstype']    = $this->newstypes[$data['google_news-newstype']];
            $newoptions['outputtype']  = $this->outputtypes[$data['google_news-outputtype']];
            $newoptions['query']       = $data['google_news-query'];
            $newoptions['desctype']    = $this->desctypes[$data['google_news-desctype']];
            $newoptions['feedtype']    = $data['google_news-region']." : ".$data['google_news-newstype'];

            if ( $options != $newoptions ) {
                $options = $newoptions;
                update_option('google_news', $options);
            }
        }

        // ************* Output *****************

        // Callback for inline replacement
        function insert_news($data) {
            $tag = "<!--google-news-->";

            $options = get_option('google_news');

            $region     = $options['region'] ? $options['region'] : 'us';
            $newstype   = $options['newstype'];
            $outputtype = $options['outputtype'];
            $query      = $options['query'];
            $numnews    = $options['numnews'] ? $options['numnews'] : 5;
            $desctype   = $options['desctype'];
            $feedtype   = $options['feedtype'] ? $options['feedtype'] : 'U.S. : All';

            if ( strlen($options['title']) ) {
                $title = $options['title'];
            } else {
                $title = "Google News : ".$feedtype;
            }

            $feed = "<!-- Start Google News code -->\n<div id=\"google-news-inline\"><h3>$title</h4>\n";
            $feed .= $this->get_feed($region, $newstype, $outputtype, 
                                     $query, $numnews, $desctype);
            $feed .= "</div><!-- End Google News code -->\n";

            return str_replace($tag, $feed, $data);
        }

        // *********** Widget support **************
        function widget_init() {

            // Check for the required plugin functions. This will prevent fatal
            // errors occurring when you deactivate the dynamic-sidebar plugin.
            if ( !function_exists('register_sidebar_widget') )
                return;

            register_widget_control('Google News', 
                                   array(&$this, 'widget_control'), 400, 400);

            // wp_* has more features, presumably fixed at a later date
            register_sidebar_widget('Google News',
                                   array(&$this, 'widget_output'));

        }

        function widget_control() {

            if ( $_POST['google_news-submit'] ) {
                $this->update_options($_POST);
            }

            $options = get_option("google_news");

            $html = $this->admin_form();
            print($html);
        }

        // Called every time we want to display ourselves as a sidebar widget
        function widget_output($args) {
            extract($args); // Gives us $before_ and $after_ I presume
                        
            $options = get_option('google_news');
            $region     = $options['region'] ? $options['region'] : 'us';
            $newstype   = $options['newstype'];
            $outputtype = $options['outputtype'];
            $query      = $options['query'];
            $numnews    = $options['numnews'] ? $options['numnews'] : 5;
            $desctype   = $options['desctype'];
            $feedtype   = $options['feedtype'] ? $options['feedtype'] : 'U.S. : All';
            if ( strlen($options['title']) ) {
                $title = $options['title'];
            } else {
                $title = "Google News<br>".$feedtype;
            }

            echo $before_widget;
            echo $before_title . $title . $after_title;
            $GoogleFeed = $this->get_feed($region, $newstype, $outputtype, 
                                          $query, $numnews, $desctype);
            echo '<div id="google-news-widget">'.$GoogleFeed.'</div>';
            echo $after_widget;

        }

        // ************** The actual work ****************
        function get_feed($region, $newstype, $outputtype, $query, $numnews, $desctype) {
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
                    $description = $this->html_decode($out[1]);
        
        
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
                    $title = $this->html_decode($out[1]);
                }
                if (eregi ('^pubDate>(.*)', $line, $out)) {
                    $date = $out[1];
                }
                if (eregi ('^link>(.*)', $line, $out)) {
                    $link = $out[1];
                }
                if (eregi ('^/item>', $line, $out)) {
                    if ( strlen($desctype) ) {
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

        // ************** Support functions ****************

        function html_decode($in) {
            $patterns = array(
                "/&amp;/",
                "/&quot;/",
                "/&lt;/",
                "/&gt;/",
            );
            $replacements = array(
                "&",
                "\"",
                "<",
                ">",
            );
            $tmp = preg_replace($patterns, $replacements, $in);
            return preg_replace('/&#39;/','\'',$tmp);

        }
    }

    // Instantiate
    $gn &= new google_news_plugin();

}
?>
