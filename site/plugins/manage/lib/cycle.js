var xmlHttp
var timerID = null
var timerIDr = null
var image = ""
var title =""
var next = 0
var prev = 0
var time = 5
var timer = 5
var newtime
var ltime=0
var ltimer=0
var url
var si=0
var vu=999
var err=0
var forceids="0"
var gr=0
var snd

var sURL = window.document.URL.toString();

if (sURL.indexOf("?") > 0)
  {
  var arrParams = sURL.split("?");
  var arrURLParams = arrParams[1].split("&");
  var arrParamNames = new Array(arrURLParams.length);
  var arrParamValues = new Array(arrURLParams.length);

  var i = 0;
  for (i=0;i<arrURLParams.length;i++)
    {
    var sParam =  arrURLParams[i].split("=");
	arrParamNames[i] = sParam[0];
	if (sParam[1] != "")
	  arrParamValues[i] = unescape(sParam[1]);
	else
	  arrParamValues[i] = "";
    }

  for (i=0;i<arrURLParams.length;i++)
	{
	if (arrParamNames[i] == "site")
	  si=+ arrParamValues[i];

	if (arrParamNames[i] == "simple")
	  vu=+ arrParamValues[i];

	if (arrParamNames[i] == "err")
	  err=+ arrParamValues[i];

	if (arrParamNames[i] == "forceids")
	  forceids=arrParamValues[i];

    if (arrParamNames[i] == "group")
	  next=arrParamValues[i];

	if (arrParamNames[i] == "manage_sound_enable")
	  snd=arrParamValues[i];

	}

  }

	
	
function calcage(secs, num1, num2) {
  return ((Math.floor(secs/num1))%num2).toString()
}

function formattime(secs) {
  days=calcage(secs,86400,100000)
  hours=calcage(secs,3600,24)
  minutes=calcage(secs,60,60)
  seconds=calcage(secs,1,60)
  newtime="("
  if (days==1) { newtime=days+" Day " }
  if (days>1) { newtime=days+" Days " }

  if (hours==1) { newtime=newtime+hours+"h" }
  if (hours>1) { newtime=newtime+hours+"h" }

  if (minutes==1) { newtime=newtime+minutes+"m" }
  if (minutes>1) { newtime=newtime+minutes+"m" }

  if (seconds==1) { newtime=newtime+seconds+"s)" }
  if (seconds>1) { newtime=newtime+seconds+"s)" }
	
  if (newtime=="(") { newtime=newtime+"0s)" }
  return newtime
}


function startTime() {
  timerID = self.setInterval('refreshTime()', 1000)
  document.getElementById("cstop").style.display="inline"
  document.getElementById("cstart").style.display="none"
}

function stopTime() {
  self.clearInterval(timerID)
  document.getElementById("cstart").style.display="inline"
  document.getElementById("cstop").style.display="none"
}

function refreshTime() {
  ltime++
  document.getElementById("countdown").innerHTML=formattime(time)
  if (time == 0) {
    time=rtime/1000+1
    url="?site="+si + "&simple="+vu + "&err="+err + "&forceids="+forceids + "&group="+next + "&manage_sound_enable="+snd
    getfromserver()
  }
  time=time-1
}

function refr() {
  ltimer++
  document.getElementById("cd").innerHTML=formattime(timer)
  if (timer == 0) {
    timer=rtimer/1000+1
	url="?site="+si + "&simple="+vu + "&err="+err + "&forceids="+forceids + "&group="+prev + "&manage_sound_enable="+snd
	getfromserver()
  }    
  timer=timer-1
}

function getfromserver() {
  xmlHttp=GetXmlHttpObject()
  if (xmlHttp==null) {
	alert ("Get Firefox!")
    return
  }

  url="manage_ajax.php"+url

  xmlHttp.onreadystatechange=stateChanged 
  xmlHttp.open("GET",url,true)
  xmlHttp.send(null)
}


