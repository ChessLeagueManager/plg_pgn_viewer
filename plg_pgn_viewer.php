<?php

/*
Plugin Name: pgnviewer
Plugin URI: http://tom.jabber.ee/chess
Description: Allows to add PGN files to your blog posts that are converted to interactive boards. Easy to share your chess games with your friends.
Version: 0.6.4a
Author: Toomas Römer
Author URI: http://tom.jabber.ee 
*/

/*  Copyright 2006  Toomas Römer  (email : toomasr[at]gmail)
*/

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.event.plugin');

class plgContentPlg_pgn_viewer extends JPlugin {

	private $script = false;

	function plgContentPlg_pgn_viewer_fct( &$subject ) {
		parent::__construct( $subject );
	}
	
	function onContentPrepare($context, $row, $params, $page = 0) {
		$siteurl = JURI::base();
		$regex = "#{pgn}(.*?){end-pgn}#s";
		$row->text = preg_replace_callback($regex,array($this,"execphp"), $row->text);
		return true;
	
	}

	function execphp($matches) {
		$siteurl = JURI::base();
		if(!$this->script) {
			$doc = JFactory::getDocument();
			$doc->addScript($siteurl."plugins/content/plg_pgn_viewer/js/jsPgnViewer.js");
			$this->script=true;
		}

		$plugin = JPluginHelper::getPlugin('content', 'plg_pgn_viewer');
		$pluginParams = new JRegistry($plugin->params);

		$style 	  = $pluginParams->get('style',"png");
		if($style!="default" && $style!="png" && $style!="zurich") {
			$style="png";
		}
		$moveFont = $pluginParams->get('moveFont',"#666");
		$moveFont = $this->validate_html_color($moveFont, "#666");
		$commentFont = $pluginParams->get('commentFont',"#888");
		$commentFont = $this->validate_html_color($commentFont, "#888");
		
		// Zufallszahl
		$now = time()+mt_rand();
		$opts = array();
		$opts['imagePrefix'] = $siteurl."plugins/content/plg_pgn_viewer/img/".$style."/";
		$opts['showMovesPane'] = true;
		$opts['moveFontColor'] = ''.$moveFont.'';
		$opts['commentFontColor'] = ''.$commentFont.'';
		$opts['markLastMove'] = false;
		$opts['blackSqColor'] = "url(\"${siteurl}plugins/content/plg_pgn_viewer/img/zurich/board/darksquare.gif\")";
		$opts['lightSqColor'] = "url(\"${siteurl}plugins/content/plg_pgn_viewer/img/zurich/board/lightquare.gif\")";
		$opts['squareBorder'] = '0px solid #000000';

		$optsStr = "";
		foreach ($opts as $key=>$value) {
			if (is_bool($value) || strtolower($value)==="true" || 
					strtolower($value) === "false") {
				$value = $value?"true":"false";
				$optsStr .= "'$key':$value,\n";
			}
			else {
				$optsStr .= "'$key':'$value',\n";
			}
		}
		$optsStr[strlen($optsStr)-2]="\n";
		$script = "<script>var brd = new Board('$now',{{$optsStr}});brd.init()</script>";
		$script .= '<noscript>You have JavaScript disabled and you are not seeing a graphical interactive chessboard!</noscript>';
		
		// Code bereinigen
		$alt = array("<p>", "</p>", "<br />", "<br>");
		$neu = array("", "", "", "");
		$matches = str_replace($alt, $neu, $matches);

		// Ersetzen
		$replacements1 = '<div id="'.$now.'_board" width="100%"></div><div id="'.$now.'" style="visibility: hidden;display:none">';
		$replacements2 = "</div>\n";
		
		// Ausgabe
		$output = "{$replacements1}{$matches[1]}{$replacements2}{$script}";
		return $output;
	}

	function validate_html_color($color, $backup) {
   		/* Validates hex color, adding #-sign if not found. Checks for a Color Name first to prevent error if a name was entered (optional).
      		* $color: the color hex value stirng to Validates
     		* $named: (optional), set to 1 or TRUE to first test if a Named color was passed instead of a Hex value
     		*/
     
    		$named = array('aliceblue', 'antiquewhite', 'aqua', 'aquamarine', 'azure', 'beige', 'bisque', 'black', 'blanchedalmond', 'blue', 'blueviolet', 'brown', 'burlywood', 'cadetblue', 'chartreuse', 'chocolate', 'coral', 'cornflowerblue', 'cornsilk', 'crimson', 'cyan', 'darkblue', 'darkcyan', 'darkgoldenrod', 'darkgray', 'darkgreen', 'darkkhaki', 'darkmagenta', 'darkolivegreen', 'darkorange', 'darkorchid', 'darkred', 'darksalmon', 'darkseagreen', 'darkslateblue', 'darkslategray', 'darkturquoise', 'darkviolet', 'deeppink', 'deepskyblue', 'dimgray', 'dodgerblue', 'firebrick', 'floralwhite', 'forestgreen', 'fuchsia', 'gainsboro', 'ghostwhite', 'gold', 'goldenrod', 'gray', 'green', 'greenyellow', 'honeydew', 'hotpink', 'indianred', 'indigo', 'ivory', 'khaki', 'lavender', 'lavenderblush', 'lawngreen', 'lemonchiffon', 'lightblue', 'lightcoral', 'lightcyan', 'lightgoldenrodyellow', 'lightgreen', 'lightgrey', 'lightpink', 'lightsalmon', 'lightseagreen', 'lightskyblue', 'lightslategray', 'lightsteelblue', 'lightyellow', 'lime', 'limegreen', 'linen', 'magenta', 'maroon', 'mediumaquamarine', 'mediumblue', 'mediumorchid', 'mediumpurple', 'mediumseagreen', 'mediumslateblue', 'mediumspringgreen', 'mediumturquoise', 'mediumvioletred', 'midnightblue', 'mintcream', 'mistyrose', 'moccasin', 'navajowhite', 'navy', 'oldlace', 'olive', 'olivedrab', 'orange', 'orangered', 'orchid', 'palegoldenrod', 'palegreen', 'paleturquoise', 'palevioletred', 'papayawhip', 'peachpuff', 'peru', 'pink', 'plum', 'powderblue', 'purple', 'red', 'rosybrown', 'royalblue', 'saddlebrown', 'salmon', 'sandybrown', 'seagreen', 'seashell', 'sienna', 'silver', 'skyblue', 'slateblue', 'slategray', 'snow', 'springgreen', 'steelblue', 'tan', 'teal', 'thistle', 'tomato', 'turquoise', 'violet', 'wheat', 'white', 'whitesmoke', 'yellow', 'yellowgreen');
     
    		if (in_array(strtolower($color), $named)) {
    		/* A color name was entered instead of a Hex Value, so just exit function */
    			return $color;
    		}
     
    		if (preg_match('/^#[a-f0-9]{6}$/i', $color)) {
    			return $color;
    		}

    		if (preg_match('/^#[a-f0-9]{3}$/i', $color)) {
    			return $color;
    		}
   		return $backup;
   	}

}
?>
