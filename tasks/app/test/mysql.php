<?
if(!PROJECT) {
	print "Can't run without project\n";
	return ;
}
$timer = new timer();
dbMysql::getConnect();
print "Time of connection: ".$timer."\n";
$timer->reset();
$timer->start();
dbMysql::getConnect()->getRows("show tables;");
print "Time of 'show talbes': ".$timer."\n";
