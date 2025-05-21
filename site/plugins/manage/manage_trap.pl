#parameters :
#hostname community version(1|2) port timestamp message oid

use strict;

use Net::SNMP qw(:ALL);

my $v ="";

if ($ARGV[2] == 1) {
   $v = "snmpv1";
}

if ($ARGV[2] == 2) {
   $v = "snmpv2c";
}

my ($session, $error) = Net::SNMP->session(
   hostname  => $ARGV[0],
   community => $ARGV[1],
   version   => $v,
   port      => $ARGV[3]
);

if (!defined($session)) {
   printf("ERROR: %s.\n", $error);
   exit 1;
}

if ($v eq "snmpv1") {
my $result = $session->trap(
   timestamp    => $ARGV[4],
   enterprise   => $ARGV[6],
   generictrap  => 6,
   specifictrap => 1,
   varbindlist  => [
      $ARGV[6].'.1', OCTET_STRING, $ARGV[5]
   ]
);
}

if ($v eq "snmpv2c") {
my $result = $session->snmpv2_trap(
   varbindlist => [
      '1.3.6.1.2.1.1.3.0', TIMETICKS, $ARGV[4],
      '1.3.6.1.6.3.1.1.4.1.0', OBJECT_IDENTIFIER, $ARGV[6],
      $ARGV[6].'.1', OCTET_STRING, $ARGV[5]
   ]
);
}

$session->close();

exit 0;