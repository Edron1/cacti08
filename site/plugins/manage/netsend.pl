use Net::NetSend qw(:all);
use Net::Nslookup;
  
my $target_netbios_name = $ARGV[0];
my $source_netbios_name = "CACTI";
my $target_ip = nslookup(host => $ARGV[0], type => "A");
my $message = $ARGV[1];
my $debug = 0;

my $success = sendMsg($target_netbios_name, $source_netbios_name, $target_ip, $message, $debug);

print ($success ? "Delivery successfull\n" : "Error in delivery! \n$@\n");