function refrstart() {
  timerIDr = self.setInterval('refr()', 1000)
  document.getElementById("rstop").style.display="inline"
  document.getElementById("rstart").style.display="none"
}

function refrstop() {
  self.clearInterval(timerIDr)
  document.getElementById("rstart").style.display="inline"
  document.getElementById("rstop").style.display="none"
}

function refr2() {
	url="?site="+si + "&simple="+vu + "&err="+err + "&forceids="+forceids + "&group="+prev + "&manage_sound_enable="+snd
	getfromserver()
}

function manage_snd() {
    if (snd=="on") {
	  snd=""
      document.getElementById("snd_off").style.display="inline"
	  document.getElementById("snd_on").style.display="none"
	} else {
	  snd="on"
      document.getElementById("snd_on").style.display="inline"
	  document.getElementById("snd_off").style.display="none"
    }
	url="?site="+si + "&simple="+vu + "&err="+err + "&forceids="+forceids + "&group="+prev + "&manage_sound_enable="+snd
//	getfromserver()
}

function stateChanged() { 
  if (xmlHttp.readyState==4 || xmlHttp.readyState=="complete") { 
    reply=xmlHttp.responseText
	reply=reply.split(":::");
	image=reply[0]

    title=reply[1]
	document.getElementById("title").innerHTML=title
		
	prev=reply[2]
	next=reply[3]
	si=reply[4]
	document.getElementById("image").innerHTML=image
  }
}

function GetXmlHttpObject() {
  var objXMLHttp=null
  if (window.XMLHttpRequest) {
	objXMLHttp=new XMLHttpRequest()
  }
	else if (window.ActiveXObject) {
		objXMLHttp=new ActiveXObject("Microsoft.XMLHTTP")
	}
  return objXMLHttp
}


function getnext() {
  time=rtime/1000
  url="?site="+si + "&simple="+vu + "&err="+err + "&forceids="+forceids + "&group="+next + "&manage_sound_enable="+snd
  getfromserver()
}

function srt_up_asc() {
  url="?site="+si + "&simple="+vu + "&err="+err + "&forceids="+forceids + "&group="+prev + "&order=uptime&asc_desc=asc" + "&manage_sound_enable="+snd
  getfromserver()
}

function srt_up_desc() {
  url="?site="+si + "&simple="+vu + "&err="+err + "&forceids="+forceids + "&group="+prev + "&order=uptime&asc_desc=desc" + "&manage_sound_enable="+snd
  getfromserver()
}

function srt_descr_asc() {
  url="?site="+si + "&simple="+vu + "&err="+err + "&forceids="+forceids + "&group="+prev + "&order=description&asc_desc=asc" + "&manage_sound_enable="+snd
  getfromserver()
}

function srt_descr_desc() {
  url="?site="+si + "&simple="+vu + "&err="+err + "&forceids="+forceids + "&group="+prev + "&order=description&asc_desc=desc" + "&manage_sound_enable="+snd
  getfromserver()
}

function srt_hst_asc() {
  url="?site="+si + "&simple="+vu + "&err="+err + "&forceids="+forceids + "&group="+prev + "&order=hostname&asc_desc=asc" + "&manage_sound_enable="+snd
  getfromserver()
}

function srt_hst_desc() {
  url="?site="+si + "&simple="+vu + "&err="+err + "&forceids="+forceids + "&group="+prev + "&order=hostname&asc_desc=desc" + "&manage_sound_enable="+snd
  getfromserver()
}

function srt_stt_asc() {
  url="?site="+si + "&simple="+vu + "&err="+err + "&forceids="+forceids + "&group="+prev + "&order=statut&asc_desc=asc" + "&manage_sound_enable="+snd
  getfromserver()
}

function srt_stt_desc() {
  url="?site="+si + "&simple="+vu + "&err="+err + "&forceids="+forceids + "&group="+prev + "&order=statut&asc_desc=desc" + "&manage_sound_enable="+snd
  getfromserver()
}
