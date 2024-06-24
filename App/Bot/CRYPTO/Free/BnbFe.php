<?php
const
host = "https://bnbfe.com/",
register_link = "https://bnbfe.com/ref/193822554287",
youtube = "https://youtube.com/@iewil";

function h(){
	$ua[]="Host: ".parse_url(host)['host'];
    $ua[]="accept: */*";
    $ua[]="content-type: application/json";
    $ua[]="user-agent: ".ua();
    return $ua;
}
function login($user){
	$res=curl(host,h(),'',1)[1];
	$token=explode('"',explode('data-csrf="',$res)[1])[0];
	$cek=explode('&quot',explode('checksum&quot;:&quot;',$res)[3])[0];
	$id=explode('"',explode('wire:effects="[]" wire:id="',$res)[3])[0];
	
	$data='{"_token":"'.$token.'","components":[{"snapshot":"{\"data\":[],\"memo\":{\"id\":\"'.$id.'\",\"name\":\"login\",\"path\":\"\\/\",\"method\":\"GET\",\"children\":[],\"scripts\":[],\"assets\":[],\"errors\":[],\"locale\":\"en\"},\"checksum\":\"'.$cek.'\"}","updates":{},"calls":[{"path":"","method":"login","params":["'.$user.'",0]}]}]}';
	return curl(host."livewire/update",h(), $data,1)[1];
}
function Sat($int){
	return sprintf('%.12f',floatval($int));
}
function wallet($address){
	$awal = substr($address,0,5);
	$akhir = substr($address,-5);
	return $awal.'****'.$akhir;
}

Ban(1);
cookie:
Cetak("Register",register_link);
print line();
$user = simpan("Original_Wallet_Faucetpay");
ua();
hapus('cookie.txt');

print p."Jangan lupa \033[101m\033[1;37m Subscribe! \033[0m youtub saya :D";sleep(2);
//system("termux-open-url ".youtube);
Ban(1);
/*gas*/
while(true){
	$dashboard = curl(host.'dashboard', h(),'',1)[1];
	$address = explode('</h4>',explode('<h4>',explode('<div class="card-body">',$dashboard)[1])[1])[0];
	$bal = Sat(explode('}',explode('<p class="fs-5 fw-bold" x-data="{ balance: ',$dashboard)[1])[0]);
	if(!$address){
		login($user);
		continue;
	}
	print k.wallet($address).m." | ".h.$bal."\n";
	$r = curl(host."rewards",h(),'',1)[1];
	$reward = explode('<div class="card-footer">',explode('<h1 class="mt-4">Rewards</h1>',$r)[1])[0];
	$rewardurl = explode('"',explode('<a target="_blank" href="',$reward)[1])[0];
	if($rewardurl){
		curl($rewardurl, h(),'',1);
		print sukses("claim reward");
	}
	$r = curl(host."withdraw",h(),'',1)[1];
	$minimum = explode('"',explode('"minimum_withdraw_amount":"',$r)[1])[0];
	if($minimum == 0.000001){
	}else{
		print Error("Sorry not original wallet faucetpay\n");
		print line();
		hapus("Original_Wallet_Faucetpay");
		goto cookie;
	}
	if($bal > 0.000001) {
		$cek=explode('&quot',explode('checksum&quot;:&quot;',$r)[3])[0];
		$id=explode('"',explode('wire:effects="[]" wire:id="',$r)[3])[0];
		$token=explode('"',explode('data-csrf="',$r)[1])[0];
		$data = '{"_token":"'.$token.'","components":[{"snapshot":"{\"data\":[],\"memo\":{\"id\":\"'.$id.'\",\"name\":\"withdraw-form\",\"path\":\"withdraw\",\"method\":\"GET\",\"children\":[],\"scripts\":[],\"assets\":[],\"errors\":[],\"locale\":\"en\"},\"checksum\":\"'.$cek.'\"}","updates":{},"calls":[{"path":"","method":"withdraw","params":["faucet_pay","'.$bal.'"]}]}]}';
		$r = curl(host.'livewire/update', h(), $data,1);
		print sukses("withdraw sukses");
	}
	tmr(60);
}