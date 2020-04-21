// ==UserScript==
// @name     writeTime
// @version  1
// @grant    none
// @include http://localhost:8888/query
// ==/UserScript==

var timeTOTAL;
var timeNOW;
var data;
var csv;

window.onload = function() {
    saveTime();
    redir();
}

function saveTime() {
  csv = localStorage.getItem("csv");
  timeNOW = localStorage.getItem("timeNOW");
  timeTOTAL = Date.now() - timeNOW;
  console.log("Time elapsed: " + timeTOTAL);
  csv+= timeTOTAL+",";
  localStorage.setItem("csv", csv);
}

function redir() {
	window.location.href = "http://localhost:8888/";
}