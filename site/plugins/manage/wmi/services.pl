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
if ($ARGV[5] ne "") {
  $o=$o." ".$ARGV[5];
}
if ($ARGV[6] ne "") {
  $o=$o." ".$ARGV[6];
}
if ($ARGV[7] ne "") {
  $o=$o." ".$ARGV[7];
}
if ($ARGV[8] ne "") {
  $o=$o." ".$ARGV[8];
}
if ($ARGV[9] ne "") {
  $o=$o." ".$ARGV[9];
}

use strict;
use Win32::OLE;

my $strComputer = $ARGV[0];

my ( $ServiceSet, $Service );
$ServiceSet = Win32::OLE->GetObject("winmgmts:{impersonationLevel=impersonate}!\\\\".$strComputer."\\root\\cimv2")->ExecQuery("SELECT * FROM Win32_Service WHERE State=\"Running\"");

my @pid = ("");
my $j=0;
my $i;
my $n = "";
my $ok;
my $replace_space;
my $extrait;
my $t;
my $precedent;

foreach $Service (in $ServiceSet) {
  $n = "";
  $t = $Service->[displayName];

  for $i (0..length($t)) {
    $ok=0;
    $replace_space=0;
    $extrait = substr($t,$i,1);
    if ($extrait ne "'") {
      if ($extrait ne "�") {
        if ($extrait ne "�") {
          if ($extrait ne "�") {
            $ok=1;
          } else {
            $replace_space=1;
          }
        } else {
          $replace_space=1;
        }
      } else {
        $replace_space=1;
      }
    }

    if ($ok eq 1) {
      $n=$n.$extrait;
    }
  
    if ($replace_space eq 1) {
      if ($i ne length($t)) {
        $n=$n." ";
      }
    }

    $precedent = $extrait;
  }

  $pid[$j]=$n;
  $j=$j+1;
}
	
$ok=0;
for $i (0..$j-1) {
  if ($o eq lc($pid[$i])) {
    $ok=1;
  }
}

if ($ok eq 1) {
  print "up";
}
else {
  print "down";
}