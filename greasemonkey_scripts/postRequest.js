// ==UserScript==
// @name     postRequest
// @version  1
// @grant    none
// @include http://localhost:8888/
// ==/UserScript==

// Time 
var timeNOW;

// Values
var iter;
var maxIter = 150;
var csv;

var complexity = "complex";
var type = "spark";
var dbSize = "one";

// Elements
var iterBox;
var querySelect;
var sparkSelect;
var mysqlSelect;
var queryBtn;

window.onload = function() {
  addListeners();
  timeNOW = Date.now();
  initialize();
	loadVars();
  dataEntry();
}

function initialize() {
  addListeners();
  querySelect = document.getElementById("queryChoice");
	iterBox = document.getElementById("iteration");
  sparkSelect = document.getElementById("spark");
	mysqlSelect = document.getElementById("mysql");
  queryBtn = document.getElementById("submit");
}

function loadVars() {
  iter = localStorage.getItem("iter");
  if(iter == null || iter == undefined) {
    iter = 0;
    console.log("Set iter to 0");
  }
  csv = localStorage.getItem("csv");
  if(csv == null || csv == undefined) {
   	csv = "";
    console.log("Set csv to none");
  }
}

function saveVars() {
  localStorage.setItem("iter", iter);
  localStorage.setItem("csv", csv);
  localStorage.setItem("timeNOW", timeNOW);
}

function dataEntry() {
	querySelect.value = complexity;
	iterBox.value = iter;
  
  if(type == "mysql") {
    mysqlSelect.checked = true;
  }
  if(type == "spark") {
    sparkSelect.checked = true;
  }
  
  document.getElementById(dbSize).checked = true;
  
  console.log(timeNOW);
  if(iter <= maxIter) {
    console.log("Iterations: " + iter);
    iter++;
    saveVars();
		post();
    
  } else {
    console.log("Completed!" + maxIter + " iterations!");
  }
  
}

function post() {
  queryBtn.click();
}

function addListeners() {
	document.addEventListener('keydown', event => {
        const key = event.key.toLowerCase();
        if(key == "d") {
         download();
        }
    		if(key == "r") {
         reset(); 
        }
    });
}

// Menu functions
function download() {
  console.log("Downloading!");
  var an = window.document.createElement('a');
  an.setAttribute('href', 'data:text/csv; charset=utf-8,' + encodeURIComponent(csv));
  an.setAttribute('download', 'data.csv');
  window.document.body.appendChild(an);
	an.click();
}

function reset() {
	console.log("Resetting!"); 
  localStorage.clear();
  location.reload();
}