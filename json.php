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
		//$userinfo = update_weighted_users();
		$userinfo = update_project();
		$_SESSION['usersjson'] = $userinfo;
	}
	$_SESSION['jsonrefresh'] += 1;
	output_json($_SESSION['usersjson']);
	//view_data($apicall);
}

function update_project()
{
	$jsonarray = $_SESSION['usersjson'];
	$finalarray = [];
	$url = "/v2/projects_users?filter[project_id]=".get_exam_id()."&per_page=100&filter[user_id]=";
	foreach ($jsonarray as $user)
	{
		$url .= $user['id'].",";
	}
	$url = substr($url, 0, -1);
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
		{
			$refresh = 0;
			$item = array($grade, $apicall->login, $apicall->image->link, $_SESSION['jsonrefresh'], $user[4], $refresh, $finishedat, $user[7], $user[8]);
		}
		else
			$item = array($grade, $apicall->login, $apicall->image->link, $_SESSION['jsonrefresh'], $user[4], $refresh, $finishedat, $user[7], $user[8]);
		array_push($finalarray, $item);
	}
	return ($finalarray);
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

function first_update()
{
	$userinfo = [];
	$lastc = 0;
	$validated = "validated?";
	foreach ($_SESSION['examusers'] as $user)
	{
		$lastc = 0;
		$apicall = api_req("/v2/users/".$user);
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
		$item = array($grade, $apicall->login, $apicall->image->link, $_SESSION['jsonrefresh'], 100, 1, 9, $cote, $oldresults);
		array_push($userinfo, $item);
		usleep(400000);
	}
	sort($userinfo);
	$userinfo = array_reverse($userinfo);
	return ($userinfo);
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

function update_weighted_users()
{
	$jsonarray = $_SESSION['usersjson'];
	$maxgrade = extract_maxgrade();
	$mingrade = extract_mingrade();
	$reqlimit = count($jsonarray) / 2;
	$reqnb = 0;
	$lastrefresh = $_SESSION['jsonrefresh'];
	error_log("json.php:115 calculating weight for every user");
	foreach ($jsonarray as &$jsonuser)
	{
		$lastuserrefresh = $jsonuser[3];
		if ($maxgrade == $mingrade)
			$weight = 100;
		else
		{
			$step1 = (($jsonuser[0] - $mingrade) * 100) / ($maxgrade - $mingrade);
			$weight = 100 + ($step1 / ($lastrefresh - $lastuserrefresh));
		}
		$jsonuser[4] = $weight;
	}
	$maxweight = extract_maxweight($jsonarray);
	shuffle($jsonarray);
	error_log("json.php:131 doing users update. should update 40 times.");
	foreach ($jsonarray as &$user)
	{
		if ($user[3] < $_SESSION['jsonrefresh'] - 4 && $user[5] != 0)
		{
			$user = update_user($user);
			$reqnb++;
		}
		if ($user[4] == $maxweight && $user[3] != $_SESSION['jsonrefresh'] - 1 && $user[5] != 0)
		{
			$user = update_user($user);
			$reqnb++;
		}
		if ($user[4] < $maxweight - 10 && $user[3] != $_SESSION['jsonrefresh'] - 1 && $user[5] != 0)
		{
			$user = update_user($user);
			$reqnb++;
		}
		if ($reqnb == $reqlimit)
			break ;
	}
	error_log("json.php:151 update done. total requests: {$reqnb}");
	error_log("json.php:152 sorting the jsonarray");
	usort($jsonarray, 'customsort');
	error_log("json.php:155 sort done. \n\t\t\t\t\tfirst user grade: {$jsonarray[0][0]}\n\t\t\t\t\tlast user grade {$jsonarray[count($jsonarray) - 1][0]}");
	error_log("json.php:155 outputing json array\n\n");
	return ($jsonarray);
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

function extract_maxgrade()
{
	$fstuser = $_SESSION['usersjson'][0];
	$maxgrade = $fstuser[0];
	return ($maxgrade);
}

function extract_mingrade()
{
	$nbuser = count($_SESSION['usersjson']);
	$lstuser = $_SESSION['usersjson'][$nbuser - 1];
	$mingrade = $lstuser[0];
	return ($mingrade);
}

function update_user($user)
{
	$finishedat = 0;
	$slug = 0;
	$state = 0;
	$grade = 0;
	$markedat = 0;
	$refresh = 0;

	$apicall = api_req("/v2/users/".$user[1]);
	foreach ($apicall->projects_users as $project)
	{
		if ($project->project->slug == $_SESSION['exam'])
		{
			$slug = $project->project->slug;
			$state = $project->status;
			$grade = $project->final_mark;
			$markedat = $project->marked_at;
			$refresh = 1;
			break;
		}
	}
	$finishedat = date("H:i:s", strtotime($markedat) + 7200);
	if ($grade == null)
		$grade = 0;
	if ($state == "finished")
	{
		$refresh = 0;
		$item = array($grade, $apicall->login, $apicall->image->link, $_SESSION['jsonrefresh'], $user[4], $refresh, $finishedat, $user[7], $user[8]);
	}
	else
		$item = array($grade, $apicall->login, $apicall->image->link, $_SESSION['jsonrefresh'], $user[4], $refresh, $finishedat, $user[7], $user[8]);
	usleep(400000);
	return ($item);
}

function extract_maxweight($jsonarray)
{
	$weight = 0;
	foreach ($jsonarray as $user)
	{
		if ($user[4] > $weight && $user[3] != $_SESSION['jsonrefresh'])
			$weight = $user[4];
	}
	return ($weight);
}

function count_maxweight($jsonarray, $weight)
{
	$count = 0;
	foreach ($jsonarray as $user)
	{
		if ($user[4] == $weight && $user[3] != $_SESSION['jsonrefresh'])
			$count++;
	}
	return ($count);
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

function output_json($userinfo)
{
	header('Content-Type:application/json');
	header('Access-Control-Allow-Origin:*');
	header('X-Next-Request-In: 60');
	echo(json_encode($userinfo));
	exit();
}
?>
