my $o;

$o=$ARGV[1];
if ($ARGV[2] ne "") {
  $o=$o." ".$ARGV[2];
}
if ($ARGV[3] ne "") {
  $o=$o." ".$ARGV[3];
}
if ($ARGV[4] ne "") {
  $o=$o." ".$ARGV[4];
}

$o=lc($o);

use strict;
use Win32::OLE;

my $strComputer = $ARGV[0];

my ( $ProcessSet, $Process );
$ProcessSet = Win32::OLE->GetObject("winmgmts:{impersonationLevel=impersonate}!\\\\".$strComputer."\\root\\cimv2")->ExecQuery("SELECT * FROM Win32_Process");

my @pid = ("");
my $j=0;
my $i;
my $ok;

foreach $Process (in $ProcessSet) {
  $pid[$j]=lc($Process->[Name]);
  $j=$j+1;
}
	
$ok=0;
for $i (0..$j-1) {
  if ($o eq $pid[$i]) {
    $ok=1;
  }
}

if ($ok eq 1) {
  print "up";
}
else {
  print "down";
}