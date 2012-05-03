<?php 
/**
 * @package Chords_And_Lyrics
 * @author  Ron Lisle
 * @version 1.7
 */
/*
Plugin Name: ChordsAndLyrics
Plugin URI: http://Lisles.net/
Description: This plugin assists in the creation of staffless lead sheets.
Version: 1.7
Author: Ron Lisle
Author URI: http://Lisles.net

Refer to Readme.txt file for more information. 

Copyright 2008-2012 Ron Lisle

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

/**
 * Settings - add a section to the read page
 * Note: a separate settings page was created prior to v1.6
 */
add_action('admin_init', 'chordsandlyrics_settings_init');

function chordsandlyrics_settings_init(){
	global $userdata;
	get_currentuserinfo();
	$user_settings_name = 'cnl_setting_values_for_' . $userdata->user_login;
	add_settings_section('cnl_setting_section','Chords and Lyrics Options','cnl_setting_section','reading');
	add_settings_field('lyrics-only','Display chords or lyrics only?','cnl_lyrics_only_enabled',
					'reading','cnl_setting_section');
	add_settings_field('european-chords','Display European chords?','cnl_european_chords_enabled',
					'reading','cnl_setting_section');
	register_setting('reading',$user_settings_name);
}

function cnl_setting_section(){
	echo '<p>Select options for displaying chords</p>';
}

function cnl_lyrics_only_enabled(){
	global $userdata;
	get_currentuserinfo();
	$user_settings_name = 'cnl_setting_values_for_' . $userdata->user_login;
	$cnl_options = get_option($user_settings_name);
	if($cnl_options['lyrics-only']){
		$checked = ' checked="checked" ';
	}
	echo '<input '.$checked.' name="'.$user_settings_name.'[lyrics-only]" type="checkbox" />Lyrics Only';
}

function cnl_european_chords_enabled(){
	global $userdata;
	get_currentuserinfo();
	$user_settings_name = 'cnl_setting_values_for_' . $userdata->user_login;
	$cnl_options = get_option($user_settings_name);
	if($cnl_options['european-chords']){
		$checked = ' checked="checked" ';
	}
	echo '<input '.$checked.' name="'.$user_settings_name.'[european-chords]" type="checkbox" />European chords';
}

/*
 * ShortCode
 */

// Create CSS layer formatted chord sheet of the type:
//
// <div class="cnl_page">                 <!-- enclosing everything inside shortcode -->
//   <div class="cnl_line">               <!-- encloses a single line -->
//     <div class="cnl">                 <!-- groups a chord and lyric fragment -->
//       <div class="chord">C#</div>
//       <div class="lyric">..lyrics..</div>
//     </div>
//     ... 
//   </div>
// </div>
//
// Each .cnl will float left.
//
// [chordsandlyrics parm=val...] ... [/chordsandlyrics]
// Parameters: 
//		format="yes"				Enable/disable formatting
//		size="normal"				Adjust display size
//		transpose="#"				Sets the # +/- half-steps to adjust chords.
//      european="yes"				Interpret input using european convention (aHcdefg and B=Bb)
function chordsandlyricstag_func($atts, $content = null){
	$cnlData = new ChordsAndLyricsData();
	extract(shortcode_atts(array('format' => 'yes', 'size' => 'normal', 'transpose' => '0', 'european' => 'no'), $atts));

	if($format != 'yes') return "[chordsandlyrics]" . $content . "[/chordsandlyrics]";
	
	$cnlData->setTranspose($transpose);
	$cnlData->setSize($size);
	$cnlData->setEuropean($european);
	
	// Break content into separate lines.
	$lines = explode("\n",$content);

	// Parse content line-by-line
	return $cnlData->DisplayText( $lines);
}
add_shortcode('chordsandlyrics', 'chordsandlyricstag_func');

// The ChordsAndLyrics object represents the text between [chordsandlyrics] tags formatted in ChordPro like format.
// Chords can be embedded within lyric text by enclosing them in square brackets (eg. [Cmaj7])
// Options are saved per-user.
class ChordsAndLyricsData
{
	private $lyricsOnly;
	private $twoPages;
	private $transpose;
	private $size;
	private $european;
	private $displayEuropean;
	
