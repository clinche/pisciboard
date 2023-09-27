<?php
session_start();
include_once('config.php');
include_once('api42.php');
set_time_limit(0);

if (isset($_GET['json']) 
	&& isset($_GET['year']) 
	&& isset($_GET['month']) 
	&& isset($_GET['exam'])
	&& isset($_GET['campus']))
{
	if (!isset($_SESSION['year']))
	{
		$_SESSION['year'] = $_GET['year'];
		$_SESSION['month'] = $_GET['month'];
		$_SESSION['exam'] = $_GET['exam'];
		$_SESSION['campus'] = $_GET['campus'];
	}
	else if ($_SESSION['year'] != $_GET['year'] 
		|| $_SESSION['month'] != $_GET['month'] 
		|| $_SESSION['campus'] != $_GET['campus']
		|| $_SESSION['exam'] != $_GET['exam'])
	{
		$_SESSION['year'] = $_GET['year'];
		$_SESSION['month'] = $_GET['month'];
		$_SESSION['exam'] = $_GET['exam'];
		$_SESSION['campus'] = $_GET['campus'];
		$_SESSION['examusers'] = null;
		$_SESSION['usersjson'] = null;
		$_SESSION['jsonrefresh'] = 0;
	}
	if (!isset($_SESSION['examusers']))
	{
		$examid = get_exam_id();
		if (!$examid)
			output_error("Exam not found. Try cloning repo and adjusting exam variables in config.php");
		$_SESSION['jsonrefresh'] = 0;
		$_SESSION['examusers'] = get_users($examid);
		if (!$_SESSION['examusers'])
			output_error("No users found. Please check your campus and month/year");
		$_SESSION['usersjson'] = null;
		output_json($_SESSION['examusers']);
	}
	else if (!isset($_SESSION['usersjson']))
	{
		$userinfo = update_userinfos();
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
    $examusers = [];

    do {
        $uri = "/v2/projects/{$exam}/users?filter[primary_campus_id]={$_SESSION['campus']}&filter[pool_month]={$_SESSION['month']}&filter[pool_year]={$_SESSION['year']}&per_page=100&page={$page}";
        $apicall = api_req($uri);

        foreach ($apicall as $item) {
            $row = [0, $item->login, $item->image->link, $_SESSION['jsonrefresh'], 100, 1, 9, "", [], $item->id];
            logger("Adding user {$item->login} to the list");
            $examusers[] = $row;
        }

        $page++;
    } while (count($apicall) >= 100);

    return count($examusers) > 0 ? $examusers : null;
}


function populate_slugs($base, $max, $min = 0)
{
	$i = $min;
	$slug = $base;
	$slugs = [];
	while ($i <= $max)
	{
		if ($i < 9)
			$slug = $base."0".($i);
		else
			$slug = $base.($i);
		array_push($slugs, $slug);
		$i++;
	}
	return ($slugs);
}

function get_slugsid($slugs)
{
	$apicall = api_req("/v2/cursus/9/projects/");
	$result = [];
	foreach ($apicall as $project)
	{
		foreach ($slugs as $slug)
		{	
			if ($slug == $project->slug)
			{
				array_push($result, $project->id);
				break;
			}
		}
	}
	return ($result);
}

function update_userinfos()
{
	$cslugs = populate_slugs("c-piscine-c-", 13, 5);
	$examslugs = populate_slugs("c-piscine-exam-", 2);
	$projectids = get_slugsid(array_merge($cslugs, $examslugs));
	//$user = ['grade' => 0, 'login' => 0, 'image' => 0, 'jsonrefresh' => 0, 'weight' => 0, 'refreshed' => 0, 'finishedat' => 0, 'cote' => 0, 'oldresults' => 0, 'id' => 0];
	$exam = ['00' => 0, '01' => 0, '02' => 0];
	$oldresults = [];
	$validated = "validated?";
	$examarray = &$_SESSION['examusers'];
	$finalarray = [];
	//TODO: wait for 42's API to be fixed (ticket created on 2023-08-02)
	//      when fixed, remove the foreach and concatenate projects ids
	//      to the url like this : `?filter[project_id]=1,2,3...&filter[user_id]=1,2,3...`
	foreach ($projectids as $project)
	{
		$tmparray = $examarray;
		while (count($tmparray))
		{
			$url = "/v2/projects_users?filter[project_id]={$project}&per_page=100&filter[user_id]=";
			for ($i = 1; $i <= 100 && count($tmparray); $i++)
				$url .= array_pop($tmparray)[9] . ",";
			$url = substr($url, 0, -1);

			$apicall = api_req($url);
			foreach ($apicall as $result)
			{
				$exam = ['00' => 0, '01' => 0, '02' => 0];
				$userid = $result->user->id;
				$userinfo = get_user_info($_SESSION['examusers'], $userid);
				$oldresults = $userinfo[8];
				$lastc = $oldresults[0];
				$grade = $userinfo[0];
				$projectslug = $result->project->slug;
				
				foreach ($cslugs as $slug)
					if ($projectslug == $slug && $result->$validated == true && $result->marked != false && $lastc < intval(substr($slug, -2), 10))
						$lastc = intval(substr($slug, -2), 10);
				
				foreach ($examslugs as $slug)
					if ($projectslug == $slug)
						$exam[substr($slug, -2)] = $result->final_mark;
				
				if ($projectslug == $_SESSION['exam'])
					$grade = $result->final_mark;
				
				$lastc = max($lastc, $oldresults[0]);
				$exam['00'] = max($exam['00'], $oldresults[1]);
				$exam['01'] = max($exam['01'], $oldresults[2]);
				$exam['02'] = max($exam['02'], $oldresults[3]);
				
				$oldresults = array($lastc, $exam['00'], $exam['01'], $exam['02']);
				$cote = determine_cote($oldresults);
				
				$item = array($grade, $result->user->login, $result->user->image->link, $_SESSION['jsonrefresh'], $userinfo[4], $userinfo[5], $userinfo[6], $cote, $oldresults, $userinfo[9]);
				set_user_info($_SESSION['examusers'], $userid, $item);
			}
		}
	}
	sort($examarray);
	$examarray = array_reverse($examarray);
	return ($examarray);
}

function update_project()
{
    $examarray = $_SESSION['examusers'];
    $tmparray = $examarray;
    $finalarray = [];

    while (count($tmparray))
    {
        $url = "/v2/projects_users?filter[project_id]=" . get_exam_id() . "&per_page=100&filter[user_id]=";
        for ($i = 1; $i <= 100 && count($tmparray); $i++)
        {
            $url .= array_pop($tmparray)[9] . ",";
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
                $refresh = 0;

            $userinfo = get_user_info($_SESSION['usersjson'], $user->user->id);
            $item = [
                $grade,
                $userinfo[1],
                $userinfo[2],
                $_SESSION['jsonrefresh'],
                $userinfo[4],
                $refresh,
                $finishedat,
                $userinfo[7],
                $userinfo[8],
                $userinfo[9]
            ];
            $finalarray[] = $item;
        }
    }

    usort($finalarray, 'customsort');
    return $finalarray;
}



function determine_cote($oldresults)
{
	$x = rand(200, 210)/100;
	$oldresults[0] >= 7 ? $x -= (rand(20, 30)/100) : $x = $x;
	$oldresults[1] >= 80 ? $x -= (rand(20, 30)/100) : $x = $x;
	$oldresults[2] >= 75 ? $x -= (rand(25, 35)/100) : $x = $x;
	$oldresults[3] >= 75 ? $x -= (rand(30, 35)/100) : $x = $x;
	return ($x < 1 ? '1.0'.rand(1, 3) : round($x, 2));
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

function output_error($error)
{
	http_response_code(400);
	header('Content-Type:application/json');
	header('Access-Control-Allow-Origin:*');
	echo(json_encode(array('message' => $error)));
	exit();
}

function output_json($userinfo)
{
	header('Content-Type:application/json');
	header('Access-Control-Allow-Origin:*');
	echo(json_encode($userinfo));
	exit();
}
?>
