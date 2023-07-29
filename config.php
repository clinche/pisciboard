<?php
session_start();

function view_data($data)
{
	echo "<pre>";
	echo json_encode($data, JSON_PRETTY_PRINT);
	echo "</pre>";
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

?>
