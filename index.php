<?php
/*
 #####                                            
#     # #####   ####  ##### # ###### #      #   # 
#       #    # #    #   #   # #      #       # #  
 #####  #    # #    #   #   # #####  #        #   
      # #####  #    #   #   # #      #        #   
#     # #      #    #   #   # #      #        #   
 #####  #       ####    #   # #      ######   # 

Under MIT licence
*/

/**********************
** Koray KIRCAOGLU ****
** koraym@gmail.com ***
***********************
* www.ohshiftlabs.com *
************2014*******
* Spotify PHP Class ***
** Free Distribution **
**********************/

ini_set('display_errors',true);
error_reporting(E_ALL);//^E_NOTICE
date_default_timezone_set("Europe/Istanbul");
//session_start();
echo "<pre>";

include "inc/class.spotifly.php";
$callbackurl = "http://127.0.0.1/~koray/dev/spotifly/";
//This must match with Redirect URIs defined at https://developer.spotify.com/my-applications

$sconfig = array(
	"clientid"		=> "",
	"clientsecret"	=> "",
	"callbackurl"	=> $callbackurl/*,
	"scope"			=> "user-read-private,user-read-email"*/
);

$spotify = new spotifly($sconfig);



//Start a login
if(!isset($_SESSION["spotify"]["login"]) || $_SESSION["spotify"]["login"]!=true){
	$loginUrl = $spotify->login();//if you need a state you can define here.
	echo "<a href='$loginUrl'>Login with spotify</a>";

}else{

	//If login succesfully done then get auth.
	if(!$_SESSION["spotify"]["auth"]){
		$authurl = $callbackurl."?get=auth";

		echo "Login successfull \n";
		echo "<a href='$authurl'>GET Auth</a>\n";
	}

	//Auth Process
	if(isset($_GET["get"]) && $_GET["get"]=="auth" && !$_SESSION["spotify"]["auth"]){
		$authresult = $spotify->auth();
	}

	//Auth is OK, get user info
	if($_SESSION["spotify"]["auth"] || isset($authresult["access_token"]) ){

		$user = $spotify->getUser();

		echo "\n";
		echo "<a href='?refreshtoken=yes'>Refresh Token</a> ";
		echo "<a href='?endpoint=browse/featured-playlists'>Featured Playlists</a> ";
		echo "<a href='?endpoint=me/tracks'>My Tracks</a>\n\n";

		if(!isset($user["error"])){
			echo parseSpotifyUser($user);			
		}else{
			echo $user["error"]["message"];
		}


	}


	//EndPointTest

	if(isset($_GET["endpoint"])){
		$data = $spotify->endPointData($_GET["endpoint"]);
		print_r($data);
	}


	//Refresh Token
	if(isset($_GET["refreshtoken"])){
		$refresh = $spotify->refreshToken();
		print_r($refresh);
	}

}




//Simple user info parser.
function parseSpotifyUser($user){

	if(is_array($user) && isset($user["display_name"])){
		$flag = '<img src="https://upload.wikimedia.org/wikipedia/commons/c/c0/Blank.gif" class="flag flag-'.strtolower($user["country"]).'" alt="'.$user["country"].'" />';
		$premium = ($user["product"]=="premium")? "Yes":"No";

		$html = "<img src='".$user["images"][0]["url"]."' border='0' align='left' />";
		$html.= "<a href='".$user["external_urls"]["spotify"]."'>";
		$html.= "<h1>".$user["display_name"].$flag."</h1>";
		$html.= '</a>';
		$html.= "<b>E-Mail:</b> ".$user["email"]. "\n";
		$html.= "<b>Follower:</b> ".$user["followers"]["total"]. "\n";
		$html.= "<b>Is Premium:</b> " . $premium;
		$html.= "\n";
		$html.= "<a href='".$user["uri"]."'>Find in spotify</a>\n";
		$html.= "<hr>";

		echo $html;
	}

//	print_r($user);
}
?>








