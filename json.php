<?php
session_start();
include_once('config.php');
set_time_limit(0);

if (isset($_GET['json']) 
	&& isset($_GET['year']) 
	&& isset($_GET['month']) 
	&& isset($_GET['exam']))
{
	if (!isset($_SESSION['year']))
	{
		$_SESSION['year'] = $_GET['year'];
		$_SESSION['month'] = $_GET['month'];
		$_SESSION['exam'] = $_GET['exam'];
	}
	else if ($_SESSION['year'] != $_GET['year'] 
		|| $_SESSION['month'] != $_GET['month'] 
		|| $_SESSION['exam'] != $_GET['exam'])
	{
		$_SESSION['year'] = $_GET['year'];
		$_SESSION['month'] = $_GET['month'];
		$_SESSION['exam'] = $_GET['exam'];
		$_SESSION['examusers'] = null;
		$_SESSION['usersjson'] = null;
		$_SESSION['jsonrefresh'] = 0;
	}
	if (!isset($_SESSION['examusers']))
	{
		$examid = get_exam_id();
		if (!$examid)
			output_json("Error: Exam not found");
		get_users($examid);
		$userinfo = first_update();
		$_SESSION['usersjson'] = $userinfo;
	}
	else
	{
		$userinfo = update_project();
		$_SESSION['usersjson'] = $userinfo;
	}
	$_SESSION['jsonrefresh'] += 1;
	output_json($_SESSION['usersjson']);
	//view_data($apicall);
}

function get_users($exam)
{
	$page = 1;
	$uri = "/v2/projects/{$exam}/users?filter[primary_campus_id]=31&filter[pool_month]={$_SESSION['month']}&filter[pool_year]={$_SESSION['year']}&per_page=100&page={$page}";
	$apicall = api_req($uri);
	$mustrecount = 0;
	if (!count($apicall))
		return (0);
	if (count($apicall) == 100)
		$mustrecount = 1;
	$examusers = [];
	foreach ($apicall as $item)
	{
		array_push($examusers, $item->id);
	}
	while ($mustrecount)
	{
		$page = $page + 1;
		$apicall = api_req("/v2/projects/{$exam}/users/?filter[primary_campus_id]=31&filter[pool_month]={$_SESSION['month']}&filter[pool_year]={$_SESSION['year']}&per_page=100&page={$page}");
		if (!count($apicall))
			break ;
		if (count($apicall) < 100)
			$mustrecount = 0;

	}
	$_SESSION['jsonrefresh'] = 0;
	$examusers = array_unique($examusers);
	$_SESSION['examusers'] = $examusers;
}

