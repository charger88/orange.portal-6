<?php

class OPAL_Parser {
	
	private $tags = array(
		'a' => 2,'abbr' => 2,'acronym' => 2,'address' => 2,'b' => 2,'bdo' => 2,
		'bis' => 2,'blockquote' => 2,'body' => 2,'br' => 2,'caption' => 2,
		'center' => 2,'cite' => 2,'code' => 2,'col' => 2,'colsroup' => 2,
		'dd' => 2,'del' => 2,'dfn' => 2,'div' => 2,'dl' => 2,'dt' => 2,
		'em' => 2,'font' => 2,'h1' => 2,'h2' => 2,'h3' => 2,'h4' => 2,
		'h5' => 2,'h6' => 2,'hr' => 1,'i' => 2,'img' => 1,'ins' => 2,'kbd' => 2,
		'li' => 2,'map' => 2,'marquee' => 2,'nobr' => 2,'ol' => 2,
		'p' => 2,'param' => 2,'pre' => 2,'q' => 2,'samp' => 2,'small' => 2,
		'span' => 2,'strike' => 2,'strons' => 2,'sub' => 2,'sup' => 2,
		'table' => 2,'tbody' => 2,'td' => 2,'tfoot' => 2,'th' => 2,'thead' => 2,
		'tr' => 2,'tt' => 2,'ul' => 2,'var' => 2,'wbr' => 2,'xmp' => 2
	);

	private $tagattrs = array(
		'a' => array('href','title'),
		'img' => array('src','alt','title'),
		'th' => array('colspan','rowspan'),
		'td' => array('colspan','rowspan'),
		'table' => array('rules')
	);

	private $allowedprotocols = array('http','https','ftp');
	private $taginfo = array();
	
	public function __construct($tags = null, $tagattrs = null){
		if (!is_null($tags)){
			$this->tags = $tags;
		}
		if (!is_null($tagattrs)){
			$this->tagattrs = $tagattrs;
		}
	}
	
	public static function formatText($text,$type){
		if ($type == 1){
			$text = str_replace('  ',' &nbsp;',nl2br(strip_tags($text,'<b><i><em><strong><strike><u><a><code><img><cite><pre><q>')));
		} elseif ($type == 2){
			$text = str_replace('  ',' &nbsp;',nl2br(self::esc($text)));
		} elseif ($type == 3){
			$parser = new OPAL_Parser();
			$text = str_replace('&amp;nbsp;', '&nbsp;', $parser->parse($text));
		} elseif ($type == 4){
			$text = self::improved_esc($text);
		}
		return $text;
	}
	
	public function parse($text){
		$text = ' '.$text;
		$currentChar = 0;
		$fullLength = mb_strlen($text);
		$clear = '';
		while ($currentChar < $fullLength) {
			$tagstart = mb_strpos($text,'<',$currentChar);
			if ($tagstart == 0) {
				$clear .= self::esc(mb_substr($text,$currentChar,$fullLength-$currentChar));
				$currentChar = $fullLength;
			} else {
				if ($tagstart > $currentChar) {
					$clear .= self::esc(mb_substr($text,$currentChar,$tagstart-$currentChar));
				}
				$currentChar = $tagstart;
				$tagfinish = mb_strpos($text,'>',$tagstart);
				if ($tagfinish == 0) {
					$clear .= self::esc(mb_substr($text,$currentChar,$fullLength-$currentChar));
					$currentChar = $fullLength;
				} else {
					$tagBody = mb_substr($text,$tagstart+1,$tagfinish-$tagstart-1);
					if ($tagBody{0} == ' ') {
						$clear .= self::esc('< ');
						$currentChar += 2;
					} else {
						$clear .= $this->processTag($tagBody);
						$currentChar = $tagfinish+1;
					}
				}
			}		
		}
		return mb_substr($clear,1,mb_strlen($clear)-1).$this->closeTags();
	}
	
	private function closeTags($finalTag = null){
		$result = '';
		if (is_null($finalTag) || in_array($finalTag,$this->taginfo)){
			$count = count($this->taginfo);
			for ($i = ($count-1); $i >= 0; $i--){
				$tag = $this->taginfo[$i];
				if ($tag){
					$this->taginfo[$i] = null;
					$result .= '</'.$tag.'>';
				}
				if ( $finalTag && ($tag == $finalTag) ){
					$i = -1;
				}
			}
		}
		return $result;
	}
	