<style type="text/css">
/* To show user's country flag. */
	.flag {
	width: 16px;
	height: 11px;
	background:url(http://flag-sprites.com/gen/flags.png?set=famfamfam&countries=ad,ae,af,ag,ai,al,am,an,ao,ar,as,at,au,aw,az,ba,bb,bd,be,bf,bg,bh,bi,bj,bm,bn,bo,br,bs,bt,bv,bw,by,bz,ca,catalonia,cd,cf,cg,ch,ci,ck,cl,cm,cn,co,cr,cu,cv,cw,cy,cz,de,dj,dk,dm,do,dz,ec,ee,eg,eh,england,er,es,et,eu,fi,fj,fk,fm,fo,fr,ga,gb,gd,ge,gf,gg,gh,gi,gl,gm,gn,gp,gq,gr,gs,gt,gu,gw,gy,hk,hm,hn,hr,ht,hu,ic,id,ie,il,im,in,io,iq,ir,is,it,je,jm,jo,jp,ke,kg,kh,ki,km,kn,kp,kr,kurdistan,kw,ky,kz,la,lb,lc,li,lk,lr,ls,lt,lu,lv,ly,ma,mc,md,me,mg,mh,mk,ml,mm,mn,mo,mp,mq,mr,ms,mt,mu,mv,mw,mx,my,mz,na,nc,ne,nf,ng,ni,nl,no,np,nr,nu,nz,om,pa,pe,pf,pg,ph,pk,pl,pm,pn,pr,ps,pt,pw,py,qa,re,ro,rs,ru,rw,sa,sb,sc,scotland,sd,se,sg,sh,si,sk,sl,sm,sn,so,somaliland,sr,ss,st,sv,sx,sy,sz,tc,td,tf,tg,th,tj,tk,tl,tm,tn,to,tr,tt,tv,tw,tz,ua,ug,um,us,uy,uz,va,vc,ve,vg,vi,vn,vu,wales,wf,ws,ye,yt,za,zanzibar,zm,zw) no-repeat
}

.flag.flag-ad {background-position: -16px 0}
.flag.flag-ae {background-position: -32px 0}
.flag.flag-af {background-position: -48px 0}
.flag.flag-ag {background-position: -64px 0}
.flag.flag-ai {background-position: -80px 0}
.flag.flag-al {background-position: -96px 0}
.flag.flag-am {background-position: -112px 0}
.flag.flag-an {background-position: -128px 0}
.flag.flag-ao {background-position: -144px 0}
.flag.flag-ar {background-position: -160px 0}
.flag.flag-as {background-position: -176px 0}
.flag.flag-at {background-position: -192px 0}
.flag.flag-au {background-position: -208px 0}
.flag.flag-aw {background-position: -224px 0}
.flag.flag-az {background-position: -240px 0}
.flag.flag-ba {background-position: 0 -11px}
.flag.flag-bb {background-position: -16px -11px}
.flag.flag-bd {background-position: -32px -11px}
.flag.flag-be {background-position: -48px -11px}
.flag.flag-bf {background-position: -64px -11px}
.flag.flag-bg {background-position: -80px -11px}
.flag.flag-bh {background-position: -96px -11px}
.flag.flag-bi {background-position: -112px -11px}
.flag.flag-bj {background-position: -128px -11px}
.flag.flag-bm {background-position: -144px -11px}
.flag.flag-bn {background-position: -160px -11px}
.flag.flag-bo {background-position: -176px -11px}
.flag.flag-br {background-position: -192px -11px}
.flag.flag-bs {background-position: -208px -11px}
.flag.flag-bt {background-position: -224px -11px}
.flag.flag-bv {background-position: -240px -11px}
.flag.flag-bw {background-position: 0 -22px}
.flag.flag-by {background-position: -16px -22px}
.flag.flag-bz {background-position: -32px -22px}
.flag.flag-ca {background-position: -48px -22px}
.flag.flag-catalonia {background-position: -64px -22px}
.flag.flag-cd {background-position: -80px -22px}
.flag.flag-cf {background-position: -96px -22px}
.flag.flag-cg {background-position: -112px -22px}
.flag.flag-ch {background-position: -128px -22px}
.flag.flag-ci {background-position: -144px -22px}
.flag.flag-ck {background-position: -160px -22px}
.flag.flag-cl {background-position: -176px -22px}
.flag.flag-cm {background-position: -192px -22px}
.flag.flag-cn {background-position: -208px -22px}
.flag.flag-co {background-position: -224px -22px}
.flag.flag-cr {background-position: -240px -22px}
.flag.flag-cu {background-position: 0 -33px}
.flag.flag-cv {background-position: -16px -33px}
.flag.flag-cw {background-position: -32px -33px}
.flag.flag-cy {background-position: -48px -33px}
.flag.flag-cz {background-position: -64px -33px}
.flag.flag-de {background-position: -80px -33px}
.flag.flag-dj {background-position: -96px -33px}
.flag.flag-dk {background-position: -112px -33px}
.flag.flag-dm {background-position: -128px -33px}
.flag.flag-do {background-position: -144px -33px}
.flag.flag-dz {background-position: -160px -33px}
.flag.flag-ec {background-position: -176px -33px}
.flag.flag-ee {background-position: -192px -33px}
.flag.flag-eg {background-position: -208px -33px}
.flag.flag-eh {background-position: -224px -33px}
.flag.flag-england {background-position: -240px -33px}
.flag.flag-er {background-position: 0 -44px}
.flag.flag-es {background-position: -16px -44px}
.flag.flag-et {background-position: -32px -44px}
.flag.flag-eu {background-position: -48px -44px}
.flag.flag-fi {background-position: -64px -44px}
.flag.flag-fj {background-position: -80px -44px}
.flag.flag-fk {background-position: -96px -44px}
.flag.flag-fm {background-position: -112px -44px}
.flag.flag-fo {background-position: -128px -44px}
.flag.flag-fr {background-position: -144px -44px}
.flag.flag-ga {background-position: -160px -44px}
.flag.flag-gb {background-position: -176px -44px}
.flag.flag-gd {background-position: -192px -44px}
.flag.flag-ge {background-position: -208px -44px}
.flag.flag-gf {background-position: -224px -44px}
.flag.flag-gg {background-position: -240px -44px}
.flag.flag-gh {background-position: 0 -55px}
.flag.flag-gi {background-position: -16px -55px}
.flag.flag-gl {background-position: -32px -55px}
.flag.flag-gm {background-position: -48px -55px}
.flag.flag-gn {background-position: -64px -55px}
.flag.flag-gp {background-position: -80px -55px}
.flag.flag-gq {background-position: -96px -55px}
.flag.flag-gr {background-position: -112px -55px}
.flag.flag-gs {background-position: -128px -55px}
.flag.flag-gt {background-position: -144px -55px}
.flag.flag-gu {background-position: -160px -55px}
.flag.flag-gw {background-position: -176px -55px}
.flag.flag-gy {background-position: -192px -55px}
.flag.flag-hk {background-position: -208px -55px}
.flag.flag-hm {background-position: -224px -55px}
.flag.flag-hn {background-position: -240px -55px}
.flag.flag-hr {background-position: 0 -66px}
.flag.flag-ht {background-position: -16px -66px}
.flag.flag-hu {background-position: -32px -66px}
.flag.flag-ic {background-position: -48px -66px}
.flag.flag-id {background-position: -64px -66px}
.flag.flag-ie {background-position: -80px -66px}
.flag.flag-il {background-position: -96px -66px}
.flag.flag-im {background-position: -112px -66px}
.flag.flag-in {background-position: -128px -66px}
.flag.flag-io {background-position: -144px -66px}
.flag.flag-iq {background-position: -160px -66px}
.flag.flag-ir {background-position: -176px -66px}
.flag.flag-is {background-position: -192px -66px}
.flag.flag-it {background-position: -208px -66px}
.flag.flag-je {background-position: -224px -66px}
.flag.flag-jm {background-position: -240px -66px}
.flag.flag-jo {background-position: 0 -77px}
.flag.flag-jp {background-position: -16px -77px}
.flag.flag-ke {background-position: -32px -77px}
.flag.flag-kg {background-position: -48px -77px}
.flag.flag-kh {background-position: -64px -77px}
.flag.flag-ki {background-position: -80px -77px}
.flag.flag-km {background-position: -96px -77px}
.flag.flag-kn {background-position: -112px -77px}
.flag.flag-kp {background-position: -128px -77px}
.flag.flag-kr {background-position: -144px -77px}
.flag.flag-kurdistan {background-position: -160px -77px}
.flag.flag-kw {background-position: -176px -77px}
.flag.flag-ky {background-position: -192px -77px}
.flag.flag-kz {background-position: -208px -77px}
.flag.flag-la {background-position: -224px -77px}
.flag.flag-lb {background-position: -240px -77px}
.flag.flag-lc {background-position: 0 -88px}
.flag.flag-li {background-position: -16px -88px}
.flag.flag-lk {background-position: -32px -88px}
.flag.flag-lr {background-position: -48px -88px}
.flag.flag-ls {background-position: -64px -88px}
.flag.flag-lt {background-position: -80px -88px}
.flag.flag-lu {background-position: -96px -88px}
.flag.flag-lv {background-position: -112px -88px}
.flag.flag-ly {background-position: -128px -88px}
.flag.flag-ma {background-position: -144px -88px}
.flag.flag-mc {background-position: -160px -88px}
.flag.flag-md {background-position: -176px -88px}
.flag.flag-me {background-position: -192px -88px}
.flag.flag-mg {background-position: -208px -88px}
.flag.flag-mh {background-position: -224px -88px}
.flag.flag-mk {background-position: -240px -88px}
.flag.flag-ml {background-position: 0 -99px}
.flag.flag-mm {background-position: -16px -99px}
.flag.flag-mn {background-position: -32px -99px}
.flag.flag-mo {background-position: -48px -99px}
.flag.flag-mp {background-position: -64px -99px}
.flag.flag-mq {background-position: -80px -99px}
.flag.flag-mr {background-position: -96px -99px}
.flag.flag-ms {background-position: -112px -99px}
.flag.flag-mt {background-position: -128px -99px}
.flag.flag-mu {background-position: -144px -99px}
.flag.flag-mv {background-position: -160px -99px}
.flag.flag-mw {background-position: -176px -99px}
.flag.flag-mx {background-position: -192px -99px}
.flag.flag-my {background-position: -208px -99px}
.flag.flag-mz {background-position: -224px -99px}
.flag.flag-na {background-position: -240px -99px}
.flag.flag-nc {background-position: 0 -110px}
.flag.flag-ne {background-position: -16px -110px}
.flag.flag-nf {background-position: -32px -110px}
.flag.flag-ng {background-position: -48px -110px}
.flag.flag-ni {background-position: -64px -110px}
.flag.flag-nl {background-position: -80px -110px}
.flag.flag-no {background-position: -96px -110px}
.flag.flag-np {background-position: -112px -110px}
.flag.flag-nr {background-position: -128px -110px}
.flag.flag-nu {background-position: -144px -110px}
.flag.flag-nz {background-position: -160px -110px}
.flag.flag-om {background-position: -176px -110px}
.flag.flag-pa {background-position: -192px -110px}
.flag.flag-pe {background-position: -208px -110px}
.flag.flag-pf {background-position: -224px -110px}
.flag.flag-pg {background-position: -240px -110px}
.flag.flag-ph {background-position: 0 -121px}
.flag.flag-pk {background-position: -16px -121px}
.flag.flag-pl {background-position: -32px -121px}
.flag.flag-pm {background-position: -48px -121px}
.flag.flag-pn {background-position: -64px -121px}
.flag.flag-pr {background-position: -80px -121px}
.flag.flag-ps {background-position: -96px -121px}
.flag.flag-pt {background-position: -112px -121px}
.flag.flag-pw {background-position: -128px -121px}
.flag.flag-py {background-position: -144px -121px}
.flag.flag-qa {background-position: -160px -121px}
.flag.flag-re {background-position: -176px -121px}
.flag.flag-ro {background-position: -192px -121px}
.flag.flag-rs {background-position: -208px -121px}
.flag.flag-ru {background-position: -224px -121px}
.flag.flag-rw {background-position: -240px -121px}
.flag.flag-sa {background-position: 0 -132px}
.flag.flag-sb {background-position: -16px -132px}
.flag.flag-sc {background-position: -32px -132px}
.flag.flag-scotland {background-position: -48px -132px}
.flag.flag-sd {background-position: -64px -132px}
.flag.flag-se {background-position: -80px -132px}
.flag.flag-sg {background-position: -96px -132px}
.flag.flag-sh {background-position: -112px -132px}
.flag.flag-si {background-position: -128px -132px}
.flag.flag-sk {background-position: -144px -132px}
.flag.flag-sl {background-position: -160px -132px}
.flag.flag-sm {background-position: -176px -132px}
.flag.flag-sn {background-position: -192px -132px}
.flag.flag-so {background-position: -208px -132px}
.flag.flag-somaliland {background-position: -224px -132px}
.flag.flag-sr {background-position: -240px -132px}
.flag.flag-ss {background-position: 0 -143px}
.flag.flag-st {background-position: -16px -143px}
.flag.flag-sv {background-position: -32px -143px}
.flag.flag-sx {background-position: -48px -143px}
.flag.flag-sy {background-position: -64px -143px}
.flag.flag-sz {background-position: -80px -143px}
.flag.flag-tc {background-position: -96px -143px}
.flag.flag-td {background-position: -112px -143px}
.flag.flag-tf {background-position: -128px -143px}
.flag.flag-tg {background-position: -144px -143px}
.flag.flag-th {background-position: -160px -143px}
.flag.flag-tj {background-position: -176px -143px}
.flag.flag-tk {background-position: -192px -143px}
.flag.flag-tl {background-position: -208px -143px}
.flag.flag-tm {background-position: -224px -143px}
.flag.flag-tn {background-position: -240px -143px}
.flag.flag-to {background-position: 0 -154px}
.flag.flag-tr {background-position: -16px -154px}
.flag.flag-tt {background-position: -32px -154px}
.flag.flag-tv {background-position: -48px -154px}
.flag.flag-tw {background-position: -64px -154px}
.flag.flag-tz {background-position: -80px -154px}
.flag.flag-ua {background-position: -96px -154px}
.flag.flag-ug {background-position: -112px -154px}
.flag.flag-um {background-position: -128px -154px}
.flag.flag-us {background-position: -144px -154px}
.flag.flag-uy {background-position: -160px -154px}
.flag.flag-uz {background-position: -176px -154px}
.flag.flag-va {background-position: -192px -154px}
.flag.flag-vc {background-position: -208px -154px}
.flag.flag-ve {background-position: -224px -154px}
.flag.flag-vg {background-position: -240px -154px}
.flag.flag-vi {background-position: 0 -165px}
.flag.flag-vn {background-position: -16px -165px}
.flag.flag-vu {background-position: -32px -165px}
.flag.flag-wales {background-position: -48px -165px}
.flag.flag-wf {background-position: -64px -165px}
.flag.flag-ws {background-position: -80px -165px}
.flag.flag-ye {background-position: -96px -165px}
.flag.flag-yt {background-position: -112px -165px}
.flag.flag-za {background-position: -128px -165px}
.flag.flag-zanzibar {background-position: -144px -165px}
.flag.flag-zm {background-position: -160px -165px}
.flag.flag-zw {background-position: -176px -165px}
</style>