function first_update()
{
	$userinfo = [];
	$lastc = 0;
	$userid = 0;
	$validated = "validated?";
	foreach ($_SESSION['examusers'] as $user)
	{
		$lastc = 0;
		$apicall = api_req("/v2/users/".$user);
		$userid = $apicall->id;
		foreach ($apicall->projects_users as $project)
		{
			if ($project->project->slug == "c-piscine-c-01" && $project->marked != false && $project->$validated == true && $lastc < 1)
				$lastc = 1;
			if ($project->project->slug == "c-piscine-c-02" && $project->marked != false && $project->$validated == true && $lastc < 2)
				$lastc = 2;
			if ($project->project->slug == "c-piscine-c-03" && $project->marked != false && $project->$validated == true && $lastc < 3)
				$lastc = 3;
			if ($project->project->slug == "c-piscine-c-04" && $project->marked != false && $project->$validated == true && $lastc < 4)
				$lastc = 4;
			if ($project->project->slug == "c-piscine-c-05" && $project->marked != false && $project->$validated == true && $lastc < 5)
				$lastc = 5;
			if ($project->project->slug == "c-piscine-c-06" && $project->marked != false && $project->$validated == true && $lastc < 6)
				$lastc = 6;
			if ($project->project->slug == "c-piscine-c-07" && $project->marked != false && $project->$validated == true && $lastc < 7)
				$lastc = 7;
			if ($project->project->slug == "c-piscine-c-08" && $project->marked != false && $project->$validated == true && $lastc < 8)
				$lastc = 8;
			if ($project->project->slug == "c-piscine-c-09" && $project->marked != false && $project->$validated == true && $lastc < 9)
				$lastc = 9;
			if ($project->project->slug == "c-piscine-c-10" && $project->marked != false && $project->$validated == true && $lastc < 10)
				$lastc = 10;
			if ($project->project->slug == "c-piscine-c-11" && $project->marked != false && $project->$validated == true && $lastc < 11)
				$lastc = 11;
			if ($project->project->slug == "c-piscine-c-12" && $project->marked != false && $project->$validated == true && $lastc < 12)
				$lastc = 12;
			if ($project->project->slug == "c-piscine-c-13" && $project->marked != false && $project->$validated == true && $lastc < 13)
				$lastc = 13;
			if ($project->project->slug == "c-piscine-exam-00")
			{
				$exam00 = $project->final_mark;
				if ($exam00 == null)
					$exam00 = 0;
			}
			if ($project->project->slug == "c-piscine-exam-01")
			{
				$exam01 = $project->final_mark;
				if ($exam01 == null)
					$exam01 = 0;
			}
			if ($project->project->slug == "c-piscine-exam-02")
			{
				$exam02 = $project->final_mark;
				if ($exam02 == null)
					$exam02 = 0;
			}
			if ($project->project->slug == "c-piscine-final-exam")
			{
				$examfinal = $project->final_mark;
				if ($examfinal == null)
					$examfinal = 0;
			}
			if ($project->project->slug == $_SESSION['exam'])
			{
				$grade = $project->final_mark;
			}
		}
		if ($grade == null)
			$grade = 0;
		$oldresults = array($lastc, $exam00, $exam01, $exam02);
		$cote = determine_cote($oldresults);
		$item = array($grade, $apicall->login, $apicall->image->link, $_SESSION['jsonrefresh'], 100, 1, 9, $cote, $oldresults, $userid);
		array_push($userinfo, $item);
		usleep(400000);
	}
	sort($userinfo);
	$userinfo = array_reverse($userinfo);
	return ($userinfo);
}

function update_project()
{
	$jsonarray = $_SESSION['usersjson'];
	$finalarray = [];
	$i = 0;
	$url = "/v2/projects_users?filter[project_id]=".get_exam_id()."&per_page=100&filter[user_id]=";
	foreach ($jsonarray as $user)
	{
		$url .= $user[9].",";
	}
	$apicall = api_req($url);
	foreach ($apicall as $user)
	{
		$state = $user->status;
		$grade = $user->final_mark;
		$markedat = $user->marked_at;
		$refresh = 1;
		$finishedat = date("H:i:s", strtotime($markedat) + 7200);
		if ($grade == null)
			$grade = 0;
		if ($state == "finished")
			$refresh = 0;
		$userinfo = get_user_info($user->user->id);
		$item = array($grade, $userinfo[1], $userinfo[2], $_SESSION['jsonrefresh'], $userinfo[4], $refresh, $finishedat, $userinfo[7], $userinfo[8], $userinfo[9]);
		array_push($finalarray, $item);
		$i++;
	}
	usort($finalarray, 'customsort');
	return ($finalarray);
}

function get_user_info($id)
{
	foreach ($_SESSION['usersjson'] as $user)
	{
		if ($user[9] == $id)
			return ($user);
	}
}

function get_exam_id()
{
	$examid = 0;
	$apicall = api_req("/v2/me");
	foreach ($apicall->projects_users as $project)
	{
		if ($project->project->slug == $_SESSION['exam'])
			$examid = $project->project->id;
	}
	return ($examid);
}

function determine_cote($oldresults)
{
	$x = 5;
	$oldresults[0] > 8 ? $x -= 1 : $x = $x;
	$oldresults[1] > 32 ? $x -= 1 : $x = $x;
	$oldresults[2] > 32 ? $x -= 1 : $x = $x;
	$oldresults[3] > 32 ? $x -= 1 : $x = $x;
	return ($x . "/1");
}

function customsort($user1, $user2)
{
	if ($user1[0] > $user2[0])
		return (-1);
	if ($user1[0] < $user2[0])
		return (1);
	if ($user1[0] == $user2[0])
	{
		if ($user1[6] > $user2[6])
			return (1);
		if ($user1[6] < $user2[6])
			return (-1);
		if ($user1[6] == $user2[6])
			return (strcmp($user1[1], $user2[1]));
		return (0);
	}
}

function output_json($userinfo)
{
	header('Content-Type:application/json');
	header('Access-Control-Allow-Origin:*');
	echo(json_encode($userinfo));
	exit();
}
?>
