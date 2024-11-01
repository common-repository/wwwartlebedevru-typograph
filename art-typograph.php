<?php
/*
Plugin Name: www.artlebedev.ru Typograph
Version: 1.1
Plugin URI: http://stasdavydov.com/typograph/
Description: Russian typography with www.artlebedev.ru typograph tool from http://www.artlebedev.ru/tools/typograf/
Author: Stas Davydov
Author URI: http://stasdavydov.com/
*/
	require_once 'remotetypograf.php';

	add_filter('the_content', 'doTypograph', 9);
	add_filter('the_excerpt', 'doTypograph', 9);

    function doTypograph($text) {
        $longText = 32000;
        if (strlen($text) > $longText) {
            $pos = strrpos($text, "\n", $longText);
            if ($pos === NULL) {
                $pos = strrpos ($text, '.', $longText);
                if ($pos === NULL) {
                    $pos = strrpos($text, ' ', $longText);
                }
            }

            if ($pos === NULL) {
                return $text;
            } else {
                return doTypograph(substr($text, 0, $pos + 1))
                    . invokeIypograph(substr($text, $pos + 1));
            }
        } else {
            return doTypograph($text);
        }
    }

	function invokeIypograph($text) {
		$artTypePath = ABSPATH . 'wp-content/art-typo-cache/';
		if (!file_exists($artTypePath))
			mkdir($artTypePath);

		$hash = md5($text);
		if (file_exists($artTypePath.$hash) && 
			file_exists($artTypePath.$hash.'.meta') && 
			file_get_contents($artTypePath.$hash.'.meta') == strlen($text)) {

			ob_start();
			readfile($artTypePath.$hash);
			$text = ob_get_clean();
			ob_end_clean();

			return $text;
		}

		$size = strlen($text);

		$remoteTypograf = new RemoteTypograf('UTF-8');
		$remoteTypograf->htmlEntities();
		$remoteTypograf->br (false);
		$remoteTypograf->p (false);
		$remoteTypograf->nobr (3);

		$text = $remoteTypograf->processText ($text);

		$f = fopen($artTypePath.$hash.'.meta', 'w');
		fwrite($f, $size);
		fclose($f);
		$f = fopen($artTypePath.$hash, 'w');
		fwrite($f, $text);
		fclose($f);


		return $text;
	}
?>