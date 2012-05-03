=== Chords and Lyrics ===
Contributors: rlisle
Tags: music, chords, lyrics, Post, posts, plugin, page, custom
Requires at least: 2.7
Tested up to: 3.3.2
Stable tag: trunk

ChordsAndLyrics will format staffless lead sheets.

== Description ==

This plugin assists in the creation of staffless lead sheets, also called chords sheets. 
It defines a [chordsandlyrics] shortcode which can be used in your post or page text.
It does not require any editing of your template files.

Text appearing between the [chordsandlyrics] and [/chordsandlyrics] tags will be
formatted for chord symbols written in square brackets (eg. [Cmaj]) embedded 
inline within lyrics.
It will then display the chord symbol at the same horizontal position above the lyrics. 
For example:

   [C]Oh [F]say can you see...

will be reformatting using HTML table to display as

   C  F
   Oh say can you see...

with the C correctly positioned over "Oh" and the F positioned over "say".
The commonly used alternative is to use fixed fonts which isn't very attractive.
This syntax is similar to the that used by ChordPro/Chordii.

**Features:**

-  Format lead sheets, keeping chord symbols above the correct lyric.
-  Appearance options page allows each end user to select Lyrics only 
   or Chords And Lyrics display via their profile (user's option page).

== Installation ==

These are the directions for the install. Be sure to read Directions for Use before using.

1. Upload the 'ChordsAndLyrics' directory to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Insert [chordsandlyrics] and [/chordsandlyrics] shortcodes around text containing
   embedded chords symbols in square brackets. 
      
== Frequently Asked Questions ==
= Is it possible to customize the formatting of the lyrics and chord symbols? =
Yes. Adjust the CSS in your theme. 
Chords are wrapped within div#cnl div#chord strong tags. 
Lyrics are wrapped within div#cnl div#lyric tags.
= Can I display European chords? =
Yes. Beginning with version 1.7, European chord convention is supported.
A new option in the Reading Settings allows enabling display using aHcdefg instead of abcdefg.
For entering chords in a post using European chord convention, add the european=yes argument
to the chordsandlyrics short tag (eg. [chordsandlyrics european=yes])

== Directions for Use ==

1. Create Posts or Pages containing the [chordsandlyrics] ... [/chordsandlyrics] tags.
1. Embed chord symbols within square brackets inline with lyrics within these tags.
   For example:
      [chordsandlyrics]
      [C]Mary had a little lamb, [G]little lamb, [C]little lamb.
      [C]Mary had a little lamb whose [F]fleece was [G]white as [C]snow.
      [/chordsandlyrics]
   Note that chords can appear in the middle of lyric words.
   
== Upgrade Notice ==
Install over previous version.

== Screenshots ==

1. User option section on settings->reading page allows control of chords and/or lyrics display.

== Changelog ==

= 1.7.0 =
* Add support for European chord style (aHcdefg, B=Bb)

= 1.6.0 =
* Fix bug preventing headings from showing.
* Improved formatting for big chords over small words.
* Clean up options API code.
* Settings now displayed at bottom of settings -> reading page.
* Removed multi-page option.

= 1.5.0 =
* Convert previous use of tables to CSS layers for formatting.
* Change multicolumn formatting to allow author to control it better. 

= 1.4.0 =
* First public release to WordPress.org subversion.

= 1.3.0 =
* Move controls from a widget to the user's profile page.

= 1.0.0 =
* Initial creation. Used privately on http://TheRockBand.org.
