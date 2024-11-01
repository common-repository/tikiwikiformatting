<?php
/*
Plugin Name: TikiWikiFormatting
Plugin URI: http://www.tikirobot.net/wp/tikiwikiformatting/
Feed URI: http://www.tikirobot.net/wp/feed/atom/
Description: A limited set of UseMod Wiki's formatting syntax
Version: 1.1
Author: rajbot
Author URI: http://www.tikirobot.net
*/

/*  Copyright 2007  rajbot  (email : rajbot at http://tikirobot.net)
**
**  This program is free software; you can redistribute it and/or modify
**  it under the terms of the GNU General Public License as published by
**  the Free Software Foundation; either version 2 of the License, or
**  (at your option) any later version.
**
**  This program is distributed in the hope that it will be useful,
**  but WITHOUT ANY WARRANTY; without even the implied warranty of
**  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
**  GNU General Public License for more details.
**
**  You should have received a copy of the GNU General Public License
**  along with this program; if not, write to the Free Software
**  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

if ( ! class_exists( 'TikiWikiFormatting' ) ) :
class TikiWikiFormatting {

    function TikiWikiFormatting() {
        if (isset($this)) {

            /* offer up the options menu */
            if (is_plugin_page()) {
                TikiWikiFormatting::plugin_options();
            }

            add_action('admin_menu',   array(&$this, 'admin_menu'));        
            add_filter('the_content', array(&$this, 'processChunk'),  3);
            add_filter('comment_text', array(&$this, 'processChunk'), 3);
        }        
    } //end constructor
    
    
    function admin_menu() {
        if (function_exists('add_options_page')) {
            add_options_page('TikiWikiFormatting', 'TikiWikiFormatting', 8, basename(__FILE__));
        }
    }
    
    function plugin_options() {
        ?>
        <div class="wrap">        
        <h2>TikiWikiFormatting Info</h2>
        <p>This plugin lets authors use <a href="http://www.usemod.com/cgi-bin/wiki.pl">UseMod Wiki's</a> formatting syntax to format posts more easily.</p>

        <p>To create links, use the following syntax:</p>
        <pre>
        [http://tikirobot.net The TikiRobot Blog!]
        </pre>

        <p>To create unordered lists, use the following syntax:</p>
        <pre>
        *item A
        *item B
        **subitem
        *item C        
        </pre>

        <p>To create ordered lists, use the following syntax:</p>
        <pre>
        #one
        ##onePointOne
        ##onePointTwo
        #two
        #three
        </pre>

        </div>
        <?php
    }

    // processChunk()
    // _____________________________________________________________________________
    // This code is copied from the WikiLinesToHtml() function in 
    // UseMod wiki.pl 1.0 (GPL2) and translated into PHP.
    function processChunk($chunk) {
        $IndentLimit = 20;                  # Maximum depth of nested lists
    
        $htmlStack = array();
        $depth = 0;
        $pageHtml = "";
        
        //PHP's strtok eats blank lines.
        //$tok=strtok($chunk, "\n");    
        //while(FALSE !== $tok) {
        foreach(preg_split('/\n/', $chunk) as $tok) {
            $code = '';
    
            //print "line = $tok";
    
            if ( preg_match("/^(\*+)/", $tok, $matches) ) {
                $code = "UL";
                $depth = strlen($matches[0]);
                $tok = preg_replace("/^(\*+)/", '<li>', $tok);
            } else if ( preg_match("/^(\#+)/", $tok, $matches) ) {
                $code = "OL";
                $depth = strlen($matches[0]);
                $tok = preg_replace("/^(\#+)/", '<li>', $tok);
            } else {
                $depth = 0;
                $tok .= "\n";
            }
    
            while (count($htmlStack) > $depth) {   # Close tags as needed
                $pageHtml .=  "</" . array_pop($htmlStack) . ">";
            }
            
            if ($depth > 0) {
                if ($depth > $IndentLimit) $depth = $IndentLimit;
                if (count($htmlStack)>0) {  # Non-empty stack
                    $oldCode = array_pop($htmlStack);
                    if ($oldCode != $code) {
                        $pageHtml .= "</$oldCode><$code>";
                    }
                    array_push($htmlStack, $code);
                }
                while (count($htmlStack) < $depth) {
                    array_push($htmlStack, $code);
                    $pageHtml .= "<$code>";
                }
            }
                    
            $pageHtml .= TikiWikiFormatting::lineMarkup($tok);
            
            //$tok=strtok("\n");                
        }
    
        while (count($htmlStack) > 0) {       # Clear stack
            $pageHtml .=  "</" . array_pop($htmlStack) . ">";
        }
    
        return $pageHtml;    
    } //processChunk()


    // lineMarkup()
    // _____________________________________________________________________________
    // This code is copied from the CommonMarkup() function in 
    // UseMod wiki.pl 1.0 (GPL2) and translated into PHP.
    function lineMarkup($text) {
        //This code is copied from UseMod wiki.pl 1.0 (GPL2) and translated into PHP
        
        $UrlProtocols = "http|https|ftp|afs|news|nntp|mid|cid|mailto|wais|"
                      . "prospero|telnet|gopher";    
    
        // This is the original UseMod pattern. 
        // $FS is the field separator used by the UseMod wiki
        //     $FS  = "\x1e\xff\xfe\x1e";    # An unlikely sequence for any charset
        // $QDelim is the quote delimiter;
        //     $QDelim = '(?:"")?';     # Optional quote delimiter (not in output)
        // $UrlPattern = "((?:(?:$UrlProtocols):[^\\]\\s\"<>$FS]+)$QDelim)";
    
        $UrlPattern = "((?:$UrlProtocols)://.+?)";
        
        //preg_match("=\[$UrlPattern\s+(.*)\]=", $text, $matches);
        //print_r($matches);
        
        return preg_replace("=\[$UrlPattern\s+(.*?)\]=i", "<a href=\"$1\">$2</a>", $text);        
    } //lineMarkup()
    
    
}//end class
endif;

$tikiwikiformatting = new TikiWikiFormatting();

?>