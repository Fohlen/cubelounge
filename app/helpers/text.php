<?php
namespace Helpers;

class Text extends \Prefab
{
	public function split($text, $maxLength)
	{
		/*
		 * Attribution goes to http://www.codediesel.com/php/splitting-a-text-on-word-boundaries/
		 *
		 * Make sure that the string will not be longer
		 * than $maxLength.
		 */
		if(strlen($text) > $maxLength)
		{
			/* Trim the text to $maxLength characters */
			$text = substr($text, 0, $maxLength - 1);
	
			/* Split words only at boundaries. This will be
			 accomplished by moving back each character from
			 the end of the split string until a space is found.
			*/
			while(substr($text,-1) != ' ')
			{
				$text = substr($text, 0, -1);
			}
	
			/* Remove the whitespace at the end. */
			$text = rtrim($text);
		}
		return $text;
	}
}