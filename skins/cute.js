function Help(section) {
  q=window.open('index.php?mod=help&section='+section, 'Help', 'scrollbars=1,resizable=1,width=450,height=400');
}

function ShowOrHide(d1, d2) {
  if (d1 != '') DoDiv(d1);
  if (d2 != '') DoDiv(d2);
}

function DoDiv(id) {
  var item = null;
  if (document.getElementById) {
	item = document.getElementById(id);
  } else if (document.all){
	item = document.all[id];
  } else if (document.layers){
	item = document.layers[id];
  }
  if (!item) {
  }
  else if (item.style) {
	if (item.style.display == "none"){ item.style.display = ""; }
	else {item.style.display = "none"; }
  }else{ item.visibility = "show"; }
 }


function confirmDelete(url){
	var agree = confirm('Do you really want to delete this?');

	if (agree){
		document.location=url;
	}
}

function ckeck_uncheck_all(area) {

	if (area == "editnews"){frm = document.editnews;}
	else if (area == "comments"){frm = document.comments;}

	for (var i=0;i<frm.elements.length;i++) {
		var elmnt = frm.elements[i];
		if (elmnt.type=="checkbox") {
			if(frm.master_box.checked == true){ elmnt.checked=true; }
			else{ elmnt.checked=false; }
		}
	}
	if(frm.master_box.checked == true){ frm.master_box.checked = true; }
	else{ frm.master_box.checked = false; }
}

function preview(mod){
	dd = window.open('', 'prv')
	document.addnews.mod.value = 'preview';
	document.addnews.target = 'prv'
	document.addnews.submit();
	dd.focus()
	setTimeout("document.addnews.mod.value='"+mod+"';document.addnews.target='_self'", 500)
}

function focus(){
	document.forms[0].title.focus();
}

function showpreview(image,name){
	if (image != ""){
		document.images[name].src = image;
	} else {
		document.images[name].src = "skins/images/blank.gif";
	}
}

function insertext(open, close, area){

	if(area=="short"){msgfield = document.addnews.short_story}
	else if(area=="full"){msgfield = document.addnews.full_story}
	else if(area=="comment"){msgfield = document.addnews.comment}
	else if(area=="reply"){msgfield = document.addnews.reply}

	// IE support
	if (document.selection && document.selection.createRange){
		msgfield.focus();
		sel = document.selection.createRange();
		sel.text = open + sel.text + close;
		msgfield.focus();
	}

	// Moz support
	else if (msgfield.selectionStart || msgfield.selectionStart == "0"){
		var startPos = msgfield.selectionStart;
		var endPos = msgfield.selectionEnd;

		msgfield.value = msgfield.value.substring(0, startPos) + open + msgfield.value.substring(startPos, endPos) + close + msgfield.value.substring(endPos, msgfield.value.length);
		msgfield.selectionStart = msgfield.selectionEnd = endPos + open.length + close.length;
		msgfield.focus();
	}

	// Fallback support for other browsers
	else {
		msgfield.value += open + close;
		msgfield.focus();
	}

	return;
}

function process_form(the_form)
{
	var element_names = new Object()
	element_names["username"]	 = "Username"
	element_names["password"]	 = "Password"
	element_names["title"]		 = "Title"
	element_names["short_story"] = "Short Story"
	element_names["poster"]		 = "Poster"
	element_names["comment"]	 = "Comment"
	element_names["regusername"] = "Username"
	element_names["regpassword"] = "Password"

	if (document.all || document.getElementById)
	{
		for (i = 0; i < the_form.length; ++i)
		{
			var elem = the_form.elements[i]
			if ((
			   elem.name == "short_story"
			|| elem.name == "poster"
			|| elem.name == "comment"
			|| elem.name == "username"
			|| elem.name == "password"
			|| elem.name == "regusername"
			|| elem.name == "regpassword"
			)
			&& elem.value==''
			)
			{
				alert("\"" + element_names[elem.name] + "\" must contain a value.")
				elem.focus()
				return false
			}
		}
	}

	return true
}