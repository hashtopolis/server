function expansionCheck(elementId) {
    var isHidden = $(elementId).is(":hidden");
    if (isHidden === true) {
        window.localStorage.setItem(elementId, "yes");
    }
    else {
        window.localStorage.setItem(elementId, "no");
    }
}

function checkOnLoading(elementId) {
    isExpanded = window.localStorage.getItem(elementId);
    if (isExpanded === "yes") {
        $(elementId).collapse("show");
    }
}

$(function () {
    $('[data-toggle="tooltip"]').tooltip()
})