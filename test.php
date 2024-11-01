<?php

$content = "blah blah blah [http://tikirobot.net tikirobot] blah blah 
blah arg arg [HTTP://wordpress.org wordpress] *foo
*arg
**blah
*arg


#one
##onepointfive
#two
#three";

echo $content;

echo "\n-----\n";

//preg_match_all("/<nowiki>(.|\n)*?\<\/nowiki>/i", $content, $matches, PREG_OFFSET_CAPTURE);
//print_r($matches);

// $split = preg_split("/(<nowiki>.*?\<\/nowiki>)/is", $content, -1, PREG_SPLIT_DELIM_CAPTURE);
// 
// print_r($split);
// 
// $newcontent = '';
// foreach($split as $blob) {
// 
//     if(preg_match("/^<nowiki>.*<\/nowiki>$/is", $blob)) {
//         $newcontent .= $blob;
//     } else {
//         $newcontent .= processChunk($blob);
//     }
// }

processChunk($content);
//echo lineMarkup($content) . "\n";

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
        //$tok .= "\n";

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
                
        $pageHtml .= lineMarkup($tok);
        
        //$tok=strtok("\n");    
        
    }

    while (count($htmlStack) > 0) {       # Clear stack
        $pageHtml .=  "</" . array_pop($htmlStack) . ">";
    }

    print $pageHtml;    
}

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

}
?>