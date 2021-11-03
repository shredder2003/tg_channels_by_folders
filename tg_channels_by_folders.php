<?php


if (!file_exists('madeline.php')) {
    copy('https://phar.madelineproto.xyz/madeline.php', 'madeline.php');
}
require_once 'madeline.php';

$MadelineProto = new \danog\MadelineProto\API('session.madeline');
$MadelineProto->start();

$me = $MadelineProto->getSelf();

$MadelineProto->logger($me);

$dialogs = $MadelineProto->getFullDialogs();

//print count($dialogs);
//print '<pre>';
//print_r($dialogs);
//print '</pre>';
//exit;
//$Chat = $MadelineProto->getInfo($dialogs[-1001378529858]);
//print_r($Chat);
//print '</pre>';
//exit;

$Vector_of_DialogFilter = $MadelineProto->messages->getDialogFilters();
//print '<pre>';
//print_r($Vector_of_DialogFilter);
//print '</pre>';
//exit;
foreach($Vector_of_DialogFilter as $folder){
    $folders[$folder['id']] = $folder['title'];
    //print "id=".$folder['id']."<br>\r\n";
    //print "title=".$folder['title']."<br>\r\n";
	foreach($folder['include_peers'] as $include_peer){
	    if( $include_peer['_']==='inputPeerChannel' ){
		    $link_channel_to_folder[ $include_peer['channel_id'] ][ $folder['id'] ] = $folder['id'];
	        $link_folder_to_channel[ $folder['id'] ][ $include_peer['channel_id'] ] = $include_peer['channel_id'];
            //print "    channel_id=".$include_peer['channel_id']."<br>\r\n";
		}else if( $include_peer['_']==='inputPeerUser' ){
		    $link_channel_to_folder[ $include_peer['user_id'] ][ $folder['id'] ] = $folder['id'];
	        $link_folder_to_channel[ $folder['id'] ][ $include_peer['user_id'] ] = $include_peer['user_id'];
            //print "    channel_id=".$include_peer['user_id']."<br>\r\n";
		}			
	}	
}	

//print '<pre>';
//print_r($folders);
//print '</pre>';

//print '<pre>';
//print_r($link_folder_to_channel);
//print '</pre>';


//$i=0;
foreach ($dialogs as $peer_id => $peer) {
	//$i++;
	//print "$i $peer_id\r\n";
	//print_r($peer);
	//print '</pre>';exit;
	if( $peer['peer']['_']==='peerChannel' ){
		//yield $MadelineProto->messages->sendMessage(['peer' => $peer, 'message' => 'Hi! Testing MadelineProto broadcasting!']);
		//$Chat = $MadelineProto->getFullInfo($peer_id);
		$Chat = $MadelineProto->getInfo($peer_id);
		//print $Chat['Chat']['title']."\r\n";
		$channels[ $peer['peer']['channel_id'] ]['title'] = $Chat['Chat']['title'];
		//$channels[$peer_id]['username'] = $Chat['Chat']['username'];
		//print_r($Chat);
		//if($i>10){print '</pre>';exit;}
		//if($i>10){break;}
	//}else if( $peer['peer']['_']!='peerUser' ){
	//	print $peer['peer']['_']."\r\n";
	}
}


//print_r($dialogs);
//print '<pre>';
//print_r($channels);
//print '</pre>';

print "total folders: ".count($folders)."<br>\r\n";
print "<table border><tr><th>ID folder</th><th>folder</th><th>channels</th></tr>";
foreach($folders as $folder_id => $folder_title){
	print "<tr>
	<td>".$folder_id."</td><td>".$folder_title."</td><td>";
	if( isset($link_folder_to_channel) && array_key_exists($folder_id,$link_folder_to_channel))
	foreach($link_folder_to_channel[$folder_id] as $channel_id ){
	    if(isset($channels) && array_key_exists($channel_id,$channels)) print "".$channels[$channel_id]['title']."<br>";
	}
	print "</td></tr>";
}
print "</table>";

print "<br><br>";

foreach($channels as $channel_id => $channel){
	if( isset($link_channel_to_folder) && array_key_exists($channel_id,$link_channel_to_folder) ){
	    $channels[$channel_id]['folders_count'] = count($link_channel_to_folder[$channel_id]);
		$folder_id = current($link_channel_to_folder[$channel_id]);
		if(isset($folder_id) && array_key_exists($folder_id,$folders)){
		    $channels[$channel_id]['first_channel'] = $folders[$folder_id];
		}else{
		    $channels[$channel_id]['first_channel'] = '';
		}
	}else{
		$channels[$channel_id]['folders_count'] = 0;
        $channels[$channel_id]['first_channel'] = '';
	}
}

// Define the custom sort function
function custom_sort($a,$b) {
  if($a['folders_count']==$b['folders_count']){
      return $a['first_channel']>$b['first_channel'];
  }else{	  
      return $a['folders_count']>$b['folders_count'];
  }
}
// Sort the multidimensional array
uasort($channels, "custom_sort");
	 
print "total channels: ".count($channels)."<br>\r\n";
print "<table border><tr><th>ID channel</th><th>channel</th><th>groups count</th><th>groups</th></tr>";
foreach($channels as $channel_id => $channel){
	print "<tr>
	<td>".$channel_id."</td><td>".$channel['title']."</td><td>".$channel['folders_count']."</td><td>";
	if( isset($link_channel_to_folder) && array_key_exists($channel_id,$link_channel_to_folder))
	foreach($link_channel_to_folder[$channel_id] as $folder_id ){
	    if( isset($folders) && array_key_exists($folder_id,$folders)) print "".$folders[$folder_id]."<br>";
	}
	print "</td></tr>";
}
print "</table>";



?>