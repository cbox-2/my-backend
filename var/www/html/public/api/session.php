<?php
require 'db.php';
if (isset($_SESSION['user_id'])) {
    echo json_encode(['success'=>true,'logged_in'=>true,'user'=>['id'=>$_SESSION['user_id'],'username'=>$_SESSION['username'],'role'=>$_SESSION['role']??'user']]);
} else { echo json_encode(['success'=>true,'logged_in'=>false]); }