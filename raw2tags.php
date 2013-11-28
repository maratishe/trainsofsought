<?php
set_time_limit( 0);
ob_implicit_flush( 1);
//ini_set( 'memory_limit', '4000M');
for ( $prefix = is_dir( 'ajaxkit') ? 'ajaxkit/' : ''; ! is_dir( $prefix) && count( explode( '/', $prefix)) < 4; $prefix .= '../'); if ( ! is_file( $prefix . "env.php")) $prefix = '/web/ajaxkit/'; if ( ! is_file( $prefix . "env.php")) die( "\nERROR! Cannot find env.php in [$prefix], check your environment! (maybe you need to go to ajaxkit first?)\n\n");
foreach ( array( 'functions', 'env') as $k) require_once( $prefix . "$k.php"); clinit(); 
clhelp( 'PURPOSE: to read all tags from raw.bz64jsonl and dump them to a file');
clhelp( '[depth] how deep to go into the file -- depends on setup of sample.php');
clhelp( '[outroot] will create outroot.txt and outroot.pdf');
htg( clget( 'depth,outroot'));


echo "\n\n"; $e = echoeinit(); $in = finopen( 'raw.bz64jsonl'); $count = 0; $H = array(); 
while ( ! findone( $in) && $count < $depth) {
	list( $h, $p) = finread( $in); if ( ! $h) continue; echoe( $e, "reading $p > $count");
	extract( $h); // tags
	foreach ( ttl( ltt( ttl( $tags, ','), ' '), ' ') as $tag) {
		htouch( $H, "$tag", 0, false, false);
		$H[ "$tag"]++;
	}
	$count++;
}
finclose( $in); echo " OK\n";


// dump to text
arsort( $H, SORT_NUMERIC);
$out = fopen( "$outroot.txt", 'w'); foreach ( $H as $tag => $count) fwrite( $out, "$tag    $count\n"); fclose( $out);
         
// dump to chart
while ( count( $H) > 25) hpop( $H);
$FS = 14; $BS = 4.5;
class MyChartFactory extends ChartFactory { public function make( $C, $margins) { return new ChartLP( $C->setup, $C->plot, $margins);}}
$S = new ChartSetupStyle(); $S->style = 'D'; $S->lw = 0.1; $S->draw = '#000'; $S->fill = null;
$S2 = clone $S; $S2->style = 'F'; $S2->lw = 0; $S2->draw = null; $S2->fill = '#000';
list( $C, $CS, $CST) = chartlayout( new MyChartFactory(), 'P', '1x1', 30, '0.25:0.1:0.45:0.2');
$C2 = lshift( $CS);
$ks = hk( $H); $vs = hv( $H); $C2->train( hk( $vs), $vs);
$C2->autoticks( null, null, 8, 8);
$C2->frame( $ks, 'Occurrence (times)', null, false, true);	// veritical tags
chartline( $C2, hk( $vs), $vs, $S);
chartscatter( $C2, hk( $vs), $vs, 'circle', $BS, $S2);
$C->dump( "$outroot.pdf");


?>