function expansionCheck(elementId) {
    var isExpanded = $(elementId).attr("aria-expanded");
    if (isExpanded === "false") {
        alert("Set yes!");
        window.localStorage.setItem(elementId, "yes");
    }
    else {
        window.localStorage.setItem(elementId, "no");
    }
}

function checkOnLoading(elementId) {
    isExpanded = window.localStorage.getItem(elementId);
    alert("Status on " + elementId + ": " + isExpanded);
    if (isExpanded === "yes") {
        $(elementId).collapse("show");
    }
}