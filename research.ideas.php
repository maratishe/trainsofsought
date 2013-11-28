<?php
set_time_limit( 0);
ob_implicit_flush( 1);
//ini_set( 'memory_limit', '4000M');
for ( $prefix = is_dir( 'ajaxkit') ? 'ajaxkit/' : ''; ! is_dir( $prefix) && count( explode( '/', $prefix)) < 4; $prefix .= '../'); if ( ! is_file( $prefix . "env.php")) $prefix = '/web/ajaxkit/'; if ( ! is_file( $prefix . "env.php")) die( "\nERROR! Cannot find env.php in [$prefix], check your environment! (maybe you need to go to ajaxkit first?)\n\n");
foreach ( array( 'functions', 'env') as $k) require_once( $prefix . "$k.php"); clinit(); 
//clhelp( "[action] parse | ");
//htg( clget( 'action'));

echo "\n\n";
echo "will use research.ideas file in this directory\n";
if ( ! is_file( 'research.topics2tags')) jsondump( array(), 'research.topics2tags');
if ( ! is_file( 'research.tags2topics')) jsondump( array(), 'research.tags2topics');
$topics = jsonload( 'research.topics2tags'); // { topic: 'tag,tag,tag', ...}
if ( ! $topics) $topics = array();
$tags = jsonload( 'research.tags2topics'); // { tag: 'topic,topic,....', ...}
foreach ( flget( '.', '', '', 'backup2') as $file) `rm -Rf $file`;
foreach ( flget( '.', '', '', 'backup') as $file) { $file2 = $file . '2'; `mv $file $file2`; }
jsondump( $topics, 'research.topics2tags.backup');
jsondump( $tags, 'research.tags2topics.backup');
foreach ( $topics as $k => $v) $topics[ $k] = array();	// every time from scratch
foreach ( $tags as $k => $v) $tags[ $k] = hvak( ttl( $v) ? ttl( $v) : array());
$ideas = array(); // { idea: { topics, tags, info(text)}, ...}
foreach ( file( 'research.ideas') as $line) {
	$line = trim( $line); 
	if ( ! $line) continue;
	if ( strpos( $line, '#') === 0) continue;
	$L = ttl( $line, "\t", '');
	if ( count( $L) != 3) die( " [research.ideas] FORMAT ERROR in line [$line]\n");
	$L[ 1] = ttl( $L[ 1]);
	extract( lth( $L, ttl( 'one,two,four')));
	foreach ( $two as $tag) htouch( $tags, $tag);
	$h = array();
	$h[ 'tags'] = $two; $h[ 'info'] = $four;
	$ideas[ "$one"] = $h;
}


// create the list of the most congested stations
$h = array(); foreach ( $tags as $tag => $h2) $h[ $tag] = count( $h2); asort( $h, SORT_NUMERIC);
$tagpos = $h; $h2 = array(); foreach  ( $h as $tag => $pos) $h2[ $tag] = $tags[ $tag];
$tags = $h2;	// now in increasing order of popularity (trains=topics passing through)
// update topics with current tag info and dump to file
foreach ( $tags as $tag => $h2) foreach ( $h2 as $topic => $v) { htouch( $topics, $topic); $topics[ $topic][ $tag] = true; }
foreach ( $topics as $topic => $h2) {
	$h = array(); 
	foreach ( $h2 as $tag => $v) $h[ $tag] = $tagpos[ $tag];
	arsort( $h, SORT_NUMERIC);	// now ordered in decreasing popularity
	$topics[ $topic] = ltt( hk( $h));
}
ksort( $topics); 	// topics (trains) should be sorted in alphabetic order
jsondump( $topics, 'research.topics2tags');	// dump
// process and dump current tags
$topicpos = hvak( hk( $topics));
foreach ( $tags as $tag => $h2) {	// already in increasing popularity, now order topics
	$h = array(); foreach ( $h2 as $topic => $v) $h[ $topic] = $topicpos[ $topic];
	asort( $h, SORT_NUMERIC);
	$tags[ $tag] = ltt( hk( $h));
}
$h = array(); $tags2 = array();
foreach ( $tags as $tag => $h2) {
	$count = count( ttl( $h2));
	htouch( $h, "$count");
	lpush( $h[ "$count"], $tag);
}
ksort( $h, SORT_NUMERIC);
foreach ( $h as $count => $L) { sort( $L); foreach ( $L as $tag) { $tags2[ $tag] = $tags[ $tag]; }}
$tags = $tags2;
jsondump( $tags, 'research.tags2topics');	// dump


?>