	public function __construct()
	{
		global $userdata;
		get_currentuserinfo();
		$user_settings_name = 'cnl_setting_values_for_' . $userdata->user_login;
		$cnl_options = get_option($user_settings_name);
		$this->lyricsOnly = $cnl_options['lyrics-only'];
		$this->twoPages = $cnl_options['two-pages'];
		$this->transpose = 0;
		$this->displayEuropean = $cnl_options['european-chords'];
	}

	public function setTranspose( $t ){
		$this->transpose = $t;
	}
	public function getTranspose(){
		return $this->transpose;
	}
	
	public function setSize( $s ){
		$this->size = $s;
	}
	public function getSize(){
		return $this->size;
	}
	
	public function setEuropean( $e ){
		$this->european = $e;
	}
	public function getEuropean(){
		return $this->european;
	}
	public function setDisplayEuropean( $e ){
		$this->displayEuropean = $e;
	}
	public function getDisplayEuropean(){
		return $this->displayEuropean;
	}
	
	//This is where we start displaying the formatted output
	public function DisplayText( $text ){
		$returnText = '<style>'
					. 'div.cnl_page { float: left; }'
					. 'div.cnl_line { margin: .7em; }'
					. 'div.cnl { display: inline; float: left; }'
					. 'div.cnl_clear { clear: both; }'
					. '</style>';
					
		$returnText .= '<div class="cnl_page">';
		$lineNum = 1;
		foreach( $text as $line ){
			if($this->twoPages=='on'){
				//TODO: provide a mechanism to allow the author to split pages
			}
			$returnText .= $this->FormatAndDisplayLine($line,$lineNum++);
		}
		$returnText .= '</div>'; 							// end of cnl_page
		//$returnText .= '<div class="cnl_clear"></div>';		// Force end of multipages. Probably not needed now.
		return $returnText;
	}
					
	//************************
	// FORMAT AND DISPLAY LINE
	//************************
	public function FormatAndDisplayLine ( $line, $lineNum = -1)
	{
		$returnText = "";
		$arrChords = array();	// Array of chords
		$arrLyrics = array();	// Array of corresponding lyrics starting at a chord 
								// and ending prior to the next chord or the end-of-line.
		// Remove any <p> and </p>
		//$line = str_replace("<p>","",$line);
		//$line = str_replace("</p>","",$line);
		
		// Split each line into separate chords and lyrics lines
		if(substr_count($line,"[") == 0){	//Are there no chords on this line?
			$arrLyrics[] = $line;
		
		// Is there an unmatched number of square brackets?
		}else if(substr_count($line,"[") != substr_count($line,"]")){
			// If so, flag the error
			$arrLyrics[] = "Unmatched square brackets: " . $line . "<br />";	
			
		}else{
			//Split line into segments beginning with '['
			$arrBracketSegments = explode("[",$line);
			foreach($arrBracketSegments as $segment){
				$pad = ($segment[strlen($segment)-1]==' ') ? '&nbsp;' : '';
				// Does the first segment start before the 1st '['?
				if(substr_count($segment,"]")==0){
					$arrChords[] = " ";
					$arrLyrics[] = $segment . $pad;
				}else{
					// Now process all the segments beginning with '['
					$arrChordLyric = explode("]",$segment);
					$arrChords[] = trim($arrChordLyric[0]);
					$arrLyrics[] = $arrChordLyric[1] . $pad;
				}
			}
		}

		// Display a line of chords and text.
		$returnText .= '<div class="cnl_line">';
		
		// Align chords and lyrics together by wrapping each in a floating inline div
		if(!$this->lyricsOnly){
			//$numChars = 0;
			for($i=0; $i<count($arrChords); $i++){
				if(strlen(trim($arrChords[$i])) > 0 
				|| strlen(trim($arrLyrics[$i]))>0){
					$lyrics = trim($this->RemoveHtmlStuff($arrLyrics[$i]));
					$returnText .= '<div class="cnl"><div class="chord"><strong>';
					if(strlen(trim($arrChords[$i])) > 0){
						$returnText .= $this->FormatChord($arrChords[$i]);
						$returnText .= '</strong>&nbsp;</div>';		// End of chord
						$returnText .= '<div class="lyric">';
						if(strlen($lyrics)>0){
							$endOf1stWord = strpos($lyrics,' ');
							$numSpaces = substr_count($lyrics,' ');
							if($endOf1stWord>0 && $numSpaces==1){
								$returnText .= substr($lyrics,0,$endOf1stWord) . '&nbsp;';
								$lyrics = substr($lyrics,$endOf1stWord);
								$returnText .= '</div></div><div class="cnl"><div class="chord">&nbsp;</div><div class="lyric">';
							}
						}
					}else{
						$returnText .= '</strong>&nbsp;</div>';		// End of chord
						$returnText .= '<div class="lyric">';
					}
					if(strlen($lyrics) > 0){
						//if( $this->size != "normal") $returnText .= "<" . $this->size . ">";
						// Limit each line to 99 chars or less
						//if($numChars+strlen($lyrics) > 99){
						//	// Find a space to break at
						//	for($breakPos = 99-$numChars; 
						//		$breakPos > 0 && $lyrics[$breakPos]!=' '; 
						//		$breakPos--);
						//	if($breakPos <= 0) $breakPos = 99-$numChars;
						//	$returnText .= substr($lyrics,0,$breakPos);
						//	$returnText .= '</td></tr></tbody></table><!--break-->';
						//	$returnText .= '<table><tbody><tr><td class="lyrics"><br />';
						//	$lyrics = substr($lyrics,$breakPos);
						//	$returnText .= $lyrics;
						//	$numChars = strlen($lyrics);
						//	if($numChars >= 99){
						//		$numChars = 0;
						//		$returnText .= '</td></tr><!--break2--><td class="lyrics">';
						//	}
						//}else{
							$returnText .= $lyrics;
							//$numChars += strlen($lyrics);
						//}
						//if( $this->size != "normal") $returnText .= "</" . $this->size . ">";
					} //else $returnText .= '<p class="lyrics"><br /></p>';
					$returnText .= "</div></div>\n";	// End of lyric and chordlyric
				}
			}
			for($i=count($arrChords); $i<count($arrLyrics); $i++){
				//$returnText .= '<div class="cnl"><div class="chord">&nbsp;</div><div class="lyric">';
				$returnText .= $arrLyrics[$i];
				//$returnText .= "</div></div>\n";	// End of lyric and cnl
			}
		}else{		// Show lyrics only
			$returnText .= '<div class="cnl"><div class="lyric">';
			for($i=0; $i<count($arrLyrics); $i++){
				$returnText .= $arrLyrics[$i];
			}
			$returnText .= "</div></div>";
		}
		$returnText .= "</div><div class='cnl_clear'></div>\n";		// End of cnl_line
		return $returnText;
	}
	
