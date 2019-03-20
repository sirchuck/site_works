<?php
	// This should normally be your first line if you are passing a variable array.
	$q = json_decode( base64_decode( getopt("q:")['q'] ) );

	// You access your variables passed like this:
	// echo $q->key1;

	// Note you can not use the framework from here. You would have to use something like file_get_contents() to open a web url or something.

?>