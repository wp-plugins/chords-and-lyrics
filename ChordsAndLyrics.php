<?php 
/**
 * @package Chords_And_Lyrics
 * @author  Ron Lisle
 * @version 1.5
 */
/*
Plugin Name: ChordsAndLyrics
Plugin URI: http://BuildAWebsiteWorkshops.com/
Description: This plugin assists in the creation of staffless lead sheets.
Version: 1.5
Author: Ron Lisle
Author URI: http://Lisles.net

Refer to Readme.txt file for more information. 

Copyright 2008-2010 Ron Lisle

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
 * Options Page
 */
function chordsandlyrics_handle_options(){
	global $userdata;
	get_currentuserinfo();
	$hidden_field_name = 'submitted';
	$chords_field_name = 'chords';
	$chords_opt_name   = 'chordsandlyrics_chords'.$userdata->user_login;
	$pages_field_name  = 'twopages';
	$pages_opt_name    = 'chordsandlyrics_pages'.$userdata->user_login; 
	
	$chords_opt_val = get_option( $chords_opt_name );
	$pages_opt_val = get_option( $pages_opt_name );
	
	if( $_POST[ $hidden_field_name ] == 'Y' ){
		$chords_opt_val = $_POST[ $chords_field_name ] ? 'on' : 'off';
		update_option( $chords_opt_name, $chords_opt_val );
		$pages_opt_val = $_POST[ $pages_field_name ] ? 'on' : 'off';
		update_option( $pages_opt_name, $pages_opt_val );
		echo '<div class="updated"><p><strong>Options saved'
			.'pages='.$pages_opt_val.', chords='.$chords_opt_val
			.'</strong></p></div>';
	}
	$chords = $chords_opt_val == 'on'?'checked':'';
	$pages  = $pages_opt_val == 'on'?'checked':'';
	
	echo '<style>';
	// label/label span
	echo 'label, label span { display: block; padding-bottom: .25em; }';
	echo 'label { float: left; width: 100%; margin: 8px 16px; }';
	echo 'label span { float: left; width: 60%; text-align: right; }';
	// Fieldset
	echo 'fieldset { border: 1px solid silver; }';
	// input
	echo 'fieldset input { margin: 2px 8px; }';
	
	echo 'form div { clear: both; margin-bottom: 20px; padding: 10px 20px; text-align: center; }';
	
	echo '</style>';
	
	echo '<div class="wrap">';
	echo '<div id="icon-chords" class="icon32"><br /></div>';
	
	echo '<h2>Chords and Lyrics Options</h2>';
	
	echo '<form id="chords1" name="chords1" method="post" action="">';
	echo "<input type='hidden' name='$hidden_field_name' value='Y'>";
	
	echo "<fieldset>";
	
	echo "<label for='$chords_field_name'><span>Show chords in addition to lyrics</span>";
	echo "<input type='checkbox' id='$chords_field_name' name='$chords_field_name' $chords />";
	echo '</label>';
	
	echo "<label for='$pages_field_name'><span>Split long pages into multiple columns as the window width pemits</span>";
	echo "<input type='checkbox' id='$pages_field_name' name='$pages_field_name' $pages />";
	echo '</label>';

	echo "</fieldset>";
	
	echo '<div>';
	echo '<input class="button-primary" type="submit" name="Submit" value="Update Options"/>';
	echo '</div>';
	echo '</form>';
	echo '</div>';

}
function chordsandlyrics_admin_menu(){
	add_theme_page('Chords and Lyrics Options','Chords and Lyrics','read',
	basename(__FILE__),'chordsandlyrics_handle_options');
}
add_action('admin_menu', 'chordsandlyrics_admin_menu');


/*
 * ShortCode
 */

// Create CSS layer formatted chord sheet of the type:
//
// <div class="chordslyrics">                 <!-- enclosing everything inside shortcode -->
//   <div class="chordslyricsline">           <!-- encloses a single line -->
//     <div class="chordlyric">               <!-- groups a chord and lyric fragment -->
//       <div class="chord">C#</div>
//       <div class="lyric">..lyrics..</div>
//     </div>
//     ... 
//   </div>
// </div>
//
// Each .chordlyric will float left.
//
// [chordsandlyrics parm=val...] ... [/chordsandlyrics]
// Parameters: 
//		transpose="#"				Sets the # +/- half-steps to adjust chords.
function chordsandlyricstag_func($atts, $content = null){
	$textFile = new TextFile();
	extract(shortcode_atts(array('format' => 'yes', 'size' => 'normal', 'transpose' => '0'), $atts));

	if($format != 'yes') return "[chordsandlyrics]" . $content . "[/chordsandlyrics]";
	
	$textFile->setTranspose($transpose);
	$textFile->setSize($size);
	
	// Break content into separate lines.
	// ??? Is this still needed ???
	$lines = explode("\n",$content);

	// Parse content line-by-line
	return $textFile->DisplayText( $lines);
}
add_shortcode('chordsandlyrics', 'chordsandlyricstag_func');

