
<script>
function showAlert(msg){
	if (msg) document.getElementById("info3txt").innerHTML = msg;
        document.getElementById('msgbg').style.display     = "block";
        document.getElementById('infopane3').style.display = "block";
	document.getElementById('warn_OK_btn').onclick = function(){
		hideAlert();
	};
}
function hideAlert(){
        document.getElementById('msgbg').style.display="none";
        document.getElementById('infopane3').style.display="none";
	//document.getElementById('id_confrmdiv').style.display="none";
}
function tmpAlert(msg, sec=1){
	showAlert(msg);
	setTimeout(hideAlert, sec*1000);
}
</script>

<!--
<div id="id_confrmdiv">
	<div> - </div>
	<button id="id_truebtn">Yes</button>
	<button id="id_falsebtn">No</button>
</div>
<br /> <button onclick="tmpAlert('TEST TEST')">submit</button>
-->


<div id="msgbg" style="
	display: none;
	z-index: 802;
	background-color:#000;
	position:fixed; 
	top: 0px; 
	left:0px; 
	bottom:0px;
	right:0px;
	text-align    :center; 
	vertical-align:middle;

	filter:   alpha(opacity=45);
	-moz-opacity  : 0.45;
	-khtml-opacity: 0.45;
	opacity       : 0.45;
	"
>
</div>

<div id="infopane3" style="
	display:none;
	a---top:100px;
	a--left:120px;
	top: 20%;
	left: 50%;
	width:400px;
	margin-left: -250px;
	vertical-align: middle;
	a--height: 400px;
	padding:20px;
	padding-top:10px;
	background-color: #ccc;
	z-index:10003;
	a---position:fixed;
	position: absolute;
	filter:alpha(opacity=95);
	-moz-opacity:0.95;
	-khtml-opacity: 0.95;
	opacity: 0.95;
	font-size: 14px;
	font-weight: normal;
	text-align:left;
	overflow-y:auto2;
	border-radius: 20px;
	border: 4px solid #435D81;
	apadding: 20px;
	opacity: 1.0;
	"
>
	<h1 align="center" style="font-weight:bold;color:#333" >  </h1>
	<center>

	<div id="info3txt">
		---
	</div>

	<br />
	<br />

	<button id="warn_OK_btn"
		style="width:100px;height:30px; display:block; margin-left:5px;border-radius:3px;border:1px solid #000;"
		onclick="document.getElementById('infopane3').style.display='none';" >OK</button>

	</center>
</div>

