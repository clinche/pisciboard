<?php
include_once('config.php');
session_start();

view_data(api_req("/v2/campus/31/users"));
