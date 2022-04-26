function toggleDiv(elementId) {
    var x = document.getElementById(elementId);
    if (x.style.display === "none") {
        x.style.display = "block";
    } else {
        x.style.display = "none";
    }
}

function onStatusChange() {
    var showDiv = document.getElementById("form_Type");
    var showDiv2 = document.getElementById("form_Status");
    if (showDiv2.value == "0" && showDiv.value == 'receipts') {
        document.getElementById("showMessage").style.display = "block";
    } else {
        document.getElementById("showMessage").style.display = "none";
    }
    onStatusChange2();
}

function disableButtons() {
    document.querySelectorAll('button.btn').forEach(elem => {
        elem.disabled = true;
    });
    document.querySelectorAll('a.btn').forEach(elem => {
        elem.setAttribute('style','pointer-events: none');
    });
}

function onStatusChange2() {
    var showDiv = document.getElementById("form_Transport");
    var showDiv2 = document.getElementById("form_Status");
    if (showDiv2.value == "0" && showDiv.value == "1") {
        document.getElementById("showMessage2").style.display = "block";
    } else {
        document.getElementById("showMessage2").style.display = "none";
    }
}

function clientPrefixChange() {
    var label = document.getElementById("prefixPreview");
    var input = document.getElementById("form_ClientPrefix");

    label.innerText = '(Example: ' + input.value + '1)';
}