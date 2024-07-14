<?php
session_start();
require_once('api42.php');
require_once('utils.php');
set_time_limit(0);

if (validateInput()) {
    handleSessionData();

	if (!isset($_SESSION['examusers'])) {
        $examId = getExamId();
        if (!$examId) {
            return null;
        }
        $_SESSION['examusers'] = fetchUsers($examId);
		if (!$_SESSION['examusers']) {
			outputError("Exam not found. Try cloning repo and adjusting exam variables in config.php");
		}
	
		updateUserInfos($_SESSION['examusers']);
		outputJson($_SESSION['examusers']);
    }
	$examinfos = updateProjectInfo();
	outputJson($examinfos);
}

function validateInput()
{
    return isset($_GET['json'], $_GET['year'], $_GET['month'], $_GET['exam'], $_GET['campus']);
}

function handleSessionData()
{
    if (!isset($_SESSION['year']) || shouldUpdateSessionData()) {
		initializeSessionData();
	}
}

function shouldUpdateSessionData()
{
	return $_SESSION['year'] != $_GET['year'] || $_SESSION['month'] != $_GET['month'] || $_SESSION['campus'] != $_GET['campus'] || $_SESSION['exam'] != $_GET['exam'];
}

function initializeSessionData()
{
    $_SESSION['year'] = $_GET['year'];
    $_SESSION['month'] = $_GET['month'];
    $_SESSION['exam'] = $_GET['exam'];
    $_SESSION['campus'] = $_GET['campus'];
	$_SESSION['examusers'] = null;
    $_SESSION['jsonrefresh'] = 0;
}

function fetchUsers($examId)
{
    $page = 1;
    $examUsers = [];

    do {
        $uri = "/v2/projects/{$examId}/users?filter[primary_campus_id]={$_SESSION['campus']}&filter[pool_month]={$_SESSION['month']}&filter[pool_year]={$_SESSION['year']}&per_page=100&page={$page}";
        $apicall = api_req($uri);

        foreach ($apicall as $item) {
            $examUsers[] = createUserRow($item);
        }

        $page++;
    } while (count($apicall) >= 100);

    return count($examUsers) > 0 ? $examUsers : null;
}

function createUserRow($item)
{
    return [0, $item->login, $item->image->link, $_SESSION['jsonrefresh'], 100, 1, "4:20.690", "", [0, 0, 0, 0], $item->id];
}

function updateUserInfos(&$examUsers)
{
    $cslugs = populateSlugs("c-piscine-c-", 13, 5);
    $examslugs = populateSlugs("c-piscine-exam-", 2);
    $projectIds = getSlugsIds(array_merge($cslugs, $examslugs));
    $userInfo = updateUserInfo($examUsers, $projectIds);
    return $userInfo;
}

function updateProjectInfo()
{
    $examarray = $_SESSION['examusers'];
    $tmparray = $examarray;
    $finalarray = [];

    while (count($tmparray))
    {
        $url = "/v2/projects_users?filter[project_id]=" . getExamId() . "&per_page=100&filter[user_id]=";
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

            $userinfo = getUserInfo($_SESSION['examusers'], $user->user->id);
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

    usort($finalarray, 'sortByGradeThenCompletionTimeThenLogin');
    return $finalarray;
}

function updateUserInfo(&$examUsers, $projectIds)
{
    $validated = "validated?";
	// SH00 to 02 and C00 to C04 are not used to determine odds to speed up the process, at the cost of accuracy for the first exam 
	$cslugs = populateSlugs("c-piscine-c-", 13, 5); // C05 to C13
    $examslugs = populateSlugs("c-piscine-exam-", 2);
    $finalArray = [];

    foreach ($projectIds as $projectId) {
        $tmpArray = $examUsers;
        while (count($tmpArray)) {
            $url = createUrlForProjectUsers($projectId, $tmpArray);
            $apicall = api_req($url);

            foreach ($apicall as $result) {
                $userInfo = getUserInfo($examUsers, $result->user->id);
                $oldResults = $userInfo[8];
                $lastC = $oldResults[0];
                $grade = $userInfo[0];
                $projectSlug = $result->project->slug;

                foreach ($cslugs as $slug) {
                    if ($projectSlug === $slug && $result->$validated && $result->marked && $lastC < intval(substr($slug, -2))) {
                        $lastC = intval(substr($slug, -2));
                    }
                }

                $exam = updateExam($examslugs, $result);
                if ($projectSlug === $_SESSION['exam']) {
                    $grade = $result->final_mark;
                }

                $lastC = max($lastC, $oldResults[0]);
                $exam['00'] = max($exam['00'], $oldResults[1]);
                $exam['01'] = max($exam['01'], $oldResults[2]);
                $exam['02'] = max($exam['02'], $oldResults[3]);

                $oldResults = [$lastC, $exam['00'], $exam['01'], $exam['02']];
                $cote = determineCote($oldResults);

                $item = [
                    $grade,
					$result->user->login,
					$result->user->image->link,
					$_SESSION['jsonrefresh'],
					$userInfo[4],
					$userInfo[5],
					$userInfo[6], 
					$cote,
					$oldResults,
					$userInfo[9]
                ];
                setUserInfo($examUsers, $result->user->id, $item);
            }
        }
    }

    sort($examUsers);
    $examUsers = array_reverse($examUsers);
    return $examUsers;
}

function createUrlForProjectUsers($projectId, &$tmpArray)
{
	//TODO: wait for 42's API to be fixed (ticket created on 2023-08-02, no response as of 2024-07-14)
	//      when fixed, make all calls for all projects at once, instead of one by one
	//      like this : `?filter[project_id]=1,2,3...&filter[user_id]=1,2,3...`
    $url = "/v2/projects_users?filter[project_id]={$projectId}&per_page=100&filter[user_id]=";
    for ($i = 1; $i <= 100 && count($tmpArray); $i++) {
        $url .= array_pop($tmpArray)[9] . ",";
    }
    return substr($url, 0, -1);
}

function updateExam($examslugs, $result)
{
    $exam = ['00' => 0, '01' => 0, '02' => 0];
    foreach ($examslugs as $slug) {
        if ($result->project->slug === $slug) {
            $exam[substr($slug, -2)] = $result->final_mark;
        }
    }
    return $exam;
}

function determineCote($oldResults)
{
    $x = rand(200, 210) / 100;
    $oldResults[0] >= 7 ? $x -= rand(20, 30) / 100 : $x = $x;
    $oldResults[1] >= 80 ? $x -= rand(20, 30) / 100 : $x = $x;
    $oldResults[2] >= 75 ? $x -= rand(25, 35) / 100 : $x = $x;
    $oldResults[3] >= 75 ? $x -= rand(30, 35) / 100 : $x = $x;
    return $x < 1 ? '1.0' . rand(1, 3) : round($x, 2);
}


function sortByGradeThenCompletionTimeThenLogin($user1, $user2)
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

?>