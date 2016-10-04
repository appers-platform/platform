<?
if(!PROJECT) {
    print "Can't run without project\n";
    return ;
}
$timer = new timer();

$timer->start();
print "Previos:\n";
var_dump(mCache::get('test'));
print "Time of 'get': ".$timer."\n";
$timer->reset();

$timer->start();
$v = time();
print "Set {$v}\n";
mCache::set('test', $v);
print "Time of 'set': ".$timer."\n";
