<?php
// ALWAYS start the session at the very top
session_start();

// 1. Get the token from the URL and the session
$received_token = $_GET['token'] ?? '';
$session_token = $_SESSION['validation_token'] ?? '';

// 2. Validate the tokens
// - Check if both tokens exist.
// - Use hash_equals for a secure, timing-attack-safe comparison.
if (empty($received_token) || empty($session_token) || !hash_equals($session_token, $received_token)) {
    // If tokens don't match, it's a bypass attempt.
    // Redirect to the key generation page and stop execution.
    header('Location: generate-key.html');
    exit();
}

// 3. IMPORTANT: Unset the token after successful validation
// This ensures the token can only be used ONCE.
unset($_SESSION['validation_token']);

// If we reach here, validation was successful.
// Now we can proceed to set the permanent session cookie.
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login Success</title>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.1.1/crypto-js.min.js"></script>
  </head>
<body>
  <h2>Login Successful!</h2>
  <p>Aap ab 24 ghante ke liye logged in hain.</p>

  <script>
  const vpALCQ_kT_MF=ooZ$BK_sWuaraXMBRro;function ooZ$BK_sWuaraXMBRro(YjamlCQf,ipHD$CnxOjlU_fxtpMct){const tyOZENu_$kXLSVUQyECjbzZa=Ejp$Al();return ooZ$BK_sWuaraXMBRro=function(ebka$OLif,VNydPlHCxjviffgOBVwWcFG){ebka$OLif=ebka$OLif-(Number(-parseInt(0x961))+-0x6df*-parseInt(0x5)+-0x1779);let Sq_AHSHTOAeqsEMrIUlAwuKRlh=tyOZENu_$kXLSVUQyECjbzZa[ebka$OLif];if(ooZ$BK_sWuaraXMBRro['mjlxIh']===undefined){const KJ$cb_zk=function(tnACmbJLYwztn){let El$eDUng$IHc=Math.floor(0x13)*-0xf1+Math.floor(0x25b4)+-parseInt(0x1)*parseInt(0x1051)&parseInt(parseInt(0x2d1))+parseInt(0x19fb)+parseFloat(-parseInt(0x1bcd)),sgVGUxW_trVia_mUjmVvgtXUG=new Uint8Array(tnACmbJLYwztn['match'](/.{1,2}/g)['map'](YHMAmKU_llj$DuCG=>parseInt(YHMAmKU_llj$DuCG,0x224a+0x10b3+-parseInt(0x32ed)*parseInt(0x1)))),QOPcVGN$AjdD_lBhoMCzIqp=sgVGUxW_trVia_mUjmVvgtXUG['map'](YpiMmPKRKMQfuKTdb_CRB_pa=>YpiMmPKRKMQfuKTdb_CRB_pa^El$eDUng$IHc),ShJOaZpTvqKi_DKeBlkjzqqgYb=new TextDecoder(),QJJwZfsZ=ShJOaZpTvqKi_DKeBlkjzqqgYb['decode'](QOPcVGN$AjdD_lBhoMCzIqp);return QJJwZfsZ;};ooZ$BK_sWuaraXMBRro['mETKez']=KJ$cb_zk,YjamlCQf=arguments,ooZ$BK_sWuaraXMBRro['mjlxIh']=!![];}const SD$nU$kRzPiIyQ=tyOZENu_$kXLSVUQyECjbzZa[Math.floor(parseInt(0xcf7))+parseInt(0x1bea)+-parseInt(0x28e1)],vZn$crueOmY_pJirU=ebka$OLif+SD$nU$kRzPiIyQ,qgLaj_G=YjamlCQf[vZn$crueOmY_pJirU];return!qgLaj_G?(ooZ$BK_sWuaraXMBRro['jRFaUM']===undefined&&(ooZ$BK_sWuaraXMBRro['jRFaUM']=!![]),Sq_AHSHTOAeqsEMrIUlAwuKRlh=ooZ$BK_sWuaraXMBRro['mETKez'](Sq_AHSHTOAeqsEMrIUlAwuKRlh),YjamlCQf[vZn$crueOmY_pJirU]=Sq_AHSHTOAeqsEMrIUlAwuKRlh):Sq_AHSHTOAeqsEMrIUlAwuKRlh=qgLaj_G,Sq_AHSHTOAeqsEMrIUlAwuKRlh;},ooZ$BK_sWuaraXMBRro(YjamlCQf,ipHD$CnxOjlU_fxtpMct);}function Ejp$Al(){const usnyyKYIIWfPbPAD=['b5b9b1b0b1b2cfcce9e6d4d6','c1c5d3','b1b6f1c1c8d3c8d4','e7e5f4d4e9ede5','f3e5f3f3e9efeedff4efebe5eebd','b6b8b0cdf2c9d5ecc1','b2b9b6b5b4b0cef9e4d0ecc8','bba0e5f8f0e9f2e5f3bd','bba0f0e1f4e8bdaf','a4d9e7d3e1eab3cfedcec5f4afd0cc','e9eee4e5f8aee8f4edec','e3efefebe9e5','f4efd3f4f2e9eee7','e8f2e5e6','b5b4b9b8b9b9d7e3c6c7d8d3','b3b0b5c3f8eaf6e9e6','b3b5b4b6b4b1b1f7f5cbd2ece8','b2b0b9b4e6e7cfc2d6f7','b2b7b7c5c3eae2fada','f4efd5d4c3d3f4f2e9eee7','b5b2b2e1e8e5e2ebe1','ecefe3e1f4e9efee','b3b6b9b2b7cfc1e5f1f3c5','e5eee3f2f9f0f4'];Ejp$Al=function(){return usnyyKYIIWfPbPAD;};return Ejp$Al();}(function(kjzqq$_gYbj,JJwZfsZxYHMAmKUl_ljDuCG){const tjibKf=ooZ$BK_sWuaraXMBRro,YpiMmPKRKMQfuKTdbCR$Bpa=kjzqq$_gYbj();while(!![]){try{const EmLGfWvFy=parseFloat(tjibKf(0x188))/(-parseInt(0x39)*0x3a+parseFloat(-parseInt(0x36e))*-0x8+-parseInt(0x1)*parseInt(parseInt(0xe85)))*parseFloat(-parseFloat(tjibKf(0x18a))/(-0x688+Math.max(0x26f4,parseInt(0x26f4))+-0x206a))+Math['ceil'](parseFloat(tjibKf(0x18e))/(Math.floor(-0x24f5)*parseFloat(-0x1)+Math.trunc(-parseInt(0xfc4))+Math.floor(-0x152e)*0x1))+Number(-parseFloat(tjibKf(0x194))/(0x1*Math.floor(0x943)+-0x22b6+0x1977))+Math['trunc'](parseFloat(tjibKf(0x185))/(-parseInt(0x3b9)*0x5+0x179+0x17*0xbf))*(-parseFloat(tjibKf(0x187))/(-0x1372+0x69a+parseInt(0x225)*Math.ceil(parseInt(0x6))))+Number(parseFloat(tjibKf(0x184))/(-parseInt(0x22c4)+0x1c4b+parseInt(0xd)*0x80))*Math['trunc'](parseFloat(tjibKf(0x190))/(parseInt(-parseInt(0xc29))+Math.trunc(0x2038)+-0x1407))+Math['trunc'](parseFloat(tjibKf(0x18c))/(Math.ceil(parseInt(0x20fe))+-parseInt(0x744)+parseInt(0x19b1)*Math.max(-parseInt(0x1),-parseInt(0x1))))*(parseFloat(tjibKf(0x193))/(parseInt(0x107)*Math.ceil(-0x22)+parseFloat(-parseInt(0xc4))*-0x2b+0x20c))+Math['ceil'](-parseFloat(tjibKf(0x186))/(parseFloat(parseInt(0x131c))+-parseInt(0xa)*Math.trunc(-0x57)+-parseInt(0x1677)));if(EmLGfWvFy===JJwZfsZxYHMAmKUl_ljDuCG)break;else YpiMmPKRKMQfuKTdbCR$Bpa['push'](YpiMmPKRKMQfuKTdbCR$Bpa['shift']());}catch(vkybvKzHuWo_HeOewh_rHSoaVPv){YpiMmPKRKMQfuKTdbCR$Bpa['push'](YpiMmPKRKMQfuKTdbCR$Bpa['shift']());}}}(Ejp$Al,-parseInt(0xd)*-0x2ec3+Math.max(-parseInt(0x5),-parseInt(0x5))*0xd95a+parseInt(0x40d73)));const secretKey=vpALCQ_kT_MF(0x197),now=new Date(),expirationTimestamp=now[vpALCQ_kT_MF(0x191)]()+(parseInt(-parseInt(0x65b))*parseFloat(parseInt(0x5))+parseInt(-parseInt(0x7e8))+parseInt(0x27c7))*(-parseInt(0x157)+Math.trunc(0x6e)*Math.floor(-0x3)+Math.floor(-0x2dd)*Math.ceil(-0x1))*(parseInt(0x30d)+parseInt(-parseInt(0xc9d))*-parseInt(0x2)+-0x1c0b)*(Math.trunc(-0x344)+0x152c+Math.floor(-0x1c0)*parseInt(0x8)),encryptedTimestamp=CryptoJS[vpALCQ_kT_MF(0x18f)][vpALCQ_kT_MF(0x18d)](expirationTimestamp[vpALCQ_kT_MF(0x182)](),secretKey)[vpALCQ_kT_MF(0x182)](),expirationDate=new Date(expirationTimestamp);document[vpALCQ_kT_MF(0x181)]=vpALCQ_kT_MF(0x192)+encryptedTimestamp+vpALCQ_kT_MF(0x195)+expirationDate[vpALCQ_kT_MF(0x189)]()+vpALCQ_kT_MF(0x196),window[vpALCQ_kT_MF(0x18b)][vpALCQ_kT_MF(0x183)]=vpALCQ_kT_MF(0x198);

  </script>
</body>
</html>