	private function RemoveHtmlStuff($string){
		$retString = "";
		$isVisible = true;
		for($i = 0; $i < strlen($string); $i++){
			if($isVisible){
				if($string[$i]=='<') $isVisible = false;
				else $retString .= $string[$i];
			}else if($string[$i]=='>') $isVisible = true;
		}
		return $retString;
	}
	
	//**************
	// FORMAT CHORD
	//**************
	public function FormatChord( $ch )
	{
		$useFlats = false;
		
		if(strlen($ch)==0) return;
		
		// The first letter should be the key
		$note = substr($ch,0,1);
		$xlatedNote = $note;
		$rem = substr($ch,1);
	
		// Convert note to a number.
		switch($note){
		case 'a':
		case 'A':
			$noteVal = 0;
			break;
		case 'b':
		case 'B':
			if($this->european == 'yes'){
				$noteVal = 1;
			}else{
				$noteVal = 2;
			}
			break;
		case 'c':
		case 'C':
			$noteVal = 3;
			break;
		case 'd':
		case 'D':
			$noteVal = 5;
			break;
		case 'e':
		case 'E':
			$noteVal = 7;
			break;
		case 'f':
		case 'F':
			$noteVal = 8;
			break;
		case 'g':
		case 'G':
			$noteVal = 10;
			break;
		case 'h':
		case 'H':
			$noteVal = 2;
			break;
		default:
			return;
			break;
		}
		
		// Add accidentals
		if(strlen($rem) > 0){
			if(substr($rem,0,1) == '#'){
				$noteVal++;
				$rem = substr($rem,1);
			}else if(strncasecmp($rem,"b",1)==0){
				$useFlats = true;
				$noteVal--;
				$rem = substr($rem,1);
			}
		}
		
		
		// Add transpose modulo 12
		$noteVal += $this->transpose;
		if($noteVal > 11) $noteVal -= 12;
		if($noteVal < 0) $noteVal += 12;
	
		// Display transposed note
		switch($noteVal){
		case 0:
			$xlatedNote = 'A';
			break;
		case 1:
			if($this->displayEuropean){
				$xlatedNote = 'B';
			}else{
				if($useFlats) $xlatedNote = "Bb";
				else $xlatedNote = "A#";
			}
			break;
		case 2:
			if($this->displayEuropean){
				$xlatedNote = 'H';
			}else{
				$xlatedNote = 'B';
			}
			break;
		case 3:
			$xlatedNote = 'C';
			break;
		case 4:
			if($useFlats) $xlatedNote = "Db";
			else $xlatedNote = "C#";
			break;
		case 5:
			$xlatedNote = 'D';
			break;
		case 6:
			if($useFlats) $xlatedNote = "Eb";
			else $xlatedNote = "D#";
			break;
		case 7:
			$xlatedNote = 'E';
			break;
		case 8:
			$xlatedNote = 'F';
			break;
		case 9:
			if($useFlats) $xlatedNote = "Gb";
			else $xlatedNote = "F#";
			break;
		case 10:
			$xlatedNote = 'G';
			break;
		case 11:
			if($useFlats) $xlatedNote = "Ab";
			else $xlatedNote = "G#";
			break;
		}
		
		//Return the xlat'ed chord and add transpose to the bass note (if any)
		return $xlatedNote . $this->FormatBassNote($rem);
	}
	//******************
	// FORMAT BASS NOTE
	//******************
	public function FormatBassNote( $ch )
	{
		$indexOfSlash = strpos($ch,'/');
		if($indexOfSlash === false) return $ch;
		
		// Break the string into 2 parts at the slash. 
		// The slash will be lost, and will need to be added back later.
		$chord = substr($ch,0,$indexOfSlash);
		$bass = substr($ch,$indexOfSlash+1);
		
		$useFlats = false;
		
		if(strlen($bass)==0) return $chord . "/?";
		
		// The first letter should be the key
		$note = substr($bass,0,1);
		$xlatedNote = $note;
		$rem = substr($bass,1);
		
		// Note: from here out, this function is the same as the above 
		// except the return value, which suggests refactoring needed :-)
		
		// Convert note to a number.
		switch($note){
		case 'a':
		case 'A':
			$noteVal = 0;
			break;
		case 'b':
		case 'B':
			$noteVal = 2;
			break;
		case 'c':
		case 'C':
			$noteVal = 3;
			break;
		case 'd':
		case 'D':
			$noteVal = 5;
			break;
		case 'e':
		case 'E':
			$noteVal = 7;
			break;
		case 'f':
		case 'F':
			$noteVal = 8;
			break;
		case 'g':
		case 'G':
			$noteVal = 10;
			break;
		default:
			return;
			break;
		}
		
		// Add accidentals
		if(strlen($rem) > 0){
			if(substr($rem,0,1) == '#'){
				$noteVal++;
				$rem = substr($rem,1);
			}else if(strncasecmp($rem,"b",1)==0){
				$useFlats = true;
				$noteVal--;
				$rem = substr($rem,1);
			}
		}
		
		
		// Add transpose modulo 11
		$noteVal += $this->transpose;
		if($noteVal > 11) $noteVal -= 12;
		if($noteVal < 0) $noteVal += 12;
	
		// Display transposed note
		switch($noteVal){
		case 0:
			$xlatedNote = 'A';
			break;
		case 1:
			if($useFlats) $xlatedNote = "Bb";
			else $xlatedNote = "A#";
			break;
		case 2:
			$xlatedNote = 'B';
			break;
		case 3:
			$xlatedNote = 'C';
			break;
		case 4:
			if($useFlats) $xlatedNote = "Db";
			else $xlatedNote = "C#";
			break;
		case 5:
			$xlatedNote = 'D';
			break;
		case 6:
			if($useFlats) $xlatedNote = "Eb";
			else $xlatedNote = "D#";
			break;
		case 7:
			$xlatedNote = 'E';
			break;
		case 8:
			$xlatedNote = 'F';
			break;
		case 9:
			if($useFlats) $xlatedNote = "Gb";
			else $xlatedNote = "F#";
			break;
		case 10:
			$xlatedNote = 'G';
			break;
		case 11:
			if($useFlats) $xlatedNote = "Ab";
			else $xlatedNote = "G#";
			break;
		}
		
		return $chord . "/" . $xlatedNote . $rem;
	}
}
?>
