<?php
session_start();
ini_set('display_errors', 'on');

if (array_key_exists('metric', $_GET)) {
  $metric = $_GET['metric'];
  $url = "https://api.playlyfe.com/v1/assets/metrics/$metric?player_id=student1";
  if (array_key_exists('item', $_GET)) {
    $item = $_GET['item'];
    $url = "https://api.playlyfe.com/v1/assets/metrics/$metric?player_id=student1&item=$item";
  }

  $ac = $_SESSION['access_token']['access_token'];
  $ch = curl_init();
  curl_setopt($ch,CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer $ac", 'Content-Type: image/png'));
  curl_setopt($ch, CURLOPT_HEADER, 0);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
  $picture = curl_exec($ch);
  curl_close($ch);
  header('Content-type: image/png');
  echo $picture;
}
?>
