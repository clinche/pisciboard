<?php

function logger($msg)
{
	$fileinfo = 'nofileinfo';
	$backtrace = debug_backtrace();
	if (!empty($backtrace[0]) && is_array($backtrace[0]))
		$fileinfo = $backtrace[0]['file'] . ':' . $backtrace[0]['line'];
	error_log('['.$fileinfo.']' . ' - ' . $msg);
}

function getCampuses()
{
	$data = api_req("/v2/campus?per_page=100");
	$campuses = [];
	foreach ($data as $campus)
	{
		array_push($campuses, ['name' => $campus->name,
							   'id' => $campus->id]);
	}
	sort($campuses);
	return ($campuses);
}

function populateSlugs($base, $max, $min = 0)
{
    $slugs = [];
    for ($i = $min; $i <= $max; $i++) {
        $slug = $i < 9 ? $base . "0" . $i : $base . $i;
        $slugs[] = $slug;
    }
    return $slugs;
}

function getSlugsIds($slugs)
{
    $apicall = api_req("/v2/cursus/9/projects/");
    $result = [];
    foreach ($apicall as $project) {
        foreach ($slugs as $slug) {
            if ($slug === $project->slug) {
                $result[] = $project->id;
                break;
            }
        }
    }
    return $result;
}

function getUserInfo($array, $id)
{
    foreach ($array as $user) {
        if ($user[9] == $id) {
            return $user;
        }
    }
    return null;
}

function setUserInfo(&$array, $id, $userInfo)
{
    foreach ($array as &$user) {
        if ($user[9] == $id) {
            $user = $userInfo;
            return;
        }
    }
}

function getExamId()
{
    $examId = 0;
    $apiCall = api_req("/v2/me");

    if ($apiCall->status) {
        handleApiError($apiCall);
    }

    foreach ($apiCall->projects_users as $project) {
        if ($project->project->slug === $_SESSION['exam']) {
            $examId = $project->project->id;
            break;
        }
    }

    return $examId;
}

function handleApiError($apiCall)
{
    header('Content-Type:application/json');
    header('Access-Control-Allow-Origin:*');
    http_response_code(500); // or use appropriate HTTP status code
    echo json_encode(['error' => 'API request failed', 'details' => $apiCall]);
    exit();
}


function outputError($error)
{
    http_response_code(400);
    header('Content-Type:application/json');
    header('Access-Control-Allow-Origin:*');
    echo(json_encode(['message' => $error]));
    exit();
}

function outputJson($userInfo)
{
    header('Content-Type:application/json');
    header('Access-Control-Allow-Origin:*');
    echo(json_encode($userInfo));
    exit();
}


function viewData($data)
{
    echo "<pre>";
    echo json_encode($data, JSON_PRETTY_PRINT);
    echo "</pre>";
}

?>