include('mohawk.UI.Progress');

document.addLoader(
    function () {
    	window.Progress = new Mohawk.UI.Progress;
        Progress.append();
    }
);