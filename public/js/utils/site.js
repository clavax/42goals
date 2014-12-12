include('mohawk.utils.Cookie');

window.htmlspecialchars = function (str) {
    str = String(str);
    str = str.replace(/&/g, '&amp;');
    str = str.replace(/</g, '&lt;');
    str = str.replace(/>/g, '&gt;');
    str = str.replace(/"/g, '&quot;');
    return str;
};

window.nl2br = function (str) {
    return str.replace(/\r\n|\n\r|\n|\r/g, '<br />');
};

function calculate_time_zone() {
    var now = new Date();
    var jan1 = new Date(now.getFullYear(), 0, 1, 0, 0, 0, 0);  // jan 1st
    var temp = jan1.toGMTString();
    var jan2 = new Date(temp.substring(0, temp.lastIndexOf(' ') - 1));
    return (jan1 - jan2) / 1000 / 3600;
}

Loader.includeTemplate('premium-info');

window.show_premium = function () {
	var div = ID('premium-info');
	if (div) {
		return;
	}
	Shadow.show();
	div = DOM.element('DIV', {
		id: 'premium-info',
		innerHTML: Template.transform(PREMIUM_INFO),
		appendTo: document.body
	});
	Dragdrop.bringToFront(div);
	return false;
}

window.hide_premium = function () {
	Shadow.hide();
	var div = ID('premium-info');
	div.purge();
	return false;
}