const rankingsBody = document.querySelector("#rankings > tbody");
const month = document.getElementById("month-select");
const year = document.getElementById("year-select");
const exam = document.getElementById("exam-select");
const letsgo = document.getElementById("letsgo");
const stop = document.getElementById("stop");
const notice = document.getElementById("notice");

let interval;

const sleep = (delay) => new Promise((resolve) => setTimeout(resolve, delay));

function populateRankings (data) {
	rankingsBody.innerHTML = "";
	// Populate Leaderboard
	let count = 1;
	data.forEach((row) => {
		const tr = document.createElement("tr");

		// RANK
		const rank = document.createElement("td");
		rank.textContent = count;
		count++;
		tr.appendChild(rank);

		// SCORE
		const score = document.createElement("td");
		if (count < 5)
			score.innerHTML = '<div class="container"> <div class="progressbar-wrapper"><div class="progressbar stripes animated">'+row[0]+'%</div></div></div>';
		else
			score.innerHTML = '<div class="container"> <div class="progressbar-wrapper"><div class="progressbar">'+row[0]+'%</div></div></div>';
		if (row[0] < 30)
			score.querySelector('.progressbar').style="background-color:red;width:"+row[0]+"%";
		else
			score.querySelector('.progressbar').style="width:"+row[0]+"%";
		tr.appendChild(score);

		// PHOTO
		const photo_url = row[2];
		const photo_container = document.createElement("td");
		const photo = document.createElement("img");
		photo.src = photo_url;
		photo_container.appendChild(photo);
		tr.appendChild(photo_container);
		photo.classList.add('image');

		// LOGIN
		const login = document.createElement("td");
		login.textContent = row[1];
		tr.appendChild(login);

		// INFOS
		const infos_data = row[8];
		const infos = document.createElement("td");
		const infos_C = document.createElement("div");
		infos_C.textContent = "Last Day: C"+infos_data[0];
		infos.appendChild(infos_C);
		const infos_exam01 = document.createElement("div");
		infos_exam01.textContent = "Exam 00: "+infos_data[1];
		infos.appendChild(infos_exam01);
		const infos_exam02 = document.createElement("div");
		infos_exam02.textContent ="Exam 01: "+infos_data[2];
		infos.appendChild(infos_exam02);
		const infos_exam03 = document.createElement("div");
		infos_exam03.textContent = "Exam 02: "+infos_data[3];
		infos.appendChild(infos_exam03);
		infos.classList.add('infos');

		tr.appendChild(infos);

		// ODDS
		const odds = document.createElement("td");
		odds.textContent = row[7];
		tr.appendChild(odds);

		// GONE
		const gone_value = row[5];
		const gone_at = row[6];
		const gone_at_child = document.createElement("p");
		const gone = document.createElement("td");
		const gone_icon = document.createElement("div");
		if (gone_value)
			gone_icon.classList.add('icon-orange');
		else
			gone_icon.classList.add('icon-green');
		gone_at_child.textContent = gone_at;
		gone_at_child.style = "padding-top:45px; margin:-20px;";
		gone_icon.appendChild(gone_at_child);
		gone.appendChild(gone_icon);
		tr.appendChild(gone);


		rankingsBody.appendChild(tr);
	});
}

function toggleSelects(state){
	month.disabled = !state;
	year.disabled = !state;
	exam.disabled = !state;
	letsgo.disabled = !state;
	stop.disabled = state;
}

async function letsgooooo(){
	toggleSelects(false);

	notice.innerHTML = "Refreshing...";
	notice.style = "color:orange;";
	
	const response = await makeRequest("GET", "/json.php?json&month="+month.value+"&year="+year.value+"&exam="+exam.value);
	const json = JSON.parse(response.responseText);
	populateRankings(json);

	notice.innerHTML = "Refreshed!";
	notice.style = "color:green;";
	stop.style = "";
}

async function letsnotgooooo(){
	clearInterval(interval);
	toggleSelects(true);
	notice.innerHTML = "Stopped.";
	notice.style = "color:red;"
	stop.style = "display:none;"
}

function makeRequest(method, url) {
    return new Promise(function (resolve, reject) {
        let xhr = new XMLHttpRequest();
        xhr.open(method, url);
        xhr.onload = function () {
            if (this.status >= 200 && this.status < 300) {
                resolve(xhr);
            } else {
                reject({
                    status: this.status,
                    statusText: xhr.statusText
                });
            }
        };
        xhr.onerror = function () {
            reject({
                status: this.status,
                statusText: xhr.statusText
            });
        };
        xhr.send();
    });
}

async function main(){
	while (1)
		await letsgooooo();
}