// The TextFile object represents a text file formatted in ChordPro like format.
// Chords can be embedded within lyric text by enclosing them in square brackets (eg. [Cmaj7])
//
class TextFile
{
	private $showChords;
	private $twoPages;
	private $transpose;
	private $size;
	
	public function __construct()
	{
		global $userdata;
		get_currentuserinfo();
	    // Read in existing option value from database
		$this->transpose = 0;
		$chords_opt_name = 'chordsandlyrics_chords'.$userdata->user_login;
		$chords_opt_val = get_option( $chords_opt_name, 'on' );
		$this->showChords = ($chords_opt_val=='on');
		
		$pages_opt_name = 'chordsandlyrics_pages'.$userdata->user_login;
		$pages_opt_val = get_option( $pages_opt_name, 'on' );
		$this->twoPages = ($pages_opt_val=='on');
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
	
	//This is where we start displaying the formatted output
	public function DisplayText( $text ){
		$returnText = '<style>'
					//. 'div.chordslyrics { float: left; border-right: 1px solid silver; padding: 0 8px; }'
					. 'div.chordslyricspage { float: left; }'
					. 'div.chordslyricsline { margin: .7em; }'
					. 'div.chordlyric { display: inline; float: left; }'
					. '</style>';
					
		$returnText .= '<div class="chordslyricspage">';
		$lineNum = 1;
		//$columnLineNum = 1;
		foreach( $text as $line ){
			//if($this->twoPages=='on' && $columnLineNum++ > 8){
			//	if($columnLineNum > 16
			//	|| !strncasecmp($line,'<h',2)){
			//		$returnText .= '</div><div class="chordslyrics">';
			//		$columnLineNum=1;
			//	}
			//}
			$returnText .= $this->FormatAndDisplayLine($line,$lineNum++);
		}
		$returnText .= '</div>'; 							// end of chordslyricspage
		$returnText .= '<div style="clear:both"></div>';	// Force end of multipages. Probably not needed now.
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
		$line = str_replace("<p>","",$line);
		$line = str_replace("</p>","",$line);
		
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
				// Does the first segment start before the 1st '['?
				if(substr_count($segment,"]")==0){
					$arrChords[] = " ";
					$arrLyrics[] = $segment;
				}else{
					// Now process all the segments beginning with '['
					$arrChordLyric = explode("]",$segment);
					$arrChords[] = trim($arrChordLyric[0]);
					$arrLyrics[] = $arrChordLyric[1];
				}
			}
		}

// TODO: limit segment to a single lyric so that long chords cause space where they occur, not in subsequent lyrics.		
// TODO: Figure out how to avoid extra space when chord occurs in the middle of a lyric
	
		// Display a line of chords and text.
		$returnText .= '<div class="chordslyricsline">';
		
		// Align chords and lyrics together by starting each in a table cell.
		if($this->showChords){
			$numChars = 0;
			for($i=0; $i<count($arrChords); $i++){
				if(strlen(trim($arrChords[$i])) > 0 
				|| strlen(trim($arrLyrics[$i]))>0){
					$returnText .= '<div class="chordlyric"><div class="chord"><strong>';
					if(strlen(trim($arrChords[$i])) > 0){
						$returnText .= $this->FormatChord($arrChords[$i]);
					// Make sure that text line is aligned vertically 
					// on those sections with only chord or lyrics.
					}
					$returnText .= '</strong>&nbsp;</div>';		// End of chord
					$returnText .= '<div class="lyric">';
					$lyrics = trim($this->RemoveHtmlStuff($arrLyrics[$i]));
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
							$numChars += strlen($lyrics);
						//}
						//if( $this->size != "normal") $returnText .= "</" . $this->size . ">";
					} //else $returnText .= '<p class="lyrics"><br /></p>';
					$returnText .= "&nbsp;</div></div>\n";	// End of lyric and chordlyric
				}
			}
			//for($i=count($arrChords); $i<count($arrLyrics); $i++){
			//	$returnText .= '<div class="chordlyric"><div class="chord">X</div><div class="lyric">';
			//	$returnText .= $arrLyrics[$i];
			//	$returnText .= "</div></div>\n";	// End of lyric and chordlyric
			//}
		}else{		// Show lyrics only
			$returnText .= '<div class="chordlyric"><div class="lyric">';
			for($i=0; $i<count($arrLyrics); $i++){
				$returnText .= $arrLyrics[$i];
			}
			$returnText .= "</div></div>";
		}
		$returnText .= "</div><div style='clear:both'></div>\n";		// End of chordslyricsline
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