	private function parseAttributes($attrs,$tag){
		$attrs = trim(trim($attrs),'/');
		$pairs = array();
		$currentChar = 0;
		$fullLength = mb_strlen($attrs);
		$string = ''; $open = true; $eqnum = 0; $last = ''; $qopenchar = '';
		while ($currentChar <= $fullLength) {
			$char = mb_substr($attrs, $currentChar, 1);
			if (($char == ' ') && ($qopenchar == '')){
				$open = (!$open);
				if (!$open && $string) {
					$pairs[] = $string;
					$qopenchar = '';
					$eqnum = 0;
					$last = '';
					$string = '';
				} else {
					$last = ' ';
					$string .= ' ';
				}
			} else {
				$open = true;
				if (($char == $qopenchar) && ($last != "\\")){
					$qopenchar = '';
				}	
				if ( ($last == '=') && ($eqnum == 1) ){
					$qopenchar = $char;
				}
				if ($char == '='){
					$eqnum++;
				}
				$last = $char;
				$string .= $char;
			}
			$currentChar++;
		}
		if ($string){
			$pairs[] = $string;
		}
		$attributes = '';
		if ($pairs){
			foreach ($pairs as $pair){
				$eqPos = mb_strpos($pair,'=');
				if ($eqPos > 0) {
					$attrname = mb_strtolower(mb_substr($pair,0,$eqPos));
					$attrvalue = mb_substr($pair,$eqPos+1,mb_strlen($pair)-($eqPos+1));
					$attrvalue = trim($attrvalue,"'\" ");
					if (isset($this->tagattrs[$tag]) && is_array($this->tagattrs[$tag])) {
						if (in_array($attrname,$this->tagattrs[$tag])) {
							if (($attrname == 'src')||($attrname == 'href')||($attrname == 'link')||($attrname == 'rel')||($attrname == 'url')) {
								$protocol = mb_substr($attrvalue,0,mb_strpos($attrvalue,':'));
								if (in_array($protocol,$this->allowedprotocols)) {
									$attrvalue = mb_substr($attrvalue,mb_strlen($protocol)+1);								
									$attrvalue = $protocol.':'.str_replace(':','%3A',self::esc($attrvalue));
								} else {
									$attrvalue = str_replace(':','%3A',self::esc($attrvalue));
								}
							}
							$attributes .= (' '.$attrname.'="'.$this->special($tag,$attrname,$attrvalue).'"');
						}
					}
				}
			}
		}
		return $attributes;
	}
	
	private function processTag($tag){
		$close = ($tag{0} == '/');
		$start = $close ? 1 : 0;
		$tbEnd = mb_strpos($tag,' ');
		if ($tbEnd < 1) {
			$tbEnd = mb_strlen($tag);
		}
		$tagBody = mb_strtolower(mb_substr($tag,$start,$tbEnd-$start));
		if (isset($this->tags[$tagBody])) {
			if ($close) {
				return $this->closeTags($tagBody);
			} else {
				if ($this->tags[$tagBody] == 2) {
					$this->taginfo[] = $tagBody;
				}
				$args = $this->parseAttributes(mb_substr($tag,mb_strlen($tagBody)),$tagBody);
				return ($this->tags[$tagBody] == 1) ? '<'.$tagBody.$args.' />' : '<'.$tagBody.$args.'>';
			}
		} else {
			return self::esc('<'.$tag.'>');
		}
	}
	
	public static function esc($text){
		return htmlspecialchars($text,ENT_COMPAT,'UTF-8');
	}
	
	public static function improved_esc($text){
		$text = self::esc($text);
		$text = explode("\n",$text);
		$text_new = array();
		$cite_char = self::esc('>');
		$cite_text = array();
		$last_enter = true;
		$cite_start = false;
		foreach ($text as $i => $text_line){
			if (trim($text_line) && (trim($text_line) !== $cite_char) ){
				if (strpos($text_line,$cite_char) === 0){
					$cite_start = true;
					$cite_text[] = trim(substr($text_line, strlen($cite_char)));
				} else {
					if ($cite_text){
						$text_line = '<blockquote>'.implode('<br/>', $cite_text).'</blockquote>'.$text_line;
					}
					$text_new[] = $text_line;
					$cite_text = array();
					$cite_start = false;
				}
				$last_enter = false;
			} else if (!$last_enter) {
				if ($cite_start){
					$cite_text[] = '';
				} else {
					$text_new[] = '';
				}
				$last_enter = true;
			}
		}
		if ($cite_text){
			$text_new[] = '<blockquote>'.implode('<br/>', $cite_text).'</blockquote>';
		}
		return implode('<br/>', $text_new);
	}

    //TODO Think about it
	private function special($tag,$attrname,$attrvalue){
		return $attrvalue;
	}

}