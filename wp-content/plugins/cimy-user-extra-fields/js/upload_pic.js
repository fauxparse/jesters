function uploadPic(form_name, field_name, msg) {
	var browser = navigator.appName;
	var formblock = document.getElementById(form_name);
	var field = document.getElementById(field_name);

	if (browser == "Microsoft Internet Explorer")
		formblock.encoding = "multipart/form-data";
	else
		formblock.enctype = "multipart/form-data";
	
	var upload = field.value;
	upload = upload.toLowerCase();
	
	if (upload != '') {
		var ext1 = upload.substring((upload.length-4),(upload.length));
		var ext2 = upload.substring((upload.length-5),(upload.length));

		if ((ext1 != '.gif') && (ext1 != '.png') && (ext1 != '.jpg') && (ext2 != '.jpeg') && (ext2 != '.tiff')) {
			field.value = '';
			alert(msg+": gif png jpg jpeg tiff");
		}
	}
}
