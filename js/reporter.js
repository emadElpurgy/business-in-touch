var ActivePageNumber = 0;
var zoomLevel = 100;
function openPage(pageNumber){
	var lastPage = document.getElementsByName("page")[0].options[(document.getElementsByName("page")[0].options.length-2)].value;
	if(pageNumber < 0 || pageNumber > lastPage && pageNumber != "all"){
		return;
	}
	var pages = document.getElementsByClassName("page");
	for(x = 0; x < pages.length; x++){
		if(pageNumber != "all"){
			pages[x].style.display = "none";
		}else{
			pages[x].style.display = "inline-block";
			document.getElementsByName("pageNumber")[0].value="1";
			//document.getElementsByName("page")[0].velue= "0";
			ActivePageNumber = 0;
		}
	}
	if(pageNumber != "all"){
		pages[pageNumber].style.display = "inline-block";
		document.getElementsByName("pageNumber")[0].value=Number(Number(pageNumber) + Number(1));
		document.getElementsByName("page")[0].velue= pageNumber;
		ActivePageNumber = Number(Number(pageNumber) - Number(0));
	}
}
function prepareToPrint(){
	document.getElementById("reporterInterfaceTable").style.display = "none";
	document.body.appendChild(document.getElementById("rep"));
	document.getElementById("rep").style.zoom = 1;
}

function cleanupFromPrint(){
	document.getElementById("reporterInterfaceTable").style.display = "";
	document.getElementById("reportPages").appendChild(document.getElementById("rep"));
	document.getElementById("rep").style.zoom = Number(Number(zoomLevel) / Number(100)).toFixed(2);
}

function firstPage(){
	ActivePageNumber = 0;
	document.getElementsByName("page")[0].selectedIndex= 0;
	openPage(0);
}
function previousPage(){
	if(ActivePageNumber > 0){
		ActivePageNumber--;
		document.getElementsByName("page")[0].selectedIndex = ActivePageNumber;
	}
	openPage(ActivePageNumber);
}
function nextPage(){
	var lastPage = document.getElementsByName("page")[0].options[(document.getElementsByName("page")[0].options.length-2)].value;
	if(ActivePageNumber < lastPage){
		ActivePageNumber++;
		document.getElementsByName("page")[0].selectedIndex = ActivePageNumber;
	}	
	openPage(ActivePageNumber);
}
function LastPage(){
	var lastPage = document.getElementsByName("page")[0].options[(document.getElementsByName("page")[0].options.length-2)].value;
	ActivePageNumber = lastPage;	
	openPage(ActivePageNumber);
	document.getElementsByName("page")[0].selectedIndex = lastPage;
}

function zoomIn(){
	zoomLevel = Number(zoomLevel) + Number(10);
	document.getElementById("rep").style.zoom = Number(Number(zoomLevel) / Number(100)).toFixed(2);
}

function ZoomOut(){
	zoomLevel = Number(zoomLevel) - Number(10);
	document.getElementById("rep").style.zoom = Number(Number(zoomLevel) / Number(100)).toFixed(2);
}

function NoZoom(){
	zoomLevel = 100;
	document.getElementById("rep").style.zoom = Number(Number(zoomLevel) / Number(100)).toFixed(2);
}

function toggleAddDialog(visible,id) {
    if (visible == "1") {
        document.getElementById(id).style.display="block";
        document.getElementById(id).classList.add('dialog-container--visible');
    } else {
        document.getElementById(id).classList.remove('dialog-container--visible');
		window.setTimeout(function(){document.getElementById(id).style.display="none";},1000);	
    }
  }

window.onbeforeprint=prepareToPrint;
window.onafterprint=cleanupFromPrint;
