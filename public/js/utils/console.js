include('mohawk.UI.Console');

document.addLoader(function () {
	window.Console = new Mohawk.UI.Console();
    window.Console.append();
    window.Console.regKey();
    window.Console.handleErrors(false);
});