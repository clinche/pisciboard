<?php
session_start();

function view_data($data)
{
	echo "<pre>";
	echo json_encode($data, JSON_PRETTY_PRINT);
	echo "</pre>";
}

function get_user_info($array, $id)
{
	foreach ($array as $user)
	{
		if ($user[9] == $id)
			return ($user);
	}
}

function set_user_info(&$array, $id, $userinfo)
{
	foreach ($array as &$user)
	{
		if ($user[9] == $id)
		{
			$user = $userinfo;
			return ;
		}
	}
}

function get_exam_id()
{
	$examid = 0;
	$apicall = api_req("/v2/me");
	if ($apicall->status)
	{
		header('Content-Type:application/json');
		header('Access-Control-Allow-Origin:*');
		echo(json_encode($userinfo));
		exit();
	}
	foreach ($apicall->projects_users as $project)
	{
		if ($project->project->slug == $_SESSION['exam'])
			$examid = $project->project->id;
	}
	return ($examid);
}

?>
