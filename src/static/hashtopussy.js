function expansionCheck(elementId) {
    var isExpanded = $(elementId).attr("aria-expanded");
    if(isExpanded === false){
        window.localStorage.setItem(elementId, "yes");
    }
    else{
        window.localStorage.setItem(elementId, "no");
    }
}

function checkOnLoading(elementId){
    isExpanded = window.localStorage.getItem(elementId);
    if(isExpanded === "yes"){
        $(elementId).collapse("show");
    }
}