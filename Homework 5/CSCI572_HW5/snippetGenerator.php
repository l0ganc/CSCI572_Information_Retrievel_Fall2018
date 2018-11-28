<?php
	ini_set('memory_limit','2048M');
        include("simple_html_dom.php");

	function generate_snippet($doc_id, $search_terms){


		$file = file_get_contents("/Users/logan/Downloads/IR-Fall-2018/mercurynews/mercurynews/".$doc_id);
		$html = str_get_html($file);
		$content =  strtolower($html->plaintext);

		$strips = explode(" ", $search_terms);
		$search_terms = array_pop($strips);
		$content = preg_replace("!\s+!"," ",$content);
		$piece = explode(" ", $content);
		$pieces = array_values(array_filter($piece));

		$index = array_search($search_terms, $pieces);

		if ($index !== false) {
			$index = max($index - 5, 0);
		} else {
			$index = 0;
		}

	    if ($index == 0) {
			$str = "";
		} else {
			$str = "...";
		}
		for ($i = $index; $i < $index + 50 && $i < count($pieces); $i++) {
			$str .= " ".$pieces[$i];
		}
		$str .= "...";
		return $str;
	}

function decorate_snippet($search_terms, $content){
	foreach($search_terms as $term){
		$content = preg_replace('/\b'.$term.'\b/i',"<span class='snippet'>\$0</span>", $content);
	}
	return $content;
}

?>
